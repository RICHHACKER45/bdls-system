<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome - Barangay Doña Lucia</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased">
    <!-- Header / Navbar -->
    <nav class="sticky top-0 z-50 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4 shadow-sm md:px-12">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white shadow">BDLS</div>
            <span class="hidden text-lg font-bold tracking-tight text-slate-800 sm:block md:text-xl">Barangay Doña Lucia Services</span>
        </div>
        <div class="flex items-center gap-4">
            <!-- Secondary Action (Sinking Box Effect) -->
            <a href="/login" class="rounded-lg px-5 py-2 font-bold text-slate-700 transition-all duration-200 hover:bg-slate-200 hover:shadow-inner active:scale-95 active:bg-slate-300 active:shadow-inner"> Login </a>
            <!-- Primary Action (Solid Red Press Effect) -->
            <a href="/signup" class="rounded-lg bg-red-600 px-5 py-2 font-bold text-white shadow-sm transition-all hover:bg-red-700 active:scale-95 active:bg-red-800 active:shadow-inner"> Mag-Signup </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative overflow-hidden bg-slate-800 px-6 pt-24 pb-32 text-white md:px-12">
        <div class="relative z-10 max-w-4xl">
            <h1 class="mb-4 text-4xl font-extrabold tracking-tight md:text-6xl">Welcome to Barangay Doña Lucia Services</h1>
            <p class="max-w-2xl text-lg text-slate-300 md:text-xl">Ang iyong mabilis at direktang koneksyon para sa mga government services, dokumento, at impormasyon ng barangay.</p>
        </div>
    </header>

    <!-- Main Content (Overlapping Section) -->
    <main class="relative z-20 mx-auto -mt-16 max-w-7xl px-6 pb-24 md:px-12">
        <div class="flex flex-col gap-8 lg:flex-row">
            <!-- Left Side: Government Services Cards -->
            <div class="mt-16 flex-1 lg:mt-24">
                <h2 class="mb-6 text-2xl font-bold text-slate-800">Government Services</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Card 1: Online Services (Clickable na) -->
                    <a href="/signup" class="block rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-200 hover:border-red-300 hover:shadow-md active:scale-95 active:bg-slate-50">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 text-red-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-slate-800">Online Services</h3>
                        <p class="text-sm text-slate-600">Mag-request ng Barangay Clearance, Indigency, at iba pang dokumento online.</p>
                    </a>

                    <!-- Card 2: Queue Tracking (Clickable na) -->
                    <a href="/login" class="block rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-200 hover:border-slate-400 hover:shadow-md active:scale-95 active:bg-slate-50">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-slate-800">Queue & SMS Tracking</h3>
                        <p class="text-sm text-slate-600">Alamin ang status ng iyong papel in real-time gamit ang SMS technology.</p>
                    </a>
                </div>
            </div>

            <!-- Right Side: The Overlapping Announcement Card -->
            <div class="w-full lg:mt-0 lg:w-1/3">
                <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-8 shadow-xl">
                    <!-- Red Accent line sa taas ng card -->
                    <div class="absolute top-0 left-0 h-1 w-full bg-red-600"></div>
                    <h3 class="mb-4 text-xl leading-snug font-bold text-slate-900">ANNOUNCEMENT: Launch of the Official BDLS System</h3>
                    <p class="mb-6 text-sm leading-relaxed text-slate-600">Ikinagagalak naming i-anunsyo ang opisyal na pagbubukas ng website ng Barangay Doña Lucia. Layunin nitong pabilisin ang inyong pag-request ng dokumento gamit ang aming bagong SMS Queuing System.</p>
                </div>
            </div>
        </div>
    </main>
    <script src="{{ asset('js/guest-cleanup.js') }}"></script>
</body>
</html>
