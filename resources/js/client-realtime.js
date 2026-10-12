/**
 * Client Real-Time Broadcasting Listeners
 *
 * Handles real-time personal updates for authenticated clients.
 * Listens to user-specific private channels for:
 * - Payment status updates
 * - Delivery status changes
 * - Contract notifications
 * - Reclamation approvals
 * - Compensation code awards
 *
 * Features:
 * - Personal notifications for relevant events
 * - Auto-refresh personal pages
 * - Toast notifications
 * - Badge updates on navigation
 */

import Echo from './echo.js';

/**
 * Initialize client real-time listeners
 * Should be called on client pages (frontend)
 * Automatically detects current user ID from page
 */
export function initClientRealtimeListeners() {
    if (!window.Echo || !isClientPage()) {
        return;
    }

    const userId = getCurrentUserId();
    if (!userId) {
        console.warn('Cannot initialize client listeners: user not authenticated');
        return;
    }

    // Initialize individual channel listeners
    listenToUserPayments(userId);
    listenToUserDeliveries(userId);
    listenToUserContracts(userId);
    listenToUserReclamations(userId);
    listenToUserCompensationCodes(userId);
}

/**
 * Check if current page is client page (not admin)
 */
function isClientPage() {
    return !document.body.classList.contains('admin') &&
           !window.location.pathname.startsWith('/admin');
}

/**
 * Get current user ID from page (meta tag, data attribute, or auth state)
 */
function getCurrentUserId() {
    // Check meta tag
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    if (userIdMeta) {
        return userIdMeta.content;
    }

    // Check data attribute on body
    if (document.body.dataset.userId) {
        return document.body.dataset.userId;
    }

    // Try to get from window object (if set by your auth system)
    if (window.currentUser?.id) {
        return window.currentUser.id;
    }

    return null;
}

/**
 * Listen to user payment updates
 */
function listenToUserPayments(userId) {
    try {
        window.Echo.private(`user.${userId}.payments`)
            .listen('payment-processed', (event) => {
                handleUserPaymentProcessed(event);
            });
    } catch (error) {
        console.error('Error setting up user payments listener:', error);
    }
}

/**
 * Handle user payment processed event
 */
function handleUserPaymentProcessed(event) {
    console.log('Your payment was processed:', event);

    const message = event.status === 'paid'
        ? `Paiement de ${event.amount} TND confirmé (${event.method})`
        : `Paiement mise à jour: ${event.status}`;

    showClientNotification(
        'Paiement',
        message,
        event.status === 'paid' ? 'success' : 'info'
    );

    // Refresh payments page if visible
    if (window.location.pathname.includes('/mes-paiements') ||
        window.location.pathname.includes('/paiement')) {
        refreshUserPage();
    }

    // Update any payment status badges
    updatePaymentBadges(event);
}

/**
 * Listen to user delivery updates
 */
function listenToUserDeliveries(userId) {
    try {
        window.Echo.private(`user.${userId}.deliveries`)
            .listen('delivery-status-changed', (event) => {
                handleUserDeliveryStatusChanged(event);
            });
    } catch (error) {
        console.error('Error setting up user deliveries listener:', error);
    }
}

/**
 * Handle user delivery status change
 */
function handleUserDeliveryStatusChanged(event) {
    console.log('Your delivery status changed:', event);

    const statusLabels = {
        'prete': 'Prête pour la livraison',
        'en_preparation': 'En préparation',
        'remise_au_client': 'Remise au client',
        'retour_recu': 'Retour reçu',
        'terminee': 'Terminée',
        'annulee': 'Annulée',
    };

    const newStatus = statusLabels[event.status] || event.status;
    showClientNotification(
        'Livraison mise à jour',
        `Votre livraison est maintenant: ${newStatus}`,
        'info'
    );

    // Refresh deliveries page if visible
    if (window.location.pathname.includes('/livraisons')) {
        refreshUserPage();
    }

    // Update delivery status displays
    updateDeliveryBadges(event);
}

/**
 * Listen to user contract updates
 */
function listenToUserContracts(userId) {
    try {
        window.Echo.private(`user.${userId}.contracts`)
            .listen('contract-created', (event) => {
                handleUserContractCreated(event);
            });
    } catch (error) {
        console.error('Error setting up user contracts listener:', error);
    }
}

/**
 * Handle user contract created
 */
function handleUserContractCreated(event) {
    console.log('Your contract is ready:', event);

    showClientNotification(
        'Contrat disponible',
        `Un nouveau contrat vous attend pour votre réservation`,
        'info',
        '/contracts'
    );

    // Refresh contracts page if visible
    if (window.location.pathname.includes('/contracts')) {
        refreshUserPage();
    }

    // Update contract badge/counter
    updateContractBadges(event);
}

/**
 * Listen to user reclamation updates
 */
function listenToUserReclamations(userId) {
    try {
        window.Echo.private(`user.${userId}.reclamations`)
            .listen('reclamation-approved', (event) => {
                handleUserReclamationApproved(event);
            });
    } catch (error) {
        console.error('Error setting up user reclamations listener:', error);
    }
}

/**
 * Handle user reclamation approved
 */
