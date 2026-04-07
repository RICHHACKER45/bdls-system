<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - BDLS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased flex h-[100dvh] overflow-hidden">

    <!-- MOBILE OVERLAY -->
    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 bg-white w-72 border-r border-slate-200 z-50 transform -translate-x-full lg:translate-x-0 lg:static lg:flex lg:flex-col transition-transform duration-300 ease-in-out">
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-slate-900 rounded-lg flex items-center justify-center text-white font-bold text-xs">BD</div>
                <span class="font-bold text-lg tracking-tight">Admin Portal</span>
            </div>
            <div class="flex items-center gap-2" title="System is live and syncing">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Live</span>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <button onclick="switchTab('pending')" id="nav-pending" class="nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg font-medium transition-all {{ session('active_tab', 'pending') == 'pending' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Pending Registrations
                </div>
                <span id="pending-badge" class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full hidden">0</span>
            </button>

            <button onclick="switchTab('queue')" id="nav-queue" class="nav-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Queue & Processing
            </button>

            <button onclick="switchTab('walkin')" id="nav-walkin" class="nav-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Walk-in Requests
            </button>

            <button onclick="switchTab('announcements')" id="nav-announcements" class="nav-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                Announcements
            </button>

            <button onclick="switchTab('audit')" id="nav-audit" class="nav-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                System Audit Logs
            </button>
        </nav>

        <div class="p-4 border-t border-slate-100">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg font-bold transition-all active:scale-95 border border-red-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout Admin
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="lg:hidden text-slate-500 hover:text-slate-900 focus:outline-none p-2 -ml-2 rounded-md active:scale-95 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h1 id="topbar-title" class="text-xl font-bold text-slate-800 hidden sm:block">Pending Registrations</h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-slate-700">Admin</span>
                <div class="w-9 h-9 rounded-full bg-slate-900 flex items-center justify-center text-white shadow-sm border-2 border-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative">
            <div id="new-data-pill" class="absolute top-4 left-1/2 transform -translate-x-1/2 z-30 hidden">
                <button onclick="location.reload()" class="bg-slate-900 text-white px-6 py-2 rounded-full shadow-lg font-bold text-sm flex items-center gap-2 hover:bg-slate-800 active:scale-95 transition-all border border-slate-700">
                    <svg class="w-4 h-4 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    May bagong update. I-click para i-refresh.
                </button>
            </div>
            @yield('content')
        </main>
    </div>

    <!-- TOAST ALERTS (Consistent with Resident UI) -->
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

    <!-- GLOBAL LOADER -->
    <div id="global-loader" class="fixed inset-0 z-[1] hidden bg-slate-900/60 backdrop-blur-sm items-center justify-center transition-opacity">
        <div class="bg-white p-6 rounded-2xl shadow-2xl flex flex-col items-center gap-4">
            <svg class="w-10 h-10 text-slate-900 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-bold text-slate-800">Pinoproseso...</p>
        </div>
    </div>

    <!-- THE LARAVEL ADMIN CONFIG INJECTOR -->
    <script>
        window.BDLS_ADMIN = {
            activeTab: "{{ session('active_tab', 'pending') }}",
            initialPendingCount: {{ isset($pendingAccounts) ? $pendingAccounts->count() : 0 }},
            initialQueueCount: {{ isset($activeQueue) ? $activeQueue->count() : 0 }},
            pollingUrl: "{{ route('admin.api.pending_count') }}",
            queuePollingUrl: "{{ route('admin.api.queue_count') }}"
        };
    </script>
    
    <!-- EXTERNAL JAVASCRIPT -->
    <script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>