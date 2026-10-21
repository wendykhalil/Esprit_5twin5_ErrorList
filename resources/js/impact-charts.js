import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
);

export function renderImpactCharts(data) {
    if (!document.getElementById('energy-monthly')) return;
    const labels = data.monthly.map((item) => item.period);
    const energy = data.monthly.map((item) => Number(item.total));
    new Chart(document.getElementById('energy-monthly'), {
        type: 'bar',
        data: { labels, datasets: [{ label: 'kWh produits', data: energy, backgroundColor: '#79a85b', borderRadius: 8 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#173b27' } } }, scales: { y: { beginAtZero: true } } },
    });

    const cumulative = data.cumulative.map((item) => Number(item.total) * Number(data.co2Factor));
    const co2Datasets = [{ label: 'CO2 évité cumulé (kg)', data: cumulative, borderColor: '#e99a2d', backgroundColor: 'rgba(233,154,45,.15)', fill: true, tension: .35, pointRadius: 4 }];
    data.co2Thresholds.forEach((threshold) => {
        co2Datasets.push({ label: `Palier ${threshold} kg`, data: labels.map(() => Number(threshold)), borderColor: '#b8d391', borderDash: [5, 5], pointRadius: 0, fill: false });
    });
    new Chart(document.getElementById('co2-cumulative'), {
        type: 'line',
        data: { labels, datasets: co2Datasets },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#173b27' } } }, scales: { y: { beginAtZero: true } } },
    });

    new Chart(document.getElementById('category-energy'), {
        type: 'doughnut',
        data: { labels: data.categories.map((item) => item.category), datasets: [{ label: 'Énergie par catégorie', data: data.categories.map((item) => Number(item.total)), backgroundColor: ['#79a85b', '#e99a2d', '#173b27', '#b8d391', '#f2c46d'] }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#173b27', padding: 18 } } } },
    });
}
