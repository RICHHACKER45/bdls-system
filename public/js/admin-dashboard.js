// ==========================================
// ADMIN DASHBOARD: AJAX POLLING & EXPONENTIAL BACKOFF
// ==========================================

let currentCount = window.initialPendingCount || 0;
let pollInterval = 10000; // Magsimula sa 10 seconds
let unchangedCycles = 0;

function pollRegistrations() {
    fetch(window.pollingUrl)
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('pending-badge');
            
            // 1. I-update ang red notification badge sa Sidebar
            if (data.count > 0) {
                badge.innerText = data.count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            // 2. Exponential Backoff Logic
            if (data.count > currentCount) {
                // MAY BAGONG DATA! Ipakita ang Twitter-style pill button
                document.getElementById('new-data-pill').classList.remove('hidden');
                
                // I-reset ang pahinga ng server dahil may aktibidad
                unchangedCycles = 0; 
                pollInterval = 10000; // Balik sa 10 seconds
            } else {
                // WALANG BAGONG DATA.
                unchangedCycles++;
                if (unchangedCycles >= 5) {
                    // Kung limang beses (50 seconds) nang walang bago,
                    // pagpahingahin ang MySQL. Gawing 30 seconds ang interval.
                    pollInterval = 30000; 
                }
            }

            // I-sync ang memory
            currentCount = data.count;
        })
        .catch(err => console.error("Polling error:", err))
        .finally(() => {
            // Recursive setTimeout (Hindi setInterval para iwas request pile-up)
            setTimeout(pollRegistrations, pollInterval);
        });
}

// Simulan ang unang ikot ng pagmamanman (Polling)
setTimeout(pollRegistrations, pollInterval);