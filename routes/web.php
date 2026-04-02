<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\Auth;
use App\Models\DocumentType;
use App\Http\Controllers\Admin\AdminDashboardController;

// ==========================================
// 1. THE TRAFFIC DIRECTOR (Welcome Page)
// ==========================================
Route::get('/', function () {
    // Kung naka-login na, dalhin sa dashboard nang direkta (Walang loop)
    if (Auth::check()) {
        return redirect()->route('resident.dashboard');
    }
    // Kung guest, ipakita ang welcome page
    return view('welcome'); 
})->name('welcome');

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
    // ADMIN DASHBOARD GROUP
    // ==========================================
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // THE LARAVEL WAY: Tinawag na natin ang Controller. 
        // Wala nang mahabang logic dito.
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        // THE LARAVEL WAY: "Skinny Endpoint" para sa AJAX Polling
        Route::get('/api/pending-count', [AdminDashboardController::class, 'checkPendingCount'])->name('api.pending_count');

    });

    // RESIDENT DASHBOARD GROUP
    Route::prefix('resident')->name('resident.')->group(function () {
        
            // Dashboard
        Route::get('/dashboard', function () {
            // Kunin ang lahat ng active documents mula sa database
            $documents = DocumentType::where('is_active', 1)->get();

            return view('resident.dashboard', compact('documents'));
        })->name('dashboard');
        // Email & Notification Preferences
        Route::post('/email/send-otp', [ProfileController::class, 'sendEmailOtp'])->name('email.send');
        Route::post('/email/verify-otp', [ProfileController::class, 'verifyEmailOtp'])->name('email.verify');
        Route::post('/email/add', [ProfileController::class, 'addEmail'])->name('email.add');
        Route::post('/settings/email-preference', [ProfileController::class, 'updateEmailPreference'])->name('settings.email_preference');

        // Service Requests (Tinanggal ang sobrang 'resident.' para hindi mag-doble)
        Route::get('/request/create', [ServiceRequestController::class, 'create'])->name('request.create');
        Route::post('/request', [ServiceRequestController::class, 'store'])->name('request.store');
    });
});

// ADMIN ROUTES
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});