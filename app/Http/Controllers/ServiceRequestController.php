<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\Attachment;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Auth;
use App\Services\SmsService; // 1. TINAWAG NATIN ANG SERVICE MO
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    // 2. THE LARAVEL WAY: Dependency Injection
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function store(Request $request)
    {
        // 1. Validation Check
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'required|string|max:255',
            'preferred_pickup_time' => 'required|date',
            'additional_details' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120'
        ]);

        // 2. Queue Number Generator
        $latestRequest = ServiceRequest::where('request_channel', 'Online')->latest('id')->first();
        $nextNumber = $latestRequest ? intval(substr($latestRequest->queue_number, 2)) + 1 : 1;
        $queueNumber = 'O-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

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
                        'file_path' => $path
                    ]);
                }
            }

            // ==========================================
            // 5. I-TRIGGER ANG SMS SERVICE (Workflow Step 8)
            // ==========================================
            // (Tinanggal ko na yung unused na $documentName query para bumilis)
            $message = "Ang iyong request ay naipasa na. Queue No: {$queueNumber}. Maghintay ng text update para sa releasing o panayam.";
            
            $this->smsService->sendSms($user->id, $user->contact_number, $message, $serviceRequest->id);

        });

        // 6. Ibalik sa Dashboard
        return redirect()->route('resident.dashboard')->with([
            'success_title' => 'Request Submitted!',
            'success_message' => "Ang iyong dokumento ay pinoproseso na. Ang iyong Queue Number ay {$queueNumber}.",
            'active_tab' => 'dashboard'
        ]);
    }
}