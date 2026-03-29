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
            <!-- SPA NAVIGATION: Lilipat lang ng tab, hindi mag-i-spam ng API -->
            <div class="w-full sm:w-auto">
                <button type="button" onclick="switchTab('settings')" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold text-sm active:scale-95 transition-all shadow-sm">
                    Pumunta sa Settings
                </button>
            </div>
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
<!-- TAB 2: SETTINGS CONTENT (SPA)         -->
<!-- ===================================== -->
<div id="tab-settings" class="tab-content hidden">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Account Settings</h1>
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 space-y-8">
        
        <!-- SPA MESSAGES -->
        @if(session('success') && session('active_tab') == 'settings')
            <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg text-sm text-green-700 font-medium shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->has('email_otp') || $errors->has('email'))
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-sm text-red-700 font-medium shadow-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- 1. PRIMARY CONTACT SECTION (Nasa itaas na) -->
        <div>
            <h2 class="text-lg font-bold text-slate-900 mb-3">Primary Contact Number</h2>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <input type="text" value="{{ Auth::user()->contact_number }}" disabled class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg block w-full sm:w-80 p-2.5 font-bold">
                <span class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700 w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Verified
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-2">Dito ipapadala ang mga pangunahing SMS notifications para sa iyong mga request.</p>
        </div>

        <hr class="border-slate-100">

        <!-- 2. EMAIL FALLBACK SECTION -->
        <div>
            <h2 class="text-lg font-bold text-slate-900 mb-3">Email Address</h2>
            
            @if(!Auth::user()->email)
                <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-xs text-slate-500 mb-3">Wala kang nakarehistrong email. Magdagdag upang makatanggap ng digital receipts para sa iyong mga request.</p>
                    <form action="{{ route('resident.email.add') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                        @csrf
                        <input type="email" name="new_email" placeholder="juan@email.com" required class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-2 focus:ring-slate-900 block w-full sm:w-64 p-2.5 outline-none">
                        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 px-6 rounded-lg text-sm transition-all active:scale-95">I-save</button>
                    </form>
                </div>
            @else
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                    <input type="email" value="{{ Auth::user()->email }}" disabled class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg block w-full sm:w-80 p-2.5">
                    
                    @if(Auth::user()->email_verified_at)
                        <span class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700 w-full sm:w-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Verified
                        </span>
                    @else
                        <span class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 w-full sm:w-auto">Unverified</span>
                    @endif
                </div>

                <!-- OTP VERIFICATION UI (Kung may email pero di pa verified) -->
                @if(!Auth::user()->email_verified_at)
                    <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl">
                        <h3 class="text-sm font-bold text-slate-800 mb-2">I-verify ang iyong Email</h3>

                        <form action="{{ route('resident.email.verify') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                            @csrf
                            <input type="text" name="email_otp" maxlength="6" placeholder="000000" class="bg-white border border-slate-300 text-center text-lg font-bold rounded-lg block w-full sm:w-40 p-2.5 tracking-[0.3em] outline-none focus:ring-2 focus:ring-slate-900">
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 px-6 rounded-lg text-sm w-full sm:w-auto transition-all active:scale-95">Verify</button>
                        </form>

                        @php $cooldown = \Illuminate\Support\Facades\RateLimiter::availableIn('resend_email_otp_' . Auth::id()); @endphp
                        <form action="{{ route('resident.email.send') }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" id="resendBtn" class="text-xs font-bold text-slate-600 hover:text-slate-900 hover:underline disabled:text-slate-400 disabled:no-underline disabled:cursor-not-allowed transition-all">
                                Magpadala ng bagong code <span id="timerDisplay" class="text-red-600 ml-1 font-mono"></span>
                            </button>
                        </form>

                        <!-- SCRIPTS PARA SA TIMER -->
                        @if($cooldown > 0)
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                let secondsLeft = {{ $cooldown }};
                                const btn = document.getElementById('resendBtn');
                                const timerDisplay = document.getElementById('timerDisplay');
                                btn.disabled = true;
                                const countdown = setInterval(() => {
                                    timerDisplay.innerText = `(${secondsLeft}s)`;
                                    secondsLeft--;
                                    if (secondsLeft < 0) { clearInterval(countdown); btn.disabled = false; timerDisplay.innerText = ''; }
                                }, 1000);
                            });
                        </script>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <hr class="border-slate-100">

        <!-- 3. NOTIFICATION PREFERENCES SECTION -->
        <div>
            <h2 class="text-lg font-bold text-slate-900 mb-4">Notification Preferences</h2>
            <p class="text-sm text-slate-500 mb-4">Piliin kung saan mo gustong makatanggap ng mga update para sa iyong mga service requests.</p>

            <div class="space-y-3">
                <!-- SMS Toggle (Always On dahil ito ang main requirement sa system mo) -->
                <label class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed opacity-80">
                    <div>
                        <p class="font-bold text-slate-800 text-sm">SMS Notifications</p>
                        <p class="text-xs text-slate-500">Pangunahing channel para sa mabilis na updates (Required).</p>
                    </div>
                    <input type="checkbox" checked disabled class="w-5 h-5 accent-slate-900">
                </label>

                <!-- Email Toggle (Para sa Opt-in Fallback na nakasulat sa Scope and Delimitation) -->
                @if(Auth::user()->email_verified_at)
                    <!-- GUMAGANANG TOGGLE (Makikita lang kung Verified na ang Email) -->
                    <form action="{{ route('resident.settings.email_preference') }}" method="POST">
                        @csrf
                        <label class="flex items-center justify-between p-4 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-all shadow-sm">
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Email / Digital Receipts</p>
                                <p class="text-xs text-slate-500">Makatanggap ng kopya ng updates sa iyong email.</p>
                            </div>
                            <!-- Ang onchange event ay nagpapa-submit agad ng form pagka-click (UX trick) -->
                            <input 
                                type="checkbox" 
                                name="wants_email_notification" 
                                value="1"
                                onchange="this.form.submit()" 
                                {{ Auth::user()->wants_email_notification ? 'checked' : '' }}
                                class="w-5 h-5 accent-slate-900 cursor-pointer"
                            >
                        </label>
                    </form>
                @else
                    <!-- NAKA-DISABLE NA TOGGLE (Makikita kung Walang Email o Unverified) -->
                    <label class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed opacity-80">
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Email / Digital Receipts</p>
                            <p class="text-xs font-bold text-amber-600 mt-1">⚠️ I-verify muna ang iyong email sa itaas upang magamit ito.</p>
                        </div>
                        <input type="checkbox" disabled class="w-5 h-5 accent-slate-400 cursor-not-allowed">
                    </label>
                @endif
            </div>
        </div>

    </div>
</div>
<!-- ===================================== -->
<!-- TAB STATE RETENTION SCRIPT            -->
<!-- ===================================== -->
@if(session('active_tab'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Babasahin ng JS ang ibinato ng Controller at awtomatikong lilipat sa tab na iyon
            switchTab("{{ session('active_tab') }}");
        });
    </script>
@endif
@endsection