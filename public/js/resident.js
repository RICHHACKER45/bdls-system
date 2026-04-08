// ==========================================
// BDLS RESIDENT MODULE: CORE JAVASCRIPT
// ==========================================

// 1. HAMBURGER & SIDEBAR LOGIC
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('mobile-overlay');
  if (sidebar && overlay) {
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
  }
}

// 2. TAB SWITCHING
function switchTab(tabId) {
  // Itago lahat ng tabs
  document.querySelectorAll('.tab-content').forEach((el) => el.classList.add('hidden'));

  // Ipakita ang piniling tab
  const targetTab = document.getElementById('tab-' + tabId);
  if (targetTab) targetTab.classList.remove('hidden');

  // I-reset ang kulay ng lahat ng buttons sa sidebar
  document.querySelectorAll('.nav-btn').forEach((btn) => {
    btn.classList.remove('bg-red-50', 'text-red-700');
    btn.classList.add('text-slate-600', 'hover:bg-slate-50');
  });

  // Kulayan ng pula ang active na button
  const activeBtn = document.getElementById('nav-' + tabId);
  if (activeBtn) {
    activeBtn.classList.add('bg-red-50', 'text-red-700');
    activeBtn.classList.remove('text-slate-600', 'hover:bg-slate-50');
  }

  // Kung nasa mobile, isara ang sidebar pagkatapos pumili
  const sidebar = document.getElementById('sidebar');
  if (window.innerWidth < 1024 && sidebar && !sidebar.classList.contains('-translate-x-full')) {
    toggleSidebar();
  }
}

// 3. TASK 4: RESUBMIT MODAL LOGIC
function openResubmitModal() {
  const modal = document.getElementById('resubmitModal');
  if (modal) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }
}

function closeResubmitModal() {
  const modal = document.getElementById('resubmitModal');
  if (modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
  }
}

// 4. TOASTS & LOADER
function initToasts() {
  const toasts = document.querySelectorAll('.toast-alert');
  toasts.forEach((toast) => {
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
  if (!loader) return;

  forms.forEach((form) => {
    // Skip kung id is resendOtpForm o ayaw mong mag-load
    if (form.id === 'resendOtpForm') return;
    form.addEventListener('submit', function () {
      loader.classList.remove('hidden');
      loader.classList.add('flex');
    });
  });
}

// 5. SERVICE REQUEST MODAL
function openRequestModal() {
  const modal = document.getElementById('requestModal');
  if (modal) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }
}

function closeRequestModal() {
  const modal = document.getElementById('requestModal');
  if (modal) {
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

  if (!box || !selectElement) return;

  const selectedOption = selectElement.options[selectElement.selectedIndex];
  const requirements = selectedOption.getAttribute('data-reqs');

  if (selectElement.value !== '') {
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

// 6. TASK 3: RESIDENT STATUS POLLING
function pollResidentStatus() {
  if (!window.BDLS || !window.BDLS.pollingUrl) return;

  fetch(window.BDLS.pollingUrl)
    .then((res) => res.json())
    .then((data) => {
      // Reload kung na-verify na (false -> true) o kung nadagdagan ang rejection
      if (
        data.is_verified !== window.BDLS.isVerified ||
        data.rejection_count > window.BDLS.rejectionCount
      ) {
        location.reload();
      }
    })
    .catch((err) => console.error('Polling error:', err));
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
    }

    if (window.BDLS.hasFormErrors) {
      openRequestModal();
    }

    setInterval(pollResidentStatus, 10000);
  }
});
