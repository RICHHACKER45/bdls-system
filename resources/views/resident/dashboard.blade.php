@extends('resident.layouts.app')

@section('title', 'Dashboard')

@section('content')

<!-- TAB 1: DASHBOARD CONTENT -->
<div id="tab-dashboard" class="tab-content hidden">

    @if(Auth::user()->email && !Auth::user()->email_verified_at)
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 sm:p-5 rounded-r-xl shadow-sm mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-blue-800 font-bold text-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                I-verify ang iyong Email
            </h3>
            <p class="text-blue-600 text-sm mt-1">Kasalukuyang naka-link ang <span class="font-bold">{{ Auth::user()->email }}</span>. I-verify ito para makatanggap ng digital receipts.</p>
        </div>
        <div class="w-full sm:w-auto">
            <button type="button" onclick="switchTab('settings')" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold text-sm active:scale-95 transition-all shadow-sm">Pumunta sa Settings</button>
        </div>
    </div>
    @endif

    <h1 class="text-2xl font-bold text-slate-900 mb-6">Resident Dashboard</h1>

    @if(!Auth::user()->is_verified)
    <div class="bg-amber-50 border-l-4 border-amber-500 p-6 rounded-r-xl shadow-sm mb-6">
        <div class="flex items-center gap-3 text-amber-800 font-bold text-lg mb-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Account is Under Review
        </div>
        <p class="text-amber-700 text-sm">Kasalukuyang sinusuri ng Barangay Administrator ang iyong Valid ID at Selfie. Hindi ka pa maaaring mag-request ng dokumento hangga't hindi ito naaaprubahan.</p>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="col-span-1 lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 flex flex-col justify-between">
            <div>
                <div class="w-14 h-14 bg-red-100 text-red-600 rounded-xl flex items-center justify-center mb-5">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Kumuha ng Dokumento</h2>
                <p class="text-slate-500 mb-8 max-w-md">Mag-request ng Barangay Clearance, Certificate of Indigency, at iba pang legal na papel nang hindi na pumipila nang matagal.</p>
            </div>
            <div>
                <button onclick="openRequestModal()" class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 px-6 rounded-xl transition-all active:scale-95 shadow-md w-full sm:w-auto focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Gumawa ng Bagong Request
                </button>
            </div>
        </div>

        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-bold text-slate-700 mb-1">Pending Requests</h3>
                <div class="flex items-end gap-3 mb-4">
                    <span class="text-4xl font-extrabold text-slate-900">0</span>
                    <span class="text-sm text-slate-500 mb-1 pb-1">dokumento</span>
                </div>
                <a href="#" class="text-sm font-bold text-red-600 hover:text-red-700 hover:underline flex items-center gap-1">Tingnan ang status <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></a>
            </div>
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

<!-- TAB 2: SETTINGS CONTENT -->
<div id="tab-settings" class="tab-content hidden">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Account Settings</h1>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 space-y-8">
        @if(session('success') && session('active_tab') == 'settings')
            <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg text-sm text-green-700 font-medium shadow-sm">{{ session('success') }}</div>
        @endif
        @if($errors->has('email_otp') || $errors->has('email'))
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-sm text-red-700 font-medium shadow-sm">{{ $errors->first() }}</div>
        @endif

        <div>
            <h2 class="text-lg font-bold text-slate-900 mb-3">Primary Contact Number</h2>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <input type="text" value="{{ Auth::user()->contact_number }}" disabled class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg block w-full sm:w-80 p-2.5 font-bold">
                <span class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700 w-full sm:w-auto"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Verified</span>
            </div>
            <p class="text-xs text-slate-500 mt-2">Dito ipapadala ang mga pangunahing SMS notifications.</p>
        </div>

        <hr class="border-slate-100">

        <div>
            <h2 class="text-lg font-bold text-slate-900 mb-3">Email Address</h2>
            @if(!Auth::user()->email)
                <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-xs text-slate-500 mb-3">Wala kang nakarehistrong email. Magdagdag upang makatanggap ng digital receipts.</p>
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
                        <span class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700 w-full sm:w-auto"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Verified</span>
                    @else
                        <span class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 w-full sm:w-auto">Unverified</span>
                    @endif
                </div>

                @if(!Auth::user()->email_verified_at)
                    <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl">
                        <h3 class="text-sm font-bold text-slate-800 mb-2">I-verify ang iyong Email</h3>
                        <form action="{{ route('resident.email.verify') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                            @csrf
                            <input type="text" name="email_otp" maxlength="6" placeholder="000000" class="bg-white border border-slate-300 text-center text-lg font-bold rounded-lg block w-full sm:w-40 p-2.5 tracking-[0.3em] outline-none focus:ring-2 focus:ring-slate-900">
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 px-6 rounded-lg text-sm w-full sm:w-auto transition-all active:scale-95">Verify</button>
                        </form>
                        <form action="{{ route('resident.email.send') }}" method="POST" id="resendOtpForm" class="mt-3">
                            @csrf
                            <button type="submit" id="resendBtn" class="text-xs font-bold text-slate-600 hover:text-slate-900 hover:underline disabled:text-slate-400 disabled:no-underline disabled:cursor-not-allowed transition-all">Magpadala ng bagong code <span id="timerDisplay" class="text-red-600 ml-1 font-mono"></span></button>
                        </form>
                    </div>
                @endif
            @endif
        </div>

        <hr class="border-slate-100">

        <div>
            <h2 class="text-lg font-bold text-slate-900 mb-4">Notification Preferences</h2>
            <div class="space-y-3">
                <label class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed opacity-80">
                    <div>
                        <p class="font-bold text-slate-800 text-sm">SMS Notifications</p>
                        <p class="text-xs text-slate-500">Pangunahing channel para sa mabilis na updates (Required).</p>
                    </div>
                    <input type="checkbox" checked disabled class="w-5 h-5 accent-slate-900">
                </label>

                @if(Auth::user()->email_verified_at)
                <form action="{{ route('resident.settings.email_preference') }}" method="POST">
                    @csrf
                    <label class="flex items-center justify-between p-4 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-all shadow-sm">
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Email / Digital Receipts</p>
                            <p class="text-xs text-slate-500">Makatanggap ng kopya ng updates sa iyong email.</p>
                        </div>
                        <input type="checkbox" name="wants_email_notification" value="1" onchange="this.form.submit()" {{ Auth::user()->wants_email_notification ? 'checked' : '' }} class="w-5 h-5 accent-slate-900 cursor-pointer">
                    </label>
                </form>
                @else
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

