<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome'); // a welcome page
});

Route::get('/login', function () {
    return view('auth.login'); // goes to login page
});

Route::get('/signup', function () {
    return view('auth.signup'); 
});

Route::post('/signup', [AuthController::class, 'register'])
    ->name('signup.post');
    // ->middleware('throttle:3,1'); // LIMIT: 3 signups per 1 minute per IP Address

// 1. GET Route: Ito ang nagpapakita ng UI ng OTP page (Kaya ka nag-404 dahil nawala ito)
Route::get('/otp', function () {
    return view('auth.otp');
})->name('otp.show');

// 2. POST Route: Ito ang sasalo sa 6-digit code kapag pinindot ang "Verify Account"
Route::post('/otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');

// Resend otp
Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend');

// Saluhin ang Login Data
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// ==========================================
// RESIDENT DASHBOARD ROUTES (Protected by Auth)
// ==========================================
Route::middleware(['auth'])->prefix('resident')->name('resident.')->group(function () {
    
    // Ang mismong Dashboard
    Route::get('/dashboard', function () {
        return view('resident.dashboard');
    })->name('dashboard');

    // Ang Settings/Preferences
    // Route::get('/settings', function () {
    //     return view('resident.settings');
    // })->name('settings');

    // EMAIL VERIFICATION ROUTES (Inilipat sa ProfileController)
    Route::post('/email/send-otp', [ProfileController::class, 'sendEmailOtp'])->name('email.send');
    Route::post('/email/verify-otp', [ProfileController::class, 'verifyEmailOtp'])->name('email.verify');
    Route::post('/email/add', [ProfileController::class, 'addEmail'])->name('email.add');

    // BAGONG ROUTE: Para sa Email Notification Toggle
    Route::post('/settings/email-preference', [ProfileController::class, 'updateEmailPreference'])->name('settings.email_preference');

});

// Call the logout function
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

