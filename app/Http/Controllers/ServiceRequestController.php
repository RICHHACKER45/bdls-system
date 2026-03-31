<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\Attachment; // Dinagdag natin ito
use App\Models\DocumentType;
use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validation & Security Check (Hanggang 5MB per file)
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'required|string|max:255',
            'preferred_pickup_time' => 'required|date',
            'additional_details' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120'
        ], [
            'attachments.*.max' => 'Ang bawat file ay hindi dapat lumagpas sa 5MB.',
        ]);

        // 2. Queue Number Generator (Halimbawa: O-001)
        $latestRequest = ServiceRequest::where('request_channel', 'Online')->latest('id')->first();
        $nextNumber = $latestRequest ? intval(substr($latestRequest->queue_number, 2)) + 1 : 1;
        $queueNumber = 'O-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // 3. I-save ang Request
        $serviceRequest = ServiceRequest::create([
            'user_id' => Auth::id(),
            'document_type_id' => $validated['document_type_id'],
            'request_channel' => 'Online',
            'queue_number' => $queueNumber,
            'purpose' => $validated['purpose'],
            'additional_details' => $validated['additional_details'],
            'preferred_pickup_time' => $validated['preferred_pickup_time'],
            'status' => 'Pending',
        ]);

        // 4. I-save ang Karagdagang Uploads (Kung mayroon)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // I-save sa storage/app/public/service_requirements
                $path = $file->store('service_requirements', 'public');
                
                Attachment::create([
                    'service_request_id' => $serviceRequest->id,
                    'file_path' => $path
                ]);
            }
        }

        // 5. I-trigger ang Success Modal
        return redirect()->route('resident.dashboard')->with([
            'success_title' => 'Request Submitted!',
            'success_message' => "Ang iyong dokumento ay pinoproseso na. Ang iyong Queue Number ay {$queueNumber}.",
            'active_tab' => 'dashboard'
        ]);
    }
}