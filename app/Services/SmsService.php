<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsService
{
    protected string $driver;
    protected int $maxLength;
    protected int $curfewStart;
    protected int $curfewEnd;
    protected array $smartPrefixes;

    protected ?string $pinasApiKey;
    protected string $pinasBaseUrl;
    protected int $pinasTimeout;

    protected ?string $fortmedApiKey;
    protected ?string $fortmedUrl;
    protected string $fortmedSender;
    protected int $fortmedTimeout;

    public function __construct()
    {
        $this->driver = config('services.sms.driver', 'hybrid');
        $this->maxLength = config('services.sms.max_length', 160);
        $this->curfewStart = config('services.sms.curfew_start', 21);
        $this->curfewEnd = config('services.sms.curfew_end', 7);

        $rawPrefixes = config('services.sms.smart_prefixes', '');
        $this->smartPrefixes = array_filter(array_map('trim', explode(',', $rawPrefixes)));

        $this->pinasApiKey = config('services.pinassms.api_key');
        $this->pinasBaseUrl = config('services.pinassms.base_url', 'https://pinassms.com/api');
        $this->pinasTimeout = config('services.pinassms.timeout', 15);

        $this->fortmedApiKey = config('services.fortmed.api_key');
        $this->fortmedUrl = config('services.fortmed.api_url');
        $this->fortmedSender = config('services.fortmed.sender_name', 'BDLS');
        $this->fortmedTimeout = config('services.fortmed.timeout', 15);
    }

    /**
     * Dispatch SMS using configured driver, telco prefix routing, and failover
     */
    public function send(string $recipient, string $message, bool $isAnnouncement = false): bool
    {
        // 1. Mock Log Driver (Local Testing)
        if ($this->driver === 'log') {
            Log::info("SMS [LOG DRIVER] to {$recipient}: {$message}");
            return true;
        }

        // 2. NTC Night Curfew Check for Announcements (9:00 PM - 7:00 AM)
        if ($isAnnouncement && $this->isWithinCurfewHours()) {
            Log::warning("NTC Compliance: SMS announcement to {$recipient} blocked during curfew hours ({$this->curfewStart}:00 - {$this->curfewEnd}:00).");
            return false;
        }

        // 3. NTC Compliance: URL Link Stripping
        $sanitizedMessage = $this->stripLinks($message);

        // 4. GSM Character Limit Safeguard
        $finalMessage = Str::limit(trim($sanitizedMessage), $this->maxLength, '');

        // 5. Explicit Driver Overrides
        if ($this->driver === 'pinassms') {
            return $this->sendViaPinasSms($recipient, $finalMessage);
        }

        if ($this->driver === 'fortmed') {
            return $this->sendViaFortmed($recipient, $finalMessage);
        }

        // 6. HYBRID MODE: Smart Routing & Failover
        if ($this->isSmartNetwork($recipient)) {
            Log::info("Telco Routing: {$recipient} identified as SMART/TNT. Attempting Fortmed API...");
            $sent = $this->sendViaFortmed($recipient, $finalMessage);

            if ($sent) {
                return true;
            }

            Log::warning("Fortmed failed for Smart recipient {$recipient}. Triggering PinasSMS fallback...");
            return $this->sendViaPinasSms($recipient, $finalMessage);
        }

        // Default Route for Globe, TM, DITO
        Log::info("Telco Routing: {$recipient} identified as GLOBE/TM/DITO. Directing to PinasSMS...");
        return $this->sendViaPinasSms($recipient, $finalMessage);
    }

    /**
     * Backward-compatible alias
     */
    public function sendNotification(string $recipient, string $message): bool
    {
        return $this->send($recipient, $message, false);
    }

    /**
     * Backward-compatible alias for existing callers throughout the application.
     */
    public function sendSms(
        $userId,
        $recipientContact,
        $messageContent,
        $serviceRequestId = null,
        $isAnnouncement = false,
        $isOtp = false
    ): bool {
        // Block unverified numbers from receiving non-OTP notifications
        if ($userId && ! $isOtp) {
            $user = User::find($userId);
            if ($user && is_null($user->contact_verified_at)) {
                Log::warning("SMS BLOCKED: Bawal padalhan ng non-OTP message ang unverified number ({$recipientContact}). Tipid API Credits.");
                return false;
            }
        }

        $sent = $this->send((string) $recipientContact, (string) $messageContent, (bool) $isAnnouncement);

        // Fail-safe database logging in notification_logs
        try {
            NotificationLog::create([
                'user_id' => $userId,
                'service_request_id' => $serviceRequestId,
                'channel' => 'SMS',
                'recipient_contact' => $recipientContact,
                'message_content' => $messageContent,
                'status' => $sent ? 'Sent' : 'Failed',
                'provider_response' => $sent ? "Delivered via {$this->driver}" : 'Failed or blocked',
            ]);
        } catch (Exception $e) {
            Log::critical("DATABASE ERROR: Hindi na-save sa notification_logs ang SMS para kay {$recipientContact}: {$e->getMessage()}");
        }

        // Throw exception on failed OTP so caller DB transactions can roll back safely
        if ($isOtp && ! $sent && $this->driver !== 'log') {
            throw new Exception('Bigo ang SMS Gateway na ipadala ang OTP. Na-rollback ang rehistrasyon.');
        }

        return $sent;
    }

    /**
     * PinasSMS Gateway Integration
     */
    protected function sendViaPinasSms(string $recipient, string $message): bool
    {
        if (! $this->pinasApiKey) {
            Log::error('PinasSMS API key is not configured.');
            return false;
        }

        $formatted = $this->formatForPinas($recipient);

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->pinasApiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->pinasTimeout)
                ->post("{$this->pinasBaseUrl}/sms/send", [
                    'recipient' => $formatted,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info("PinasSMS successfully delivered to {$formatted}");
                return true;
            }

            Log::error("PinasSMS rejected dispatch: HTTP {$response->status()} - {$response->body()}");
            return false;
        } catch (\Throwable $e) {
            Log::error("PinasSMS network exception: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Fortmed (FMCSMS) Gateway Integration
     */
    protected function sendViaFortmed(string $recipient, string $message): bool
    {
        if (! $this->fortmedApiKey || ! $this->fortmedUrl) {
            Log::error('Fortmed API credentials not configured.');
            return false;
        }

        $formatted = $this->formatForFortmed($recipient);

        try {
            $response = Http::asForm()
                ->timeout($this->fortmedTimeout)
                ->post($this->fortmedUrl, [
                    'api_key' => $this->fortmedApiKey,
                    'number' => $formatted,
                    'message' => $message,
                    'sender' => $this->fortmedSender,
                ]);

            if ($response->successful()) {
                Log::info("Fortmed successfully delivered to {$formatted}");
                return true;
            }

            Log::error("Fortmed rejected dispatch: HTTP {$response->status()} - {$response->body()}");
            return false;
        } catch (\Throwable $e) {
            Log::error("Fortmed network exception: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Check if number prefix matches Smart/TNT/Sun list
     */
    protected function isSmartNetwork(string $number): bool
    {
        $clean = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($clean, '639')) {
            $prefix = '09' . substr($clean, 3, 2);
        } elseif (str_starts_with($clean, '09')) {
            $prefix = substr($clean, 0, 4);
        } elseif (str_starts_with($clean, '9')) {
            $prefix = '09' . substr($clean, 1, 2);
        } else {
            return false;
        }

        return in_array($prefix, $this->smartPrefixes, true);
    }

    /**
     * PinasSMS requires 639XXXXXXXXX (strictly no leading '+')
     */
    protected function formatForPinas(string $number): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($cleaned, '09')) {
            return '63' . substr($cleaned, 1);
        }

        return str_starts_with($cleaned, '639') ? $cleaned : '63' . $cleaned;
    }

    /**
     * Fortmed standard local format 09XXXXXXXXX
     */
    protected function formatForFortmed(string $number): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($cleaned, '639')) {
            return '09' . substr($cleaned, 3);
        }

        return str_starts_with($cleaned, '09') ? $cleaned : '09' . $cleaned;
    }

    /**
     * NTC Compliance Link Stripping & Unicode Clean
     */
    protected function stripLinks(string $text): string
    {
        return preg_replace('/\b(?:https?:\/\/|www\.)\S+\b/i', '[link removed]', $text);
    }

    /**
     * NTC Night Curfew Logic (21:00 to 07:00 Asia/Manila)
     */
    protected function isWithinCurfewHours(): bool
    {
        $hour = Carbon::now('Asia/Manila')->hour;
        return $hour >= $this->curfewStart || $hour < $this->curfewEnd;
    }

    /**
     * Balance Check Endpoint for PinasSMS
     */
    public function checkBalance(): ?array
    {
        if (! $this->pinasApiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->pinasApiKey,
            ])
                ->timeout(10)
                ->get("{$this->pinasBaseUrl}/balance");

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error("PinasSMS balance check exception: {$e->getMessage()}");
            return null;
        }
    }
}
