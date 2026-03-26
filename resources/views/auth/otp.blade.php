<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - Barangay Doña Lucia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased min-h-screen flex items-center justify-center p-4">

     <!-- OTP CARD CONTAINER (Dynamic Padding: p-6 sa mobile, p-8 sa desktop) -->
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6 md:p-8 text-center border border-slate-100 mx-4">
        <h2 class="text-2xl font-bold text-slate-900 mb-2">Verify Your Number</h2>
        <p class="text-sm text-slate-500 mb-8">
            Nagpadala kami ng 6-digit code sa iyong numero. I-enter ito sa ibaba.
        </p>
        <!-- LARAVEL SUCCESS DISPLAY -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg text-left shadow-sm">
                <div class="flex items-center gap-2 text-green-700 font-bold mb-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Success
                </div>
                <p class="text-sm text-green-600 font-medium">{{ session('success') }}</p>
            </div>
        @endif
        <!-- LARAVEL ERROR DISPLAY -->
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-left shadow-sm">
                <div class="flex items-center gap-2 text-red-700 font-bold mb-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Verification Failed
                </div>
                <p class="text-sm text-red-600 font-medium">{{ $errors->first() }}</p>
            </div>
        @endif

        <!-- OTP FORM -->
        <form action="{{ route('otp.verify') }}" method="POST">
            @csrf
            
            <!-- 6 KAHON (Dynamic Sizes: w-10 h-12 sa mobile, w-14 h-16 sa desktop, justify-center para laging gitna) -->
            <div class="flex justify-center gap-1 sm:gap-2 md:gap-3 mb-8" id="otp-container">
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-10 h-12 sm:w-12 sm:h-14 md:w-14 md:h-16 text-center text-xl md:text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-10 h-12 sm:w-12 sm:h-14 md:w-14 md:h-16 text-center text-xl md:text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-10 h-12 sm:w-12 sm:h-14 md:w-14 md:h-16 text-center text-xl md:text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-10 h-12 sm:w-12 sm:h-14 md:w-14 md:h-16 text-center text-xl md:text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-10 h-12 sm:w-12 sm:h-14 md:w-14 md:h-16 text-center text-xl md:text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-10 h-12 sm:w-12 sm:h-14 md:w-14 md:h-16 text-center text-xl md:text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200">
                Verify Account
            </button>
        </form>

        <!-- RESEND LINK (Converted to Secure Form) -->
        <div class="mt-6 text-sm text-slate-500">
            Hindi nakuha ang code? 
            <form action="{{ route('otp.resend') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-slate-900 font-bold hover:underline focus:outline-none focus:ring-2 focus:ring-slate-200 rounded">
                    Mag-resend
                </button>
            </form>
        </div>

    </div>

    <!-- JAVASCRIPT PARA SA AUTO-ADVANCE UX -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const otpBoxes = document.querySelectorAll('.otp-box');

            otpBoxes.forEach((box, index) => {
                // Kapag nag-type, lipat sa next box
                box.addEventListener('input', (e) => {
                    e.target.value = e.target.value.replace(/[^0-9]/g, ''); // Numbers only
                    if (e.target.value !== '' && index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                    }
                });

                // Kapag nag-backspace at walang laman, balik sa prev box
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