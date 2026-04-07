<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * THE SMART TRAFFIC DIRECTOR: Handle the root landing page.
     * Inalis ang logic sa routes/web.php para sa better caching support.
     */
    public function index()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('resident.dashboard');
        }
        
        return view('welcome');
    }
}
