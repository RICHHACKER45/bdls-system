<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SmsService;
use App\Models\ServiceRequest;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. The Laravel Way: Base Query (Filtered at DB level)
        $query = User::where('role', 'resident');

        // 2. Search Logic
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
            $query->latest();
        }

        // 4. THE LARAVEL WAY: DB-Level Filtering (Iwas Memory Exhaustion)
        $pendingAccounts = (clone $query)->pending()->get();
        $approvedAccounts = (clone $query)->approved()->get();
        $rejectedAccounts = (clone $query)->rejected()->get();

        // 5. QUEUE LOGIC: Separate Active Queue from Received History
        $queueBase = ServiceRequest::with(['user', 'documentType'])->orderBy('created_at', 'asc');

        $activeQueue = (clone $queueBase)
            ->whereIn('status', ['pending', 'processing', 'for_interview', 'released'])
            ->get();

        $receivedQueue = (clone $queueBase)->where('status', 'received')->get();

        return view(
            'admin.dashboard',
            compact(
                'pendingAccounts',
                'approvedAccounts',
                'rejectedAccounts',
                'activeQueue',
                'receivedQueue',
            ),
        );
    }

    public function checkPendingCount()
    {
        $count = User::where('role', 'resident')->where('is_verified', false)->count();
        return response()->json(['count' => $count]);
    }

    public function approveAccount(User $user, SmsService $smsService)
    {
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
            ->with('active_tab', 'pending')
            ->with('success_title', 'Account Approved')
            ->with(
                'success_message',
                'Matagumpay na na-verify ang account ni ' . $user->first_name,
            );
    }

    public function rejectAccount(Request $request, User $user, SmsService $smsService)
    {
        $request->validate(['rejection_reason' => 'required|string|max:60']);

        $user->rejection_count += 1;
        $user->rejection_reason = $request->rejection_reason;
        $user->rejected_at = now();

        if ($user->rejection_count >= 5) {
            $user->locked_until = now()->addHours(24);
            $message =
                'Brgy Dona Lucia: Naka-lock ang iyong account ng 24 oras dahil sa 5 failed attempts.';
        } else {
            $message =
                "Brgy Dona Lucia: Registration rejected. Rason: {$request->rejection_reason}. May " .
                (5 - $user->rejection_count) .
                ' attempts ka pa.';
        }

        $user->save();
        $smsService->sendSms($user->id, $user->contact_number, $message);

        return back()
            ->with('active_tab', 'pending')
            ->with('success_title', 'Account Rejected')
            ->with('success_message', 'Na-reject ang registration ni ' . $user->first_name);
    }

    public function updateRequestStatus(
        Request $request,
        ServiceRequest $serviceRequest,
        SmsService $smsService,
    ) {
        $request->validate(['status' => 'required|string']);
        $newStatus = $request->status;
        $serviceRequest->status = $newStatus;
        $message = '';

        if ($newStatus === 'processing') {
            $message = "Brgy Dona Lucia: Ang iyong request ({$serviceRequest->queue_number}) ay kasalukuyang pino-proseso.";
        } elseif ($newStatus === 'for_interview') {
            $message = "Brgy Dona Lucia: Ang request ({$serviceRequest->queue_number}) ay nangangailangan ng panayam. Pumunta sa hall.";
        } elseif ($newStatus === 'released') {
            $serviceRequest->released_at = now();
            $serviceRequest->released_by_admin_id = Auth::id();
            $message = "Brgy Dona Lucia: Ang dokumento para sa ({$serviceRequest->queue_number}) ay ready for release na. Maaari nang kunin.";
        }

        $serviceRequest->save();

        if ($message !== '' && $newStatus !== 'received') {
            $smsService->sendSms(
                $serviceRequest->user_id,
                $serviceRequest->user->contact_number,
                $message,
            );
        }

        return back()
            ->with('active_tab', 'queue')
            ->with('success_title', 'Status Updated')
            ->with('success_message', 'Queue updated at na-notify na ang residente!');
    }

    public function checkQueueCount()
    {
        $count = ServiceRequest::whereIn('status', [
            'pending',
            'for_interview',
            'processing',
            'released',
        ])->count();

        return response()->json(['count' => $count]);
    }
}
