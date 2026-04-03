<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // DAGDAG: Para mabasa ang Auth
use App\Services\SmsService;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. SECURITY LENS: Ito yung inilipat natin mula sa web.php
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized.');
        }

        // 2. THE LARAVEL WAY: Kunin ang mga pending registrations
        $pendingAccounts = User::where('role', 'resident')
                               ->where('is_verified', false)
                               ->latest()
                               ->get();

        // 3. I-return ang view kasama ang data
        // (Gamitin ang 'Admin.dashboard' kung capital A ang folder mo sa resources/views)
        return view('admin.dashboard', compact('pendingAccounts'));
    }

     // BAGONG FUNCTION PARA SA AJAX POLLING
    public function checkPendingCount()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $count = User::where('role', 'resident')
                     ->where('is_verified', false)
                     ->count();

        return response()->json(['count' => $count]);
    }

    // ==========================================
    // APPROVE ACCOUNT LOGIC
    // ==========================================
    public function approveAccount(User $user, SmsService $smsService)
    {
        // 1. Security Lens
        if (Auth::user()->role !== 'admin') abort(403);

        // 2. Database Update
        $user->update(['is_verified' => true]);

        // 3. SMS Notification (Idinagdag natin ang $user->id bilang first argument)
        $message = "Brgy Dona Lucia: Ang iyong account ay approved na. Maaari ka nang mag-request ng dokumento.";
        $smsService->sendSms($user->id, $user->contact_number, $message);

        // 4. Return with Success Message
        return back()->with('success_title', 'Account Approved')->with('success_message', 'Matagumpay na na-verify ang account ni ' . $user->first_name);
    }

    // ==========================================
    // REJECT & DELETE ACCOUNT LOGIC
    // ==========================================
    public function rejectAccount(User $user, SmsService $smsService)
    {
        // 1. Security Lens
        if (Auth::user()->role !== 'admin') abort(403);

        // 2. Kunin ang details bago burahin
        $userId = $user->id;
        $contactNumber = $user->contact_number;
        $name = $user->first_name;

        // 3. THE LARAVEL WAY: I-send muna ang SMS BAGO burahin ang user
        // (Para hindi magka-Foreign Key Error sa notification_logs table mo)
        $message = "Brgy Dona Lucia: Ang iyong registration ay na-reject. Mangyaring subukan muli at siguraduhing malinaw ang ID.";
        $smsService->sendSms($userId, $contactNumber, $message);

        // 4. Saka i-delete ang record para makapag-signup ulit
        $user->delete();

        // 5. Return with Success Message
        return back()->with('success_title', 'Account Rejected')->with('success_message', 'Na-reject at nabura ang registration ni ' . $name);
    }
    
}