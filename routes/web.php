<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminDashboardController;

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
});

// ==========================================
// 3. OTP ROUTES (Para sa Account Verification)
// ==========================================
Route::get('/otp', function () {
    return view('auth.otp');
})->name('otp.show');
Route::post('/otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend');

// ==========================================
// 4. AUTHENTICATED ROUTES (Bawal ang walang account)
// ==========================================
Route::middleware(['auth'])->group(function () {
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
        });
});
