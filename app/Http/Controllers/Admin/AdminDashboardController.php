<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminDashboardUpdated;
use App\Events\ResidentRequestUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessAnnouncementSms;
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
        // 1. The Laravel Way: Base Query (Filtered at DB level)
        $query = User::where('role', 'resident');

        // 2. Search Logic
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        // 3. Sorting Logic
        if ($request->get('sort') == 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        // 5. QUEUE LOGIC: Separate Active Queue from Received History
        $queueBase = ServiceRequest::with(['user', 'documentType'])->orderBy('created_at', 'asc');

        $activeQueue = (clone $queueBase)
            ->whereIn('status', ['pending', 'processing', 'for_interview', 'released'])
            ->paginate(15, ['*'], 'active_page');

        // THE FIX: Isinama ang rejected at canceled sa History
        $receivedQueue = (clone $queueBase)
            ->whereIn('status', ['received', 'rejected', 'canceled'])
            ->paginate(15, ['*'], 'history_page');

        $documents = DocumentType::where('is_active', 1)->get();

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
        ];

        // THE ENTERPRISE FIX: Inertia Render with Auth Prop
        return Inertia::render('Admin/Admin-Dashboard', [
            'activeQueue' => $activeQueue,
            'receivedQueue' => $receivedQueue,
            'documents' => $documents,
            'auditLogs' => $auditLogs,
            'analyticsSummary' => $analyticsSummary,
            'filters' => [
                'analytics_month' => $analyticsMonth,
                'analytics_year' => $analyticsYear,
                'audit_search' => $auditSearch,
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
            $message = "Brgy Dona Lucia: Ang iyong request ({$serviceRequest->queue_number}) ay kasalukuyang pino-proseso.";
        } elseif ($newStatus === 'for_interview') {
            // Ito ay magte-text lang kapag naging "For Interview" ang papel
            $message = "Brgy Dona Lucia: Ang request ({$serviceRequest->queue_number}) ay nangangailangan ng panayam. Pumunta sa hall.";
        } elseif ($newStatus === 'released') {
            $serviceRequest->released_at = now();
            $serviceRequest->released_by_admin_id = Auth::id();
            $message = "Brgy Dona Lucia: Ang dokumento para sa ({$serviceRequest->queue_number}) ay ready for release na. Maaari nang kunin.";
        } elseif ($newStatus === 'rejected') {
            // THE FIX: Admin Reject Logic
            $message = "Brgy Dona Lucia: Ang iyong request ({$serviceRequest->queue_number}) ay nai-reject dahil sa hindi sapat na detalye o requirements. Maaaring mag-request muli.";
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
     * PHASE 1: Walk-In Search-First Logic
     */
    public function searchWalkinAccount(Request $request)
    {
        $request->validate([
            'contact_number' => 'required|string|max:20',
        ]);

        // Hanapin ang user gamit ang unique contact number
        $walkinUser = User::where('contact_number', $request->contact_number)->first();

        // Ibalik sa Walk-in Tab kasama ang resulta
        return back()->with([
            'active_tab' => 'walkin',
            'walkin_searched' => true,
            'walkin_search_number' => $request->contact_number,
            'walkin_user' => $walkinUser,
        ]);
    }

    /**
     * PHASE 2: Walk-in Shadow Profile & Request Creation
     */
    public function storeWalkinRequest(Request $request, SmsService $smsService)
    {
        // 1. Validation (Pinagsamang Resident Data at Request Data)
        $request->validate([
            'contact_number' => 'required|string|max:20',
            'is_new_user' => 'required|boolean',
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'required|string|max:255',

            // Required lang kung gagawa ng Shadow Profile:
            'first_name' => 'required_if:is_new_user,1|string|max:255',
            'last_name' => 'required_if:is_new_user,1|string|max:255',
            'sex' => 'required_if:is_new_user,1|string|in:Male,Female',
            'date_of_birth' => 'required_if:is_new_user,1|date',
            'address' => $validatedData['address'],
        ]);

        // THE FIX: Harangin kung ang number na nai-search ay pagmamay-ari ng Admin
        $isAdmin = User::where('contact_number', $request->contact_number)->where('role', 'admin')->exists();

        if ($isAdmin) {
            return back()->withErrors([
                'walkin_error' => 'Bawal gamitin ang numero ng Admin para sa Service Requests.',
            ])->with('active_tab', 'walkin');
        }

        // 2. I-wrap sa Transaction para ligtas ang pera sa SMS
        DB::transaction(function () use ($request, $smsService) {
            // A. Hanapin o Gumawa ng Shadow Profile
            if ($request->is_new_user) {
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'sex' => $request->sex,
                    'date_of_birth' => $request->date_of_birth,
                    'house_number' => $request->house_number,
                    'purok_street' => $request->purok_street,
                    'contact_number' => $request->contact_number,
                    'password' => Hash::make(Str::random(12)),
                    'role' => 'resident',
                    'contact_verified_at' => now(),
                    'is_verified' => true, // Walk-ins are physically verified by Admin
                    'terms_accepted_at' => now(),
                ]);
            } else {
                $user = User::where('contact_number', $request->contact_number)->firstOrFail();
            }

            // B. Gumawa ng W-XXX Queue Number (W para sa Walk-in)
            $latestRequest = ServiceRequest::where('request_channel', 'Walk-in')
                ->latest('id')
                ->first();
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

            // ========================================================
            // THE FIX: AUDIT LOG AT TRY-CATCH NA NASA LOOB NG TRANSACTION
            // ========================================================

            // 1. THE LARAVEL WAY: I-record agad sa Audit Log ang ginawa ni Admin
            AuditLog::create([
                'admin_id' => Auth::id(),
                'action' => 'WALKIN_ENCODED',
                'description' => "Nag-encode ng walk-in request ({$queueNumber}) para kay {$user->first_name} {$user->last_name}.",
            ]);

            // 2. DEFENSIVE SECURITY: I-wrap ang SMS sa Try-Catch para hindi mag-rollback ang DB kapag Curfew
            try {
                $message = "Brgy Dona Lucia: Ang iyong walk-in request ay naipasa na. Queue No: {$queueNumber}. Maghintay tawagin o maka-receive ng text update.";
                $smsService->sendSms(
                    $user->id,
                    $user->contact_number,
                    $message,
                    $serviceRequest->id,
                );
            } catch (\Exception $e) {
                // I-log lang ang error para makita mo, pero HINDI magka-crash ang system. Ligtas ang data sa taas.
                Log::error("Walk-in SMS Failed (Queue: {$queueNumber}): ".$e->getMessage());
            }

            // ========================================================
        }); // <-- Dito nagtatapos ang DB::transaction()

        event(new AdminDashboardUpdated);

        // 3. I-redirect pabalik sa Queue Tab para makita agad ni Admin ang bagong pila
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

        // 3. THE FIX: Kunin LAHAT ng Verified na "Residente" lamang (Exclude Admins)
        $verifiedResidents = User::approved()->where('role', 'resident')->get();
        $sentCount = 0;

        // 4. THE LARAVEL WAY: Mag-dispatch ng Background Jobs para hindi mag-hang ang system!
        foreach ($verifiedResidents as $resident) {
            ProcessAnnouncementSms::dispatch($resident, $request->message_body);
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
}
