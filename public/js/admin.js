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

// 3. SUB-TAB LOGIC (UNIVERSAL PILL DESIGN)
function showSubTab(tabId, btnElement) {
  const subTabContainer = btnElement.closest('.tab-content') || document.body;
  subTabContainer.querySelectorAll('.sub-tab-content').forEach((el) => el.classList.add('hidden'));

  const targetTab = document.getElementById(tabId);
  if (targetTab) targetTab.classList.remove('hidden');

  const siblingButtons = btnElement.parentElement.querySelectorAll('.sub-tab-btn');
  siblingButtons.forEach((btn) => {
    btn.classList.remove(
      'bg-slate-900',
      'text-white',
      'bg-red-100',
      'text-red-700',
      'border-slate-900'
    );
    btn.classList.add('bg-slate-200', 'text-slate-700', 'border-transparent');
  });

  // Kung ang pinindot ay mga "History" o "Rejected", gawing red ang pill
  if (tabId === 'sub-rejected' || tabId === 'queue-received') {
    btnElement.classList.add('bg-red-100', 'text-red-700');
    btnElement.classList.remove('bg-slate-200', 'text-slate-700');
  } else {
    // Default Active Tab
    btnElement.classList.add('bg-slate-900', 'text-white', 'border-slate-900');
    btnElement.classList.remove('bg-slate-200', 'text-slate-700', 'border-transparent');
  }
}

// 4. TASK 2: UPDATED ONE-WAY STATUS MODAL (Grammar & UX Fix)
function openStatusModal(requestId, nextStatus, nextStatusLabel) {
  const modal = document.getElementById('statusModal');
  const titleLabel = document.getElementById('statusModalTitle'); // THE FIX: Kinuha ang buong Title
  const input = document.getElementById('targetStatusInput');
  const form = document.getElementById('statusForm');

  if (modal && titleLabel && input && form) {
    // THE FIX: Smart Grammar Routing
    if (nextStatus === 'rejected') {
      titleLabel.innerHTML = `<span class="text-red-600">Reject Request?</span>`;
    } else {
      titleLabel.innerHTML = `Move to <span class="text-blue-600">${nextStatusLabel}</span>?`;
    }

    input.value = nextStatus;
    form.action = `/admin/request/${requestId}/update-status`;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }
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

// ==========================================
// SUSPEND MODAL LOGIC (7-Day Penalty)
// ==========================================
function openSuspendModal(userId, userName) {
  const modal = document.getElementById('suspendModal');
  const form = document.getElementById('suspendForm');
  const nameLabel = document.getElementById('suspendUserName');

  if (modal && form) {
    if (nameLabel) nameLabel.innerText = userName;
    form.action = `/admin/account/${userId}/suspend`;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }
}

function closeSuspendModal() {
  const modal = document.getElementById('suspendModal');
  if (modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
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
