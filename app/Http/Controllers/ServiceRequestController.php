<?php

namespace App\Http\Controllers;

use App\Events\AdminDashboardUpdated;
use App\Models\Announcement;
use App\Models\Attachment;
use App\Models\DocumentType;
use App\Models\NotificationLog;
use App\Models\ServiceRequest;
use App\Services\SmsService;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ServiceRequestController extends Controller
{
    // Dependency Injection
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Display the Resident Dashboard.
     */
    public function index()
    {
        $documents = DocumentType::where('is_active', 1)->get();
        $user = Auth::user();

        $myRequests = ServiceRequest::with('documentType')
            ->withTrashed()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        // 1. Mga Aktibong Pinoproseso
        $pendingRequests = $myRequests->where('status', 'pending');
        // 2. Handa Nang Kunin
        $readyRequests = $myRequests->where('status', 'released');

        // 3. Kasaysayan (Tapos na, na-reject, o na-cancel)
        $historyRequests = $myRequests->whereIn('status', ['received', 'canceled', 'rejected']);

        // THE FIX: Kunin ang pinakabagong 3 announcements
        $announcements = Announcement::latest()->take(3)->get();

        // Kunin ang bilang ng tao sa pila at total na oras
        $activeQueueCount = ServiceRequest::whereIn('status', ['pending', 'processing'])->count();
        $currentBacklogMinutes = (int) DB::table('service_requests')
            ->join('document_types', 'service_requests.document_type_id', '=', 'document_types.id')
            ->whereIn('service_requests.status', ['pending', 'processing'])
            ->sum('document_types.processing_time_minutes');

        // Kunin ang personal Notification History ng naka-login na residente
        $notificationLogs = NotificationLog::where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'notifs_page');

        return Inertia::render('Resident/Dashboard', [
            'documents' => $documents,
            'myRequests' => $myRequests->values(),
            'pendingRequests' => $pendingRequests->values(),
            'readyRequests' => $readyRequests->values(),
            'historyRequests' => $historyRequests->values(),
            'announcements' => $announcements,
            'auth' => ['user' => $user],
            'activeQueueCount' => $activeQueueCount,
            'currentBacklogMinutes' => $currentBacklogMinutes,
            'notificationLogs' => $notificationLogs,
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validation Check
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'required|string|max:255',
            'additional_details' => 'nullable|string',
            'payment_method' => 'required|string|in:Cash,GCash',
            'payment_receipt_path' => 'required_if:payment_method,GCash|nullable|image|mimes:jpeg,png,jpg|max:5120',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        // 2. Queue Number Generator
        $latestRequest = ServiceRequest::where('request_channel', 'Online')->latest('id')->first();
        $nextNumber = $latestRequest ? intval(substr($latestRequest->queue_number, 2)) + 1 : 1;
        $queueNumber = 'O-'.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $user = Auth::user();

        // THE LARAVEL WAY: I-wrap ang Service Request sa Transaction
        DB::transaction(function () use ($validated, $request, $queueNumber, $user) {
            // 2.5 I-save ang Resibo kung GCash ang pinili
            $receiptPath = null;
            if ($validated['payment_method'] === 'GCash' && $request->hasFile('payment_receipt_path')) {
                $receiptPath = $request->file('payment_receipt_path')->store('payment_receipts', 'local');
            }

            // 3. I-save ang Request
            $serviceRequest = ServiceRequest::create([
                'user_id' => $user->id,
                'document_type_id' => $validated['document_type_id'],
                'request_channel' => 'Online',
                'queue_number' => $queueNumber,
                'purpose' => $validated['purpose'],
                'additional_details' => $validated['additional_details'],
                'payment_method' => $validated['payment_method'],
                'payment_receipt_path' => $receiptPath,
                'status' => 'pending',
            ]);

            // 4. I-save ang Attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    // PALITAN ITO:
                    $path = $file->store('service_requirements', 'local');
                    Attachment::create([
                        'service_request_id' => $serviceRequest->id,
                        'file_path' => $path,
                    ]);
                }
            }

            // 5.TRIGGER SMS SERVICE (Workflow Step 8)
            // 5. CALCULATE ESTIMATED WAITING TIME PARA SA SMS
            $peopleInQueue = ServiceRequest::whereIn('status', ['pending', 'processing'])
                ->where('id', '<', $serviceRequest->id)
                ->count();

            $queueBacklogMinutes = DB::table('service_requests')
                ->join('document_types', 'service_requests.document_type_id', '=', 'document_types.id')
                ->whereIn('service_requests.status', ['pending', 'processing'])
                ->where('service_requests.id', '<=', $serviceRequest->id)
                ->sum('document_types.processing_time_minutes');

            $maxMins = $queueBacklogMinutes;
            $minMins = max(15, floor($maxMins / 2));

            $formatTime = function ($m) {
                $h = floor($m / 60);
                $r = $m % 60;
                if ($h > 0) {
                    return $r > 0 ? "{$h} hr at {$r} mins" : "{$h} hr";
                }

                return "{$m} mins";
            };

            $timeString = $formatTime($minMins).' - '.$formatTime($maxMins);

            $message = "BDLS: Ang iyong request ({$queueNumber}) ay naipasa na. Estimated Waiting Time: {$timeString}.";

            $this->smsService->sendSms(
                $user->id,
                $user->contact_number,
                $message,
                $serviceRequest->id,
            );
        });

        // Paputukin ang event para lilitaw agad ang request sa screen ng mga naka-login na Admin!
        event(new AdminDashboardUpdated);

        // 6. Ibalik sa Dashboard
        return redirect()
            ->route('resident.dashboard')
            ->with([
                'success_title' => 'Request Submitted!',
                'success_message' => "Ang iyong dokumento ay pinoproseso na. Ang iyong Queue Number ay {$queueNumber}.",
                'active_tab' => 'dashboard',
            ]);
    }

    /**
     * RESIDENT CANCEL REQUEST LOGIC
     */
    public function cancelRequest(ServiceRequest $serviceRequest)
    {
        // 1. SECURITY: Siguraduhing kanya ang request at 'pending' pa lang
        if ($serviceRequest->user_id !== Auth::id() || $serviceRequest->status !== 'pending') {
            abort(403, 'Hindi mo maaaring i-cancel ang request na ito.');
        }

        // 2. THE LARAVEL WAY: Soft Delete + Update Status
        $serviceRequest->status = 'canceled';
        $serviceRequest->save();
        $serviceRequest->delete(); // Ligtas na itatago ng system

        return back()->with([
            'success_title' => 'Request Canceled',
            'success_message' => 'Matagumpay mong kinansela ang dokumento.',
            'active_tab' => 'dashboard',
        ]);
    }

    public function verifyId(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'id_photo_path' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $fullImagePath = $request->file('id_photo_path')->getPathname();

        $isAutoApproved = false;
        $ocrErrorMessage = 'Hindi mabasa nang malinaw ang ID.';

        if (env('OCR_DRIVER', 'live') === 'mock') {
            $isAutoApproved = true;
            Log::info('DEV MODE: Bypassed Google Vision API in Dashboard. Auto-approved.');
        } else {
            try {
                $envPath = env('GOOGLE_CREDENTIALS_PATH', 'app/private/google-credentials.json');
                $credentialsPath = storage_path($envPath);
                $apiUrl = env('GOOGLE_VISION_API_URL', 'https://vision.googleapis.com/v1/images:annotate');
                $authScope = env('GOOGLE_AUTH_SCOPE', 'https://www.googleapis.com/auth/cloud-platform');

                if (file_exists($credentialsPath)) {
                    $credentials = new ServiceAccountCredentials([$authScope], $credentialsPath);
                    $token = $credentials->fetchAuthToken();
                    $accessToken = $token['access_token'];

                    $base64Image = base64_encode(file_get_contents($fullImagePath));

                    $response = Http::withToken($accessToken)
                        ->timeout(20)
                        ->post($apiUrl, [
                            'requests' => [
                                [
                                    'image' => ['content' => $base64Image],
                                    'features' => [['type' => 'DOCUMENT_TEXT_DETECTION']],
                                ],
                            ],
                        ]);

                    if ($response->successful()) {
                        $visionResult = $response->json();
                        $scannedText = data_get($visionResult, 'responses.0.textAnnotations.0.description');

                        if (! empty($scannedText)) {
                            $scannedText = strtoupper($scannedText);
                            $firstName = strtoupper($user->first_name);
                            $lastName = strtoupper($user->last_name);

                            if (str_contains($scannedText, $firstName) && str_contains($scannedText, $lastName)) {
                                $inCensus = DB::table('census_records')
                                    ->where('first_name', $user->first_name)
                                    ->where('last_name', $user->last_name)
                                    ->where('is_alive', 1)
                                    ->exists();

                                if ($inCensus) {
                                    $isAutoApproved = true;
                                    Log::info("AUTO-ID SUCCESS: Nag-match ang ID at Census ni {$firstName} {$lastName}!");
                                } else {
                                    $ocrErrorMessage = 'Hindi tumugma ang pangalan sa ID at sa Census.';
                                    Log::warning("OCR: Nabasa sa ID pero WALA SA CENSUS si {$firstName} {$lastName}");
                                }
                            } else {
                                $ocrErrorMessage = 'Hindi malinaw ang pangalan sa ID.';
                                Log::warning('OCR: Hindi nakita ang pangalan sa ID. Nakita: '.$scannedText);
                            }
                        } else {
                            $ocrErrorMessage = 'Walang nabasang text sa ID. Masyadong malabo.';
                            Log::warning('OCR: Walang text na nakuha. Resulta: '.json_encode($visionResult));
                        }
                    } elseif ($response->clientError()) {
                        Log::error("OCR Client Error (HTTP {$response->status()}): May mali sa data o expired ang credentials. Detalye: ".$response->body());
                    } elseif ($response->serverError()) {
                        Log::error("OCR Server Error (HTTP {$response->status()}): Nag-crash ang servers ng Google. Detalye: ".$response->body());
                    } else {
                        Log::error("OCR Unknown Error (HTTP {$response->status()}): ".$response->body());
                    }
                } else {
                    Log::error('OCR Error: Nawawala ang Google Credentials JSON file.');
                }
            } catch (\Exception $e) {
                Log::error('OCR Exception: '.$e->getMessage());
            }
        }

        if ($isAutoApproved) {
            // Reset trackers and verify user
            $user->update([
                'is_verified' => true,
                'ocr_attempts' => 0,
                'ocr_locked_until' => null,
            ]);

            return back()->with([
                'success_title' => 'ID Verified!',
                'success_message' => 'Matagumpay na na-verify ang iyong ID. Maaari ka nang mag-request ng dokumento.',
                'active_tab' => 'dashboard',
            ]);
        } else {
            // Increment attempts
            $user->increment('ocr_attempts');

            // Check if reached 5 attempts
            if ($user->ocr_attempts >= 5) {
                $user->update(['ocr_locked_until' => now()->addMinutes(20)]);

                return back()->withErrors(['id_photo_path' => 'Naubos mo na ang 5 attempts. Naka-lock pansamantala ang iyong verification.']);
            }

            return back()->withErrors(['id_photo_path' => "{$ocrErrorMessage} (Attempt {$user->ocr_attempts} of 5)"]);
        }
    }
}
