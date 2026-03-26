@extends('resident.layouts.app')

@section('title', 'Dashboard')

@section('content')

<!-- ===================================== -->
<!-- TAB 1: DASHBOARD CONTENT              -->
<!-- ===================================== -->
<div id="tab-dashboard" class="tab-content">
    
    <!-- OPTIONAL EMAIL VERIFICATION BANNER (Blue Accent) -->
    <!-- Lalabas lang ito kung naglagay siya ng email sa registration pero hindi pa verified -->
    @if(Auth::user()->email && !Auth::user()->email_verified_at)
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 sm:p-5 rounded-r-xl shadow-sm mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-blue-800 font-bold text-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    I-verify ang iyong Email
                </h3>
                <p class="text-blue-600 text-sm mt-1">
                    Kasalukuyang naka-link ang <span class="font-bold">{{ Auth::user()->email }}</span>. I-verify ito para makatanggap ng digital receipts ng iyong mga request.
                </p>
            </div>
            <!-- Pansamantalang # muna ang form action hanggang magawa natin ang controller function -->
            <form action="#" method="POST" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold text-sm active:scale-95 transition-all shadow-sm">
                    I-verify Ngayon
                </button>
            </form>
        </div>
    @endif

    <h1 class="text-2xl font-bold text-slate-900 mb-6">Resident Dashboard</h1>

    <!-- THE WAITING ROOM SHIELD -->
    @if(!Auth::user()->is_verified)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-6 rounded-r-xl shadow-sm mb-6">
            <div class="flex items-center gap-3 text-amber-800 font-bold text-lg mb-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Account is Under Review
            </div>
            <p class="text-amber-700 text-sm">
                Kasalukuyang sinusuri ng Barangay Administrator ang iyong Valid ID at Selfie. Hindi ka pa maaaring mag-request ng dokumento hangga't hindi ito naaaprubahan.
            </p>
        </div>
    @else
        <!-- VERIFIED DASHBOARD CONTENT (60/30/10 Rule) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- PRIMARY ACTION: Request Document (10% Red Accent) -->
            <div class="col-span-1 lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 flex flex-col justify-between">
                <div>
                    <div class="w-14 h-14 bg-red-100 text-red-600 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">Kumuha ng Dokumento</h2>
                    <p class="text-slate-500 mb-8 max-w-md">
                        Mag-request ng Barangay Clearance, Certificate of Indigency, at iba pang legal na papel nang hindi na pumipila nang matagal.
                    </p>
                </div>
                <div>
                    <a href="#" class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 px-6 rounded-xl transition-all active:scale-95 shadow-md hover:shadow-lg w-full sm:w-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Gumawa ng Bagong Request
                    </a>
                </div>
            </div>

            <!-- SECONDARY ACTIONS / STATS (Right Side) -->
            <div class="flex flex-col gap-6">
                <!-- Track Requests Widget -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-bold text-slate-700 mb-1">Pending Requests</h3>
                    <div class="flex items-end gap-3 mb-4">
                        <span class="text-4xl font-extrabold text-slate-900">0</span>
                        <span class="text-sm text-slate-500 mb-1 pb-1">dokumento</span>
                    </div>
                    <a href="#" class="text-sm font-bold text-red-600 hover:text-red-700 hover:underline flex items-center gap-1">
                        Tingnan ang status 
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
                
                <!-- Approved Documents Widget -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-bold text-slate-700 mb-1">Ready for Pick-up</h3>
                    <div class="flex items-end gap-3 mb-4">
                        <span class="text-4xl font-extrabold text-slate-900">0</span>
                        <span class="text-sm text-slate-500 mb-1 pb-1">dokumento</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- ===================================== -->
<!-- TAB 2: SETTINGS CONTENT (Nakatago)    -->
<!-- ===================================== -->
<div id="tab-settings" class="tab-content hidden">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Account Settings</h1>
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Email Verification Form</h2>
        <p class="text-slate-500 text-sm mb-6">Dito natin ilalagay ang logic para makapag-add o makapag-verify ng email ang user nang hindi umaalis sa dashboard.</p>
        <!-- Bubuin natin ang laman nito sa susunod na step! -->
    </div>
</div>

@endsection