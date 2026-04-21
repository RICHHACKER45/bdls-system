<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        @yield ('title', 'Admin Dashboard')
        - BDLS
    </title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-[100dvh] overflow-hidden bg-slate-50 font-sans text-slate-900 antialiased">
    <!-- MOBILE OVERLAY -->
    <div id="mobile-overlay" class="fixed inset-0 z-40 hidden bg-slate-900/50 lg:hidden" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full transform border-r border-slate-200 bg-white transition-transform duration-300 ease-in-out lg:static lg:flex lg:translate-x-0 lg:flex-col">
        <div class="flex h-16 items-center justify-between border-b border-slate-100 px-6">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <img src="{{ asset('images/bdls-logo-large.png') }}" alt="BDLS Logo" class="h-full w-full object-cover" onerror="this.outerHTML = '<span class=\'text-xs font-bold text-slate-900\'>BD</span>'" />
                </div>
                <span class="text-lg font-bold tracking-tight">Admin Portal</span>
            </div>
            <div class="flex items-center gap-2" title="System is live and syncing">
                <span class="relative flex h-3 w-3">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex h-3 w-3 rounded-full bg-green-500"></span>
                </span>
                <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Live</span>
            </div>
        </div>

        <!-- NAVIGATION TABS -->
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <button onclick="switchTab('pending')" id="nav-pending" class="nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg font-medium transition-all {{ session('active_tab', 'pending') == 'pending' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Pending Registrations
                </div>
                <span id="pending-badge" class="hidden rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white">0</span>
            </button>

            <button onclick="switchTab('queue')" id="nav-queue" class="nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg font-medium transition-all {{ session('active_tab', 'queue') == 'queue' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    Queue & Processing
                </div>
                <span id="queue-badge" class="hidden rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white">0</span>
            </button>

            <button onclick="switchTab('walkin')" id="nav-walkin" class="nav-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all {{ session('active_tab', 'walkin') == 'walkin' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Mga Walk-in Request
            </button>

            <button onclick="switchTab('announcements')" id="nav-announcements" class="nav-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all {{ session('active_tab', 'announcements') == 'announcements' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                Mga Anunsyo
            </button>

            <button onclick="switchTab('audit')" id="nav-audit" class="nav-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all {{ session('active_tab', 'audit') == 'audit' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Audit Logs ng System
            </button>
        </nav>

        <div class="border-t border-slate-100 p-4">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 font-bold text-red-700 transition-all hover:bg-red-100 active:scale-95">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout Admin
                </button>
            </form>
            <button onclick="switchTab('settings')" id="nav-settings" class="nav-btn w-full mt-2 flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all {{ session('active_tab', 'settings') == 'settings' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Mga Setting ng Account
            </button>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
        <header class="flex h-16 items-center justify-between border-b border-slate-100 bg-white px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="-ml-2 rounded-md p-2 text-slate-500 transition-all hover:text-slate-900 focus:outline-none active:scale-95 lg:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h1 id="topbar-title" class="hidden text-xl font-bold text-slate-800 sm:block">{{ session('active_tab') == 'queue' ? 'Queue & Processing' : 'Pending Registrations' }}</h1>
            </div>

            <!-- DYNAMIC GREETING & ROLE -->
            <div class="flex items-center gap-3">
                @php
                    $hour = \Carbon\Carbon::now()->timezone('Asia/Manila')->format('H');
                    $greeting = $hour < 12 ? 'Magandang umaga' : ($hour < 18 ? 'Magandang hapon' : 'Magandang gabi');

                    $title = 'Admin';
                    if (Auth::check()) {
                        if (Auth::user()->email === 'barangaycap@bdlsgov.ph') {
                            $title = 'Kapitan ng Barangay';
                        } elseif (Auth::user()->email === 'barangaysec@bdlsgov.ph') {
                            $title = 'Sekretarya';
                        }
                    }
                @endphp

                <div class="hidden text-right sm:block">
                    <p class="mb-0.5 text-[10px] font-black tracking-widest text-slate-400 uppercase">{{ $greeting }},</p>
                    <p class="text-sm leading-none font-bold text-slate-800">{{ $title }}!</p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-slate-900 text-white shadow-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
            </div>
        </header>

        <main class="relative flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div id="new-data-pill" class="absolute top-4 left-1/2 z-30 hidden -translate-x-1/2 transform">
                <button onclick="location.reload()" class="flex items-center gap-2 rounded-full border border-slate-700 bg-slate-900 px-6 py-2 text-sm font-bold text-white shadow-lg transition-all hover:bg-slate-800 active:scale-95">
                    <svg class="animate-spin-slow h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    May bagong update. I-click para i-refresh.
                </button>
            </div>

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
        <div class="flex flex-col items-center gap-4 rounded-2xl bg-white p-6 shadow-2xl">
            <svg class="h-10 w-10 animate-spin text-slate-900" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-bold tracking-widest text-slate-800 uppercase">Pinoproseso...</p>
        </div>
    </div>

    <script>
        window.BDLS_ADMIN = @js ([
                                    'activeTab' => session('active_tab', 'pending'),
                                    'pollingUrl' => route('admin.api.pending_count'),
                                    'queuePollingUrl' => route('admin.api.queue_count'),
                                    'initialPendingCount' => isset($pendingAccounts) ? $pendingAccounts->count() : 0,
                                    'initialQueueCount' => isset($activeQueue) ? $activeQueue->count() : 0
                                ]);
    </script>
    <script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>
