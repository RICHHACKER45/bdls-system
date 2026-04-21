<?php

namespace App\Services;

use App\Mail\BdlsNotificationMail;
use App\Models\NotificationLog;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * THE LARAVEL WAY: Centralized Email Logic with Database Logging
     */
    public function sendEmail(
        $userId,
        $recipientEmail,
        $subject,
        $messageContent,
        $serviceRequestId = null,
    ) {
        // 1. Poka-Yoke: Haharangin agad kung walang email na ipinasa
        if (! $recipientEmail) {
            return false;
        }

        $status = 'Pending';
        $providerResponse = null;

        // ==========================================
        // 2. EMAIL SENDING EXECUTION (DYNAMIC MOCK/LIVE)
        // ==========================================
        try {
            // THE LARAVEL WAY: Babasahin ng system ang iyong .env kung 'log' o 'smtp'
            $driver = env('MAIL_MAILER', 'log');

            if ($driver === 'log') {
                // --------------------------------------
                // A. THE MOCK MODE (For Local Testing)
                // --------------------------------------
                Log::info('====================================');
                Log::info("EMAIL SEND INITIATED [To: {$recipientEmail}]");
                Log::info("Subject: {$subject}");
                Log::info("Message: {$messageContent}");
                Log::info('====================================');

                $status = 'Sent (Mock)';
                $providerResponse = 'Simulated via Laravel Log';

            } else {
                // --------------------------------------
                // B. THE LIVE MODE (For Defense/Production)
                // --------------------------------------
                Mail::to($recipientEmail)->send(
                    new BdlsNotificationMail($subject, $messageContent)
                );

                $status = 'Sent (Live)';
                $providerResponse = 'Delivered via SMTP';
                Log::info("EMAIL LIVE SUCCESS: Naipadala kay {$recipientEmail}. Subject: {$subject}");
            }

        } catch (Exception $e) {
            $status = 'Failed (Exception)';
            $providerResponse = $e->getMessage();
            Log::error(
                "EMAIL CRITICAL EXCEPTION: Failed to send to {$recipientEmail}. Error: ".$e->getMessage(),
            );
        }

        // ==========================================
        // 3. FAIL-SAFE DATABASE LOGGING (D4 Notification Logs)
        // ==========================================
        try {
            NotificationLog::create([
                'user_id' => $userId,
                'service_request_id' => $serviceRequestId,
                'channel' => 'Email', // Explicitly tagged as Email
                'recipient_contact' => $recipientEmail,
                'message_content' => "SUBJECT: {$subject} \n\n{$messageContent}",
                'status' => $status,
                'provider_response' => $providerResponse,
            ]);
        } catch (Exception $e) {
            // Ligtas ang system kahit mag-crash ang database
            Log::critical(
                "DATABASE ERROR: Hindi na-save sa notification_logs ang Email para kay {$recipientEmail}. Error: ".
                    $e->getMessage(),
            );
        }

        return $status === 'Sent (Mock)';
    }
}
