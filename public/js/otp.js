// ==========================================
// BDLS OTP MODULE: JAVASCRIPT LOGIC
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // 1. OTP AUTO-ADVANCE UX
    const otpBoxes = document.querySelectorAll('.otp-box');
    otpBoxes.forEach((box, index) => {
        box.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, ''); // Numbers only
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

    // 2. FRONTEND TIMER SCRIPT
    if (typeof window.OTP_CONFIG !== 'undefined' && window.OTP_CONFIG.cooldown > 0) {
        let secondsLeft = window.OTP_CONFIG.cooldown;
        const btn = document.getElementById('resendBtn');
        const timerDisplay = document.getElementById('timerDisplay');

        if (btn && timerDisplay) {
            btn.disabled = true;
            const countdown = setInterval(() => {
                timerDisplay.innerText = `(${secondsLeft}s)`;
                secondsLeft--;
                if (secondsLeft < 0) {
                    clearInterval(countdown);
                    btn.disabled = false;
                    timerDisplay.innerText = '';
                }
            }, 1000);
        }
    }
});
