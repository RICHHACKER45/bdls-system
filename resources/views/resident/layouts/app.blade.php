<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Resident Dashboard') - BDLS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased flex h-[100dvh] overflow-hidden">

    <!-- MOBILE OVERLAY -->
    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 bg-white w-64 border-r border-slate-200 z-50 transform -translate-x-full lg:translate-x-0 lg:static lg:flex lg:flex-col transition-transform duration-300 ease-in-out">
        <div class="h-16 flex items-center px-6 border-b border-slate-100">
            <div class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3">BD</div>
            <span class="font-bold text-lg tracking-tight">BDLS System</span>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <button onclick="switchTab('dashboard')" id="nav-dashboard" class="nav-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all bg-red-50 text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </button>
            <button onclick="switchTab('settings')" id="nav-settings" class="nav-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Account Settings
            </button>
        </nav>

        <div class="p-4 border-t border-slate-100">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-500 hover:text-slate-900 focus:outline-none p-2 -ml-2 rounded-md focus:ring-2 focus:ring-slate-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <div class="ml-auto flex items-center gap-3">
                <span class="text-sm font-semibold text-slate-700 truncate max-w-[180px] sm:max-w-xs">Kamusta, {{ Auth::user()->first_name }}!</span>
                <div class="w-9 h-9 rounded-full bg-slate-200 shadow-sm overflow-hidden border-2 border-white">
                    <img src="{{ asset('storage/' . Auth::user()->selfie_photo_path) }}" alt="Profile" class="w-full h-full object-cover">
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    <!-- TOAST ALERTS -->
    <div id="toast-container" class="fixed top-6 left-1/2 transform -translate-x-1/2 z-[1] flex flex-col gap-3 w-full max-w-md px-4 pointer-events-none">
        @if(session('success_message') || session('success'))
        <div class="toast-alert bg-slate-900 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-4 transform -translate-y-20 opacity-0 transition-all duration-500 pointer-events-auto border-l-4 border-green-400">
            <svg class="w-6 h-6 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                @if(session('success_title'))<p class="font-bold text-sm">{{ session('success_title') }}</p>@endif
                <p class="text-sm font-medium">{{ session('success_message') ?? session('success') }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- GLOBAL LOADER (Increased z-index) -->
    <div id="global-loader" class="fixed inset-0 z-[200] hidden bg-slate-900/60 backdrop-blur-sm items-center justify-center transition-opacity">
        <div class="bg-white p-6 rounded-2xl shadow-2xl flex flex-col items-center gap-4 animate-bounce-slight">
            <svg class="w-10 h-10 text-slate-900 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-bold text-slate-800">Pinoproseso...</p>
        </div>
    </div>

    <!-- THE LARAVEL CONFIG INJECTOR PARA SA EXTERNAL JS -->
    <script>
        window.BDLS = {
            activeTab: "{{ session('active_tab', 'dashboard') }}",
            pollingUrl: "{{ route('resident.api.status') }}",
            isVerified: {{ Auth::user()->is_verified ? 'true' : 'false' }},
            rejectionCount: {{ Auth::user()->rejection_count }},
            emailCooldown: {{ \Illuminate\Support\Facades\RateLimiter::availableIn('resend_email_otp_' . Auth::id()) ?: 0 }},
            hasFormErrors: {{ ($errors->has('purpose') || $errors->has('document_type_id') || $errors->has('preferred_pickup_time') || $errors->has('attachments.*')) ? 'true' : 'false' }}
        };
    </script>
    
    <script src="{{ asset('js/resident.js') }}"></script>
</body>
</html>
