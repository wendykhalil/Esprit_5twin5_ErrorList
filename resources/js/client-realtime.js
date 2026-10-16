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
    listenToUserReservationStatusChanges(userId);
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

    // Update payment status on client page if visible
    if (window.location.pathname.includes('/mes-paiements') ||
        window.location.pathname.includes('/paiement')) {
        updateClientPaymentStatus(event);
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

    // Update delivery status on client page if visible
    if (window.location.pathname.includes('/livraisons')) {
        updateClientDeliveryStatus(event);
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

    // No page refresh needed for contracts page
    // Just update the badge/counter
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

    // Update reclamation status badge without page reload
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

    // Update code counter badge without page reload
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
 * Update client reservation status on page
 */
function updateClientReservationStatus(event) {
    // Find reservation element by ID
    const reservationElement = document.querySelector(`[data-reservation-id="${event.id}"]`);
    if (!reservationElement) return;
    
    // Update status badge/text
    const statusBadge = reservationElement.querySelector('.reservation-status');
    if (statusBadge) {
        const statusLabels = {
            'confirmee': 'Confirmée',
            'en_attente': 'En attente',
            'en_cours': 'En cours',
            'terminee': 'Terminée',
            'annulee': 'Annulée',
            'refusee': 'Refusée',
            'litige': 'Litige',
        };
        
        const newStatus = statusLabels[event.new_status] || event.new_status;
        statusBadge.textContent = newStatus;
        
        // Update CSS classes based on status
        const oldClasses = statusBadge.className.split(' ');
        const newClasses = oldClasses.filter(cls => !cls.startsWith('bg-') && !cls.startsWith('text-') && !cls.startsWith('border-'));
        
        if (event.new_status === 'confirmee' || event.new_status === 'terminee') {
            newClasses.push('bg-green-50', 'text-green-700', 'border-green-200');
        } else if (event.new_status === 'en_cours') {
            newClasses.push('bg-blue-50', 'text-blue-700', 'border-blue-200');
        } else if (event.new_status === 'refusee' || event.new_status === 'litige' || event.new_status === 'annulee') {
            newClasses.push('bg-red-50', 'text-red-600', 'border-red-200');
        } else {
            newClasses.push('bg-amber-50', 'text-amber-700', 'border-amber-200');
        }
        
        statusBadge.className = newClasses.join(' ');
    }
}

/**
 * Update client delivery status on page
 */
function updateClientDeliveryStatus(event) {
    // Find delivery element by ID
    const deliveryElement = document.querySelector(`[data-delivery-id="${event.id}"]`);
    if (!deliveryElement) return;
    
    // Update status badge/text
    const statusBadge = deliveryElement.querySelector('.delivery-status');
    if (statusBadge) {
        const statusLabels = {
            'prete': 'Prête pour la livraison',
            'en_preparation': 'En préparation',
            'remise_au_client': 'Remise au client',
            'retour_recu': 'Retour reçu',
            'terminee': 'Terminée',
            'annulee': 'Annulée',
        };
        
        const newStatus = statusLabels[event.status] || event.status;
        statusBadge.textContent = newStatus;
    }
}

/**
 * Update client payment status on page
 */
function updateClientPaymentStatus(event) {
    // Find payment element by ID
    const paymentElement = document.querySelector(`[data-payment-id="${event.id}"]`);
    if (!paymentElement) return;
    
    // Update status
    const statusElement = paymentElement.querySelector('.payment-status');
    if (statusElement) {
        statusElement.textContent = event.status;
        
        // Update CSS class based on status
        statusElement.className = event.status === 'paid' ? 'payment-status-paid' :
                                 event.status === 'failed' ? 'payment-status-failed' :
                                 'payment-status-pending';
    }
}

/**
 * Listen to user reservation status changes
 */
function listenToUserReservationStatusChanges(userId) {
    try {
        window.Echo.private(`user.${userId}.reservations`)
            .listen('reservation-status-changed', (event) => {
                handleUserReservationStatusChanged(event);
            });
    } catch (error) {
        console.error('Error setting up user reservation status listener:', error);
    }
}

/**
 * Handle user reservation status change
 */
function handleUserReservationStatusChanged(event) {
    console.log('Your reservation status changed:', event);

    const statusLabels = {
        'confirmee': 'Confirmée',
        'en_attente': 'En attente',
        'en_cours': 'En cours',
        'terminee': 'Terminée',
        'annulee': 'Annulée',
        'refusee': 'Refusée',
        'litige': 'Litige',
    };

    const newStatus = statusLabels[event.new_status] || event.new_status;
    const notificationType = event.new_status === 'confirmee' ? 'success' : 
                           event.new_status === 'refusee' ? 'error' : 'info';

    showClientNotification(
        'Statut de réservation mis à jour',
        `Votre réservation pour ${event.equipment_name} est maintenant: ${newStatus}`,
        notificationType,
        '/reservations'
    );

    // Update reservation status on client page if visible
    if (window.location.pathname.includes('/reservations')) {
        updateClientReservationStatus(event);
    }
}

/**
 * Update client UI without page reload
 */
function updateClientUI(event, pageType) {
    // No-op - page-specific handlers will handle updates
    console.log('Client event received for page type:', pageType, event);
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
