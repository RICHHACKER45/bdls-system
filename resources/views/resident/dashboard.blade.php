@extends ('resident.layouts.app')

@section ('title', 'Dashboard')

@section ('content')
    <!-- TAB 1: DASHBOARD CONTENT -->
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
                <!-- TASK 4: Functional Rejection Alert -->
                <div class="mb-6 rounded-r-xl border-l-4 border-red-500 bg-red-50 p-6 shadow-sm">
                    <div class="mb-2 flex items-center gap-3 text-lg font-bold text-red-800">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Registration Rejected
                    </div>
                    <p class="mb-4 text-sm text-red-700">Your request is rejected. Reason: <span class="font-bold underline">{{ Auth::user()->rejection_reason }}</span>. You still have {{ 5 - Auth::user()->rejection_count }} attempts to request.</p>
                    <button type="button" onclick="openResubmitModal()" class="rounded-lg bg-red-600 px-5 py-2.5 text-xs font-bold tracking-widest text-white uppercase shadow-sm transition-all hover:bg-red-700 active:scale-95">Re-upload Requirements</button>
                </div>
            @else
                <div class="mb-6 rounded-r-xl border-l-4 border-amber-500 bg-amber-50 p-6 shadow-sm">
                    <div class="mb-2 flex items-center gap-3 text-lg font-bold text-amber-800">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Account is Under Review
                    </div>
                    <p class="text-sm text-amber-700">Kasalukuyang sinusuri ng Barangay Administrator ang iyong Valid ID at Selfie. Hindi ka pa maaaring mag-request ng dokumento hangga't hindi ito naaaprubahan.</p>
                </div>
            @endif
        @else
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="col-span-1 flex flex-col justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm md:p-8 lg:col-span-2">
                    <div>
                        <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-red-100 text-red-600">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h2 class="mb-2 text-2xl font-bold text-slate-900">Kumuha ng Dokumento</h2>
                        <p class="mb-8 max-w-md text-slate-500">Mag-request ng Barangay Clearance, Certificate of Indigency, at iba pang legal na papel nang hindi na pumipila nang matagal.</p>
                    </div>
                    <div>
                        @if (Auth::user()->locked_until && Auth::user()->locked_until > now())
                            <!-- NAKA-LOCK KUNG MAY PENALTY -->
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-center">
                                <p class="text-sm font-bold text-amber-700">⚠️ Naka-Suspend ang iyong account.</p>
                                <p class="mt-1 text-xs text-amber-600">Maaari ka muling mag-request sa: <br />
                                <span class="font-black">{{ Auth::user()->locked_until->format('M d, Y h:i A') }}</span></p>
                            </div>
                        @else
                            <!-- NORMAL BUTTON KUNG WALANG PENALTY -->
                            <button onclick="openRequestModal()" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-6 py-3.5 font-bold text-white shadow-md transition-all hover:bg-red-700 focus:outline-none active:scale-95 sm:w-auto">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Gumawa ng Bagong Request
                            </button>
                        @endif
                    </div>
                </div>

                <!-- THE FIX: Dynamic Resident Dashboard Statistics & Lists -->
                <div class="flex flex-col gap-6">
                    <!-- PENDING BOX -->
                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 font-bold text-slate-700">Pending Requests ({{ $pendingRequests->count() }})</h3>
                        <div class="max-h-48 space-y-3 overflow-y-auto pr-2">
                            @forelse ($pendingRequests as $req)
                                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 p-3">
                                    <div>
                                        <p class="text-xs font-black text-slate-900 uppercase">{{ $req->queue_number }}</p>
                                        <p class="text-[10px] font-bold text-slate-500">{{ $req->documentType->name ?? 'Dokumento' }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded bg-slate-200 px-2 py-1 text-[9px] font-black tracking-widest text-slate-700 uppercase"> {{ str_replace('_', ' ', $req->status) }} </span>

                                        <!-- LILITAW LANG ANG CANCEL KUNG PENDING PA (Hindi pa nahawakan ni Admin) -->
                                        @if ($req->status === 'pending')
                                            <form action="{{ route('resident.request.cancel', $req->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Sigurado kang gusto mong i-cancel ang request na ito?');" class="rounded border border-slate-200 bg-white p-1.5 text-slate-400 shadow-sm transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600 active:scale-95" title="Kanselahin">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs font-bold text-slate-400 italic">Wala kang naka-pending na request.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- READY FOR PICK-UP BOX -->
                    <div class="rounded-2xl border border-l-4 border-slate-100 border-l-green-500 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 font-bold text-slate-700">Ready for Pick-up ({{ $readyRequests->count() }})</h3>
                        <div class="max-h-48 space-y-3 overflow-y-auto pr-2">
                            @forelse ($readyRequests as $req)
                                <div class="flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-3 shadow-sm">
                                    <div>
                                        <p class="text-xs font-black text-green-800 uppercase">{{ $req->queue_number }}</p>
                                        <p class="text-[10px] font-bold text-green-600">{{ $req->documentType->name ?? 'Dokumento' }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs font-bold text-slate-400 italic">Walang dokumentong pwedeng kunin.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- TAB 2: SETTINGS CONTENT -->
    <div id="tab-settings" class="tab-content hidden">
        <h1 class="mb-6 text-2xl font-bold text-slate-900">Account Settings</h1>

        <div class="space-y-8 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm md:p-8">
            @if (session('success') && session('active_tab') == 'settings')
                <div class="rounded-r-lg border-l-4 border-green-500 bg-green-50 p-4 text-sm font-medium text-green-700 shadow-sm">{{ session('success') }}</div>
            @endif
            @if ($errors->any() && session('active_tab') == 'settings')
                <div class="mb-4 rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4 text-sm font-medium text-red-700 shadow-sm">{{ $errors->first() }}</div>
            @endif

            <div>
                <h2 class="mb-3 text-lg font-bold text-slate-900">Primary Contact Number</h2>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <input type="text" value="{{ Auth::user()->contact_number }}" disabled class="block w-full rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm font-bold text-slate-700 sm:w-80" />
                    <span class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-green-100 px-3 py-1.5 text-xs font-bold text-green-700 sm:w-auto"
                        ><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Verified</span
                    >
                </div>
                <p class="mt-2 text-xs text-slate-500">Dito ipapadala ang mga pangunahing SMS notifications.</p>
            </div>

            <hr class="border-slate-100" />

            <div>
                <h2 class="mb-3 text-lg font-bold text-slate-900">Email Address</h2>
                @if (!Auth::user()->email)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <p class="mb-3 text-xs text-slate-500">Wala kang nakarehistrong email. Magdagdag upang makatanggap ng digital receipts.</p>
                        <form action="{{ route('resident.email.add') }}" method="POST" class="flex flex-col gap-2 sm:flex-row">
                            @csrf
                            <input type="email" name="new_email" placeholder="juan@email.com" required class="block w-full rounded-lg border border-slate-300 bg-white p-2.5 text-sm text-slate-900 outline-none focus:ring-2 focus:ring-slate-900 sm:w-64" />
                            <button type="submit" class="rounded-lg bg-slate-900 px-6 py-2.5 text-sm font-bold text-white transition-all hover:bg-slate-800 active:scale-95">I-save</button>
                        </form>
                    </div>
                @else
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input type="email" value="{{ Auth::user()->email }}" disabled class="block w-full rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm text-slate-700 sm:w-80" />
                        @if (Auth::user()->email_verified_at)
                            <span class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-green-100 px-3 py-1.5 text-xs font-bold text-green-700 sm:w-auto"
                                ><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Verified</span
                            >
                        @else
                            <span class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700 sm:w-auto">Unverified</span>
                        @endif
                    </div>
                    @if (!Auth::user()->email_verified_at)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                            <h3 class="mb-2 text-sm font-bold text-slate-800">I-verify ang iyong Email</h3>
                            <form action="{{ route('resident.email.verify') }}" method="POST" class="flex flex-col gap-3 sm:flex-row">
                                @csrf
                                <input type="text" name="email_otp" maxlength="6" placeholder="000000" class="block w-full rounded-lg border border-slate-300 bg-white p-2.5 text-center text-lg font-bold tracking-[0.3em] outline-none focus:ring-2 focus:ring-slate-900 sm:w-40" />
                                <button type="submit" class="w-full rounded-lg bg-slate-900 px-6 py-2.5 text-sm font-bold text-white transition-all hover:bg-slate-800 active:scale-95 sm:w-auto">Verify</button>
                            </form>
                            <form action="{{ route('resident.email.send') }}" method="POST" id="resendOtpForm" class="mt-3">
                                @csrf
                                <button type="submit" id="resendBtn" class="text-xs font-bold text-slate-600 transition-all hover:text-slate-900 hover:underline disabled:cursor-not-allowed disabled:text-slate-400 disabled:no-underline">
                                    Magpadala ng bagong code
                                    <span id="timerDisplay" class="ml-1 font-mono text-red-600"></span>
                                </button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>

            <hr class="border-slate-100" />

            <div>
                <h2 class="mb-4 text-lg font-bold text-slate-900">Notification Preferences</h2>
                <div class="space-y-3">
                    <label class="flex cursor-not-allowed items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4 opacity-80">
                        <div>
                            <p class="text-sm font-bold text-slate-800">SMS Notifications</p>
                            <p class="text-xs text-slate-500">Pangunahing channel para sa mabilis na updates (Required).</p>
                        </div>
                        <input type="checkbox" checked disabled class="h-5 w-5 accent-slate-900" />
                    </label>

                    @if (Auth::user()->email_verified_at)
                        <form action="{{ route('resident.settings.email_preference') }}" method="POST">
                            @csrf
                            <label class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-all hover:bg-slate-50">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Email / Digital Receipts</p>
                                    <p class="text-xs text-slate-500">Makatanggap ng kopya ng updates sa iyong email.</p>
                                </div>
                                <input type="checkbox" name="wants_email_notification" value="1" onchange="this.form.submit()" {{ Auth::user()->wants_email_notification ? 'checked' : '' }} class="h-5 w-5 cursor-pointer accent-slate-900" />
                            </label>
                        </form>
                    @else
                        <label class="flex cursor-not-allowed items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4 opacity-80">
                            <div>
                                <p class="text-sm font-bold text-slate-800">Email / Digital Receipts</p>
                                <p class="mt-1 text-xs font-bold text-amber-600">⚠️ I-verify muna ang iyong email sa itaas upang magamit ito.</p>
                            </div>
                            <input type="checkbox" disabled class="h-5 w-5 cursor-not-allowed accent-slate-400" />
                        </label>
                    @endif
                </div>
            </div>
            <hr class="my-8 border-slate-100" />

            <div>
                <h2 class="mb-4 text-lg font-bold text-slate-900">Security Settings</h2>
                <form action="{{ route('password.update') }}" method="POST" class="max-w-md space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-700">Kasalukuyang Password <span class="text-red-500">*</span></label>
                        <input type="password" name="current_password" required class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition-all outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-bold text-slate-700">Bagong Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" minlength="8" required class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition-all outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-bold text-slate-700">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" minlength="8" required class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition-all outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900" />
                        </div>
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-slate-900 px-8 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 sm:w-auto">I-save ang Bagong Password</button>
                </form>
            </div>
        </div>
    </div>
    <!-- TASK 4: RESUBMIT REQUIREMENTS MODAL -->
    <div id="resubmitModal" class="fixed inset-0 z-[110] flex hidden items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
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
    <!-- MODAL: SERVICE REQUEST FORM -->
    <div id="requestModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-opacity">
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
