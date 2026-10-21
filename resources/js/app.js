import './bootstrap';
import './echo';

import Alpine from 'alpinejs';
import { initAdminRealtimeListeners } from './admin-realtime';
import { initClientRealtimeListeners } from './client-realtime';
import { renderImpactCharts } from './impact-charts';

window.Alpine = Alpine;

Alpine.start();

// Initialize real-time listeners based on page type
document.addEventListener('DOMContentLoaded', () => {
    const chartData = document.getElementById('impact-chart-data');
    if (chartData) {
        renderImpactCharts(JSON.parse(chartData.textContent));
    }
    // Initialize admin listeners if on admin pages
    if (document.body.classList.contains('admin') || window.location.pathname.startsWith('/admin')) {
        initAdminRealtimeListeners();
    }
    
    // Initialize client listeners if authenticated client
    if (!window.location.pathname.startsWith('/admin')) {
        initClientRealtimeListeners();
    }
});
