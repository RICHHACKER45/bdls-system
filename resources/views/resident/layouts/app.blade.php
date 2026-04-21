<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        @yield ('title', 'Resident Dashboard')
        - BDLS
    </title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-[100dvh] overflow-hidden bg-slate-50 font-sans text-slate-900 antialiased">
    <!-- MOBILE OVERLAY -->
    <div id="mobile-overlay" class="fixed inset-0 z-40 hidden bg-slate-900/50 lg:hidden" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 -translate-x-full transform border-r border-slate-200 bg-white transition-transform duration-300 ease-in-out lg:static lg:flex lg:translate-x-0 lg:flex-col">
        <div class="flex h-16 items-center border-b border-slate-100 px-6">
            <div class="mr-3 flex h-8 w-8 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-white shadow-sm">
                <img src="{{ asset('images/bdls-logo-large.png') }}" alt="BDLS Logo" class="h-full w-full object-cover" onerror="this.outerHTML = '<span class=\'text-xs font-bold text-red-600\'>BD</span>'" />
            </div>
            <span class="text-lg font-bold tracking-tight">BDLS System</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <button onclick="switchTab('dashboard')" id="nav-dashboard" class="nav-btn flex w-full items-center gap-3 rounded-lg bg-red-50 px-3 py-2.5 font-medium text-red-700 transition-all">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </button>
            <button onclick="switchTab('tracking')" id="nav-tracking" class="nav-btn flex w-full items-center gap-3 rounded-lg px-3 py-2.5 font-medium text-slate-600 transition-all hover:bg-slate-50 hover:text-slate-900">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                I-track ang mga Request
            </button>
            <button onclick="switchTab('settings')" id="nav-settings" class="nav-btn flex w-full items-center gap-3 rounded-lg px-3 py-2.5 font-medium text-slate-600 transition-all hover:bg-slate-50 hover:text-slate-900">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Mga Setting ng Account
            </button>
        </nav>

        <div class="border-t border-slate-100 p-4">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-slate-100 px-4 py-2 font-bold text-slate-700 transition-all hover:bg-slate-200 active:scale-95">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
        <header class="flex h-16 items-center justify-between border-b border-slate-100 bg-white px-4 sm:px-6 lg:px-8">
            <button onclick="toggleSidebar()" class="-ml-2 rounded-md p-2 text-slate-500 hover:text-slate-900 focus:ring-2 focus:ring-slate-200 focus:outline-none lg:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <div class="ml-auto flex items-center gap-3">
                <span class="max-w-[180px] truncate text-sm font-semibold text-slate-700 sm:max-w-xs">Kumusta, {{ Auth::user()->first_name }}!</span>
                <div class="h-9 w-9 overflow-hidden rounded-full border-2 border-white bg-slate-200 shadow-sm">
                    <img src="{{ asset('storage/' . Auth::user()->selfie_photo_path) }}" alt="Profile" class="h-full w-full object-cover" />
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            @yield ('content')
        </main>
    </div>

    <!-- TOAST ALERTS -->
    <div id="toast-container" class="pointer-events-none fixed top-6 left-1/2 z-[1] flex w-full max-w-md -translate-x-1/2 transform flex-col gap-3 px-4">
        @if (session('success_message') || session('success'))
            <div class="toast-alert pointer-events-auto flex -translate-y-20 transform items-center gap-4 rounded-xl border-l-4 border-green-400 bg-slate-900 px-6 py-4 text-white opacity-0 shadow-2xl transition-all duration-500">
                <svg class="h-6 w-6 shrink-0 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    @if (session('success_title'))
                        <p class="text-sm font-bold">{{ session('success_title') }}</p>
                    @endif
                    <p class="text-sm font-medium">{{ session('success_message') ?? session('success') }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- GLOBAL LOADER -->
    <div id="global-loader" class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
        <div class="animate-bounce-slight flex flex-col items-center gap-4 rounded-2xl bg-white p-6 shadow-2xl">
            <svg class="h-10 w-10 animate-spin text-slate-900" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-bold text-slate-800">Pinoproseso...</p>
        </div>
    </div>

    <script>
        window.BDLS = {
            activeTab: '{{ session('active_tab', 'dashboard') }}',
            pollingUrl: '{{ route('resident.api.status') }}',
            isVerified: {{ Auth::user()->is_verified ? 'true' : 'false' }},
            rejectionCount: {{ Auth::user()->rejection_count }},
            emailCooldown: {{ \Illuminate\Support\Facades\RateLimiter::availableIn('resend_email_otp_' . Auth::id()) ?: 0 }},
            hasFormErrors: {{ ($errors->has('purpose') || $errors->has('document_type_id') || $errors->has('preferred_pickup_time') || $errors->has('attachments.*')) ? 'true' : 'false' }},
        };
    </script>

    <script src="{{ asset('js/resident.js') }}"></script>
</body>
</html>
