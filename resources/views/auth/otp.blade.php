<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OTP Verification - Barangay Doña Lucia</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-4 font-sans text-slate-900 antialiased">
    <!-- OTP CARD CONTAINER (Dynamic Padding: p-6 sa mobile, p-8 sa desktop) -->
    <div class="mx-4 w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-xl md:p-8">
        <h2 class="mb-2 text-2xl font-bold text-slate-900">I-verify ang iyong Numero</h2>
        <p class="mb-8 text-sm text-slate-500">Nagpadala kami ng 6-digit code sa iyong numero. I-enter ito sa ibaba.</p>
        <!-- LARAVEL SUCCESS DISPLAY -->
        @if (session('success'))
            <div class="mb-6 rounded-r-lg border-l-4 border-green-500 bg-green-50 p-4 text-left shadow-sm">
                <div class="mb-1 flex items-center gap-2 font-bold text-green-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Tagumpay
                </div>
                <p class="text-sm font-medium text-green-600">{{ session('success') }}</p>
            </div>
        @endif
        <!-- LARAVEL ERROR DISPLAY -->
        @if ($errors->any())
            <div class="mb-6 rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4 text-left shadow-sm">
                <div class="mb-1 flex items-center gap-2 font-bold text-red-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Bigo ang Verification
                </div>
                <p class="text-sm font-medium text-red-600">{{ $errors->first() }}</p>
            </div>
        @endif

        <!-- OTP FORM -->
        <form action="{{ $verifyRoute }}" method="POST">
            @csrf

            <!-- 6 KAHON (Dynamic Sizes: w-10 h-12 sa mobile, w-14 h-16 sa desktop, justify-center para laging gitna) -->
            <div class="mb-8 flex justify-center gap-1 sm:gap-2 md:gap-3" id="otp-container">
                <input type="text" name="otp[]" maxlength="1" class="otp-box h-12 w-10 rounded-xl border border-slate-300 bg-slate-50 text-center text-xl font-extrabold text-slate-900 transition-all outline-none focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-200 sm:h-14 sm:w-12 md:h-16 md:w-14 md:text-2xl" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box h-12 w-10 rounded-xl border border-slate-300 bg-slate-50 text-center text-xl font-extrabold text-slate-900 transition-all outline-none focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-200 sm:h-14 sm:w-12 md:h-16 md:w-14 md:text-2xl" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box h-12 w-10 rounded-xl border border-slate-300 bg-slate-50 text-center text-xl font-extrabold text-slate-900 transition-all outline-none focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-200 sm:h-14 sm:w-12 md:h-16 md:w-14 md:text-2xl" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box h-12 w-10 rounded-xl border border-slate-300 bg-slate-50 text-center text-xl font-extrabold text-slate-900 transition-all outline-none focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-200 sm:h-14 sm:w-12 md:h-16 md:w-14 md:text-2xl" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box h-12 w-10 rounded-xl border border-slate-300 bg-slate-50 text-center text-xl font-extrabold text-slate-900 transition-all outline-none focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-200 sm:h-14 sm:w-12 md:h-16 md:w-14 md:text-2xl" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box h-12 w-10 rounded-xl border border-slate-300 bg-slate-50 text-center text-xl font-extrabold text-slate-900 transition-all outline-none focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-200 sm:h-14 sm:w-12 md:h-16 md:w-14 md:text-2xl" />
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" class="w-full rounded-xl bg-slate-900 px-8 py-3 font-bold text-white transition-all duration-200 hover:bg-slate-800 active:scale-95">I-verify ang Account</button>
        </form>

        <!-- RESEND OTP SECTION WITH RATE LIMITER -->
        @php
            // Dahil baka hindi pa naka-login, IP address ang gagamitin nating identifier para sa lock
            $cooldown = \Illuminate\Support\Facades\RateLimiter::availableIn('resend_sms_otp_' . request()->ip());
        @endphp

        <div class="mt-8 border-t border-slate-100 pt-6 text-center">
            <p class="mb-3 text-sm text-slate-500">Hindi nakuha ang code?</p>

            <!-- Palitan ang 'otp.resend' ng totoong pangalan ng route mo para sa resend -->
            <form action="{{ $resendRoute }}" method="POST" id="resendOtpForm">
                @csrf
                <button type="submit" id="resendBtn" class="text-sm font-bold text-slate-900 transition-all hover:underline disabled:cursor-not-allowed disabled:text-slate-400 disabled:no-underline">Magpadala ulit ng code <span id="timerDisplay" class="ml-1 font-mono text-red-600"></span></button>
            </form>
        </div>

        <!-- THE LARAVEL CONFIG INJECTOR -->
        <script>
            window.OTP_CONFIG = {
                cooldown: {{ $cooldown }},
            };
        </script>
        <script src="{{ asset('js/otp.js') }}"></script>
</body>
</html>
