<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function create()
    {
        return view('resident.request-form');
    }
    
    public function store(Request $request) 
    {
        // Pansamantalang idederetso natin pabalik para ma-test ang UI
        return back(); 
    }
}
