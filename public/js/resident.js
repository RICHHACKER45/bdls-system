// ==========================================
// BDLS RESIDENT MODULE: CORE JAVASCRIPT
// ==========================================

// 1. HAMBURGER & SIDEBAR LOGIC
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobile-overlay');
    if(sidebar && overlay) {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
}

// 2. TAB SWITCHING (SPA ILLUSION)
function switchTab(tabId) {
    // Itago lahat ng tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Ipakita ang piniling tab
    const targetTab = document.getElementById('tab-' + tabId);
    if(targetTab) targetTab.classList.remove('hidden');

    // I-reset ang kulay ng lahat ng buttons sa sidebar
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.classList.remove('bg-red-50', 'text-red-700');
        btn.classList.add('text-slate-600', 'hover:bg-slate-50', 'hover:text-slate-900');
    });

    // Kulayan ng pula ang active na button
    const activeBtn = document.getElementById('nav-' + tabId);
    if(activeBtn) {
        activeBtn.classList.add('bg-red-50', 'text-red-700');
        activeBtn.classList.remove('text-slate-600', 'hover:bg-slate-50', 'hover:text-slate-900');
    }

    // Kung nasa mobile, isara ang sidebar pagkatapos pumili
    const sidebar = document.getElementById('sidebar');
    if(window.innerWidth < 1024 && sidebar && !sidebar.classList.contains('-translate-x-full')) {
        toggleSidebar();
    }
}

// 3. TOAST NOTIFICATION ANIMATION
function initToasts() {
    const toasts = document.querySelectorAll('.toast-alert');
    toasts.forEach(toast => {
        // I-slide pababa
        setTimeout(() => {
            toast.classList.remove('-translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 100);
        // I-slide pabalik (Auto-dismiss after 5 seconds)
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('-translate-y-20', 'opacity-0');
        }, 5000);
    });
}

// 4. GLOBAL FORM SUBMIT LISTENER WITH 30s TIMEOUT
function initGlobalLoader() {
    const forms = document.querySelectorAll('form');
    const loader = document.getElementById('global-loader');
    if(!loader) return;
    const loaderText = loader.querySelector('p');

    forms.forEach(form => {
        // Skip kung id is resendOtpForm o ayaw mong mag-load
        if(form.id === 'resendOtpForm') return; 

        form.addEventListener('submit', function (e) {
            loader.classList.remove('hidden');
            loader.classList.add('flex');
            loaderText.innerText = "Pinoproseso...";
            loaderText.classList.replace('text-red-600', 'text-slate-800');

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('cursor-not-allowed', 'opacity-70');
            }

            // SAFETY NET (30 seconds)
            setTimeout(() => {
                loaderText.innerText = "Masyadong matagal ang server. Paki-refresh ang page.";
                loaderText.classList.replace('text-slate-800', 'text-red-600');
                window.stop();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('cursor-not-allowed', 'opacity-70');
                }
            }, 30000);
        });
    });
}

// 5. SERVICE REQUEST MODAL LOGIC
function openRequestModal() {
    const modal = document.getElementById('requestModal');
    if(modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function closeRequestModal() {
    const modal = document.getElementById('requestModal');
    if(modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }
}

function showRequirements(selectElement) {
    const box = document.getElementById('requirements_box');
    const textElement = document.getElementById('requirements_text');
    const uploadSection = document.getElementById('upload_section');
    const attachmentInput = document.getElementById('attachments');

    if(!box || !selectElement) return;

    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const requirements = selectedOption.getAttribute('data-reqs');

    if (selectElement.value !== "") {
        textElement.innerText = requirements;
        box.classList.remove('hidden');
        box.classList.add('block');

        if (requirements && requirements.toLowerCase() !== 'valid id') {
            uploadSection.classList.remove('hidden');
            uploadSection.classList.add('block');
            attachmentInput.required = true;
        } else {
            uploadSection.classList.add('hidden');
            uploadSection.classList.remove('block');
            attachmentInput.required = false;
        }
    } else {
        box.classList.add('hidden');
        box.classList.remove('block');
        uploadSection.classList.add('hidden');
        uploadSection.classList.remove('block');
        attachmentInput.required = false;
    }
}

// 6. EMAIL OTP TIMER LOGIC
function initEmailTimer(secondsLeft) {
    const btn = document.getElementById('resendBtn');
    const timerDisplay = document.getElementById('timerDisplay');
    if(!btn || !timerDisplay || secondsLeft <= 0) return;

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
// INITIALIZER (Babasahin ang window.BDLS Config galing sa Laravel Blade)
document.addEventListener('DOMContentLoaded', () => {
    // I-setup ang UI Utilities
    initToasts();
    initGlobalLoader();

    // I-read ang Laravel Variables mula sa window.BDLS object
    if (typeof window.BDLS !== 'undefined') {
        // A. Tab Retention State
        if (window.BDLS.activeTab) {
            switchTab(window.BDLS.activeTab);
        } else {
            switchTab('dashboard'); // Default
        }

        // B. OTP Cooldown Timer
        if (window.BDLS.emailCooldown > 0) {
            initEmailTimer(window.BDLS.emailCooldown);
        }

        // C. Form Error Auto-Open Modal
        if (window.BDLS.hasFormErrors) {
            openRequestModal();
            setTimeout(() => {
                const selectEl = document.getElementById('document_type_id');
                if(selectEl && selectEl.value !== "") {
                    showRequirements(selectEl);
                }
            }, 100);
        }
    }
});