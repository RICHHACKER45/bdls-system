<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. THE SMART TRAFFIC DIRECTOR (Welcome Page)
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');

// ==========================================
// 2. GUEST ROUTES (Para lang sa mga HINDI pa naka-login)
// ==========================================
Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/signup', function () {
        return view('auth.signup');
    })->name('signup');
    Route::post('/signup', [AuthController::class, 'register'])->name('signup.post');

    // ==========================================
    // SMS FORGOT PASSWORD ROUTES (3-Step Flow)
    // ==========================================
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetOtp'])->name('password.send_otp');
    
    Route::get('/forgot-password/otp', [AuthController::class, 'showResetOtpForm'])->name('password.otp.show');
    Route::post('/forgot-password/otp', [AuthController::class, 'verifyResetOtp'])->name('password.otp.verify');
    Route::post('/forgot-password/otp/resend', [AuthController::class, 'resendResetOtp'])->name('password.otp.resend');
    
    Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update.submit');
});

// ==========================================
// 3. OTP ROUTES (Para sa Account Verification)
// ==========================================
Route::get('/otp', function () {
    return view('auth.otp', [
        'verifyRoute' => route('otp.verify'),
        'resendRoute' => route('otp.resend')
    ]);
})->name('otp.show');
Route::post('/otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend');

// ==========================================
// 4. AUTHENTICATED ROUTES (Bawal ang walang account)
// ==========================================
Route::middleware(['auth'])->group(function () {

    // UNIVERSAL PASSWORD UPDATE ROUTE
    Route::post('/password/update', [ProfileController::class, 'updatePassword'])->name('password.update');

    // LOGOUT (Dapat naka-login bago makapag-logout)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ==========================================
    // ADMIN DASHBOARD GROUP (Protected by Middleware)
    // ==========================================
    Route::middleware(['admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            // Wala nang mahabang logic dito.
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            // "Skinny Endpoint" para sa AJAX Polling
            Route::get('/api/pending-count', [
                AdminDashboardController::class,
                'checkPendingCount',
            ])->name('api.pending_count');
            // THE LARAVEL WAY: Route Model Binding para sa Approve at Reject
            Route::post('/account/{user}/approve', [
                AdminDashboardController::class,
                'approveAccount',
            ])->name('approve_account');
            Route::post('/account/{user}/reject', [
                AdminDashboardController::class,
                'rejectAccount',
            ])->name('reject_account');

            // TASK 1: Admin Delete Functionality
            Route::delete('/account/{user}', [
                AdminDashboardController::class,
                'destroyAccount',
            ])->name('delete_account');

            // QUEUE MANAGEMENT ROUTE
            Route::post('/request/{serviceRequest}/update-status', [
                AdminDashboardController::class,
                'updateRequestStatus',
            ])->name('request.update_status');
            // AJAX Polling para sa Live Queue
            Route::get('/api/queue-count', [
                AdminDashboardController::class,
                'checkQueueCount',
            ])->name('api.queue_count');
            // WALK-IN MODULE ROUTES
            Route::post('/walkin/search', [
                AdminDashboardController::class,
                'searchWalkinAccount',
            ])->name('walkin.search');
            // IDAGDAG ITO PARA SA PHASE 2:
            Route::post('/walkin/store', [
                AdminDashboardController::class,
                'storeWalkinRequest',
            ])->name('walkin.store');

            // ANNOUNCEMENTS ROUTE
            Route::post('/announcements/broadcast', [
                AdminDashboardController::class,
                'broadcastAnnouncement',
            ])->name('announcements.broadcast');
            // REPORTS & LOGS ROUTE (Process 5.0)
            Route::post('/reports/generate', [
                AdminDashboardController::class,
                'generateReport',
            ])->name('reports.generate');

            // LOGBOOK ROUTE (Maintain Release Logbook Use Case)
            Route::get('/queue/logbook/print', [
                AdminDashboardController::class,
                'printReleaseLogbook',
            ])->name('queue.print_logbook');
            // 1-WEEK PENALTY ROUTE
            Route::post('/account/{user}/suspend', [
                AdminDashboardController::class,
                'suspendAccount',
            ])->name('suspend_account');
        });

    // ==========================================
    // RESIDENT DASHBOARD GROUP
    // ==========================================
    Route::prefix('resident')
        ->name('resident.')
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [ServiceRequestController::class, 'index'])->name('dashboard');

            // TASK 3: Verification Status Polling Endpoint
            Route::get('/api/status', [
                ServiceRequestController::class,
                'checkVerificationStatus',
            ])->name('api.status');

            // TASK 4: Resubmit Registration
            Route::post('/request/resubmit', [
                ServiceRequestController::class,
                'resubmitRegistration',
            ])->name('resubmit_registration');

            // Email & Notification Preferences
            Route::post('/email/send-otp', [ProfileController::class, 'sendEmailOtp'])->name(
                'email.send',
            );
            Route::post('/email/verify-otp', [ProfileController::class, 'verifyEmailOtp'])->name(
                'email.verify',
            );
            Route::post('/email/add', [ProfileController::class, 'addEmail'])->name('email.add');
            Route::post('/settings/email-preference', [
                ProfileController::class,
                'updateEmailPreference',
            ])->name('settings.email_preference');

            // Service Requests
            Route::get('/request/create', [ServiceRequestController::class, 'create'])->name(
                'request.create',
            );
            Route::post('/request', [ServiceRequestController::class, 'store'])->name(
                'request.store',
            );
            // CANCEL REQUEST (Resident Side)
            Route::post('/request/{serviceRequest}/cancel', [
                ServiceRequestController::class,
                'cancelRequest',
            ])->name('request.cancel');
        });
});
