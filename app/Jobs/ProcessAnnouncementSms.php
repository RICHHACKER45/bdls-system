<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EmailService;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAnnouncementSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public mixed $recipients;
    public string $message;

    /**
     * Create a new job instance.
     * Supports a single User, contact string, or array/collection of recipients.
     */
    public function __construct(
        mixed $recipients,
        ?string $message = null
    ) {
        $this->recipients = $recipients;
        $this->message = $message ?? '';
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService, ?EmailService $emailService = null): void
    {
        $delay = (int) config('services.sms.queue_delay_ms', 200000);

        $recipientList = is_iterable($this->recipients) ? $this->recipients : [$this->recipients];

        foreach ($recipientList as $recipient) {
            try {
                $contactNumber = null;
                $user = null;

                if ($recipient instanceof User) {
                    $user = $recipient;
                    $contactNumber = $recipient->contact_number;
                } elseif (is_string($recipient)) {
                    $contactNumber = $recipient;
                }

                if ($contactNumber) {
                    $smsService->send($contactNumber, $this->message, true);
                }

                if ($user && $emailService && $user->email_verified_at && $user->wants_email_notification) {
                    $emailService->sendEmail(
                        $user->id,
                        $user->email,
                        'BDLS Barangay Announcement',
                        $this->message
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Failed to blast SMS/Email to recipient: '.$e->getMessage());
            }

            if ($delay > 0) {
                usleep($delay);
            }
        }
    }
}
