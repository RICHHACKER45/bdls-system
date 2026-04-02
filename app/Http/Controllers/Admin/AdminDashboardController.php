<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // DAGDAG: Para mabasa ang Auth

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. SECURITY LENS: Ito yung inilipat natin mula sa web.php
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized. Para lamang ito sa mga Barangay Admins.');
        }

        // 2. THE LARAVEL WAY: Kunin ang mga pending registrations
        $pendingAccounts = User::where('role', 'resident')
                               ->where('is_verified', false)
                               ->latest()
                               ->get();

        // 3. I-return ang view kasama ang data
        // (Gamitin ang 'Admin.dashboard' kung capital A ang folder mo sa resources/views)
        return view('admin.dashboard', compact('pendingAccounts'));
    }

     // BAGONG FUNCTION PARA SA AJAX POLLING
    public function checkPendingCount()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $count = User::where('role', 'resident')
                     ->where('is_verified', false)
                     ->count();

        return response()->json(['count' => $count]);
    }
}