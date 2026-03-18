<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome'); // a welcome page
});

Route::get('/login', function () {
    return view('auth.login'); // goes to login page
});

Route::get('/signup', function () {
    return view('auth.signup'); 
});