<!-- MODAL: SERVICE REQUEST FORM -->
<div id="requestModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden border border-slate-100">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Gumawa ng Request</h2>
                <p class="text-slate-500 text-sm mt-1">Kumpletuhin ang detalye para sa iyong queue number.</p>
            </div>
            <button type="button" onclick="closeRequestModal()" class="text-slate-400 hover:text-red-600 active:scale-95 transition-all p-2 bg-slate-200 hover:bg-red-100 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>

        <div class="p-6 overflow-y-auto">
            <form action="{{ route('resident.request.store') }}" method="POST" id="requestForm" class="space-y-6" enctype="multipart/form-data">
                @csrf
                <div class="bg-slate-100 p-5 rounded-xl border border-slate-200 shadow-inner">
                    <h3 class="text-sm font-bold text-slate-800 mb-3 border-b border-slate-200 pb-2">Impormasyon ng Nagre-request</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                    <label class="block text-sm font-bold text-slate-800 mb-2">Uri ng Dokumento <span class="text-red-500">*</span></label>
                    <select name="document_type_id" id="document_type_id" required onchange="showRequirements(this)" class="w-full px-4 py-3 rounded-xl border @error('document_type_id') border-red-500 ring-1 ring-red-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all cursor-pointer">
                        <option value="">-- Pumili ng Dokumento --</option>
                        @foreach($documents as $doc)
                            <option value="{{ $doc->id }}" data-reqs="{{ $doc->requirements_description }}" {{ old('document_type_id') == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                        @endforeach
                    </select>
                    @error('document_type_id') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror

                    <div id="requirements_box" class="hidden mt-3 p-4 bg-blue-50 border border-blue-200 rounded-lg shadow-inner transition-all">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-sm text-blue-800 font-bold">Mga Kinakailangang Dalhin / I-upload:</p>
                        </div>
                        <p id="requirements_text" class="text-sm text-blue-700 font-medium ml-7"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-2">Layunin (Purpose) <span class="text-red-500">*</span></label>
                    <input type="text" name="purpose" value="{{ old('purpose') }}" placeholder="Hal. Requirement sa Trabaho..." required class="w-full px-4 py-3 rounded-xl border @error('purpose') border-red-500 ring-1 ring-red-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all">
                    @error('purpose') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-2">Kailan mo gustong kunin? <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="preferred_pickup_time" value="{{ old('preferred_pickup_time') }}" required class="w-full px-4 py-3 rounded-xl border @error('preferred_pickup_time') border-red-500 ring-1 ring-red-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all">
                    @error('preferred_pickup_time') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-2">Karagdagang Detalye (Optional)</label>
                    <textarea name="additional_details" rows="2" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-slate-900 outline-none bg-slate-50 transition-all">{{ old('additional_details') }}</textarea>
                </div>

                <div id="upload_section" class="hidden p-5 bg-white border-2 border-dashed @error('attachments.*') border-red-500 bg-red-50 @else border-slate-300 @enderror rounded-xl">
                    <label class="block text-sm font-bold text-slate-800 mb-2">I-upload ang Karagdagang Dokumento <span class="text-red-500">*</span></label>
                    <input type="file" name="attachments[]" id="attachments" multiple accept="image/jpeg, image/png, image/jpg, application/pdf" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                    @error('attachments.*') <p class="text-red-600 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                    <button type="button" onclick="closeRequestModal()" class="px-6 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-200 active:scale-95 transition-all">Kanselahin</button>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-2.5 px-8 rounded-xl shadow-md">I-submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection