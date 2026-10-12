/**
 * Admin Real-Time Broadcasting Listeners
 *
 * Handles real-time updates for admin dashboard and list pages.
 * Listens to private admin channels for:
 * - New reservations
 * - Payment processing
 * - Delivery status changes
 * - Contract creation
 * - Reclamations
 * - Compensation code creation
 *
 * Features:
 * - Auto-refresh list pages when new items are created
 * - Toast notifications for important events
 * - Status badge updates
 * - Sound notification (optional)
 */

import Echo from './echo.js';

/**
 * Initialize admin real-time listeners
 * Should be called on admin dashboard and list pages
 */
export function initAdminRealtimeListeners() {
    if (!window.Echo || !isAdminPage()) {
        return;
    }

    // Initialize individual channel listeners
    listenToReservations();
    listenToPayments();
    listenToDeliveries();
    listenToContracts();
    listenToReclamations();
    listenToCompensationCodes();
}

/**
 * Check if current user is admin (can be customized)
 */
function isAdminPage() {
    return document.body.classList.contains('admin') ||
           window.location.pathname.startsWith('/admin');
}

/**
 * Listen to new reservations
 */
function listenToReservations() {
    try {
        window.Echo.private('admin.reservations')
            .listen('reservation-created', (event) => {
                handleReservationCreated(event);
            });
    } catch (error) {
        console.error('Error setting up reservations listener:', error);
    }
}

/**
 * Handle new reservation event
 */
function handleReservationCreated(event) {
    console.log('New reservation received:', event);

    // Show notification
    showNotification(
        'Nouvelle réservation',
        `${event.user.name} a créé une réservation pour ${event.equipment.name}`,
        'info'
    );

    // Optionally refresh reservations list if on that page
    if (window.location.pathname.includes('/admin/reservations')) {
        // Add new row to table if possible, or refresh
        refreshPage();
    }
}

/**
 * Listen to payment processing
 */
function listenToPayments() {
    try {
        window.Echo.private('admin.payments')
            .listen('payment-processed', (event) => {
                handlePaymentProcessed(event);
            });
    } catch (error) {
        console.error('Error setting up payments listener:', error);
    }
}

/**
 * Handle payment processed event
 */
function handlePaymentProcessed(event) {
    console.log('Payment processed:', event);

    // Show notification based on status
    const statusText = event.status === 'paid' ? 'effectué' : 'mis à jour';
    showNotification(
        'Paiement ' + statusText,
        `${event.user.name} - ${event.amount} TND - ${event.method}`,
        event.status === 'paid' ? 'success' : 'info'
    );

    // Refresh payments list if on that page
    if (window.location.pathname.includes('/admin/payments')) {
        refreshPage();
    }
}

/**
 * Listen to delivery status changes
 */
function listenToDeliveries() {
    try {
        window.Echo.private('admin.deliveries')
            .listen('delivery-status-changed', (event) => {
                handleDeliveryStatusChanged(event);
            });
    } catch (error) {
        console.error('Error setting up deliveries listener:', error);
    }
}

/**
 * Handle delivery status change event
 */
function handleDeliveryStatusChanged(event) {
    console.log('Delivery status changed:', event);

    showNotification(
        'Livraison mise à jour',
        `Statut: ${event.old_status} → ${event.status}`,
        'info'
    );

    // Refresh deliveries list if on that page
    if (window.location.pathname.includes('/admin/livraisons')) {
        refreshPage();
    }
}

/**
 * Listen to contract creation
 */
function listenToContracts() {
    try {
        window.Echo.private('admin.contracts')
            .listen('contract-created', (event) => {
                handleContractCreated(event);
            });
    } catch (error) {
        console.error('Error setting up contracts listener:', error);
    }
}

/**
 * Handle contract created event
 */
function handleContractCreated(event) {
    console.log('Contract created:', event);

    showNotification(
        'Nouveau contrat',
        `${event.user.name} - Réservation #${event.reservation.id}`,
        'info'
    );

    // Refresh contracts list if on that page
    if (window.location.pathname.includes('/admin/contracts')) {
        refreshPage();
    }
}

/**
 * Listen to reclamations
 */
function listenToReclamations() {
    try {
        window.Echo.private('admin.reclamations')
            .listen('reclamation-created', (event) => {
                handleReclamationCreated(event);
            })
            .listen('reclamation-approved', (event) => {
                handleReclamationApproved(event);
            });
    } catch (error) {
        console.error('Error setting up reclamations listener:', error);
    }
}

/**
 * Handle reclamation created event
 */
function handleReclamationCreated(event) {
    console.log('Reclamation created:', event);

    showNotification(
        'Nouvelle réclamation',
        `${event.user.name} - ${event.title} (${event.incidents_count} incident(s))`,
        'warning'
    );

    // Refresh reclamations list if on that page
    if (window.location.pathname.includes('/admin/reclamations')) {
        refreshPage();
    }
}

/**
 * Handle reclamation approved event
 */
function handleReclamationApproved(event) {
    console.log('Reclamation approved:', event);

    const codeInfo = event.compensation_code ? ` - Code: ${event.compensation_code.code}` : '';
    showNotification(
        'Réclamation approuvée',
        `Réclamation #${event.id}${codeInfo}`,
        'success'
    );

    // Refresh reclamations list if on that page
    if (window.location.pathname.includes('/admin/reclamations')) {
        refreshPage();
    }
}

/**
 * Listen to compensation code creation
 */
function listenToCompensationCodes() {
    try {
        window.Echo.private('admin.compensation-codes')
            .listen('compensation-code-created', (event) => {
                handleCompensationCodeCreated(event);
            });
    } catch (error) {
        console.error('Error setting up compensation codes listener:', error);
    }
}

/**
 * Handle compensation code created event
 */
function handleCompensationCodeCreated(event) {
    console.log('Compensation code created:', event);

    showNotification(
        'Nouveau code de compensation',
        `Code: ${event.code} - ${event.value}${event.type === 'percentage' ? '%' : ' TND'}`,
        'info'
    );

    // Refresh compensation codes list if on that page
    if (window.location.pathname.includes('/admin/codes-compensation')) {
        refreshPage();
    }
}

/**
 * Show browser notification using native API or custom toast
 */
function showNotification(title, message, type = 'info') {
    // Try to use browser notification API if available
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, {
            body: message,
            tag: 'solarshare-admin',
        });
    }

    // Always show custom toast (implement or use your toast library)
    showToast(title, message, type);
}

/**
 * Show toast notification
 * Can be customized to use your preferred toast library (Bootstrap, Tailwind, etc.)
 */
function showToast(title, message, type = 'info') {
    // Create a simple toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toast = document.createElement('div');
    const bgColor = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6',
    }[type] || '#3b82f6';

    toast.style.cssText = `
        background-color: ${bgColor};
        color: white;
        padding: 16px;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        font-size: 14px;
        animation: slideIn 0.3s ease-in-out;
    `;

    toast.innerHTML = `
        <strong>${title}</strong>
        <div style="margin-top: 8px; font-size: 13px; opacity: 0.9;">${message}</div>
    `;

    toastContainer.appendChild(toast);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in-out';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

/**
 * Refresh the current page
 */
function refreshPage() {
    // Option 1: Soft refresh (AJAX reload of table/list)
    // Option 2: Full page refresh
    // For now, we'll do a soft refresh by triggering a custom event
    // that pages can listen to

    window.dispatchEvent(new CustomEvent('admin-realtime-refresh'));

    // Alternatively, do a full refresh after a delay
    // setTimeout(() => {
    //     location.reload();
    // }, 1000);
}

// Add CSS animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
