<?php

namespace App\Services;

use App\Models\NotificationLog;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected const EMAILJS_ENDPOINT = 'https://api.emailjs.com/api/v1.0/email/send';

    protected ?string $serviceId;

    protected ?string $templateId;

    protected ?string $publicKey;

    protected ?string $privateKey;

    /**
     * Provider outcome of the most recent dispatch, used for notification logging.
     */
    protected string $lastStatus = 'Pending';

    protected ?string $lastProviderResponse = null;

    public function __construct()
    {
        $this->serviceId = config('services.emailjs.service_id');
        $this->templateId = config('services.emailjs.template_id');
        $this->publicKey = config('services.emailjs.public_key');
        $this->privateKey = config('services.emailjs.private_key');
    }

    /**
     * Core HTTPS REST dispatcher to EmailJS.
     *
     * @param  array<string, mixed>  $templateParams
     */
    protected function dispatchEmail(array $templateParams, string $subject): bool
    {
        if (! $this->serviceId || ! $this->templateId || ! $this->publicKey) {
            Log::warning('EmailJS credentials missing in environment. Email dispatch skipped.');
            $this->lastStatus = 'Failed (No Config)';
            $this->lastProviderResponse = 'EmailJS credentials missing';

            return false;
        }

        $payload = [
            'service_id' => $this->serviceId,
            'template_id' => $this->templateId,
            'user_id' => $this->publicKey,
            'accessToken' => $this->privateKey,
            'template_params' => array_merge($templateParams, [
                'subject' => $subject,
                'portal_url' => config('app.url', 'https://bdls.up.railway.app'),
            ]),
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(self::EMAILJS_ENDPOINT, $payload);

            if ($response->successful()) {
                Log::info("EmailJS dispatched successfully to {$templateParams['to_email']}");
                $this->lastStatus = 'Sent (EmailJS)';
                $this->lastProviderResponse = 'Delivered via EmailJS REST API';

                return true;
            }

            Log::error("EmailJS API rejected payload: {$response->status()} - {$response->body()}");
            $this->lastStatus = 'Failed (API)';
            $this->lastProviderResponse = "{$response->status()} - {$response->body()}";

            return false;
        } catch (\Throwable $e) {
            Log::error("EmailJS network exception: {$e->getMessage()}");
            $this->lastStatus = 'Failed (Exception)';
            $this->lastProviderResponse = $e->getMessage();

            return false;
        }
    }

    /**
     * Fail-safe D4 notification log. Skipped when no user is attached because
     * notification_logs.user_id is a required foreign key.
     */
    protected function logNotification(
        ?int $userId,
        string $recipientEmail,
        string $subject,
        string $messageContent,
        ?int $serviceRequestId,
    ): void {
        if (! $userId) {
            return;
        }

        try {
            NotificationLog::create([
                'user_id' => $userId,
                'service_request_id' => $serviceRequestId,
                'channel' => 'Email',
                'recipient_contact' => $recipientEmail,
                'message_content' => "SUBJECT: {$subject} \n\n{$messageContent}",
                'status' => $this->lastStatus,
                'provider_response' => $this->lastProviderResponse,
            ]);
        } catch (Exception $e) {
            Log::critical(
                "DATABASE ERROR: Hindi na-save sa notification_logs ang Email para kay {$recipientEmail}. Error: ".
                    $e->getMessage(),
            );
        }
    }

    /**
     * Generic transactional email (announcements, profile verification, etc.).
     * Signature preserved for existing callers.
     */
    public function sendEmail(
        $userId,
        $recipientEmail,
        $subject,
        $messageContent,
        $serviceRequestId = null,
    ): bool {
        // Poka-Yoke: Haharangin agad kung walang email na ipinasa
        if (! $recipientEmail) {
            return false;
        }

        $sent = $this->dispatchEmail([
            'to_email' => $recipientEmail,
            'to_name' => 'Residente',
            'message' => $messageContent,
            'otp_code' => null,
            'tracking_code' => null,
            'status' => null,
        ], $subject);

        $this->logNotification($userId, $recipientEmail, $subject, $messageContent, $serviceRequestId);

        return $sent;
    }

    /**
     * Send Service Request Status Update Notification
     */
    public function sendStatusUpdate(
        string $recipientEmail,
        string $recipientName,
        string $trackingCode,
        string $status,
        ?string $customMessage = null,
        ?int $userId = null,
        ?int $serviceRequestId = null,
    ): bool {
        $subject = "Update sa Iyong Request - {$trackingCode} [BDLS]";
        $defaultMessage = "Ang iyong request na may tracking code na {$trackingCode} ay kasalukuyang nasa status na: ".strtoupper($status).'.';
        $message = $customMessage ?: $defaultMessage;

        $sent = $this->dispatchEmail([
            'to_email' => $recipientEmail,
            'to_name' => $recipientName,
            'message' => $message,
            'tracking_code' => $trackingCode,
            'status' => strtoupper($status),
            'otp_code' => null,
        ], $subject);

        $this->logNotification($userId, $recipientEmail, $subject, $message, $serviceRequestId);

        return $sent;
    }

    /**
     * Send OTP Verification Code (Signup / Password Reset)
     */
    public function sendOtp(
        string $recipientEmail,
        string $recipientName,
        string $otpCode,
        ?int $userId = null,
    ): bool {
        $subject = 'Iyong Verification Code - BDLS';
        $message = 'Gamitin ang 6-digit verification code sa ibaba upang makumpleto ang iyong transaksyon sa BDLS Portal.';

        $sent = $this->dispatchEmail([
            'to_email' => $recipientEmail,
            'to_name' => $recipientName,
            'message' => $message,
            'otp_code' => $otpCode,
            'tracking_code' => null,
            'status' => null,
        ], $subject);

        // The OTP value itself is intentionally never written to the database log.
        $this->logNotification($userId, $recipientEmail, $subject, '[OTP verification code sent]', null);

        return $sent;
    }
}
