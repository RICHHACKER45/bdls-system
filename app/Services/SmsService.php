<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\NotificationLog;
use Exception; // Idinagdag para makapagbato tayo ng errors kapag may lumabag sa rules
use App\Models\User;

class SmsService
{
    /**
     * Ipadala ang SMS nang may mahigpit na NTC Compliance at Credit Optimization.
     * Nagdagdag tayo ng $isAnnouncement parameter.
     */
    public function sendSms(
        $userId,
        $recipientContact,
        $messageContent,
        $serviceRequestId = null,
        $isAnnouncement = false,
        $isOtp = false,
    ) {
        // ==========================================
        // 0. SECURITY: UNVERIFIED NUMBER BLOCKER (Process 1.0)
        // ==========================================
        $user = User::find($userId);

        // Kung walang contact_verified_at at HINDI ito OTP message, harangin!
        if ($user && is_null($user->contact_verified_at) && !$isOtp) {
            Log::warning(
                "SMS BLOCKED: Bawal padalhan ng non-OTP message ang unverified number ({$recipientContact}). Tipid API Credits.",
            );
            return false; // I-abort agad ang proseso
        }

        // ==========================================
        // 1. LINK BLOCKER (NTC Compliance)
        // ==========================================
        // Bawal ang links para hindi ma-ban ang shared API mo.
        if (preg_match('/(http|https|www\.)/i', $messageContent)) {
            Log::warning("SMS Blocked: URL detected in message for {$recipientContact}.");
            throw new Exception(
                'Bawal mag-send ng links o website URLs ayon sa NTC Anti-Spam rules.',
            );
        }

        // ==========================================
        // 2. NIGHT CURFEW (9:00 PM to 7:00 AM)
        // ==========================================
        // Haharangin natin ang system kapag gabi na at announcement ito.
        if ($isAnnouncement) {
            $currentHour = (int) now()->format('H');
            if ($currentHour >= 21 || $currentHour < 7) {
                throw new Exception(
                    'Bawal mag-send ng announcements mula 9:00 PM hanggang 7:00 AM.',
                );
            }
        }

        // ==========================================
        // 3. UNICODE SANITIZER (Iwas 70-character Trap)
        // ==========================================

        // A. Palitan ang smart quotes ng normal quotes
        $messageContent = str_replace(['“', '”', '‘', '’'], ['"', '"', "'", "'"], $messageContent);

        // B. Burahin ang lahat ng Emojis gamit ang Regex para manatili sa 160 ang limit
        $messageContent = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $messageContent);
        $messageContent = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $messageContent);
        $messageContent = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $messageContent);
        $messageContent = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $messageContent);
        $messageContent = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $messageContent);

        // ==========================================
        // 4. IDENTITY HEADER (Mandatory NTC Prefix)
        // ==========================================
        $prefix = env('SMS_PREFIX');
        $fullMessage = $prefix . trim($messageContent);

        $driver = env('SMS_DRIVER', 'log');

        // ==========================================
        // 5. ROUTING: Transactional vs Announcement
        // ==========================================
        if (!$isAnnouncement) {
            // TRANSACTIONAL (OTP / Queue Updates) -> STRICTLY 160 CHARACTERS
            if (Str::length($fullMessage) > 160) {
                $fullMessage = Str::limit($fullMessage, 160, ''); // Puputulin pilit para tipid credit
                Log::warning(
                    "SMS Truncated to 160 chars for {$recipientContact} para iwas double-charge.",
                );
            }
            $this->executeSend(
                $userId,
                $recipientContact,
                $fullMessage,
                $serviceRequestId,
                $driver,
            );
        } else {
            // ANNOUNCEMENTS (Concatenation Logic - Multi-part SMS)
            if (Str::length($fullMessage) <= 160) {
                $this->executeSend(
                    $userId,
                    $recipientContact,
                    $fullMessage,
                    $serviceRequestId,
                    $driver,
                );
            } else {
                // Hahatiin natin sa 148 characters + 5 chars para sa "(1/2) " = 153 max payload per credit
                $chunks = str_split($fullMessage, 148);
                $totalChunks = count($chunks);

                foreach ($chunks as $index => $chunk) {
                    $partNum = $index + 1;
                    $segmentMessage = $chunk . " ({$partNum}/{$totalChunks})";
                    $this->executeSend(
                        $userId,
                        $recipientContact,
                        $segmentMessage,
                        $serviceRequestId,
                        $driver,
                    );
                }
            }
        }

        return true;
    }

    /**
     * Private function na tagapag-padala (The Laravel Way: DRY Principle)
     */
    private function executeSend(
        $userId,
        $recipientContact,
        $messageContent,
        $serviceRequestId,
        $driver,
    ) {
        $status = 'Pending';
        $providerResponse = null;

        // ==========================================
        // UNIVERSAL LOGGING: Isusulat ito sa laravel.log
        // bago pa man tingnan kung 'log' o 'api' ang gamit mo!
        // ==========================================
        Log::info('====================================');
        Log::info("SMS SEND INITIATED [Driver: {$driver}] [To: {$recipientContact}]");
        Log::info('Length: ' . Str::length($messageContent) . ' chars');
        Log::info("Message: {$messageContent}");
        Log::info('====================================');

        if ($driver === 'log') {
            $status = 'Sent (Mock)';
            $providerResponse = 'Simulated via Laravel Log';
        } elseif ($driver === 'api') {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-API-Key' => env('SMS_API_KEY'),
                    ])
                    ->post(env('SMS_API_URL'), [
                        // INAYOS KO ITO: Ginawa kong SMS_API_URL
                        'SenderName' => env('SMS_SENDER_NAME'),
                        'ToNumber' => $recipientContact,
                        'MessageBody' => $messageContent,
                        'FromNumber' => env('SMS_FROM_NUMBER'),
                    ]);

                if ($response->successful()) {
                    $status = 'Sent (Live)';
                    $providerResponse = $response->body();
                    // DAGDAG RESIBO: Isusulat kung success ang API
                    Log::info(
                        "SMS API SUCCESS: Message delivered to {$recipientContact}. Provider Response: {$providerResponse}",
                    );
                } else {
                    $status = 'Failed';
                    $providerResponse =
                        'HTTP Error: ' . $response->status() . ' - ' . $response->body();
                    // DAGDAG RESIBO: Isusulat kung nag-fail ang API
                    // BAGONG LOG: Isusulat nito sa laravel.log kapag nag 400 or 500 error ang Fortmed
                    Log::error(
                        "SMS API HTTP FAILED: Hindi naipadala kay {$recipientContact}. Reason: {$providerResponse}",
                    );
                }
            } catch (Exception $e) {
                $status = 'Failed (Exception)';
                $providerResponse = $e->getMessage();
                // BAGONG LOG: Isusulat nito kapag nag-timeout o nawalan ng internet ang server natin
                Log::error(
                    "SMS API CRITICAL EXCEPTION: Server failed to contact API for {$recipientContact}. Error: " .
                        $e->getMessage(),
                );
            }
        }

        // ==========================================
        // FAIL-SAFE DATABASE LOGGING
        // ==========================================
        try {
            NotificationLog::create([
                'user_id' => $userId,
                'service_request_id' => $serviceRequestId,
                'channel' => 'SMS',
                'recipient_contact' => $recipientContact,
                'message_content' => $messageContent,
                'status' => $status,
                'provider_response' => $providerResponse,
            ]);
        } catch (Exception $e) {
            // Kung sakaling mag-crash ang MySQL Database mo, hindi sasabog ang screen ng user.
            // Isusulat na lang niya ito sa laravel.log bilang emergency backup.
            Log::critical(
                "DATABASE ERROR: Hindi na-save sa notification_logs ang SMS para kay {$recipientContact}. Error: " .
                    $e->getMessage(),
            );
        }
    }
}
