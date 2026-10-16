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
            })
            .listen('reservation-status-changed', (event) => {
                handleReservationStatusChanged(event);
            });
    } catch (error) {
        console.error('Error setting up reservations listener:', error);
    }
}

/**
 * Handle reservation status changed event
 */
function handleReservationStatusChanged(event) {
    console.log('Reservation status changed (admin):', event);

    showNotification(
        'Réservation mise à jour',
        `Statut: ${event.old_status} → ${event.new_status}`,
        'info'
    );

    // Update reservation status in table if on reservations page
    if (window.location.pathname.includes('/admin/reservations')) {
        updateReservationStatus(event);
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

    // Update reservations table if on that page
    if (window.location.pathname.includes('/admin/reservations')) {
        addReservationRow(event);
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

    // Update payment status in table if on payments page
    if (window.location.pathname.includes('/admin/payments')) {
        updatePaymentStatus(event);
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

    // Update delivery status in table if on deliveries page
    if (window.location.pathname.includes('/admin/livraisons')) {
        updateDeliveryStatus(event);
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

    // Update display without page reload
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

    // Update display without page reload
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

    // Update display without page reload
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

    // Update display without page reload
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
 * Update UI without page reload
 * This function now updates specific elements based on the current page
 */
function updatePage(event, pageType) {
    // No-op - page-specific handlers will handle updates
    console.log('Event received for page type:', pageType, event);
}

/**
 * Update reservation status in the table
 */
function updateReservationStatus(event) {
    // Find reservation row by ID (check for data-reservation-id attribute or ID in content)
    let row = null;
    
    // First check for data-reservation-id attribute
    row = document.querySelector(`tr[data-reservation-id="${event.id}"]`);
    
    // If not found, check for ID in the first cell
    if (!row) {
        const rows = document.querySelectorAll('table tbody tr');
        for (const r of rows) {
            const firstCell = r.querySelector('td:first-child');
            if (firstCell && firstCell.textContent.includes(`#${String(event.id).padStart(3, '0')}`)) {
                row = r;
                // Add data attribute for future reference
                row.setAttribute('data-reservation-id', event.id);
                break;
            }
        }
    }
    
    if (!row) return;
    
    // Update status badge
    const statusCell = row.querySelector('td:nth-child(7)'); // 7th column is status
    if (statusCell) {
        const statusLabels = {
            'en_attente': 'En attente',
            'en_cours': 'En cours', 
            'confirmee': 'Confirmée',
            'terminee': 'Terminée',
            'litige': 'Litige',
            'annulee': 'Annulée',
            'refusee': 'Refusée'
        };
        
        const statusClasses = {
            'confirmee': 'bg-green-50 text-green-700 border-green-200',
            'terminee': 'bg-green-50 text-green-700 border-green-200',
            'en_cours': 'bg-blue-50 text-blue-700 border-blue-200',
            'refusee': 'bg-red-50 text-red-600 border-red-200',
            'litige': 'bg-red-50 text-red-600 border-red-200',
            'annulee': 'bg-red-50 text-red-600 border-red-200',
            'en_attente': 'bg-amber-50 text-amber-700 border-amber-200'
        };

        const statusLabel = statusLabels[event.new_status] || event.new_status;
        const statusClass = statusClasses[event.new_status] || 'bg-amber-50 text-amber-700 border-amber-200';
        
        statusCell.innerHTML = `<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-600 ${statusClass}">${statusLabel}</span>`;
    }
    
    // Update action buttons (remove approve/reject if no longer en_attente)
    const actionsCell = row.querySelector('td:last-child');
    if (actionsCell && event.new_status !== 'en_attente') {
        const approveBtn = actionsCell.querySelector('form[action*="/approve"]');
        const rejectBtn = actionsCell.querySelector('form[action*="/reject"]');
        if (approveBtn) approveBtn.remove();
        if (rejectBtn) rejectBtn.remove();
    }
}

/**
 * Update payment status in the table
 */
function updatePaymentStatus(event) {
    // Find payment row by ID
    let row = document.querySelector(`tr[data-payment-id="${event.id}"]`);
    
    if (!row) {
        // Payment might not be in table yet, could add it here if needed
        return;
    }
    
    // Update status cell (adjust column index based on your table structure)
    const statusCell = row.querySelector('td:nth-child(5)'); // Adjust based on your table
    if (statusCell) {
        statusCell.textContent = event.status;
        // Update CSS class based on status
        statusCell.className = event.status === 'paid' ? 'text-green-600' : 
                              event.status === 'failed' ? 'text-red-600' : 
                              'text-amber-600';
    }
}

/**
 * Update delivery status in the table
 */
function updateDeliveryStatus(event) {
    // Find delivery row by ID
    let row = document.querySelector(`tr[data-delivery-id="${event.id}"]`);
    
    if (!row) {
        // Delivery might not be in table yet
        return;
    }
    
    // Update status cell (adjust column index based on your table structure)
    const statusCell = row.querySelector('td:nth-child(4)'); // Adjust based on your table
    if (statusCell) {
        const statusLabels = {
            'prete': 'Prête',
            'remise_au_client': 'Remise au client',
            'retour_recu': 'Retour reçu',
            'terminee': 'Terminée',
            'annulee': 'Annulée'
        };
        
        const statusLabel = statusLabels[event.status] || event.status;
        statusCell.textContent = statusLabel;
    }
}

/**
 * Add a new reservation row to the admin reservations table
 */
function addReservationRow(event) {
    const tbody = document.querySelector('table tbody');
    if (!tbody) return;

    // Check if reservation already exists in table
    const existingRow = document.querySelector(`tr[data-reservation-id="${event.id}"]`);
    if (existingRow) return;

    // Status labels and classes (matching backend logic)
    const statusLabels = {
        'en_attente': 'En attente',
        'en_cours': 'En cours', 
        'confirmee': 'Confirmée',
        'terminee': 'Terminée',
        'litige': 'Litige',
        'annulee': 'Annulée',
        'refusee': 'Refusée'
    };
    
    const statusClasses = {
        'confirmee': 'bg-green-50 text-green-700 border-green-200',
        'terminee': 'bg-green-50 text-green-700 border-green-200',
        'en_cours': 'bg-blue-50 text-blue-700 border-blue-200',
        'refusee': 'bg-red-50 text-red-600 border-red-200',
        'litige': 'bg-red-50 text-red-600 border-red-200',
        'annulee': 'bg-red-50 text-red-600 border-red-200',
        'en_attente': 'bg-amber-50 text-amber-700 border-amber-200'
    };

    const statusLabel = statusLabels[event.statut] || event.statut;
    const statusClass = statusClasses[event.statut] || 'bg-amber-50 text-amber-700 border-amber-200';

    // Format dates
    const formatDate = (dateStr) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    // Format ID
    const reservationId = String(event.id).padStart(3, '0');

    // Create new row
    const row = document.createElement('tr');
    row.className = 'hover:bg-slate-50 transition-colors';
    row.setAttribute('data-reservation-id', event.id);
    
    row.innerHTML = `
        <td class="px-5 py-4 text-slate-400 font-mono text-xs">#${reservationId}</td>
        <td class="px-5 py-4 font-500 text-slate-800">${event.user.name}</td>
        <td class="px-5 py-4 text-slate-600 hidden md:table-cell">${event.equipment.name}</td>
        <td class="px-5 py-4 text-slate-500 text-xs hidden lg:table-cell">${formatDate(event.date_debut)}</td>
        <td class="px-5 py-4 text-slate-500 text-xs hidden lg:table-cell">${formatDate(event.date_fin)}</td>
        <td class="px-5 py-4 font-600 text-slate-800 hidden sm:table-cell">${event.prix_total.toFixed(2)} TND</td>
        <td class="px-5 py-4"><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-600 ${statusClass}">${statusLabel}</span></td>
        <td class="px-5 py-4">
            <div class="flex items-center justify-end gap-2">
                <a href="/admin/reservations/${event.id}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Voir" aria-label="Voir la réservation">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </a>
                ${event.statut === 'en_attente' ? `
                    <form action="/admin/reservations/${event.id}/approve" method="POST" style="display: inline;">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                        <input type="hidden" name="_method" value="PATCH">
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Accepter" aria-label="Accepter la réservation">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </button>
                    </form>
                    <form action="/admin/reservations/${event.id}/reject" method="POST" style="display: inline;">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                        <input type="hidden" name="_method" value="PATCH">
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Refuser" aria-label="Refuser la réservation" onclick="return confirm('Refuser cette réservation ?');">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </form>
                ` : ''}
                <a href="/admin/reservations/${event.id}/edit" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Modifier" aria-label="Modifier la réservation">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                </a>
                <form action="/admin/reservations/${event.id}" method="POST" style="display: inline;">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer" aria-label="Supprimer la réservation" onclick="return confirm('Supprimer cette réservation ?');">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                    </button>
                </form>
            </div>
        </td>
    `;

    // Add to beginning of table
    tbody.insertBefore(row, tbody.firstChild);

    // Update the counter if exists
    const counter = document.querySelector('p.text-slate-500.text-sm.mt-0.5');
    if (counter) {
        const match = counter.textContent.match(/(\d+)/);
        if (match) {
            const count = parseInt(match[1]) + 1;
            counter.textContent = `${count} réservations trouvées`;
        }
    }
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
