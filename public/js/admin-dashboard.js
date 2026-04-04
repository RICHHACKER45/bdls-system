// ==========================================
// ADMIN DASHBOARD: UNIFIED AJAX POLLING & BACKOFF
// ==========================================

let pendingCount = window.initialPendingCount || 0;
let queueCount = window.initialQueueCount || 0;

let pollInterval = 10000; // Magsimula sa 10 seconds
let unchangedCycles = 0;

function pollDashboard() {
    // Sabay na kukunin ang Pending at Queue para tipid sa server load
    Promise.all([
        fetch(window.pollingUrl).then(res => res.json()),
        fetch(window.queuePollingUrl).then(res => res.json())
    ])
    .then(([pendingData, queueData]) => {
        let hasNewData = false;

        // 1. Pending Notification Badge Update
        const badge = document.getElementById('pending-badge');
        if (pendingData.count > 0) {
            if (badge) { badge.innerText = pendingData.count; badge.classList.remove('hidden'); }
        } else {
            if (badge) badge.classList.add('hidden');
        }

        // Check kung may nadagdag sa Pending
        if (pendingData.count > pendingCount) {
            hasNewData = true;
            pendingCount = pendingData.count;
        }

        // 2. Queue Update Check
        if (queueData.count > queueCount) {
            hasNewData = true;
            queueCount = queueData.count;
        }

        // 3. Exponential Backoff Logic
        if (hasNewData) {
            // Ipakita ang Twitter-style refresh pill
            document.getElementById('new-data-pill').classList.remove('hidden');
            unchangedCycles = 0;
            pollInterval = 10000; // Balik sa 10 seconds
        } else {
            unchangedCycles++;
            if (unchangedCycles >= 5) {
                // Pagpahingahin ang server (30 seconds)
                pollInterval = 30000; 
            }
        }
    })
    .catch(err => console.error('Dashboard Polling error:', err))
    .finally(() => {
        setTimeout(pollDashboard, pollInterval);
    });
}

// Simulan
setTimeout(pollDashboard, pollInterval);
