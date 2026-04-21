<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BDLS - Barangay Doña Lucia Services</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 font-sans text-slate-900 antialiased">
    <!-- UPDATED BACK BUTTON (May Mobile Touch Target at Tactile Feedback na!) -->
    <div class="absolute top-4 left-4 md:top-8 md:left-8">
        <a href="/" class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 transition-all duration-200 hover:text-red-600 focus:ring-4 focus:ring-slate-200 focus:outline-none active:scale-95 active:bg-slate-200">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Bumalik sa Home
        </a>
    </div>

    <!-- Main Container: Split screen for Desktop, Stacked for Mobile -->
    <main class="mx-auto flex w-full max-w-5xl flex-col items-center gap-8 p-4 md:flex-row md:gap-16 md:p-8">
        <!-- LEFT SIDE: Branding -->
        <div class="flex w-full flex-col items-center text-center md:w-1/2 md:items-start md:text-left">
            <div class="mt-12 mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-red-600 shadow-lg md:mt-0">
                <span class="text-2xl font-bold text-white">BDLS</span>
            </div>
            <h1 class="mb-4 text-3xl font-extrabold tracking-tight md:text-5xl">Barangay Doña Lucia <span class="text-red-600">Services</span></h1>
            <p class="max-w-md text-lg text-slate-600 md:text-xl">Ang iyong mabilis at direktang koneksyon para sa mga dokumento at serbisyo ng barangay.</p>
        </div>

        <!-- RIGHT SIDE: Login Form -->
        <div class="w-full md:w-1/2">
            <div class="rounded-2xl border border-slate-100 bg-white p-8 shadow-xl">
                <h2 class="mb-6 text-center text-2xl font-bold">Mag-login sa System</h2>
                <!-- LARAVEL ERROR DISPLAY -->
                @if ($errors->any())
                    <div class="mb-4 rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4 text-left shadow-sm">
                        <div class="mb-1 flex items-center gap-2 font-bold text-red-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Login Failed
                        </div>
                        <p class="text-sm font-medium text-red-600">{{ $errors->first() }}</p>
                    </div>
                @endif

                <!-- SUCCESS DISPLAY (Galing sa OTP) -->
                @if (session('success'))
                    <div class="mb-4 rounded-r-lg border-l-4 border-green-500 bg-green-50 p-4 text-left shadow-sm">
                        <p class="text-sm font-medium text-green-600">{{ session('success') }}</p>
                    </div>
                @endif
                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf
                    <!-- Login Credential Input (Contact Number or Email) -->
                    <div>
                        <label for="login_id" class="mb-1 block text-sm font-semibold text-slate-700">Contact Number o Email</label>
                        <input type="text" id="login_id" name="login_id" placeholder="09123456789 / juan@email.com" required class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-all outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600" />
                    </div>

                    <!-- Password Input with Forgot Password Link -->
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            <!-- THE NEW FORGOT PASSWORD LINK -->
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-slate-400 transition-all hover:text-slate-900 hover:underline">Nakalimutan ang password?</a>
                        </div>
                        <input type="password" id="password" name="password" placeholder="••••••••" required class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-all outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600" />
                    </div>

                    <!-- Login Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-3.5 font-bold text-white shadow-md transition-all duration-200 hover:bg-red-700 hover:shadow-lg active:scale-95">Pumasok</button>
                    </div>
                </form>

                <!-- THE NEW DIVIDER -->
                <div class="mt-6 flex items-center gap-3 text-sm font-medium text-slate-400">
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <span class="text-[10px] tracking-widest uppercase">o kaya</span>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>

                <!-- THE NEW GHOST BUTTON FOR SIGNUP -->
                <div class="mt-6">
                    <a href="/signup" class="flex w-full items-center justify-center rounded-lg border-2 border-slate-200 bg-transparent px-4 py-3.5 font-bold text-slate-600 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 active:scale-95"> Gumawa ng Bagong Account </a>
                </div>
            </div>
        </div>
    </main>
    <script src="{{ asset('js/guest-cleanup.js') }}"></script>
</body>
</html>
