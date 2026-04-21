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

    /**
     * THE LARAVEL WAY: PHP 8 Constructor Property Promotion
     * Ipapasa natin ang Residente at ang Mensahe papunta sa background worker.
     */
    public function __construct(
        public User $resident,
        public string $messageBody
    ) {}

    /**
     * Dito gagawin ang mabigat na trabaho. 
     * Awtomatikong i-i-inject ng Laravel ang SmsService at EmailService natin dito.
     */
    public function handle(SmsService $smsService, EmailService $emailService): void
    {
        try {
            // 1. Ipadala ang SMS (Pansinin ang 'true' para sa isAnnouncement rules)
            $smsService->sendSms(
                $this->resident->id,
                $this->resident->contact_number,
                $this->messageBody,
                null,
                true 
            );

            // 2. Ipadala ang Email Broadcast (Kung verified at naka-opt-in)
            if ($this->resident->email_verified_at && $this->resident->wants_email_notification) {
                $emailService->sendEmail(
                    $this->resident->id,
                    $this->resident->email,
                    'BDLS Barangay Announcement',
                    $this->messageBody
                );
            }
        } catch (\Exception $e) {
            // I-log lang kung may pumalyang isa, wag i-crash ang queue worker
            Log::error("Failed to blast SMS/Email to {$this->resident->contact_number}: " . $e->getMessage());
        }
    }
}