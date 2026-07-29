<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\RateLimiter;
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
        return Inertia\Inertia::render('Auth/Login');
    })->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/signup', function () {
        return Inertia\Inertia::render('Auth/Signup');
    })->name('signup');
    Route::post('/signup', [AuthController::class, 'register'])->name('signup.post');
    Route::post('/signup/validate-step', [AuthController::class, 'validateStepOne'])->name('signup.validate_step');

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
    return Inertia\Inertia::render('Auth/Otp', [
        'verifyRoute' => route('otp.verify'),
        'resendRoute' => route('otp.resend'),
        'cooldown' => RateLimiter::availableIn('resend_sms_otp_'.request()->ip()),
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

            // QUEUE MANAGEMENT ROUTE
            Route::post('/request/{serviceRequest}/update-status', [
                AdminDashboardController::class,
                'updateRequestStatus',
            ])->name('request.update_status');

            // BATCH PROCESSING ROUTE
            Route::post('/request/batch-update', [
                AdminDashboardController::class,
                'batchUpdateStatus',
            ])->name('request.batch_update');

            // // WALK-IN MODULE ROUTES
            Route::get('/walkin/check-number/{number}', [
                AdminDashboardController::class,
                'checkWalkinNumber',
            ])->name('walkin.check_number');

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

            Route::get('/analytics/accounts/print', [
                AdminDashboardController::class,
                'printRegisteredAccountsPDF',
            ])->name('analytics.print_accounts');

            // LOGBOOK ROUTE (Maintain Release Logbook Use Case)
            Route::get('/queue/logbook/print', [
                AdminDashboardController::class,
                'printReleaseLogbook',
            ])->name('queue.print_logbook');

            // DOCUMENT MANAGEMENT ROUTES
            Route::post('/documents', [AdminDashboardController::class, 'storeDocument'])->name('documents.store');
            Route::post('/documents/{documentType}/update', [AdminDashboardController::class, 'updateDocument'])->name('documents.update');
            Route::post('/documents/{documentType}/toggle', [AdminDashboardController::class, 'toggleDocumentStatus'])->name('documents.toggle');

            // CENSUS MANAGEMENT ROUTES
            Route::post('/census', [AdminDashboardController::class, 'storeCensus'])->name('census.store');
            Route::post('/census/{id}/update', [AdminDashboardController::class, 'updateCensus'])->name('census.update');
            Route::delete('/census/{id}', [AdminDashboardController::class, 'deleteCensus'])->name('census.destroy');
            Route::post('/census/batch-delete', [AdminDashboardController::class, 'deleteCensusBatch'])->name('census.batch_destroy');
            Route::post('/census/import', [AdminDashboardController::class, 'importCensus'])->name('census.import');
            Route::get('/census/template', [AdminDashboardController::class, 'downloadCensusTemplate'])->name('census.template');

            // MANAGE ACCOUNTS ROUTES
            Route::post('/accounts/{id}/suspend', [AdminDashboardController::class, 'suspendAccount'])->name('accounts.suspend');
            Route::delete('/accounts/{id}', [AdminDashboardController::class, 'deleteAccount'])->name('accounts.destroy');
        });

    // ==========================================
    // RESIDENT DASHBOARD GROUP
    // ==========================================
    Route::prefix('resident')
        ->name('resident.')
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [ServiceRequestController::class, 'index'])->name('dashboard');

            // Live KYC ID Verification Route
            Route::post('/verify-id', [
                ServiceRequestController::class,
                'verifyId',
            ])->name('verify_id');

            // BINURA: resubmit_registration route
            // Email & Notification Preferences
            Route::post('/email/send-otp', [ProfileController::class, 'sendEmailOtp'])->name('email.send');
            Route::post('/email/verify-otp', [ProfileController::class, 'verifyEmailOtp'])->name('email.verify');
            Route::post('/email/add', [ProfileController::class, 'addEmail'])->name('email.add');
            Route::post('/settings/update-contact', [ProfileController::class, 'updateContactNumber'])->name('settings.update_contact');
            Route::post('/settings/verify-contact', [ProfileController::class, 'verifyContactOtp'])->name('settings.verify_contact');
            Route::post('/settings/email-preference', [
                ProfileController::class,
                'updateEmailPreference',
            ])->name('settings.email_preference');

            // Service Requests
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
