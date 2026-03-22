<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - Barangay Doña Lucia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased min-h-screen flex items-center justify-center p-4">

    <!-- OTP CARD CONTAINER -->
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-8 text-center border border-slate-100">
        
        <h2 class="text-2xl font-bold text-slate-900 mb-2">Verify Your Number</h2>
        <p class="text-sm text-slate-500 mb-8">
            Nagpadala kami ng 6-digit code sa iyong numero. I-enter ito sa ibaba.
        </p>

        <!-- OTP FORM -->
        <form action="#" method="POST">
            @csrf
            
            <!-- 6 KAHON -->
            <div class="flex justify-between gap-2 mb-8" id="otp-container">
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-12 h-14 md:w-14 md:h-16 text-center text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-12 h-14 md:w-14 md:h-16 text-center text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-12 h-14 md:w-14 md:h-16 text-center text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-12 h-14 md:w-14 md:h-16 text-center text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-12 h-14 md:w-14 md:h-16 text-center text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
                <input type="text" name="otp[]" maxlength="1" class="otp-box w-12 h-14 md:w-14 md:h-16 text-center text-2xl font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-4 focus:ring-slate-200 focus:border-slate-900 outline-none transition-all" />
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-3 px-8 rounded-xl transition-all duration-200">
                Verify Account
            </button>
        </form>

        <!-- RESEND LINK -->
        <div class="mt-6 text-sm text-slate-500">
            Hindi nakuha ang code? 
            <button type="button" class="text-slate-900 font-bold hover:underline focus:outline-none focus:ring-2 focus:ring-slate-200 rounded">
                Mag-resend
            </button>
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