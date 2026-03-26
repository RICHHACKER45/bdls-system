<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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