function handleUserReclamationApproved(event) {
    console.log('Your reclamation was approved:', event);

    let message = `Votre réclamation #${event.id} a été approuvée`;
    if (event.compensation_code) {
        message += `. Code reçu: ${event.compensation_code.code}`;
    }

    showClientNotification(
        'Réclamation approuvée',
        message,
        'success',
        '/reclamations'
    );

    // Refresh reclamations page if visible
    if (window.location.pathname.includes('/reclamations')) {
        refreshUserPage();
    }

    // Update reclamation status
    updateReclamationBadges(event);
}

/**
 * Listen to user compensation codes
 */
function listenToUserCompensationCodes(userId) {
    try {
        window.Echo.private(`user.${userId}.compensation-codes`)
            .listen('compensation-code-created', (event) => {
                handleUserCompensationCodeCreated(event);
            });
    } catch (error) {
        console.error('Error setting up user compensation codes listener:', error);
    }
}

/**
 * Handle user compensation code created
 */
function handleUserCompensationCodeCreated(event) {
    console.log('You received a compensation code:', event);

    const value = event.type === 'percentage' ? `${event.value}%` : `${event.value} TND`;
    showClientNotification(
        'Code de compensation reçu!',
        `${value} de réduction - Code: ${event.code}`,
        'success',
        '/codes-compensation'
    );

    // Refresh compensation codes page if visible
    if (window.location.pathname.includes('/codes-compensation')) {
        refreshUserPage();
    }

    // Update code counter/badge
    updateCompensationCodeBadges(event);
}

/**
 * Show client notification with optional action link
 */
function showClientNotification(title, message, type = 'info', actionUrl = null) {
    // Try browser notification if permission granted
    if ('Notification' in window && Notification.permission === 'granted') {
        const notification = new Notification(title, {
            body: message,
            tag: 'solarshare-client',
            badge: '/favicon.ico',
        });

        if (actionUrl) {
            notification.onclick = () => {
                window.location.href = actionUrl;
                notification.close();
            };
        }
    }

    // Show custom toast
    showClientToast(title, message, type, actionUrl);
}

/**
 * Show custom toast for client
 */
function showClientToast(title, message, type = 'info', actionUrl = null) {
    // Create container if needed
    let toastContainer = document.getElementById('client-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'client-toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
        `;
        document.body.appendChild(toastContainer);
    }

    // Create toast
    const toast = document.createElement('div');
    const bgColor = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6',
    }[type] || '#3b82f6';

    let html = `
        <strong>${title}</strong>
        <div style="margin-top: 8px; font-size: 13px; opacity: 0.9;">${message}</div>
    `;

    if (actionUrl) {
        html += `
            <div style="margin-top: 10px;">
                <a href="${actionUrl}" style="color: white; text-decoration: underline; font-size: 12px;">
                    Voir →
                </a>
            </div>
        `;
    }

    toast.innerHTML = html;
    toast.style.cssText = `
        background-color: ${bgColor};
        color: white;
        padding: 16px;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        font-size: 14px;
        animation: slideIn 0.3s ease-in-out;
        cursor: ${actionUrl ? 'pointer' : 'default'};
    `;

    if (actionUrl) {
        toast.style.cursor = 'pointer';
        toast.onclick = () => window.location.href = actionUrl;
    }

    toastContainer.appendChild(toast);

    // Auto-remove after 6 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in-out';
        setTimeout(() => toast.remove(), 300);
    }, 6000);
}

/**
 * Update payment-related badges/displays
 */
function updatePaymentBadges(event) {
    // Update any payment status badges in the DOM
    const badges = document.querySelectorAll('[data-payment-id="' + event.id + '"]');
    badges.forEach(badge => {
        badge.textContent = event.status;
        badge.className = 'payment-status-' + event.status;
    });
}

/**
 * Update delivery-related badges/displays
 */
function updateDeliveryBadges(event) {
    // Update delivery status displays
    const badges = document.querySelectorAll('[data-delivery-id="' + event.id + '"]');
    badges.forEach(badge => {
        badge.textContent = event.status;
        badge.className = 'delivery-status-' + event.status;
    });
}

/**
 * Update contract badges
 */
function updateContractBadges(event) {
    // Increment contract counter if exists
    const counter = document.getElementById('contract-counter');
    if (counter) {
        const count = parseInt(counter.textContent) || 0;
        counter.textContent = count + 1;
    }
}

/**
 * Update reclamation badges
 */
function updateReclamationBadges(event) {
    // Update reclamation status
    const statusBadge = document.querySelector('[data-reclamation-id="' + event.id + '"]');
    if (statusBadge) {
        statusBadge.textContent = event.status;
        statusBadge.className = 'reclamation-status-' + event.status;
    }
}

/**
 * Update compensation code badges
 */
function updateCompensationCodeBadges(event) {
    // Increment code counter if exists
    const counter = document.getElementById('compensation-code-counter');
    if (counter) {
        const count = parseInt(counter.textContent) || 0;
        counter.textContent = count + 1;
    }
}

/**
 * Refresh user page (soft reload)
 */
function refreshUserPage() {
    // Dispatch custom event for pages to listen to
    window.dispatchEvent(new CustomEvent('client-realtime-refresh'));
}

// Add CSS animations
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
