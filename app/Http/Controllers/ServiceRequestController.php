<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\DocumentType;
use App\Models\ServiceRequest;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // 1. TINAWAG NATIN ANG SERVICE MO
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    // 2. THE LARAVEL WAY: Dependency Injection
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Display the Resident Dashboard.
     * Inalis ang logic sa routes/web.php.
     */
    /**
     * Display the Resident Dashboard.
     */
    /**
     * Display the Resident Dashboard.
     */
    public function index()
    {
        $documents = DocumentType::where('is_active', 1)->get();
        $user = Auth::user();

        // THE FIX: Idinagdag ang withTrashed() para makuha pati ang mga na-reject at na-cancel (Soft Deleted)
        $myRequests = ServiceRequest::with('documentType')
            ->withTrashed()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 1. Mga Aktibong Pinoproseso
        $pendingRequests = $myRequests->whereIn('status', ['pending', 'processing', 'for_interview']);

        // 2. Handa Nang Kunin
        $readyRequests = $myRequests->where('status', 'released');

        // 3. Kasaysayan (Tapos na, na-reject, o na-cancel)
        $historyRequests = $myRequests->whereIn('status', ['received', 'canceled', 'rejected']);

        // THE FIX: Idinagdag ang myRequests para magamit sa buong Tracking Tab
        return view('resident.dashboard', compact('documents', 'myRequests', 'pendingRequests', 'readyRequests', 'historyRequests'));
    }

    /**
     * TASK 3: Check Verification Status for Polling
     */
    public function checkVerificationStatus()
    {
        return response()->json([
            'is_verified' => Auth::user()->is_verified,
            'rejection_count' => Auth::user()->rejection_count,
        ]);
    }

    /**
     * TASK 4: Resubmit Registration Logic
     */
    public function resubmitRegistration(Request $request)
    {
        $request->validate([
            'id_photo_path' => 'required|image|max:5120',
            'selfie_photo_path' => 'required|image|max:5120',
        ]);

        $user = Auth::user();

        // Store new photos
        $idPath = $request->file('id_photo_path')->store('verification_ids', 'public');
        $selfiePath = $request->file('selfie_photo_path')->store('verification_selfies', 'public');

        // Reset rejection data
        $user->update([
            'id_photo_path' => $idPath,
            'selfie_photo_path' => $selfiePath,
            'rejection_count' => 0,
            'rejection_reason' => null,
            'rejected_at' => null,
            'locked_until' => null,
        ]);

        return back()->with(
            'success_message',
            'Requirements resubmitted. Your account is back under review.',
        );
    }

    public function store(Request $request)
    {
        // 1. Validation Check
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'required|string|max:255',
            'preferred_pickup_time' => 'required|date',
            'additional_details' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        // 2. Queue Number Generator
        $latestRequest = ServiceRequest::where('request_channel', 'Online')->latest('id')->first();
        $nextNumber = $latestRequest ? intval(substr($latestRequest->queue_number, 2)) + 1 : 1;
        $queueNumber = 'O-'.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $user = Auth::user();

        // THE LARAVEL WAY: I-wrap ang Service Request sa Transaction
        DB::transaction(function () use ($validated, $request, $queueNumber, $user) {
            // 3. I-save ang Request
            $serviceRequest = ServiceRequest::create([
                'user_id' => $user->id,
                'document_type_id' => $validated['document_type_id'],
                'request_channel' => 'Online',
                'queue_number' => $queueNumber,
                'purpose' => $validated['purpose'],
                'additional_details' => $validated['additional_details'],
                'preferred_pickup_time' => $validated['preferred_pickup_time'],
                'status' => 'Pending',
            ]);

            // 4. I-save ang Attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('service_requirements', 'public');
                    Attachment::create([
                        'service_request_id' => $serviceRequest->id,
                        'file_path' => $path,
                    ]);
                }
            }

            // ==========================================
            // 5. I-TRIGGER ANG SMS SERVICE (Workflow Step 8)
            // ==========================================
            // (Tinanggal ko na yung unused na $documentName query para bumilis)
            $message = "Ang iyong request ay naipasa na. Queue No: {$queueNumber}. Maghintay ng text update para sa releasing o panayam.";

            $this->smsService->sendSms(
                $user->id,
                $user->contact_number,
                $message,
                $serviceRequest->id,
            );
        });

        // 6. Ibalik sa Dashboard
        return redirect()
            ->route('resident.dashboard')
            ->with([
                'success_title' => 'Request Submitted!',
                'success_message' => "Ang iyong dokumento ay pinoproseso na. Ang iyong Queue Number ay {$queueNumber}.",
                'active_tab' => 'dashboard',
            ]);
    }

    /**
     * RESIDENT CANCEL REQUEST LOGIC
     */
    public function cancelRequest(ServiceRequest $serviceRequest)
    {
        // 1. SECURITY: Siguraduhing kanya ang request at 'pending' pa lang
        if ($serviceRequest->user_id !== Auth::id() || $serviceRequest->status !== 'pending') {
            abort(403, 'Hindi mo maaaring i-cancel ang request na ito.');
        }

        // 2. THE LARAVEL WAY: Soft Delete + Update Status
        $serviceRequest->status = 'canceled';
        $serviceRequest->save();
        $serviceRequest->delete(); // Ligtas na itatago ng system

        return back()->with([
            'success_title' => 'Request Canceled',
            'success_message' => 'Matagumpay mong kinansela ang dokumento.',
            'active_tab' => 'dashboard',
        ]);
    }
}
