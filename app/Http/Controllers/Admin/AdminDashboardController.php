<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SmsService;
use App\Models\ServiceRequest;
use App\Models\DocumentType;

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

        $documents = DocumentType::where('is_active', 1)->get();

        return view(
            'admin.admin-panel',
            compact(
                'pendingAccounts',
                'approvedAccounts',
                'rejectedAccounts',
                'activeQueue',
                'receivedQueue',
                'documents'
            ),
        );
    }

    public function checkPendingCount()
    {
        // TASK 1: Use pending scope for accurate count
        return response()->json(['count' => User::pending()->count()]);
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
            'Ang iyong account ay approved na. Maaari ka nang mag-request ng dokumento.';
        $smsService->sendSms($user->id, $user->contact_number, $message);

        return back()->with('active_tab', 'pending')->with('success_message', 'Account Approved');
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
                'Naka-lock ang iyong account ng 24 oras dahil sa 5 failed attempts.';
        } else {
            $message =
                "Registration rejected. Rason: {$request->rejection_reason}. May " .
                (5 - $user->rejection_count) .
                ' attempts ka pa.';
        }

        $user->save();
        $smsService->sendSms($user->id, $user->contact_number, $message);

        return back()->with('active_tab', 'pending')->with('success_message', 'Account Rejected');
    }

    /**
     * TASK 1: Admin Delete Functionality
     */
    public function destroyAccount(User $user)
    {
        $user->delete();
        return back()
            ->with('active_tab', 'pending')
            ->with('success_message', 'Resident account deleted permanently.');
    }

    public function updateRequestStatus(
        Request $request,
        ServiceRequest $serviceRequest,
        SmsService $smsService,
    ) {
        $request->validate(['status' => 'required|string']);
        
        // THE LARAVEL WAY FIX: I-force sa lowercase bago i-save sa database
        $newStatus = strtolower($request->status);
        
        $serviceRequest->status = $newStatus;
        $message = '';

        if ($newStatus === 'processing') {
            $message = "Brgy Dona Lucia: Ang iyong request ({$serviceRequest->queue_number}) ay kasalukuyang pino-proseso.";
        } elseif ($newStatus === 'for_interview') {
            // Ito ay magte-text lang kapag naging "For Interview" ang papel
            $message = "Brgy Dona Lucia: Ang request ({$serviceRequest->queue_number}) ay nangangailangan ng panayam. Pumunta sa hall.";
        } elseif ($newStatus === 'released') {
            $serviceRequest->released_at = now();
            $serviceRequest->released_by_admin_id = Auth::id();
            $message = "Brgy Dona Lucia: Ang dokumento para sa ({$serviceRequest->queue_number}) ay ready for release na. Maaari nang kunin.";
        }

        $serviceRequest->save();

        // I-skip ang pag-text kung marked as received na para hindi masayang ang SMS API Budget
        if ($message !== '' && $newStatus !== 'received') {
            $smsService->sendSms(
                $serviceRequest->user_id,
                $serviceRequest->user->contact_number,
                $message,
            );
        }

        return back()->with('active_tab', 'queue')->with('success_message', 'Status Updated');
    }

    public function checkQueueCount()
    {
        return response()->json([
            'count' => ServiceRequest::whereIn('status', [
                'pending',
                'for_interview',
                'processing',
                'released',
            ])->count(),
        ]);
    }

    /**
     * PHASE 1: Walk-In Search-First Logic
     */
    public function searchWalkinAccount(Request $request)
    {
        $request->validate([
            'contact_number' => 'required|string|max:20',
        ]);

        // Hanapin ang user gamit ang unique contact number
        $walkinUser = User::where('contact_number', $request->contact_number)->first();

        // Ibalik sa Walk-in Tab kasama ang resulta
        return back()->with([
            'active_tab' => 'walkin',
            'walkin_searched' => true,
            'walkin_search_number' => $request->contact_number,
            'walkin_user' => $walkinUser,
        ]);
    }

    /**
     * PHASE 2: Walk-in Shadow Profile & Request Creation
     */
    public function storeWalkinRequest(Request $request, SmsService $smsService)
    {
        // 1. Validation (Pinagsamang Resident Data at Request Data)
        $request->validate([
            'contact_number' => 'required|string|max:20',
            'is_new_user' => 'required|boolean',
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'required|string|max:255',
            
            // Required lang kung gagawa ng Shadow Profile:
            'first_name' => 'required_if:is_new_user,1|string|max:255',
            'last_name' => 'required_if:is_new_user,1|string|max:255',
            'sex' => 'required_if:is_new_user,1|string|in:Male,Female',
            'date_of_birth' => 'required_if:is_new_user,1|date',
            'house_number' => 'required_if:is_new_user,1|string|max:255',
            'purok_street' => 'required_if:is_new_user,1|string|max:255',
        ]);

        // 2. I-wrap sa Transaction para ligtas ang pera sa SMS
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $smsService) {
            
            // A. Hanapin o Gumawa ng Shadow Profile
            if ($request->is_new_user) {
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'sex' => $request->sex,
                    'date_of_birth' => $request->date_of_birth,
                    'house_number' => $request->house_number,
                    'purok_street' => $request->purok_street,
                    'contact_number' => $request->contact_number,
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(12)),
                    'role' => 'resident',
                    'is_verified' => true, // Walk-ins are physically verified by Admin
                    'terms_accepted_at' => now(),
                ]);
            } else {
                $user = User::where('contact_number', $request->contact_number)->firstOrFail();
            }

            // B. Gumawa ng W-XXX Queue Number (W para sa Walk-in)
            $latestRequest = ServiceRequest::where('request_channel', 'Walk-in')->latest('id')->first();
            $nextNumber = $latestRequest ? intval(substr($latestRequest->queue_number, 2)) + 1 : 1;
            $queueNumber = 'W-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // C. I-save ang Request sa Database
            $serviceRequest = ServiceRequest::create([
                'user_id' => $user->id,
                'document_type_id' => $request->document_type_id,
                'request_channel' => 'Walk-in',
                'queue_number' => $queueNumber,
                'purpose' => $request->purpose,
                'preferred_pickup_time' => now()->addDay(), // Default pick-up time
                'status' => 'pending',
            ]);

            // D. Magpadala ng SMS Ticket
            $message = "Brgy Dona Lucia: Ang iyong walk-in request ay naipasa na. Queue No: {$queueNumber}. Maghintay tawagin o maka-receive ng text update.";
            $smsService->sendSms($user->id, $user->contact_number, $message, $serviceRequest->id);
        });

        // 3. I-redirect pabalik sa Queue Tab para makita agad ni Admin ang bagong pila
        return redirect()->route('admin.dashboard')->with('active_tab', 'queue')->with('success_message', 'Walk-in Request at Queue Number ay matagumpay na nagawa!');
    }

}
