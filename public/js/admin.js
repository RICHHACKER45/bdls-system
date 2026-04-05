// ==========================================
// BDLS ADMIN MODULE: CORE JAVASCRIPT
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
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    const selectedTab = document.getElementById('tab-' + tabId);
    if(selectedTab) {
        selectedTab.classList.remove('hidden');
        selectedTab.classList.add('block');
    }

    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.classList.remove('bg-slate-900', 'text-white');
        btn.classList.add('text-slate-600', 'hover:bg-slate-100', 'hover:text-slate-900');
    });

    const activeBtn = document.getElementById('nav-' + tabId);
    if(activeBtn) {
        activeBtn.classList.add('bg-slate-900', 'text-white');
        activeBtn.classList.remove('text-slate-600', 'hover:bg-slate-100', 'hover:text-slate-900');
        const titleEl = document.getElementById('topbar-title');
        if(titleEl) titleEl.innerText = activeBtn.innerText.trim();
    }

    if(window.innerWidth < 1024) toggleSidebar();
}

// 3. SUB-TAB LOGIC (Pending/Approved/Locked)
function showSubTab(tabId, btnElement) {
    document.querySelectorAll('.sub-tab-content').forEach(el => el.classList.add('hidden'));
    const targetTab = document.getElementById(tabId);
    if(targetTab) targetTab.classList.remove('hidden');

    document.querySelectorAll('.sub-tab-btn').forEach(btn => {
        btn.classList.remove('bg-slate-900', 'text-white', 'bg-red-100', 'text-red-700');
        btn.classList.add('bg-slate-200', 'text-slate-700');
    });

    if(tabId === 'sub-pending') {
        btnElement.classList.add('bg-slate-900', 'text-white');
        btnElement.classList.remove('bg-slate-200', 'text-slate-700');
    } else if(tabId === 'sub-locked') {
        btnElement.classList.add('bg-red-100', 'text-red-700');
        btnElement.classList.remove('bg-slate-200', 'text-slate-700');
    } else {
        btnElement.classList.add('bg-slate-900', 'text-white');
        btnElement.classList.remove('bg-slate-200', 'text-slate-700');
    }
}

// 4. MODALS (Reject & Image Preview)
function openRejectModal(userId, userName) {
    const modal = document.getElementById('rejectModal');
    if(modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('rejectUserName').innerText = userName;
        document.getElementById('rejectForm').action = `/admin/account/${userId}/reject`;
    }
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    if(modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function openModal(imageSrc, title) {
    document.getElementById('modalImg').src = imageSrc;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('modalImg').src = '';
}

// 5. TOAST NOTIFICATIONS & GLOBAL LOADER
function initUI() {
    const toasts = document.querySelectorAll('.toast-alert');
    toasts.forEach(toast => {
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
    if(!loader) return;
    
    forms.forEach(form => {
        if(form.method.toUpperCase() === 'GET') return; // Skip search bars
        form.addEventListener('submit', () => {
            loader.classList.remove('hidden');
            loader.classList.add('flex');
        });
    });
}

// 6. UNIFIED AJAX POLLING WITH EXPONENTIAL BACKOFF
let pendingCount = 0;
let queueCount = 0;
let pollInterval = 10000;
let unchangedCycles = 0;

function pollDashboard() {
    if (!window.BDLS_ADMIN || !window.BDLS_ADMIN.pollingUrl) return;

    Promise.all([
        fetch(window.BDLS_ADMIN.pollingUrl).then(res => res.json()),
        fetch(window.BDLS_ADMIN.queuePollingUrl).then(res => res.json())
    ])
    .then(([pendingData, queueData]) => {
        let hasNewData = false;

        // Pending Badge Update
        const badge = document.getElementById('pending-badge');
        if (pendingData.count > 0) {
            if (badge) { badge.innerText = pendingData.count; badge.classList.remove('hidden'); }
        } else {
            if (badge) badge.classList.add('hidden');
        }

        if (pendingData.count > pendingCount) {
            hasNewData = true;
            pendingCount = pendingData.count;
        }

        if (queueData.count > queueCount) {
            hasNewData = true;
            queueCount = queueData.count;
        }

        if (hasNewData) {
            const newPill = document.getElementById('new-data-pill');
            if(newPill) newPill.classList.remove('hidden');
            unchangedCycles = 0;
            pollInterval = 10000;
        } else {
            unchangedCycles++;
            if (unchangedCycles >= 5) {
                pollInterval = 30000; // Backoff to 30s
            }
        }
    })
    .catch(err => console.error('Dashboard Polling error:', err))
    .finally(() => {
        setTimeout(pollDashboard, pollInterval);
    });
}

// ==========================================
// INITIALIZER
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    initUI();

    if (typeof window.BDLS_ADMIN !== 'undefined') {
        if (window.BDLS_ADMIN.activeTab) {
            switchTab(window.BDLS_ADMIN.activeTab);
        } else {
            switchTab('pending');
        }

        pendingCount = window.BDLS_ADMIN.initialPendingCount || 0;
        queueCount = window.BDLS_ADMIN.initialQueueCount || 0;

        setTimeout(pollDashboard, pollInterval);
    }
});