<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDLS - Barangay Doña Lucia Services</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center font-sans antialiased text-slate-900">

    <!-- UPDATED BACK BUTTON (May Mobile Touch Target at Tactile Feedback na!) -->
    <div class="absolute top-4 left-4 md:top-8 md:left-8">
        <a href="/" class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-red-600 active:bg-slate-200 active:scale-95 focus:outline-none focus:ring-4 focus:ring-slate-200 py-2 px-4 rounded-xl transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Bumalik sa Home
        </a>
    </div>

    <!-- Main Container: Split screen for Desktop, Stacked for Mobile -->
    <main class="w-full max-w-5xl mx-auto p-4 md:p-8 flex flex-col md:flex-row items-center gap-8 md:gap-16">
        
        <!-- LEFT SIDE: Branding -->
        <div class="w-full md:w-1/2 flex flex-col items-center md:items-start text-center md:text-left">
            <div class="w-24 h-24 bg-red-600 rounded-full flex items-center justify-center mt-12 md:mt-0 mb-6 shadow-lg">
                <span class="text-white font-bold text-2xl">BDLS</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-4">
                Barangay Doña Lucia <span class="text-red-600">Services</span>
            </h1>
            <p class="text-slate-600 text-lg md:text-xl max-w-md">
                Ang iyong mabilis at direktang koneksyon para sa mga dokumento at serbisyo ng barangay.
            </p>
        </div>

        <!-- RIGHT SIDE: Login Form -->
        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-slate-100">
                <h2 class="text-2xl font-bold mb-6 text-center">Mag-login sa System</h2>
                <!-- LARAVEL ERROR DISPLAY -->
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-left shadow-sm">
                        <div class="flex items-center gap-2 text-red-700 font-bold mb-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Login Failed
                        </div>
                        <p class="text-sm text-red-600 font-medium">{{ $errors->first() }}</p>
                    </div>
                @endif
                
                <!-- SUCCESS DISPLAY (Galing sa OTP) -->
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg text-left shadow-sm">
                        <p class="text-sm text-green-600 font-medium">{{ session('success') }}</p>
                    </div>
                @endif
                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf
                    <!-- Login Credential Input (Contact Number or Email) -->
                    <div>
                        <label for="login_id" class="block text-sm font-semibold text-slate-700 mb-1">Contact Number o Email</label>
                        <input type="text" id="login_id" name="login_id" placeholder="09123456789 / juan@email.com" required
                            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none transition-all">
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required
                            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none transition-all">
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 active:scale-95 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        Pumasok
                    </button>
                </form>

                <!-- Signup Link -->
                <div class="mt-6 text-center text-sm text-slate-600">
                    Walang account?
                    <a href="/signup" class="font-bold text-red-600 hover:underline">Mag-signup dito</a>
                </div>
            </div>
        </div>
    </main>
    <!-- CLEANUP SCRIPT: Burahin ang mga "Sticky Form" drafts mula sa Signup kapag nag-logout o napunta sa Login -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            sessionStorage.clear();
        });
    </script>
</body>
</html>