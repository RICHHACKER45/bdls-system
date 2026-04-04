// ==========================================
// BDLS SIGNUP WIZARD LOGIC
// ==========================================

// 1. Live Image Preview & Size Validation (5MB Limit)
function previewImage(event, previewId) {
  // Custom function para sa live image preview at size validation
  let input = event.target;
  let previewImg = document.getElementById(previewId);
  let placeholder = document.getElementById(previewId.replace('preview', 'placeholder'));
  // Kunin ang unang file
  let file = input.files.item(0);

  // DEFENSIVE DESIGN: Kung nag-cancel ang user
  if (!file) return;

  // ---> FILE SIZE VALIDATION (5MB LIMIT) <---
  const maxSizeInBytes = 5 * 1024 * 1024; // 5MB
  if (file.size > maxSizeInBytes) {
    showToast('Ang file ay masyadong malaki. Maximum size ay 5MB.');
    // I-reset ang input field para hindi ma-submit ang malaking file
    input.value = '';
    // Ibalik ang preview box sa "No image selected" state kung may dating picture
    previewImg.classList.add('hidden');
    previewImg.src = '';
    placeholder.classList.remove('hidden');
    return; // Patayin ang function dito, wag nang ituloy ang pag-load
  }

  // Kung nakapasa sa 5MB rule, i-load ang preview
  let reader = new FileReader();
  reader.onload = function (e) {
    previewImg.src = e.target.result;
    previewImg.classList.remove('hidden');
    placeholder.classList.add('hidden');
  };
  // IPASA ANG FILE BLOB NANG LIGTAS
  reader.readAsDataURL(file);
}

// 2. Frontend Toast Notification
function showToast(message) {
  const toast = document.getElementById('frontend-toast');
  const msgEl = document.getElementById('frontend-toast-message');
  msgEl.innerText = message;

  toast.classList.remove('hidden');
  toast.classList.add('flex');
  // Slide down animation
  setTimeout(() => {
    toast.classList.remove('-translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
  }, 10);
  // Auto-dismiss after 5 seconds
  setTimeout(() => {
    toast.classList.remove('translate-y-0', 'opacity-100');
    toast.classList.add('-translate-y-20', 'opacity-0');
    setTimeout(() => {
      toast.classList.add('hidden');
      toast.classList.remove('flex');
    }, 500); // hintayin matapos ang animation bago itago nang tuluyan
  }, 5000);
}

// 3. Progress Bar Logic and indicators
function updateProgressBar(stepNumber) {
  for (let i = 1; i <= 4; i++) {
    let indicator = document.getElementById('ind-' + i);
    if (indicator) {
      if (i <= stepNumber) {
        indicator.classList.remove('bg-slate-200', 'text-slate-500');
        indicator.classList.add('bg-red-600', 'text-white', 'shadow-md');
      } else {
        indicator.classList.add('bg-slate-200', 'text-slate-500');
        indicator.classList.remove('bg-red-600', 'text-white', 'shadow-md');
      }
    }
  }
  let progressLine = document.getElementById('progress-line');
  if (progressLine) {
    let progressPercentage = ((stepNumber - 1) / 3) * 100;
    progressLine.style.width = progressPercentage + '%';
  }
}

// 4. "Next" Button Validation
function validateAndGo(currentStepId, nextStepId) {
  let currentStepElement = document.getElementById(currentStepId);
  if (!currentStepElement) return;

  let isValid = true;
  let inputs = currentStepElement.querySelectorAll('input[required], select[required]');

  inputs.forEach((input) => {
    let errorMessage = document.getElementById('error-' + input.id);
    let hasError = false;

    // 1. Check kung blangko
    if (input.value.trim() === '') {
      hasError = true;
      if (errorMessage) errorMessage.textContent = 'This field is required.';

      // 2. SPECIAL RULE: Kung password field at less than 8 chars
    } else if (
      (input.id === 'password' || input.id === 'password_confirmation') &&
      input.value.length < 8
    ) {
      hasError = true;
      if (errorMessage) errorMessage.textContent = 'Password must be at least 8 characters.';

      // 3. SPECIAL RULE: Kung confirm password at hindi match sa password
    } else if (
      input.id === 'password_confirmation' &&
      input.value !== document.getElementById('password').value
    ) {
      hasError = true;
      if (errorMessage) errorMessage.textContent = 'Passwords do not match.';
    }

    // Ipakita o itago ang error base sa mga rules sa itaas
    if (hasError) {
      isValid = false;
      input.classList.add('border-red-500', 'ring-1', 'ring-red-500');
      if (errorMessage) errorMessage.classList.remove('hidden');
    } else {
      input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
      if (errorMessage) errorMessage.classList.add('hidden');
    }
  });

  if (isValid) {
    currentStepElement.classList.add('hidden');
    let nextElement = document.getElementById(nextStepId);
    if (nextElement) {
      nextElement.classList.remove('hidden');
      let stepNum = parseInt(nextStepId.replace('step', ''));
      updateProgressBar(stepNum);
      // I-save ang step sa memory <---
      sessionStorage.setItem('bdls_active_step', nextStepId);
    }
  }
}

// 5. "Back" Button Function
function goBack(currentStepId, prevStepId) {
  document.getElementById(currentStepId).classList.add('hidden');
  document.getElementById(prevStepId).classList.remove('hidden');
  let stepNum = parseInt(prevStepId.replace('step', ''));
  updateProgressBar(stepNum);
}

// 6. Real-Time Error Removal
document.addEventListener('DOMContentLoaded', function () {
  let requiredInputs = document.querySelectorAll('input[required], select[required]');
  requiredInputs.forEach((input) => {
    input.addEventListener('input', function () {
      let errorMessage = document.getElementById('error-' + this.id);
      this.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
      if (errorMessage) errorMessage.classList.add('hidden');
    });
  });
});

// 7. Show/Hide Password
function togglePassword(inputId) {
  let input = document.getElementById(inputId);
  let button = input.nextElementSibling; // Kukunin yung mismong SHOW button
  if (input.type === 'password') {
    input.type = 'text';
    button.innerText = 'HIDE';
  } else {
    input.type = 'password';
    button.innerText = 'SHOW';
  }
}

// 8. Legal Modals
function openLegalModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.body.style.overflow = 'hidden';
}

function closeLegalModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  document.body.style.overflow = 'auto';
}

function showErrorModal(message) {
  document.getElementById('errorModalMessage').innerText = message;
  document.getElementById('errorModal').classList.remove('hidden');
}

function closeErrorModal() {
  document.getElementById('errorModal').classList.add('hidden');
}

// 9. Form Submit Loader & Timeout (Global Safety Net)
document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  const loader = document.getElementById('global-loader');

  if (form) {
    form.addEventListener('submit', function (e) {
      // 1. Ipakita ang Loading Screen
      loader.classList.remove('hidden');
      loader.classList.add('flex');

      // 2. I-disable ang Submit Button para iwas spam
      const submitBtn = document.getElementById('submitBtn');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('cursor-not-allowed', 'opacity-50');
      }

      // 3. 30-Second Timeout Safety Net Modal
      setTimeout(() => {
        loader.classList.add('hidden');
        loader.classList.remove('flex');
        window.stop();

        // SERVER TIMEOUT
        document.getElementById('errorModalMessage').innerText =
          'May nangyaring error sa aming server o nawalan ka ng connection. Paki-try ulit.';
        document.getElementById('errorModal').classList.remove('hidden');
        document.getElementById('errorModal').classList.add('flex');

        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.classList.remove('cursor-not-allowed', 'opacity-50');
        }
      }, 30000);
    });
  }
});

// 10. Sticky Form & Fallback Logic
document.addEventListener('DOMContentLoaded', function () {
  const formElements = document.querySelectorAll(
    'input:not([type="password"]):not([type="file"]):not([type="checkbox"]), select',
  );

  formElements.forEach((element) => {
    const key = 'bdls_draft_' + (element.id || element.name);
    const savedValue = sessionStorage.getItem(key);
    if (savedValue) {
      element.value = savedValue;
    }
    element.addEventListener('input', function () {
      sessionStorage.setItem(key, this.value);
    });
    element.addEventListener('change', function () {
      sessionStorage.setItem(key, this.value);
    });
  });

  // STICKY STEP LOGIC WITH FALLBACK
  const savedStep = sessionStorage.getItem('bdls_active_step');
  if (savedStep && savedStep !== 'step1') {
    document.getElementById('step1').classList.add('hidden');

    // SECURITY FALLBACK: Kung ire-reload at nasa Step 4 pero walang password, IBALIK SA STEP 3
    if (savedStep === 'step4' && document.getElementById('password').value === '') {
      sessionStorage.setItem('bdls_active_step', 'step3');
      document.getElementById('step3').classList.remove('hidden');
      updateProgressBar(3);
    } else {
      let activeStepElement = document.getElementById(savedStep);
      if (activeStepElement) {
        activeStepElement.classList.remove('hidden');
        let stepNum = parseInt(savedStep.replace('step', ''));
        updateProgressBar(stepNum);
      }
    }
  }
});
