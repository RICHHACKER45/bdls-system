<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BDLS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 font-sans text-slate-900 antialiased p-4">
    <div class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 shadow-xl md:p-8">
        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold text-slate-900">I-reset ang Password</h2>
            <p class="mt-2 text-sm text-slate-500">I-enter ang 6-digit code na ipinadala sa <span class="font-bold text-slate-900">{{ session('reset_contact') }}</span> at ang iyong bagong password.</p>
        </div>

        @if ($errors->any())
        <div class="mb-6 rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4 text-sm font-medium text-red-700 shadow-sm">{{ $errors->first() }}</div>
        @endif
        @if (session('success'))
        <div class="mb-6 rounded-r-lg border-l-4 border-green-500 bg-green-50 p-4 text-sm font-medium text-green-700 shadow-sm">{{ session('success') }}</div>
        @endif

        <form action="{{ route('password.update.submit') }}" method="POST" class="space-y-6">
            @csrf
            <!-- New Password & Confirm (F-Pattern Grid) -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Bagong Password</label>
                    <input type="password" name="password" minlength="8" required class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-all outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900 bg-slate-50" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" minlength="8" required class="w-full rounded-lg border border-slate-300 px-4 py-3 transition-all outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900 bg-slate-50" />
                </div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-red-600 px-8 py-3.5 font-bold text-white transition-all hover:bg-red-700 active:scale-95 shadow-md mt-4">
                I-save at Mag-login
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-all">I-cancel at bumalik sa Login</a>
        </div>
    </div>

    <!-- Vanilla JS para sa 6-OTP Boxes (Auto-Advance) -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const otpBoxes = document.querySelectorAll('.otp-box');
            otpBoxes.forEach((box, index) => {
                box.addEventListener('input', (e) => {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    if (e.target.value !== '' && index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                    }
                });
                box.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                        otpBoxes[index - 1].focus();
                    }
                });
            });
        });
    </script>
</body>
</html>