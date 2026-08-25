<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminDashboardUpdated;
use App\Events\ResidentRequestUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessAnnouncementSms;
use App\Jobs\ProcessRequestUpdate;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\DocumentType;
use App\Models\NotificationLog;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\EmailService;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. THE LARAVEL WAY: Account Management (Resident Accounts)
        $accountSearch = $request->get('account_search', '');
        $accountsQuery = User::where('role', 'resident')->latest();

        if (! empty($accountSearch)) {
            $accountsQuery->where(function ($q) use ($accountSearch) {
                $q->where('first_name', 'like', "%{$accountSearch}%")
                    ->orWhere('last_name', 'like', "%{$accountSearch}%")
                    ->orWhere('contact_number', 'like', "%{$accountSearch}%")
                    ->orWhere('email', 'like', "%{$accountSearch}%");
            });
        }
        $residentAccounts = $accountsQuery->paginate(15, ['*'], 'accounts_page')->withQueryString();

        // 5. QUEUE LOGIC WITH FILTERS & SORTING
        $queueStatus = $request->get('queue_status', 'all');
        $queueDoc = $request->get('queue_doc', 'all');
        $queueSort = $request->get('queue_sort', 'oldest');
        $queueSearch = $request->get('queue_search', '');

        $queueBase = ServiceRequest::with(['user', 'documentType']);

        // THE FIX: Universal Search across relationships
        if (! empty($queueSearch)) {
            $queueBase->where(function ($q) use ($queueSearch) {
                $q->where('queue_number', 'like', "%{$queueSearch}%")
                    ->orWhereHas('user', function ($userQ) use ($queueSearch) {
                        $userQ->where('first_name', 'like', "%{$queueSearch}%")
                            ->orWhere('last_name', 'like', "%{$queueSearch}%");
                    })
                    ->orWhereHas('documentType', function ($docQ) use ($queueSearch) {
                        $docQ->where('name', 'like', "%{$queueSearch}%");
                    });
            });
        }

        if ($queueSort === 'newest') {
            $queueBase->latest();
        } else {
            $queueBase->oldest(); // Ascending queue
        }

        $activeQuery = (clone $queueBase)->whereIn('status', ['pending', 'processing', 'for_interview', 'released']);

        if ($queueStatus !== 'all') {
            $activeQuery->where('status', $queueStatus);
        }
        if ($queueDoc !== 'all') {
            $activeQuery->where('document_type_id', $queueDoc);
        }

        $activeQueue = $activeQuery->paginate(15, ['*'], 'active_page')->withQueryString();

        // THE FIX: Isinama ang rejected at canceled sa History
        $receivedQueue = (clone $queueBase)
            ->whereIn('status', ['received', 'rejected', 'canceled'])
            ->paginate(15, ['*'], 'history_page');

        // THE FIX: Kunin lahat ng dokumento para sa Management Table
        $documents = DocumentType::all();

        // 6. SYSTEM AUDIT LOGS WITH SEARCH (Process 6.0)
        $auditSearch = $request->get('audit_search', '');
        $auditQuery = AuditLog::with('admin')->latest();

        if ($auditSearch) {
            $auditQuery->where(function ($q) use ($auditSearch) {
                $q->where('action', 'like', "%{$auditSearch}%")
                    ->orWhere('description', 'like', "%{$auditSearch}%")
                    ->orWhereHas('admin', function ($adminQ) use ($auditSearch) {
                        $adminQ->where('first_name', 'like', "%{$auditSearch}%")
                            ->orWhere('last_name', 'like', "%{$auditSearch}%");
                    });
            });
        }
        $auditLogs = $auditQuery->paginate(20, ['*'], 'audit_page')->withQueryString();

        // ==========================================
        // 7. LIVE ANALYTICS WITH FILTERING (Sir Philip's Request)
        // ==========================================
        $analyticsYear = $request->get('analytics_year', date('Y'));
        $analyticsMonth = $request->get('analytics_month', 'all');

        $analyticsQuery = ServiceRequest::query();
        if ($analyticsMonth !== 'all') {
            $analyticsQuery->whereYear('created_at', $analyticsYear)
                ->whereMonth('created_at', $analyticsMonth);
        } else {
            $analyticsQuery->whereYear('created_at', $analyticsYear);
        }

        $analyticsSummary = [
            'total' => (clone $analyticsQuery)->count(),
            'walkin' => (clone $analyticsQuery)->where('request_channel', 'Walk-in')->count(),
            'online' => (clone $analyticsQuery)->where('request_channel', 'Online')->count(),
            'pending' => (clone $analyticsQuery)->where('status', 'pending')->count(),
            'processing' => (clone $analyticsQuery)->whereIn('status', ['processing', 'for_interview'])->count(),
            'released' => (clone $analyticsQuery)->whereIn('status', ['released', 'received'])->count(),
            'rejected' => (clone $analyticsQuery)->whereIn('status', ['rejected', 'canceled'])->count(),
            'total_registered' => User::where('role', 'resident')->count(),
            'total_census' => DB::table('census_records')->count(),
        ];

        // CENSUS / RESIDENT MASTERLIST LOGIC
        $residentSearch = $request->get('resident_search', '');
        $censusQuery = DB::table('census_records')->orderBy('last_name', 'asc');

        if (! empty($residentSearch)) {
            $censusQuery->where(function ($q) use ($residentSearch) {
                $q->where('first_name', 'like', "%{$residentSearch}%")
                    ->orWhere('last_name', 'like', "%{$residentSearch}%")
                    ->orWhere('address', 'like', "%{$residentSearch}%");
            });
        }
        $censusRecords = $censusQuery->paginate(15, ['*'], 'census_page')->withQueryString();

        // THE ENTERPRISE FIX: Inertia Render with Auth Prop
        return Inertia::render('Admin/Admin-Dashboard', [
            'activeQueue' => $activeQueue,
            'receivedQueue' => $receivedQueue,
            'documents' => $documents,
            'auditLogs' => $auditLogs,
            'analyticsSummary' => $analyticsSummary,
            'censusRecords' => $censusRecords,
            'residentAccounts' => $residentAccounts,
            'filters' => [
                'analytics_month' => $analyticsMonth,
                'analytics_year' => $analyticsYear,
                'audit_search' => $auditSearch,
                'queue_status' => $queueStatus,
                'queue_doc' => $queueDoc,
                'queue_sort' => $queueSort,
                'queue_search' => $queueSearch,
                'resident_search' => $residentSearch,
                'account_search' => $accountSearch,
            ],
            'auth' => ['user' => Auth::user()], // Ito ang pipigil sa WSoD!
        ]);
    }

    public function updateRequestStatus(
        Request $request,
        ServiceRequest $serviceRequest,
        SmsService $smsService,
        EmailService $emailService,
    ) {
        $request->validate(['status' => 'required|string']);

        // THE LARAVEL WAY FIX: I-force sa lowercase bago i-save sa database
        $newStatus = strtolower($request->status);

        $serviceRequest->status = $newStatus;
        $message = '';

        if ($newStatus === 'processing') {
            $message = "Queue {$serviceRequest->queue_number} ay kasalukuyang pino-proseso na.";
        } elseif ($newStatus === 'for_interview') {
            $message = "Queue {$serviceRequest->queue_number}: Kailangan ng panayam (interview). Mangyaring pumunta sa hall.";
        } elseif ($newStatus === 'released') {
            $serviceRequest->released_at = now();
            $serviceRequest->released_by_admin_id = Auth::id();
            $message = "Queue {$serviceRequest->queue_number} ay ready for release na. Maaari nang kunin sa hall.";
        } elseif ($newStatus === 'rejected') {
            $message = "Queue {$serviceRequest->queue_number} ay nai-reject (kulang sa detalye/reqs). Maaaring mag-request muli.";
        }

        $serviceRequest->save();

        // I-skip ang pag-text kung marked as received na para hindi masayang ang SMS API Budget
        if ($message !== '' && $newStatus !== 'received') {
            $smsService->sendSms(
                $serviceRequest->user_id,
                $serviceRequest->user->contact_number,
                $message,
            );

            // 2. Ipadala ang Email (Kung verified at naka-opt-in ang residente)
            if (
                $serviceRequest->user->email_verified_at &&
                $serviceRequest->user->wants_email_notification
            ) {
                $emailService->sendEmail(
                    $serviceRequest->user_id,
                    $serviceRequest->user->email,
                    'BDLS Request Update: '.strtoupper($newStatus),
                    $message,
                    $serviceRequest->id,
                );
            }
        }

        // SYSTEM AUDIT LOG RECORDER (Process 6.0)
        AuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'STATUS_UPDATE',
            'description' => "Binago ang status ng request {$serviceRequest->queue_number} papuntang '".
                strtoupper($newStatus).
                "'.",
        ]);

        // Sabihan ang mismong residente na nag-iba na ang status niya in real-time
        event(new ResidentRequestUpdated($serviceRequest->user_id, $message));
        // Sabihan din ang sariling dashboard na mag-update ng listahan at counts
        event(new AdminDashboardUpdated);

        // ==========================================
        // THE FIX: Soft Delete for Rejected Requests
        // ==========================================
        if ($newStatus === 'rejected') {
            $serviceRequest->delete(); // Ligtas na itatago ng Laravel ang request

            return back()->with('active_tab', 'queue')->with('success_message', 'Request Rejected at inalis sa listahan.');
        }

        return back()->with('active_tab', 'queue')->with('success_message', 'Status Updated');
    }

    /**
     * MODULE: Silent Background Number Checker
     * Used by React Axios to auto-fill the form if the resident already exists.
     */
    public function checkWalkinNumber($number)
    {
        // Hanapin ang resident record
        $user = User::where('contact_number', $number)
            ->where('role', 'resident')
            ->first();

        if ($user) {
            return response()->json([
                'found' => true,
                'user' => [
                    'first_name' => $user->first_name,
                    'middle_name' => $user->middle_name,
                    'last_name' => $user->last_name,
                    'suffix' => $user->suffix,
                    'sex' => $user->sex,
                    'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '',
                    'address' => $user->address,
                ],
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * MODULE: Direct Unified Walk-in Encoding
     */
    public function storeWalkinRequest(Request $request, SmsService $smsService)
    {
        // 1. Unified Validation (Isang bagsakang validation)
        $request->validate([
            'contact_number' => 'required|string|max:20',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'sex' => 'required|string|in:Male,Female',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:255',
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'required|string|max:255',
        ]);

        // 2. Harangin kung ang number ay pagmamay-ari ng Admin
        $isAdmin = User::where('contact_number', $request->contact_number)->where('role', 'admin')->exists();

        if ($isAdmin) {
            return back()->withErrors([
                'walkin_error' => 'Bawal gamitin ang numero ng Admin para sa Service Requests.',
            ])->with('active_tab', 'walkin');
        }

        // 3. Ligtas na Database Transaction
        DB::transaction(function () use ($request, $smsService) {
            // A. Hahanapin kung may existing resident record na gamit ang number, kung wala, gagawa ng bago.
            $user = User::where('contact_number', $request->contact_number)->first();

            if (! $user) {
                $user = User::create([
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'suffix' => $request->suffix,
                    'sex' => $request->sex,
                    'date_of_birth' => $request->date_of_birth,
                    'address' => $request->address, // THE FIX: Isang address field na lang
                    'contact_number' => $request->contact_number,
                    'password' => Hash::make(Str::random(12)),
                    'role' => 'resident',
                    'contact_verified_at' => now(),
                    'is_verified' => true, // Auto-verified kasi kaharap ng Admin
                    'terms_accepted_at' => now(),
                ]);
            }

            // B. Gumawa ng W-XXX Queue Number
            $latestRequest = ServiceRequest::where('request_channel', 'Walk-in')->latest('id')->first();
            $nextNumber = $latestRequest ? intval(substr($latestRequest->queue_number, 2)) + 1 : 1;
            $queueNumber = 'W-'.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // C. I-save ang Request sa Database
            $serviceRequest = ServiceRequest::create([
                'user_id' => $user->id,
                'document_type_id' => $request->document_type_id,
                'request_channel' => 'Walk-in',
                'queue_number' => $queueNumber,
                'purpose' => $request->purpose,
                'status' => 'pending',
            ]);

            // D. I-record sa System Audit Log
            AuditLog::create([
                'admin_id' => Auth::id(),
                'action' => 'WALKIN_ENCODED',
                'description' => "Nag-encode ng walk-in request ({$queueNumber}) para kay {$user->first_name} {$user->last_name}.",
            ]);

            // E. Magpadala ng SMS Payload (Decluttered & Optimized)
            try {
                $message = "Walk-in Queue: {$queueNumber}. Naipasa na ang request. Maghintay tawagin o ng text update.";
                $smsService->sendSms(
                    $user->id,
                    $user->contact_number,
                    $message,
                    $serviceRequest->id,
                );
            } catch (\Exception $e) {
                Log::error("Walk-in SMS Failed (Queue: {$queueNumber}): ".$e->getMessage());
            }
        });

        event(new AdminDashboardUpdated);

        return redirect()
            ->route('admin.dashboard')
            ->with('active_tab', 'queue')
            ->with('success_message', 'Walk-in Request at Queue Number ay matagumpay na nagawa!');
    }

    /**
     * MODULE: Announcements Broadcast
     */
    public function broadcastAnnouncement(
        Request $request,
        SmsService $smsService,
        EmailService $emailService,
    ) {
        // THE LARAVEL WAY: Harangin agad sa controller bago pa mag-process
        $currentHour = (int) now()->format('H');
        if ($currentHour >= 21 || $currentHour < 7) {
            return back()
                ->withErrors([
                    'curfew' => 'NTC Curfew Active: Bawal mag-text blast mula 9:00 PM hanggang 7:00 AM.',
                ])
                ->with('active_tab', 'announcements');
        }
        // 1. The Laravel Way: Validation + NTC Anti-Spam Link Blocker
        $request->validate(
            [
                'message_body' => [
                    'required',
                    'string',
                    'not_regex:/(http|https|www\.)/i', // Pinipigilan agad ang links sa backend
                ],
            ],
            [
                'message_body.not_regex' => 'Bawal mag-send ng links o website URLs ayon sa NTC Anti-Spam rules.',
            ],
        );

        // 2. I-save ang kopya sa Database
        Announcement::create([
            'admin_id' => Auth::id(),
            'message_body' => $request->message_body,
        ]);

        // 3. THE FIX: Kunin LAHAT ng Verified na "Residente" lamang gamit ang bagong KYC flag
        $verifiedResidents = User::where('is_verified', 1)->where('role', 'resident')->get();
        $sentCount = 0;

        // 4. THE LARAVEL WAY: Mag-dispatch ng Background Jobs na may DELAY para hindi ma-spam ang API
        foreach ($verifiedResidents as $resident) {
            // THE FIX: Magdadagdag ng 2 segundo na delay bawat residente
            ProcessAnnouncementSms::dispatch($resident, $request->message_body)
                ->delay(now()->addSeconds($sentCount * 2));
            $sentCount++;
        }

        // SYSTEM AUDIT LOG RECORDER (Process 6.0)
        AuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'BROADCAST_SMS',
            'description' => "Nagpadala ng text blast announcement sa {$sentCount} verified na residente.",
        ]);

        // 5. Ibalik sa tab na may success message
        return back()->with([
            'active_tab' => 'announcements',
            'success_message' => "Broadcast Sent! Matagumpay na naipadala ang anunsyo sa {$sentCount} verified na residente.",
        ]);
    }

    /**
     * MODULE: Generate Analytics PDF (Process 5.0)
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'report_month' => 'required|string',
            'report_year' => 'required|numeric|min:2024',
        ]);

        $year = $request->report_year;
        $month = $request->report_month;

        if ($month === 'all') {
            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate = Carbon::create($year, 1, 1)->endOfYear();
            $reportTitle = 'Taunang Ulat para sa '.$year;
        } else {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            $reportTitle = 'Ulat para sa Buwan ng '.$startDate->format('F Y');
        }

        // 1. THE LARAVEL WAY: Eloquent Analytics Aggregation
        $requestsQuery = ServiceRequest::whereBetween('created_at', [$startDate, $endDate]);
        $notifsQuery = NotificationLog::whereBetween('created_at', [$startDate, $endDate]);

        $data = [
            'reportTitle' => $reportTitle,
            'totalRequests' => (clone $requestsQuery)->count(),
            'walkinCount' => (clone $requestsQuery)->where('request_channel', 'Walk-in')->count(),
            'onlineCount' => (clone $requestsQuery)->where('request_channel', 'Online')->count(),

            'pendingCount' => (clone $requestsQuery)->where('status', 'pending')->count(),
            'processingCount' => (clone $requestsQuery)->where('status', 'processing')->count(),
            'interviewCount' => (clone $requestsQuery)->where('status', 'for_interview')->count(),
            'releasedCount' => (clone $requestsQuery)
                ->whereIn('status', ['released', 'received'])
                ->count(),

            'smsCount' => (clone $notifsQuery)
                ->where('channel', 'SMS')
                ->where('status', 'like', '%Sent%')
                ->count(),
            'emailCount' => (clone $notifsQuery)
                ->where('channel', 'Email')
                ->where('status', 'like', '%Sent%')
                ->count(),
            'failedCount' => (clone $notifsQuery)->where('status', 'like', '%Failed%')->count(),
        ];

        // 2. Generate the PDF
        $pdf = Pdf::loadView('admin.pdf.analytics', $data);

        // 3. I-check kung "View in App" ba o "Force Download" ang pinindot
        if ($request->has('is_download') && $request->is_download == '1') {
            return $pdf->download("BDLS_Analytics_{$year}_{$month}.pdf");
        }

        // Default: Ipakita sa loob ng iframe (stream)
        return $pdf->stream("BDLS_Analytics_{$year}_{$month}.pdf");
    }

    /**
     * MODULE: Maintain Release Logbook (Use Case Requirement)
     * Nag-ge-generate ng PDF listahan ng lahat ng nai-release na dokumento.
     */
    public function printReleaseLogbook(Request $request)
    {
        // <-- THE FIX: Dinagdagan ng Request $request
        // 1. Kunin ang lahat ng tapos nang dokumento
        $receivedRequests = ServiceRequest::with(['user', 'documentType'])
            ->whereIn('status', ['released', 'received'])
            ->orderBy('released_at', 'desc')
            ->get();

        // 2. SYSTEM AUDIT LOG RECORDER (Process 6.0)
        AuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'PRINT_LOGBOOK',
            'description' => 'Nag-generate ng Official Release Logbook PDF.',
        ]);

        // 3. I-pasa sa PDF Engine
        $pdf = Pdf::loadView('admin.pdf.release_logbook', compact('receivedRequests'));
        $filename = 'BDLS_Release_Logbook_'.now()->format('Y_m_d').'.pdf';

        // 4. THE FIX: Check kung In-App View ba o Force Download
        if ($request->has('download') && $request->download == '1') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * MODULE: Print Registered Accounts (Live Analytics -> User Accounts Tab)
     */
    public function printRegisteredAccountsPDF(Request $request)
    {
        // 1. Fetch all registered residents (sorted by created_at)
        $registeredAccounts = User::where('role', 'resident')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. LOG THE ACTION
        AuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'PRINT_ACCOUNTS_LOG',
            'description' => 'Nag-generate ng PDF para sa Registered User Accounts.',
        ]);

        // 3. GENERATE PDF
        $pdf = Pdf::loadView('admin.pdf.registered_accounts', compact('registeredAccounts'));
        $filename = 'BDLS_Registered_Accounts_'.now()->format('Y_m_d').'.pdf';

        if ($request->has('download') && $request->download == '1') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * MODULE: Document Management - Store New Document
     */
    public function storeDocument(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'requirements_description' => 'required|string',
            'processing_fee' => 'required|numeric|min:0',
            'processing_time_minutes' => 'required|integer|min:1',
        ]);

        DocumentType::create([
            'name' => $request->name,
            'requirements_description' => $request->requirements_description,
            'processing_fee' => $request->processing_fee,
            'processing_time_minutes' => $request->processing_time_minutes,
            'is_active' => 1,
        ]);

        event(new AdminDashboardUpdated);

        return back()->with([
            'active_tab' => 'documents',
            'success_message' => 'Bagong dokumento ay matagumpay na naidagdag.',
        ]);
    }

    /**
     * MODULE: Document Management - Update Existing Document
     */
    public function updateDocument(Request $request, DocumentType $documentType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'requirements_description' => 'required|string',
            'processing_fee' => 'required|numeric|min:0',
            'processing_time_minutes' => 'required|integer|min:1',
        ]);

        $documentType->update($request->only(
            'name', 'requirements_description', 'processing_fee', 'processing_time_minutes'
        ));

        event(new AdminDashboardUpdated);

        return back()->with([
            'active_tab' => 'documents',
            'success_message' => 'Impormasyon ng dokumento ay nai-update.',
        ]);
    }

    /**
     * MODULE: Document Management - Toggle Active Status
     */
    public function toggleDocumentStatus(DocumentType $documentType)
    {
        $documentType->is_active = ! $documentType->is_active;
        $documentType->save();

        event(new AdminDashboardUpdated);

        $statusStr = $documentType->is_active ? 'Na-activate' : 'Na-deactivate';

        return back()->with([
            'active_tab' => 'documents',
            'success_message' => "Ang dokumento ay {$statusStr}.",
        ]);
    }

    /**
     * MODULE: Batch Processing (Process Multiple Requests)
     */
    public function batchUpdateStatus(Request $request)
    {
        $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:service_requests,id',
            'status' => 'required|string',
        ]);

        $newStatus = strtolower($request->status);
        $requests = ServiceRequest::with('user')->whereIn('id', $request->request_ids)->get();
        $adminId = Auth::id();
        $processedCount = 0;

        foreach ($requests as $serviceRequest) {
            $serviceRequest->status = $newStatus;
            $message = '';

            if ($newStatus === 'processing') {
                $message = "Queue {$serviceRequest->queue_number} ay kasalukuyang pino-proseso na.";
            } elseif ($newStatus === 'for_interview') {
                $message = "Queue {$serviceRequest->queue_number}: Kailangan ng panayam (interview). Mangyaring pumunta sa hall.";
            } elseif ($newStatus === 'released') {
                $serviceRequest->released_at = now();
                $serviceRequest->released_by_admin_id = $adminId;
                $message = "Queue {$serviceRequest->queue_number} ay ready for release na. Maaari nang kunin sa hall.";
            } elseif ($newStatus === 'rejected') {
                $message = "Queue {$serviceRequest->queue_number} ay nai-reject (kulang sa detalye/reqs). Maaaring mag-request muli.";
            }

            $serviceRequest->save();

            if ($message !== '' && $newStatus !== 'received') {
                // THE FIX: Magdadagdag ng 2 segundo na delay bawat request para iwas spam block
                ProcessRequestUpdate::dispatch($serviceRequest, $message)
                    ->delay(now()->addSeconds($processedCount * 2));
            }

            // Real-time Push via WebSockets
            event(new ResidentRequestUpdated($serviceRequest->user_id, $message));

            // Soft Delete kung rejected
            if ($newStatus === 'rejected') {
                $serviceRequest->delete();
            }

            $processedCount++;
        }

        // SYSTEM AUDIT LOG RECORDER
        AuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'BATCH_UPDATE',
            'description' => "Sabay-sabay na binago ang status ng {$processedCount} requests papuntang '".strtoupper($newStatus)."'.",
        ]);

        event(new AdminDashboardUpdated);

        return back()->with([
            'active_tab' => 'queue',
            'success_message' => "Matagumpay na nai-proseso ang {$processedCount} requests.",
        ]);
    }

    public function storeCensus(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'sex' => 'required|string|in:Male,Female',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:255',
        ]);
        $validated['is_alive'] = 1;
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table('census_records')->insert($validated);
        event(new AdminDashboardUpdated);

        return back()->with(['success_message' => 'Bagong residente ay matagumpay na naidagdag.', 'active_tab' => 'residents']);
    }

    public function updateCensus(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'sex' => 'required|string|in:Male,Female',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:255',
        ]);
        $validated['updated_at'] = now();

        DB::table('census_records')->where('id', $id)->update($validated);
        event(new AdminDashboardUpdated);

        return back()->with(['success_message' => 'Impormasyon ng residente ay nai-update.', 'active_tab' => 'residents']);
    }

    public function deleteCensus($id)
    {
        DB::table('census_records')->where('id', $id)->delete();
        event(new AdminDashboardUpdated);

        return back()->with(['success_message' => 'Residente ay tinanggal sa masterlist.', 'active_tab' => 'residents']);
    }

    public function deleteCensusBatch(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        DB::table('census_records')->whereIn('id', $request->ids)->delete();
        event(new AdminDashboardUpdated);

        return back()->with(['success_message' => count($request->ids).' residente ay sabay-sabay na tinanggal sa masterlist.', 'active_tab' => 'residents']);
    }

    public function importCensus(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('import_file');
        $handle = fopen($file->getPathname(), 'r');
        fgetcsv($handle); // Skip header row

        $bulkData = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 7) {
                continue;
            }

            $firstName = trim($row[0]);
            $lastName = trim($row[1]);
            $dob = trim($row[2]);

            if (empty($firstName) || empty($lastName) || empty($dob)) {
                continue;
            }

            // THE FIX: Prepare bulk array for UPSERT
            $bulkData[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => date('Y-m-d', strtotime($dob)),
                'middle_name' => trim($row[3]),
                'suffix' => trim($row[4]),
                'sex' => ucfirst(trim($row[5])),
                'address' => trim($row[6]),
                'is_alive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        fclose($handle);

        // Chunking para hindi sumabog ang RAM/Query limit sa napakalaking CSV
        foreach (array_chunk($bulkData, 500) as $chunk) {
            DB::table('census_records')->upsert(
                $chunk,
                ['first_name', 'last_name', 'date_of_birth'], // Unique columns
                ['middle_name', 'suffix', 'sex', 'address', 'is_alive', 'updated_at'] // Update columns
            );
        }
        event(new AdminDashboardUpdated);

        return back()->with(['success_message' => 'Import tapos na! Na-proseso ang '.count($bulkData).' census records gamit ang bulk Upsert.', 'active_tab' => 'residents']);
    }

    public function suspendAccount($id)
    {
        $user = User::findOrFail($id);
        $user->update(['locked_until' => now()->addDays(7)]);
        event(new AdminDashboardUpdated);

        return back()->with(['success_message' => "Ang account ni {$user->first_name} ay sinuspinde ng 7 araw.", 'active_tab' => 'accounts']);
    }

    public function deleteAccount($id)
    {
        $user = User::findOrFail($id);
        $name = $user->first_name.' '.$user->last_name;
        $user->delete();
        event(new AdminDashboardUpdated);

        return back()->with(['success_message' => "Ang account ni {$name} ay permanenteng nabura.", 'active_tab' => 'accounts']);
    }

    /**
     * MODULE: Manual KYC Verification Override
     * Binabago nito ang KYC verification status ng isang resident account bilang Verified,
     * nire-reset ang OCR attempts, at nire-record sa System Audit Log.
     */
    public function manualVerifyAccount($id)
    {
        $user = User::findOrFail($id);

        // Update user KYC verification status
        $user->update([
            'is_verified' => true,
            'ocr_attempts' => 0,
            'ocr_locked_until' => null,
        ]);

        // Process 6.0: System Audit Log Recorder
        AuditLog::create([
            'admin_id' => Auth::id(),
            'action' => 'MANUAL_VERIFY',
            'description' => "Manwal na binago ang KYC verification status ng account ni {$user->first_name} {$user->last_name} bilang Verified.",
        ]);

        // Trigger real-time UI synchronizer event (ShouldBroadcastNow)
        event(new AdminDashboardUpdated);

        return back()->with([
            'success_message' => "Ang account ni {$user->first_name} {$user->last_name} ay manu-manong na-verify.",
            'active_tab' => 'accounts',
        ]);
    }

    public function downloadCensusTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=BDLS_Census_Template.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        $columns = ['first_name', 'middle_name', 'last_name', 'suffix', 'sex', 'date_of_birth', 'address'];
        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Juan', 'Reyes', 'Dela Cruz', 'Jr.', 'Male', '1990-05-15', '123 Purok 1, Brgy. Dona Lucia']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
