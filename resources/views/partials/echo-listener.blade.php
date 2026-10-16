{{-- Include this partial in views that need real-time updates --}}
@auth
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Echo for real-time notifications
    if (window.Echo) {
        // Listen for reservation status changes on user's private channel
        window.Echo.private('user.' + {{ auth()->id() }} + '.reservations')
            .listen('ReservationStatusChanged', function(data) {
                console.log('Reservation status changed:', data);
                
                // Update the reservation status in the table/view if visible
                updateReservationStatus(data);
                
                // Show a toast notification
                showToastNotification(data);
                
                // Update unread notification count
                updateNotificationCount();
            });
    }
});

/**
 * Update the reservation status in the current view
 */
function updateReservationStatus(data) {
    const reservationElement = document.querySelector(`[data-reservation-id="${data.id}"]`);
    if (reservationElement) {
        // Update status display
        const statusElement = reservationElement.querySelector('[data-status]');
        if (statusElement) {
            const statusText = statusMap[data.new_status] || data.new_status;
            statusElement.textContent = statusText;
            statusElement.className = getStatusClass(data.new_status);
        }
        
        // Optionally reload the entire reservation row or section
        // location.reload(); // Not recommended - disrupts user experience
    }
}

/**
 * Status display mapping
 */
const statusMap = {
    'en_attente': 'En attente',
    'confirmee': 'Confirmée',
    'en_cours': 'En cours',
    'terminee': 'Terminée',
    'annulee': 'Annulée',
    'refusee': 'Refusée',
    'litige': 'Litige'
};

/**
 * Get CSS class for status badge
 */
function getStatusClass(status) {
    const classes = {
        'en_attente': 'px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800',
        'confirmee': 'px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800',
        'en_cours': 'px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800',
        'terminee': 'px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800',
        'annulee': 'px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800',
        'refusee': 'px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800',
        'litige': 'px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800',
    };
    return classes[status] || classes['en_attente'];
}

/**
 * Show a toast notification
 */
function showToastNotification(data) {
    const message = data.new_status === 'confirmee' 
        ? `Votre réservation pour ${data.equipment_name} a été acceptée!`
        : `Votre réservation pour ${data.equipment_name} a été refusée.`;
    
    // Create and show toast
    const toast = document.createElement('div');
    toast.className = `
        fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4
        ${data.new_status === 'confirmee' ? 'border-l-4 border-green-600' : 'border-l-4 border-red-600'}
    `;
    toast.innerHTML = `
        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-slate-900">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

/**
 * Update notification count in the header
 */
function updateNotificationCount() {
    fetch('{{ route("notifications.api.count") }}')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('[data-notification-badge]');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error updating notification count:', error));
}

// Update count on page load
document.addEventListener('DOMContentLoaded', updateNotificationCount);
</script>
@endauth
