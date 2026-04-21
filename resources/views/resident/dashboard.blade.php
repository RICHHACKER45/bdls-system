@extends ('resident.layouts.app')

@section ('title', 'Dashboard')

@section ('content')
    <!-- ===================================== -->
    <!-- TAB 1: DASHBOARD CONTENT              -->
    <!-- ===================================== -->
    <div id="tab-dashboard" class="tab-content hidden">
        @if (Auth::user()->email && !Auth::user()->email_verified_at)
            <div class="mb-8 flex flex-col items-start justify-between gap-4 rounded-r-xl border-l-4 border-blue-500 bg-blue-50 p-4 shadow-sm sm:flex-row sm:items-center sm:p-5">
                <div>
                    <h3 class="flex items-center gap-2 text-lg font-bold text-blue-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        I-verify ang iyong Email
                    </h3>
                    <p class="mt-1 text-sm text-blue-600">Kasalukuyang naka-link ang <span class="font-bold">{{ Auth::user()->email }}</span>. I-verify ito para makatanggap ng digital receipts.</p>
                </div>
                <div class="w-full sm:w-auto">
                    <button type="button" onclick="switchTab('settings')" class="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-blue-700 active:scale-95 sm:w-auto">Pumunta sa Settings</button>
                </div>
            </div>
        @endif

        <h1 class="mb-6 text-2xl font-bold text-slate-900">Resident Dashboard</h1>

        @if (!Auth::user()->is_verified)
            @if (Auth::user()->rejection_count > 0)
                <!-- Rejection Alert -->
                <div class="mb-6 rounded-r-xl border-l-4 border-red-500 bg-red-50 p-6 shadow-sm">
                    <div class="mb-2 flex items-center gap-3 text-lg font-bold text-red-800">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Registration Rejected
                    </div>
                    <p class="mb-4 text-sm text-red-700">Your request is rejected. Reason: <span class="font-bold underline">{{ Auth::user()->rejection_reason }}</span>. You still have {{ 5 - Auth::user()->rejection_count }} attempts to request.</p>
                    <button type="button" onclick="openResubmitModal()" class="rounded-lg bg-red-600 px-5 py-2.5 text-xs font-bold tracking-widest text-white uppercase shadow-sm transition-all hover:bg-red-700 active:scale-95">Re-upload Requirements</button>
                </div>
            @else
                <!-- Under Review Alert -->
                <div class="mb-6 rounded-r-xl border-l-4 border-amber-500 bg-amber-50 p-6 shadow-sm">
                    <div class="mb-2 flex items-center gap-3 text-lg font-bold text-amber-800">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Account is Under Review
                    </div>
                    <p class="text-sm text-amber-700">Kasalukuyang sinusuri ng Barangay Administrator ang iyong Valid ID at Selfie. Hindi ka pa maaaring mag-request ng dokumento hangga't hindi ito naaaprubahan.</p>
                </div>
            @endif
        @else
            <!-- THE FIX: Trimmed Dashboard Layout (No Whitespace) -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <!-- LEFT COLUMN (Span 2): Request Card + Announcements -->
                <div class="col-span-1 flex flex-col gap-4 lg:col-span-2">
                    <!-- 1. REQUEST CARD -->
                    <div class="flex flex-col justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm md:p-8">
                        <div>
                            <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-red-100 text-red-600">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h2 class="mb-2 text-2xl font-bold text-slate-900">Kumuha ng Dokumento</h2>
                            <p class="mb-8 max-w-md text-slate-500">Mag-request ng Barangay Clearance, Certificate of Indigency, at iba pang legal na papel nang hindi na pumipila nang matagal.</p>
                        </div>
                        <div>
                            @if (Auth::user()->locked_until && Auth::user()->locked_until > now())
                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-center">
                                    <p class="text-sm font-bold text-amber-700">⚠️ Naka-Suspend ang iyong account.</p>
                                    <p class="mt-1 text-xs text-amber-600">Maaari ka muling mag-request sa: <br />
                                    <span class="font-black">{{ Auth::user()->locked_until->format('M d, Y h:i A') }}</span></p>
                                </div>
                            @else
                                <button onclick="openRequestModal()" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-6 py-3.5 font-bold text-white shadow-md transition-all hover:bg-red-700 focus:outline-none active:scale-95 sm:w-auto">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Gumawa ng Bagong Request
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- 2. BARANGAY ANNOUNCEMENTS (Isiniksik sa ilalim para walang blank space) -->
                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 flex items-center gap-2 font-bold text-slate-700">
                            <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                            Mga Anunsyo ng Barangay
                        </h3>
                        <div class="space-y-3">
                            @if (isset($announcements) && $announcements->count() > 0)
                                @foreach ($announcements as $announcement)
                                    <div class="rounded-lg border border-red-100 bg-red-50/60 p-4 transition-all hover:bg-red-50">
                                        <p class="mb-1 text-[9px] font-black tracking-widest text-red-600 uppercase">{{ $announcement->created_at->format('M d, Y h:i A') }}</p>
                                        <p class="text-xs font-bold text-slate-800">{{ $announcement->message_body }}</p>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-xs font-bold text-slate-400 italic">Walang bagong anunsyo.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN (Span 1): Active Requests + History -->
                <div class="flex flex-col gap-4">
                    <!-- 1. COMPRESSED ACTIVE REQUESTS SUMMARY -->
                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 font-bold text-slate-700">Active Requests</h3>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <button
                                onclick="
                                    switchTab('tracking');
                                    setTimeout(() => document.getElementById('btn-track-pending').click(), 100);
                                "
                                class="flex flex-col items-center justify-center rounded-xl border border-yellow-200 bg-yellow-50 py-3 transition-all hover:bg-yellow-100 active:scale-95"
                            >
                                <span class="text-2xl font-black text-yellow-600">{{ $myRequests->where('status', 'pending')->count() }}</span>
                                <span class="mt-1 text-[8px] font-bold tracking-widest text-yellow-700 uppercase">Pending</span>
                            </button>
                            <button
                                onclick="
                                    switchTab('tracking');
                                    setTimeout(() => document.getElementById('btn-track-status').click(), 100);
                                "
                                class="flex flex-col items-center justify-center rounded-xl border border-blue-200 bg-blue-50 py-3 transition-all hover:bg-blue-100 active:scale-95"
                            >
                                <span class="text-2xl font-black text-blue-600">{{ $myRequests->whereIn('status', ['processing', 'for_interview'])->count() }}</span>
                                <span class="mt-1 text-[8px] font-bold tracking-widest text-blue-700 uppercase">Process</span>
                            </button>
                            <button
                                onclick="
                                    switchTab('tracking');
                                    setTimeout(() => document.getElementById('btn-track-status').click(), 100);
                                "
                                class="flex flex-col items-center justify-center rounded-xl border border-orange-200 bg-orange-50 py-3 transition-all hover:bg-orange-100 active:scale-95"
                            >
                                <span class="text-2xl font-black text-orange-600">{{ $readyRequests->count() }}</span>
                                <span class="mt-1 text-[8px] font-bold tracking-widest text-orange-700 uppercase">Ready</span>
                            </button>
                        </div>
                        <p class="mt-4 text-center text-[10px] font-medium text-slate-400">I-click ang mga numero upang makita ang detalye.</p>
                    </div>

                    <!-- 2. RECENT HISTORY BOX (Top 3 Only) -->
                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 font-bold text-slate-700">Recent History</h3>
                        <div class="space-y-3">
                            @forelse ($historyRequests->where('status', 'received')->take(3) as $req)
                                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 p-3">
                                    <div class="opacity-75">
                                        <p class="text-xs font-black text-slate-900 uppercase">{{ $req->queue_number }}</p>
                                        <p class="text-[10px] font-bold text-slate-500">{{ $req->documentType->name ?? 'Dokumento' }}</p>
                                    </div>
                                    <span class="rounded border border-green-200 bg-green-100 px-2 py-1 text-[9px] font-black tracking-widest text-green-700 uppercase shadow-sm"> {{ $req->status }} </span>
                                </div>
                            @empty
                                <p class="text-xs font-bold text-slate-400 italic">Wala ka pang nakaraang transaksyon.</p>
                            @endforelse
                        </div>
                        @if ($historyRequests->where('status', 'received')->count() > 3)
                            <button
                                onclick="
                                    switchTab('tracking');
                                    setTimeout(() => document.getElementById('btn-track-history').click(), 100);
                                "
                                class="mt-4 w-full rounded-lg bg-slate-100 py-2 text-[10px] font-black tracking-widest text-slate-600 uppercase transition-all hover:bg-slate-200 active:scale-95"
                            >
                                Tingnan Lahat
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- ===================================== -->
    <!-- TAB 2: SETTINGS CONTENT (Modal-Driven)-->
    <!-- ===================================== -->
    <div id="tab-settings" class="tab-content {{ session('active_tab') == 'settings' ? 'block' : 'hidden' }}">
        <h1 class="mb-6 text-2xl font-bold text-slate-900">Account Settings</h1>

        <div class="space-y-8 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm md:p-8">
            @if (session('success') && session('active_tab') == 'settings')
                <div class="rounded-r-lg border-l-4 border-green-500 bg-green-50 p-4 text-sm font-medium text-green-700 shadow-sm">{{ session('success') }}</div>
            @endif
            @if ($errors->any() && session('active_tab') == 'settings')
                <div class="mb-4 rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4 text-sm font-medium text-red-700 shadow-sm">{{ $errors->first() }}</div>
            @endif

            <!-- CONTACT NUMBER BOX -->
            <div>
                <h2 class="mb-3 text-lg font-bold text-slate-900">Primary Contact Number</h2>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="block w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm font-bold text-slate-700 sm:w-80">{{ Auth::user()->contact_number }}</div>

                    @if (Auth::user()->contact_verified_at)
                        <span class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-green-100 px-3 py-1.5 text-xs font-bold text-green-700 sm:w-auto">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Verified
                        </span>
                        @if (Auth::user()->contact_verified_at->copy()->addDays(30)->isPast())
                            <button onclick="openSettingsModal('changeContactModal')" class="w-full rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 transition-all hover:bg-slate-50 active:scale-95 sm:w-auto">Palitan</button>
                        @else
                            <span class="flex items-center justify-center gap-1 text-[10px] font-bold text-slate-400 italic sm:justify-start">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Locked hanggang {{ Auth::user()->contact_verified_at->copy()->addDays(30)->format('M d') }}
                            </span>
                        @endif
                    @else
                        <span class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700 sm:w-auto">Unverified</span>
                        <button onclick="openSettingsModal('verifyContactModal')" class="w-full animate-pulse rounded-lg bg-red-600 px-6 py-2.5 text-sm font-bold text-white transition-all hover:bg-red-700 active:scale-95 sm:w-auto">Verify OTP</button>
                        <button onclick="openSettingsModal('changeContactModal')" class="w-full rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 transition-all hover:bg-slate-50 active:scale-95 sm:w-auto">Palitan</button>
                    @endif
                </div>
                <p class="mt-2 text-xs text-slate-500">Ito ang iyong login ID at channel para sa SMS updates.</p>
            </div>

            <hr class="border-slate-100" />

            <!-- EMAIL ADDRESS BOX -->
            <div>
                <h2 class="mb-3 text-lg font-bold text-slate-900">Email Address</h2>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="block w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm {{ Auth::user()->email ? 'text-slate-700 font-bold' : 'text-slate-400 italic' }} sm:w-80">{{ Auth::user()->email ?? 'Walang nakarehistrong email.' }}</div>

                    @if (Auth::user()->email)
                        @if (Auth::user()->email_verified_at)
                            <span class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-green-100 px-3 py-1.5 text-xs font-bold text-green-700 sm:w-auto">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Verified
                            </span>
                            @if (Auth::user()->email_verified_at->copy()->addDays(30)->isPast())
                                <button onclick="openSettingsModal('changeEmailModal')" class="w-full rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 transition-all hover:bg-slate-50 active:scale-95 sm:w-auto">Palitan</button>
                            @else
                                <span class="flex items-center justify-center gap-1 text-[10px] font-bold text-slate-400 italic sm:justify-start">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Locked hanggang {{ Auth::user()->email_verified_at->copy()->addDays(30)->format('M d') }}
                                </span>
                            @endif
                        @else
                            <span class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700 sm:w-auto">Unverified</span>
                            <button onclick="openSettingsModal('verifyEmailModal')" class="w-full animate-pulse rounded-lg bg-red-600 px-6 py-2.5 text-sm font-bold text-white transition-all hover:bg-red-700 active:scale-95 sm:w-auto">Verify OTP</button>
                        @endif
                    @else
                        <button onclick="openSettingsModal('changeEmailModal')" class="w-full rounded-lg bg-slate-900 px-6 py-2.5 text-sm font-bold text-white transition-all hover:bg-slate-800 active:scale-95 sm:w-auto">Magdagdag ng Email</button>
                    @endif
                </div>
                <p class="mt-2 text-xs text-slate-500">Optional: Para makatanggap ng kopya ng digital receipts.</p>
            </div>

            <hr class="border-slate-100" />

            <!-- PASSWORD SECURITY -->
            <div>
                <h2 class="mb-4 text-lg font-bold text-slate-900">Security Settings</h2>
                <form action="{{ route('password.update') }}" method="POST" class="max-w-md space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-700">Kasalukuyang Password</label>
                        <input type="password" name="current_password" required class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition-all outline-none focus:ring-2 focus:ring-slate-900" />
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-bold text-slate-700">Bagong Password</label>
                            <input type="password" name="password" minlength="8" required class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition-all outline-none focus:ring-2 focus:ring-slate-900" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-bold text-slate-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" minlength="8" required class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition-all outline-none focus:ring-2 focus:ring-slate-900" />
                        </div>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-slate-900 px-8 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 sm:w-auto">I-save Password</button>
                </form>
            </div>
        </div>
    </div>
    <!-- ========================================== -->
    <!-- SETTINGS MODALS (Put these before scripts) -->
    <!-- ========================================== -->
    <!-- 1. CHANGE CONTACT MODAL -->
    <div id="changeContactModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
            <h3 class="mb-2 text-lg font-black text-slate-900 uppercase">Palitan ang Numero</h3>
            <p class="mb-6 text-sm text-slate-500">I-type ang iyong bagong 11-digit mobile number.</p>
            <form action="{{ route('resident.settings.update_contact') }}" method="POST">
                @csrf
                <!-- THE FIX 1: Corrected Regex Pattern (09[1-9]{9}) -->
                <input type="tel" name="contact_number" required pattern="09[1-9]{9}" maxlength="11" placeholder="09XXXXXXXXX" class="mb-6 w-full rounded-lg border border-slate-300 bg-slate-50 p-3 text-center font-mono text-xl font-bold tracking-widest outline-none focus:ring-2 focus:ring-slate-900" />
                <div class="flex gap-2">
                    <button type="button" onclick="closeSettingsModal('changeContactModal')" class="flex-1 rounded-xl bg-slate-200 py-3 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95">Cancel</button>
                    <button type="submit" class="flex-1 rounded-xl bg-slate-900 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95">I-Save</button>
                </div>
            </form>
        </div>
    </div>
    <!-- 2. VERIFY CONTACT OTP MODAL -->
    <div id="verifyContactModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
            <h3 class="mb-2 text-lg font-black text-slate-900 uppercase">Verify Number</h3>
            <p class="mb-4 text-sm text-slate-500">I-enter ang 6-digit OTP na ipinadala sa {{ Auth::user()->contact_number }}.</p>

            @if ($errors->has('otp_error'))
                <div class="mb-4 rounded-lg bg-red-50 p-3 text-xs font-bold text-red-600">{{ $errors->first('otp_error') }}</div>
            @endif
            <!-- Kapag tinry i-spam ang resend at naharang ng RateLimiter -->
            @if ($errors->has('contact_number'))
                <div class="mb-4 rounded-lg bg-red-50 p-3 text-xs font-bold text-red-600">{{ $errors->first('contact_number') }}</div>
            @endif

            <!-- Form 1: The Verification Submit -->
            <form action="{{ route('resident.settings.verify_contact') }}" method="POST">
                @csrf
                <input type="text" name="otp_code" maxlength="6" required placeholder="000000" class="mb-4 w-full rounded-lg border {{ $errors->has('otp_error') ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300' }} bg-slate-50 p-3 text-center font-mono text-3xl font-bold tracking-[0.3em] outline-none focus:ring-2 focus:ring-slate-900" />
                <div class="mb-4 flex gap-2">
                    <button type="button" onclick="closeSettingsModal('verifyContactModal')" class="flex-1 rounded-xl bg-slate-200 py-3 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95">Cancel</button>
                    <button type="submit" class="flex-1 rounded-xl bg-red-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95">Verify OTP</button>
                </div>
            </form>

            <!-- THE FIX 2: Ang Nawawalang Resend Form para sa Contact Number -->
            <div class="border-t border-slate-100 pt-4 text-center">
                <form action="{{ route('resident.settings.update_contact') }}" method="POST" id="resendContactOtpForm">
                    @csrf
                    <!-- Lihim na isasama ang current number para ma-trigger ang 'update' route bilang Resend -->
                    <input type="hidden" name="contact_number" value="{{ Auth::user()->contact_number }}" />
                    <button type="submit" id="resendContactBtn" class="text-xs font-bold text-slate-500 transition-all hover:text-slate-900 hover:underline">Resend SMS Code <span id="timerContactDisplay" class="font-mono text-red-600"></span></button>
                </form>
            </div>
        </div>
    </div>
    <!-- 3. CHANGE EMAIL MODAL -->
    <div id="changeEmailModal" class="fixed inset-0 z-[100] flex hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
            <h3 class="mb-2 text-lg font-black text-slate-900 uppercase">I-setup ang Email</h3>
            <p class="mb-6 text-sm text-slate-500">Makatatanggap ka ng verification code sa email na ito.</p>
            <form action="{{ route('resident.email.add') }}" method="POST">
                @csrf
                <input type="email" name="new_email" required placeholder="juan@email.com" class="mb-6 w-full rounded-lg border border-slate-300 bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-slate-900" />
                <div class="flex gap-2">
                    <button type="button" onclick="closeSettingsModal('changeEmailModal')" class="flex-1 rounded-xl bg-slate-200 py-3 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95">Cancel</button>
                    <button type="submit" class="flex-1 rounded-xl bg-slate-900 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95">I-Save</button>
                </div>
            </form>
        </div>
    </div>
    <!-- 4. VERIFY EMAIL OTP MODAL -->
    <div id="verifyEmailModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
            <h3 class="mb-2 text-lg font-black text-slate-900 uppercase">Verify Email</h3>
            <p class="mb-4 text-sm text-slate-500">I-enter ang 6-digit code na ipinadala sa iyong email.</p>

            <!-- THE FIX: Nasa loob na ng modal ang error -->
            @if ($errors->has('email_otp'))
                <div class="mb-4 rounded-lg bg-red-50 p-3 text-xs font-bold text-red-600">{{ $errors->first('email_otp') }}</div>
            @endif

            <!-- Form 1: The Verification Submit -->
            <form action="{{ route('resident.email.verify') }}" method="POST">
                @csrf
                <input type="text" name="email_otp" maxlength="6" required placeholder="000000" class="mb-4 w-full rounded-lg border {{ $errors->has('email_otp') ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300' }} bg-slate-50 p-3 text-center font-mono text-3xl font-bold tracking-[0.3em] outline-none focus:ring-2 focus:ring-slate-900" />
                <div class="mb-4 flex gap-2">
                    <button type="button" onclick="closeSettingsModal('verifyEmailModal')" class="flex-1 rounded-xl bg-slate-200 py-3 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95">Cancel</button>
                    <button type="submit" class="flex-1 rounded-xl bg-red-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95">Verify OTP</button>
                </div>
            </form>

            <!-- Form 2: The Resend Form (May Timer Na!) -->
            <div class="border-t border-slate-100 pt-4 text-center">
                <form action="{{ route('resident.email.send') }}" method="POST" id="resendOtpForm">
                    @csrf
                    <button type="submit" id="resendEmailBtn" class="text-xs font-bold text-slate-500 transition-all hover:text-slate-900 hover:underline">Resend Email Code <span id="timerEmailDisplay" class="font-mono text-red-600"></span></button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function openSettingsModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeSettingsModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
                const form = modal.querySelector('form');
                if (form) form.reset();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // THE FIX: Clean JS Variables using Laravel json
            // Iniiwasan natin ang hilaw na arrow para hindi masira ang Syntax Highlighting
            const activeTab = @json (session('active_tab'));
            const hasContactError = @json ($errors->has('otp_error'));
            const hasEmailError = @json ($errors->has('email_otp'));
            const needsContactVerification = @json (Auth::user()->contact_number && !Auth::user()->contact_verified_at);
            const needsEmailVerification = @json (Auth::user()->email && !Auth::user()->email_verified_at);

            // Strict if-else Modal Router (Bawal ang overlap!)
            if (activeTab === 'settings') {
                if (hasContactError || needsContactVerification) {
                    openSettingsModal('verifyContactModal');
                } else if (hasEmailError || needsEmailVerification) {
                    openSettingsModal('verifyEmailModal');
                }
            }

            // THE FIX 3: 1-Minute Countdown Timers para sa parehong Email at Contact
            const emailCooldown = @json (\Illuminate\Support\Facades\RateLimiter::availableIn('resend_email_otp_' . Auth::id()) ?: 0);
            const contactCooldown = @json (\Illuminate\Support\Facades\RateLimiter::availableIn('update_contact_' . Auth::id()) ?: 0);

            function startTimer(duration, btnId, displayId) {
                if (duration <= 0) return;
                const btn = document.getElementById(btnId);
                const display = document.getElementById(displayId);
                if (!btn || !display) return;

                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed', 'no-underline');

                let timer = duration;
                const interval = setInterval(() => {
                    display.innerText = `(${timer}s)`;
                    timer--;
                    if (timer < 0) {
                        clearInterval(interval);
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed', 'no-underline');
                        display.innerText = '';
                    }
                }, 1000);
            }

            // I-activate ang timers sa magkabilang button
            startTimer(emailCooldown, 'resendEmailBtn', 'timerEmailDisplay');
            startTimer(contactCooldown, 'resendContactBtn', 'timerContactDisplay');
        });
    </script>
    <!-- ===================================== -->
    <!-- TAB 3: DEDICATED TRACKING TAB         -->
    <!-- ===================================== -->
    <div id="tab-tracking" class="tab-content hidden">
        <h1 class="mb-6 text-2xl font-bold text-slate-900">Track My Requests</h1>

        <!-- THE FIX: Sub-tab Buttons (With Rejected) -->
        <div class="mb-6 flex gap-2 overflow-x-auto border-b border-slate-100 pb-2">
            <button id="btn-track-pending" onclick="showResidentSubTab('track-pending', this)" class="res-sub-tab-btn rounded-full bg-slate-900 px-5 py-2 text-sm font-bold whitespace-nowrap text-white shadow-sm transition-all">Pending ({{ $myRequests->where('status', 'pending')->count() }})</button>
            <button id="btn-track-status" onclick="showResidentSubTab('track-status', this)" class="res-sub-tab-btn rounded-full bg-slate-100 px-5 py-2 text-sm font-bold whitespace-nowrap text-slate-600 transition-all hover:bg-slate-200">Status ({{ $myRequests->whereIn('status', ['processing', 'for_interview', 'released'])->count() }})</button>
            <button id="btn-track-history" onclick="showResidentSubTab('track-history', this)" class="res-sub-tab-btn rounded-full bg-slate-100 px-5 py-2 text-sm font-bold whitespace-nowrap text-slate-600 transition-all hover:bg-slate-200">Received ({{ $myRequests->where('status', 'received')->count() }})</button>
            <button id="btn-track-rejected" onclick="showResidentSubTab('track-rejected', this)" class="res-sub-tab-btn rounded-full bg-slate-100 px-5 py-2 text-sm font-bold whitespace-nowrap text-slate-600 transition-all hover:bg-slate-200">Rejected / Canceled ({{ $myRequests->whereIn('status', ['rejected', 'canceled'])->count() }})</button>
        </div>

        <!-- 1. PENDING SUB-TAB (MAY CANCEL BUTTON) -->
        <div id="track-pending" class="res-sub-tab-content block space-y-4">
            @forelse ($myRequests->where('status', 'pending') as $req)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="mb-2 flex items-center gap-3">
                                <span class="text-lg font-black tracking-tighter text-slate-900 uppercase">{{ $req->queue_number }}</span>
                                <span class="rounded-md border border-yellow-200 bg-yellow-100 px-2 py-1 text-[10px] font-black tracking-widest text-yellow-700 uppercase shadow-sm"> {{ str_replace('_', ' ', $req->status) }} </span>
                            </div>
                            <p class="text-sm font-bold text-slate-800">{{ $req->documentType->name ?? 'Dokumento' }}</p>
                            <p class="mt-1 text-xs text-slate-500"><span class="font-semibold">Layunin:</span> {{ $req->purpose }}</p>
                            <p class="text-xs text-slate-500"><span class="font-semibold">Petsa:</span> {{ $req->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <!-- THE CANCEL BUTTON (Visible only here!) -->
                        <form action="{{ route('resident.request.cancel', $req->id) }}" method="POST" class="shrink-0">
                            @csrf
                            <button type="submit" onclick="return confirm('Sigurado kang gusto mong i-cancel ang request na ito?');" class="flex w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-6 py-3 text-[10px] font-black tracking-widest text-red-600 uppercase shadow-sm transition-all hover:bg-red-100 active:scale-95 sm:w-auto">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Cancel Request
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-12 text-center">
                    <p class="font-bold text-slate-500 italic">Wala kang pending na request.</p>
                </div>
            @endforelse
        </div>

        <!-- 2. STATUS SUB-TAB (WALANG CANCEL BUTTON) -->
        <div id="track-status" class="res-sub-tab-content hidden space-y-4">
            @forelse ($myRequests->whereIn('status', ['processing', 'for_interview', 'released']) as $req)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="mb-2 flex items-center gap-3">
                                <span class="text-lg font-black tracking-tighter text-slate-900 uppercase">{{ $req->queue_number }}</span>
                                <span
                                    class="rounded-md px-2 py-1 text-[10px] font-black tracking-widest uppercase shadow-sm
                                {{ $req->status === 'processing' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                                {{ $req->status === 'for_interview' ? 'bg-purple-100 text-purple-700 border border-purple-200' : '' }}
                                {{ $req->status === 'released' ? 'bg-orange-100 text-orange-700 border border-orange-200' : '' }}
                            "
                                >
                                    {{ str_replace('_', ' ', $req->status) }}
                                </span>
                            </div>
                            <p class="text-sm font-bold text-slate-800">{{ $req->documentType->name ?? 'Dokumento' }}</p>
                            <p class="mt-1 text-xs text-slate-500"><span class="font-semibold">Layunin:</span> {{ $req->purpose }}</p>
                            <p class="text-xs text-slate-500"><span class="font-semibold">Petsa:</span> {{ $req->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-12 text-center">
                    <p class="font-bold text-slate-500 italic">Walang request na pinoproseso sa ngayon.</p>
                </div>
            @endforelse
        </div>

        <!-- 3. RECEIVED / HISTORY SUB-TAB -->
        <div id="track-history" class="res-sub-tab-content hidden space-y-4">
            @forelse ($historyRequests->where('status', 'received') as $req)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 opacity-80 transition-all hover:opacity-100">
                    <!-- (Iwanan ang laman ng div na ito kung paano man yan) -->
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="mb-2 flex items-center gap-3">
                                <span class="text-lg font-black tracking-tighter text-slate-600 uppercase">{{ $req->queue_number }}</span>
                                <span class="rounded-md border border-green-200 bg-green-100 px-2 py-1 text-[10px] font-black tracking-widest text-green-700 uppercase shadow-sm"> {{ $req->status }} </span>
                            </div>
                            <p class="text-sm font-bold text-slate-700">{{ $req->documentType->name ?? 'Dokumento' }}</p>
                            <p class="mt-1 text-xs text-slate-500"><span class="font-semibold">Petsa:</span> {{ $req->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-12 text-center">
                    <p class="font-bold text-slate-500 italic">Wala kang nakaraang transaksyon.</p>
                </div>
            @endforelse
        </div>
        <!-- 4. REJECTED / CANCELED SUB-TAB -->
        <div id="track-rejected" class="res-sub-tab-content hidden space-y-4">
            @forelse ($myRequests->whereIn('status', ['rejected', 'canceled']) as $req)
                <div class="rounded-xl border border-red-200 bg-red-50 p-5 transition-all">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="mb-2 flex items-center gap-3">
                                <span class="text-lg font-black tracking-tighter text-slate-600 uppercase">{{ $req->queue_number }}</span>
                                <span class="rounded-md border border-red-300 bg-red-100 px-2 py-1 text-[10px] font-black tracking-widest text-red-700 uppercase shadow-sm"> {{ $req->status }} </span>
                            </div>
                            <p class="text-sm font-bold text-red-900">{{ $req->documentType->name ?? 'Dokumento' }}</p>
                            <p class="mt-1 text-xs text-red-700"><span class="font-semibold">Layunin:</span> {{ $req->purpose }}</p>
                            <p class="text-xs text-red-700"><span class="font-semibold">Petsa:</span> {{ $req->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-12 text-center">
                    <p class="font-bold text-slate-500 italic">Wala kang rejected o canceled na request.</p>
                </div>
            @endforelse
        </div>
    </div>
    <!-- ===================================== -->
    <!-- TASK 4: RESUBMIT REQUIREMENTS MODAL   -->
    <!-- ===================================== -->
    <div id="resubmitModal" class="fixed inset-0 z-[100] flex hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md transform overflow-hidden rounded-2xl border border-red-100 bg-white shadow-2xl transition-all">
            <div class="flex items-center justify-between border-b border-red-100 bg-red-50 p-4 text-red-700">
                <h3 class="text-lg font-black tracking-tight uppercase">Resubmit Requirements</h3>
                <button onclick="closeResubmitModal()" class="text-2xl font-bold text-red-300 transition-all hover:text-red-700">&times;</button>
            </div>
            <form action="{{ route('resident.resubmit_registration') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="mb-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">Upload Valid ID</label>
                        <input type="file" name="id_photo_path" required class="w-full cursor-pointer text-xs text-slate-500 transition-all file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-xs file:font-black file:text-white hover:file:bg-slate-800" />
                    </div>
                    <div>
                        <label class="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">Upload Selfie with ID</label>
                        <input type="file" name="selfie_photo_path" required class="w-full cursor-pointer text-xs text-slate-500 transition-all file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-xs file:font-black file:text-white hover:file:bg-slate-800" />
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <button type="submit" class="w-full rounded-xl bg-slate-900 py-3.5 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95">Submit for Re-review</button>
                    <button type="button" onclick="closeResubmitModal()" class="w-full py-2 text-[10px] font-black tracking-widest text-slate-400 uppercase">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- ===================================== -->
    <!-- MODAL: SERVICE REQUEST FORM           -->
    <!-- ===================================== -->
    <div id="requestModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-opacity">
        <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Gumawa ng Request</h2>
                    <p class="mt-1 text-sm text-slate-500">Kumpletuhin ang detalye para sa iyong queue number.</p>
                </div>
                <button type="button" onclick="closeRequestModal()" class="rounded-full bg-slate-200 p-2 text-slate-400 transition-all hover:bg-red-100 hover:text-red-600 active:scale-95">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="overflow-y-auto p-6">
                <form action="{{ route('resident.request.store') }}" method="POST" id="requestForm" class="space-y-6" enctype="multipart/form-data">
                    @csrf

                    <!-- THE FIX: Laravel Form Validation Error Catcher -->
                    @if ($errors->hasAny(['document_type_id', 'purpose', 'preferred_pickup_time', 'attachments.*']))
                        <div class="mb-5 rounded-lg border-l-4 border-red-500 bg-red-50 p-4 shadow-sm">
                            <div class="mb-1 flex items-center gap-2 text-sm font-bold text-red-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                May Kulang o Mali sa Form
                            </div>
                            <p class="text-xs font-medium text-red-600">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <div class="rounded-xl border border-slate-200 bg-slate-100 p-5 shadow-inner">
                        <h3 class="mb-3 border-b border-slate-200 pb-2 text-sm font-bold text-slate-800">Impormasyon ng Nagre-request</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500">Buong Pangalan</label>
                                <p class="text-sm font-bold text-slate-900">{{ Auth::user()->first_name }} {{ Auth::user()->middle_name }} {{ Auth::user()->last_name }} {{ Auth::user()->suffix }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500">Edad</label>
                                <p class="text-sm font-bold text-slate-900">{{ Auth::user()->age }} taong gulang</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500">Tirahan</label>
                                <p class="text-sm font-bold text-slate-900">{{ Auth::user()->house_number }} {{ Auth::user()->purok_street }}, Barangay Doña Lucia</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-800">Uri ng Dokumento <span class="text-red-500">*</span></label>
                        <select name="document_type_id" id="document_type_id" required onchange="showRequirements(this)" class="w-full px-4 py-3 rounded-xl border @error('document_type_id') border-red-500 ring-1 ring-red-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all cursor-pointer">
                            <option value="">-- Pumili ng Dokumento --</option>
                            @foreach ($documents as $doc)
                                <option value="{{ $doc->id }}" data-reqs="{{ $doc->requirements_description }}" {{ old('document_type_id') == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                            @endforeach
                        </select>
                        @error ('document_type_id')
                            <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>
                        @enderror

                        <div id="requirements_box" class="mt-3 hidden rounded-lg border border-blue-200 bg-blue-50 p-4 shadow-inner transition-all">
                            <div class="mb-1 flex items-center gap-2">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm font-bold text-blue-800">Mga Kinakailangang Dalhin / I-upload:</p>
                            </div>
                            <p id="requirements_text" class="ml-7 text-sm font-medium text-blue-700"></p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-800">Layunin (Purpose) <span class="text-red-500">*</span></label>
                        <input type="text" name="purpose" value="{{ old('purpose') }}" placeholder="Hal. Requirement sa Trabaho..." required class="w-full px-4 py-3 rounded-xl border @error('purpose') border-red-500 ring-1 ring-red-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all" />
                        @error ('purpose')
                            <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-800">Kailan mo gustong kunin? <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="preferred_pickup_time" value="{{ old('preferred_pickup_time') }}" required class="w-full px-4 py-3 rounded-xl border @error('preferred_pickup_time') border-red-500 ring-1 ring-red-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all" />
                        @error ('preferred_pickup_time')
                            <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-800">Karagdagang Detalye (Optional)</label>
                        <textarea name="additional_details" rows="2" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 transition-all outline-none focus:ring-2 focus:ring-slate-900">{{ old('additional_details') }}</textarea>
                    </div>

                    <div id="upload_section" class="hidden p-5 bg-white border-2 border-dashed @error('attachments.*') border-red-500 bg-red-50 @else border-slate-300 @enderror rounded-xl">
                        <label class="mb-2 block text-sm font-bold text-slate-800">I-upload ang Karagdagang Dokumento <span class="text-red-500">*</span></label>
                        <input type="file" name="attachments[]" id="attachments" multiple accept="image/jpeg, image/png, image/jpg, application/pdf" class="w-full cursor-pointer text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-white hover:file:bg-slate-800" />
                        @error ('attachments.*')
                            <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 p-4">
                        <button type="button" onclick="closeRequestModal()" class="rounded-xl px-6 py-2.5 font-bold text-slate-600 transition-all hover:bg-slate-200 active:scale-95">Kanselahin</button>
                        <button type="submit" class="rounded-xl bg-slate-900 px-8 py-2.5 font-bold text-white shadow-md hover:bg-slate-800 active:scale-95">I-submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
