<?php

namespace App\Jobs;

use App\Models\ServiceRequest;
use App\Services\EmailService;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRequestUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public string $message
    ) {}

    public function handle(SmsService $smsService, EmailService $emailService): void
    {
        try {
            $user = $this->serviceRequest->user;
            
            // 1. Ipadala ang SMS (isAnnouncement = false, isOtp = false)
            $smsService->sendSms(
                $user->id, 
                $user->contact_number, 
                $this->message, 
                $this->serviceRequest->id, 
                false, 
                false
            );

            // 2. Ipadala ang Email (Kung naka-verify at opt-in)
            if ($user->email_verified_at && $user->wants_email_notification) {
                $emailService->sendEmail(
                    $user->id, 
                    $user->email, 
                    'BDLS Request Update', 
                    $this->message, 
                    $this->serviceRequest->id
                );
            }
        } catch (\Exception $e) {
            Log::error("Failed background notification for {$this->serviceRequest->queue_number}: " . $e->getMessage());
        }
    }
}
