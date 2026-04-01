<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // THE LARAVEL WAY
use App\Models\NotificationLog;

class SmsService
{
    /**
     * Ipadala ang SMS (Log man o totoong API) at i-save sa database.
     */
    public function sendSms($userId, $recipientContact, $messageContent, $serviceRequestId = null)
    {
        $driver = env('SMS_DRIVER', 'log');
        $status = 'Pending';
        $providerResponse = null;

        // ==========================================
        // ANG "SAFETY NET" (LOG DRIVER) - Libre at Ligtas
        // ==========================================
        if ($driver === 'log') {
            Log::info("====================================");
            Log::info("MOCK SMS SENT [To: {$recipientContact}]");
            Log::info("Message: {$messageContent}");
            Log::info("====================================");
            
            $status = 'Sent (Mock)';
            $providerResponse = 'Simulated via Laravel Log';
        } 
        // ==========================================
        // ANG TOTOONG API DRIVER (FORTMED FMCSMS)
        // ==========================================
        else if ($driver === 'api') {
            try {
                // The Laravel HTTP Client Way (Clean & Secure)
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-API-Key' => env('SMS_API_KEY'),
                ])->post('https://fortmed.org/web/FMCSMS/api/messages.php', [
                    'SenderName'  => 'Barangay Dona Lucia Services', // Pinalitan ko ng BDLS para mas propesyonal
                    'ToNumber'    => $recipientContact,
                    'MessageBody' => $messageContent,
                    'FromNumber'  => '+639189876543', // Default mula sa provider mo
                ]);

                // I-check kung successful ang bato natin sa API
                if ($response->successful()) {
                    $status = 'Sent (Live)';
                    $providerResponse = $response->body(); // I-save ang sagot ng Fortmed
                } else {
                    $status = 'Failed';
                    $providerResponse = 'HTTP Error: ' . $response->status() . ' - ' . $response->body();
                }

            } catch (\Exception $e) {
                // Kung nawalan ng internet ang server natin
                $status = 'Failed (Exception)';
                $providerResponse = $e->getMessage();
                Log::error("SMS API Error: " . $e->getMessage());
            }
        }

        // 2. I-save sa database para sa Audit at View ng Admin (Strict ERD Compliance)
        NotificationLog::create([
            'user_id' => $userId,
            'service_request_id' => $serviceRequestId,
            'channel' => 'SMS',
            'recipient_contact' => $recipientContact,
            'message_content' => $messageContent,
            'status' => $status,
            'provider_response' => $providerResponse,
        ]);

        return true;
    }
}