
@extends('layouts.backend')

@section('title', 'Statistiques équipements - SolarShare Admin')

@section('content')

<div class="w-full space-y-6 pb-8" style="font-family: Outfit, sans-serif;">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">
                SolarShare / Administration / Équipements / Statistiques
            </p>

            <h1 class="text-2xl font-bold text-slate-900">
                Statistiques des équipements
            </h1>

            <p class="text-sm text-slate-500 mt-1">
                Analyse et suivi du catalogue SolarShare
            </p>
        </div>

        <a href="{{ route('admin.equipments') }}"
           class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
            ← Retour aux équipements
        </a>

    </div>

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- TOTAL --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold text-slate-500">
                    Total équipements
                </p>

                <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 7.5L12 3l9 4.5M3 7.5V17l9 4.5m-9-14L12 12m9-4.5V17L12 21.5m0-9.5v9.5"/>
                    </svg>
                </div>
            </div>

            <p class="text-4xl font-bold text-slate-900 mt-4">
                {{ number_format($totalEquipment) }}
            </p>

            <p class="text-xs text-slate-400 mt-2">
                Équipements enregistrés
            </p>
        </div>

        {{-- AVAILABLE --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold text-slate-500">
                    Disponibles
                </p>

                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <p class="text-4xl font-bold text-emerald-600 mt-4">
                {{ number_format($availableEquipment) }}
            </p>

            <p class="text-xs text-slate-400 mt-2">
                Disponibilité déclarée
            </p>
        </div>

        {{-- UNAVAILABLE --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold text-slate-500">
                    Indisponibles
                </p>

                <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-500" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.72 3h16.92a2 2 0 001.72-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
            </div>

            <p class="text-4xl font-bold text-red-500 mt-4">
                {{ number_format($unavailableEquipment) }}
            </p>

            <p class="text-xs text-slate-400 mt-2">
                Équipements non disponibles
            </p>
        </div>

        {{-- CATEGORIES --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold text-slate-500">
                    Catégories
                </p>

                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z"/>
                    </svg>
                </div>
            </div>

            <p class="text-4xl font-bold text-slate-900 mt-4">
                {{ number_format($totalCategories) }}
            </p>

            <p class="text-xs text-slate-400 mt-2">
                Catégories enregistrées
            </p>
        </div>

    </div>

    {{-- CHARTS --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

        {{-- CATEGORY CHART --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6">
            <div class="mb-5">
                <h2 class="text-base font-bold text-slate-900">
                    Équipements par catégorie
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Répartition du catalogue
                </p>
            </div>

            <div class="relative" style="height: 300px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        {{-- CONDITION CHART --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6">
            <div class="mb-5">
                <h2 class="text-base font-bold text-slate-900">
                    État des équipements
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Répartition selon leur condition
                </p>
            </div>

            <div class="relative" style="height: 300px;">
                <canvas id="conditionChart"></canvas>
            </div>
        </div>

        {{-- MONTHLY CHART --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6 xl:col-span-2">
            <div class="mb-5">
                <h2 class="text-base font-bold text-slate-900">
                    Évolution du catalogue
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Équipements ajoutés durant les six derniers mois
                </p>
            </div>

            <div class="relative" style="height: 320px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

    </div>

    {{-- AVAILABILITY RATE --}}
    @php
        $availabilityRate = $totalEquipment > 0
            ? round(($availableEquipment / $totalEquipment) * 100, 1)
            : 0;
    @endphp

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">

            <div>
                <h2 class="text-base font-bold text-slate-900">
                    Taux de disponibilité
                </h2>

                <p class="text-xs text-slate-500 mt-1">
                    Pourcentage des équipements déclarés disponibles
                </p>
            </div>

            <span class="text-2xl font-bold text-amber-600">
                {{ $availabilityRate }}%
            </span>

        </div>

        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
            <div class="bg-amber-500 h-3 rounded-full"
                 style="width: {{ $availabilityRate }}%">
            </div>
        </div>

        <p class="text-xs text-slate-400 mt-4">
            Statistiques calculées à partir des données SolarShare.
        </p>

    </div>

</div>

{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const categoryStats = @json($categoryStats);
    const conditionStats = @json($conditionStats);
    const monthlyStats = @json($monthlyStats);

    if (typeof Chart === 'undefined') {
        console.error('Chart.js could not be loaded.');
        return;
    }

    Chart.defaults.font.family = 'Outfit, sans-serif';
    Chart.defaults.color = '#64748b';

    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: categoryStats.map(item => item.name),
            datasets: [{
                label: 'Équipements',
                data: categoryStats.map(item => item.count),
                backgroundColor: '#f59e0b',
                hoverBackgroundColor: '#d97706',
                borderRadius: 8,
                maxBarThickness: 55
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    new Chart(document.getElementById('conditionChart'), {
        type: 'doughnut',
        data: {
            labels: conditionStats.map(item => item.name),
            datasets: [{
                data: conditionStats.map(item => item.count),
                backgroundColor: [
                    '#f59e0b',
                    '#22c55e',
                    '#3b82f6',
                    '#ef4444',
                    '#8b5cf6'
                ],
                borderColor: '#ffffff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 18
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyStats.map(item => item.name),
            datasets: [{
                label: 'Équipements ajoutés',
                data: monthlyStats.map(item => item.count),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.12)',
                fill: true,
                tension: 0.35,
                borderWidth: 3,
                pointRadius: 5,
                pointBackgroundColor: '#f59e0b',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

});
</script>

@endsection
