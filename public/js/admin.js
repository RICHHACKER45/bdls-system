// ==========================================
// BDLS ADMIN MODULE: CORE JAVASCRIPT
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
  document.querySelectorAll('.tab-content').forEach((el) => {
    el.classList.add('hidden');
    el.classList.remove('block');
  });

  const selectedTab = document.getElementById('tab-' + tabId);
  if (selectedTab) {
    selectedTab.classList.remove('hidden');
    selectedTab.classList.add('block');
  }

  document.querySelectorAll('.nav-btn').forEach((btn) => {
    btn.classList.remove('bg-slate-900', 'text-white');
    btn.classList.add('text-slate-600', 'hover:bg-slate-100');
  });

  const activeBtn = document.getElementById('nav-' + tabId);
  if (activeBtn) {
    activeBtn.classList.add('bg-slate-900', 'text-white');
    activeBtn.classList.remove('text-slate-600', 'hover:bg-slate-100');
    const titleEl = document.getElementById('topbar-title');
    if (titleEl) titleEl.innerText = activeBtn.innerText.trim();
  }

  if (window.innerWidth < 1024) toggleSidebar();
}

// 3. SUB-TAB LOGIC
function showSubTab(tabId, btnElement) {
  // Determine the container context (sub-tab buttons are grouped per main tab)
  const subTabContainer = btnElement.closest('.tab-content') || document.body;

  // Hide all sub-tab contents within this context
  subTabContainer.querySelectorAll('.sub-tab-content').forEach((el) => el.classList.add('hidden'));

  // Show target sub-tab
  const targetTab = document.getElementById(tabId);
  if (targetTab) targetTab.classList.remove('hidden');

  // Handle button highlights based on which sub-tab group it belongs to
  const siblingButtons = btnElement.parentElement.querySelectorAll('.sub-tab-btn');

  siblingButtons.forEach((btn) => {
    btn.classList.remove(
      'bg-slate-900',
      'text-white',
      'bg-red-100',
      'text-red-700',
      'border-slate-900',
      'text-slate-900',
    );
    btn.classList.add('bg-slate-200', 'text-slate-700', 'border-transparent', 'text-slate-400');
  });

  if (tabId.startsWith('queue-')) {
    // Queue Sub-tab logic (Underline style)
    btnElement.classList.add('text-slate-900', 'border-slate-900');
    btnElement.classList.remove('text-slate-400', 'border-transparent');
  } else {
    if (tabId === 'sub-pending' || tabId === 'sub-approved') {
      btnElement.classList.add('bg-slate-900', 'text-white');
    } else if (tabId === 'sub-rejected') {
      btnElement.classList.add('bg-red-100', 'text-red-700');
    }
    btnElement.classList.remove('bg-slate-200', 'text-slate-700');
  }
}

// 4. TASK 2: UPDATED ONE-WAY STATUS MODAL
function openStatusModal(requestId, nextStatus, nextStatusLabel) {
  const modal = document.getElementById('statusModal');
  const label = document.getElementById('nextStatusLabel');
  const input = document.getElementById('targetStatusInput');
  const form = document.getElementById('statusForm');

  if (modal && label && input && form) {
    label.innerText = nextStatusLabel;
    input.value = nextStatus;
    form.action = `/admin/request/${requestId}/update-status`;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }
}

function closeStatusModal() {
  document.getElementById('statusModal').classList.add('hidden');
}

// 5. TASK 1: DELETE MODAL LOGIC
function openDeleteModal(userId) {
  const modal = document.getElementById('deleteModal');
  const form = document.getElementById('deleteForm');
  if (modal && form) {
    form.action = `/admin/account/${userId}`;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }
}

function closeDeleteModal() {
  document.getElementById('deleteModal').classList.add('hidden');
}

// 6. EXISTING MODALS
function openRejectModal(userId, userName) {
  const modal = document.getElementById('rejectModal');
  if (modal) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('rejectUserName').innerText = userName;
    document.getElementById('rejectForm').action = `/admin/account/${userId}/reject`;
  }
}

function closeRejectModal() {
  document.getElementById('rejectModal').classList.add('hidden');
}

function openModal(imageSrc, title) {
  document.getElementById('modalImg').src = imageSrc;
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('imageModal').classList.remove('hidden');
  document.getElementById('imageModal').classList.add('flex');
}

function closeModal() {
  document.getElementById('imageModal').classList.add('hidden');
  document.getElementById('imageModal').classList.remove('flex');
  document.getElementById('modalImg').src = '';
}

// 7. TOASTS & LOADER
function initUI() {
  const toasts = document.querySelectorAll('.toast-alert');
  toasts.forEach((toast) => {
    setTimeout(() => {
      toast.classList.remove('-translate-y-20', 'opacity-0');
      toast.classList.add('translate-y-0', 'opacity-100');
    }, 100);
    setTimeout(() => {
      toast.classList.remove('translate-y-0', 'opacity-100');
      toast.classList.add('-translate-y-20', 'opacity-0');
    }, 5000);
  });

  const forms = document.querySelectorAll('form');
  const loader = document.getElementById('global-loader');

  forms.forEach((form) => {
    if (form.method.toUpperCase() === 'GET') return;
    form.addEventListener('submit', () => {
      if (loader) {
        loader.classList.remove('hidden');
        loader.classList.add('flex');
      }
    });
  });
}

// 8. POLLING
let pendingCount = 0;
let queueCount = 0;

function pollDashboard() {
  if (!window.BDLS_ADMIN) return;

  Promise.all([
    fetch(window.BDLS_ADMIN.pollingUrl).then((res) => res.json()),
    fetch(window.BDLS_ADMIN.queuePollingUrl).then((res) => res.json()),
  ])
    .then(([pendingData, queueData]) => {
      const pBadge = document.getElementById('pending-badge');
      if (pendingData.count > 0) {
        pBadge.innerText = pendingData.count;
        pBadge.classList.remove('hidden');
      } else {
        pBadge.classList.add('hidden');
      }

      const qBadge = document.getElementById('queue-badge');
      if (queueData.count > 0) {
        qBadge.innerText = queueData.count;
        qBadge.classList.remove('hidden');
      } else {
        qBadge.classList.add('hidden');
      }

      if (pendingData.count > pendingCount || queueData.count > queueCount) {
        document.getElementById('new-data-pill').classList.remove('hidden');
      }
    })
    .finally(() => {
      setTimeout(pollDashboard, 10000);
    });
}

// ==========================================
// INITIALIZER
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
  initUI();
  if (typeof window.BDLS_ADMIN !== 'undefined') {
    switchTab(window.BDLS_ADMIN.activeTab || 'pending');
    pendingCount = window.BDLS_ADMIN.initialPendingCount || 0;
    queueCount = window.BDLS_ADMIN.initialQueueCount || 0;
    setTimeout(pollDashboard, 5000);
  }
});
