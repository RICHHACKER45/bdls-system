<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // DAGDAG: Para mabasa ang Auth
use App\Services\SmsService;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized.');
        }

        // 1. The Laravel Way: Base Query
        $query = User::where('role', 'resident');

        // 2. Search Logic (Hahanapin sa First name, Last name, o Contact)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        // 3. Sorting Logic
        if ($request->get('sort') == 'oldest') {
            $query->oldest();
        } else {
            $query->latest(); // Default: Pinakabago
        }

        $allAccounts = $query->get();

        // 4. Sub-Tab Grouping
        $pendingAccounts = $allAccounts
            ->where('is_verified', false)
            ->where('rejection_count', '<', 5);
        $approvedAccounts = $allAccounts->where('is_verified', true);
        $lockedAccounts = $allAccounts
            ->where('is_verified', false)
            ->where('rejection_count', '>=', 5);

        return view(
            'admin.dashboard',
            compact('pendingAccounts', 'approvedAccounts', 'lockedAccounts'),
        );
    }

    // BAGONG FUNCTION PARA SA AJAX POLLING
    public function checkPendingCount()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $count = User::where('role', 'resident')->where('is_verified', false)->count();

        return response()->json(['count' => $count]);
    }

    // ==========================================
    // APPROVE ACCOUNT LOGIC
    // ==========================================
    public function approveAccount(User $user, SmsService $smsService)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // Kapag na-approve, i-reset ang lahat ng rejection records
        $user->update([
            'is_verified' => true,
            'rejection_count' => 0,
            'rejection_reason' => null,
            'rejected_at' => null,
            'locked_until' => null,
        ]);

        $message =
            'Brgy Dona Lucia: Ang iyong account ay approved na. Maaari ka nang mag-request ng dokumento.';
        $smsService->sendSms($user->id, $user->contact_number, $message);

        return back()
            ->with('success_title', 'Account Approved')
            ->with(
                'success_message',
                'Matagumpay na na-verify ang account ni ' . $user->first_name,
            );
    }

    // ==========================================
    // REJECT ACCOUNT LOGIC (HINDI NA BUBURAHIN)
    // ==========================================
    public function rejectAccount(Request $request, User $user, SmsService $smsService)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // 1. Validation para sa Rason ni Admin (Nilimitahan sa 60 chars para hindi lumampas sa 160 SMS limit)
        $request->validate([
            'rejection_reason' => 'required|string|max:60',
        ]);

        // 2. Dagdagan ang count at i-set ang rason
        $user->rejection_count += 1;
        $user->rejection_reason = $request->rejection_reason;
        $user->rejected_at = now();

        $remaining = 5 - $user->rejection_count;

        // 3. 5-Attempts & 24-Hour Lock Logic
        if ($user->rejection_count >= 5) {
            $user->locked_until = now()->addHours(24);
            $message =
                'Brgy Dona Lucia: Naka-lock ang iyong account ng 24 oras dahil sa 5 failed attempts.';
        } else {
            $message = "Brgy Dona Lucia: Registration rejected. Rason: {$request->rejection_reason}. May {$remaining} attempts ka pa.";
        }

        $user->save(); // The Laravel Way: I-save ang pagbabago

        // 4. I-send ang SMS
        $smsService->sendSms($user->id, $user->contact_number, $message);

        return back()
            ->with('success_title', 'Account Rejected')
            ->with('success_message', 'Na-reject ang registration ni ' . $user->first_name);
    }
}
