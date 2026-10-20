@extends('layouts.backend')

@section('title', 'Résultat du rapport - SolarShare Admin')

@section('content')

<div class="space-y-6">
    {{-- Header with Back Button --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.reports.index') }}" class="text-amber-600 hover:text-amber-700 font-medium text-sm flex items-center gap-1" style="font-family: Outfit, sans-serif">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Retour
                </a>
            </div>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">
                Rapport - {{ ucfirst($period) }}
            </h2>
            <p class="text-slate-500 text-sm mt-1" style="font-family: Outfit, sans-serif">
                Période: {{ \Carbon\Carbon::parse($stats['period']['start'])->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($stats['period']['end'])->format('d/m/Y') }}
            </p>
        </div>
    </div>

    {{-- AI Summary Section --}}
    @if($aiSummary)
        <div class="bg-gradient-to-r] from-purple-50 to-purple-100 rounded-xl border border-purple-200 p-6">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0] text-purple-600 mt-0.5">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-purple-900 mb-3" style="font-family: Outfit, sans-serif">Analyse IA - Résumé</h3>
                    <p class="text-purple-800 text-sm leading-relaxed" style="font-family: Outfit, sans-serif">
                        {{ $aiSummary }}
                    </p>
                </div>
            </div>
        </div>
    @elseif($aiError)
        <div class="bg-gradient-to-r] from-orange-50 to-orange-100 rounded-xl border border-orange-200 p-6">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0] text-orange-600 mt-0.5">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-orange-900 mb-2" style="font-family: Outfit, sans-serif">Résumé IA non disponible</h3>
                    <p class="text-orange-800 text-sm" style="font-family: Outfit, sans-serif">
                        {{ $aiError }}
                    </p>
                    <p class="text-orange-700 text-xs mt-2">Les statistiques ci-dessous sont toujours disponibles et à jour.</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-slate-100 rounded-xl border border-slate-300 p-6 flex items-center justify-center gap-3">
            <div class="animate-spin">
                <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <p class="text-slate-700 font-medium" style="font-family: Outfit, sans-serif">Génération du résumé IA en cours...</p>
        </div>
    @endif

    {{-- Key Metrics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Reservations --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider" style="font-family: Outfit, sans-serif">Réservations</p>
                    <p class="text-2xl font-bold text-slate-900 mt-3 mb-2" style="font-family: Outfit, sans-serif">{{ $stats['reservations']['total'] }}</p>
                </div>
                <div class="text-amber-600 opacity-80">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold text-amber-600" style="font-family: Outfit, sans-serif">
                ✓ {{ $stats['reservations']['completed'] }} complétées
            </p>
        </div>

        {{-- Revenue --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider" style="font-family: Outfit, sans-serif">Revenus</p>
                    <div class="flex items-baseline gap-1 mt-3 mb-2">
                        <p class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">{{ number_format($stats['revenue']['total'], 2, ',', ' ') }}</p>
                        <span class="text-slate-500 text-sm font-600">TND</span>
                    </div>
                </div>
                <div class="text-green-600 opacity-80">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold text-green-600" style="font-family: Outfit, sans-serif">
                Devises: {{ $stats['revenue']['currency'] }}
            </p>
        </div>

        {{-- Reclamations --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider" style="font-family: Outfit, sans-serif">Réclamations</p>
                    <p class="text-2xl font-bold text-slate-900 mt-3 mb-2" style="font-family: Outfit, sans-serif">{{ $stats['reclamations']['total'] }}</p>
                </div>
                <div class="text-red-600 opacity-80">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold text-red-600" style="font-family: Outfit, sans-serif">
                Taux: {{ $stats['reclamations']['rate'] }}%
            </p>
        </div>

        {{-- Refunds --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider" style="font-family: Outfit, sans-serif">Remboursements</p>
                    <div class="flex items-baseline gap-1 mt-3 mb-2">
                        <p class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">{{ number_format($stats['revenue']['refunded'], 2, ',', ' ') }}</p>
                        <span class="text-slate-500 text-sm font-600">TND</span>
                    </div>
                </div>
                <div class="text-blue-600 opacity-80">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold text-blue-600" style="font-family: Outfit, sans-serif">
                Approuvés
            </p>
        </div>
    </div>

    {{-- Detailed Statistics Sections --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Reservations by Status --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Réservations par statut</h3>
            <div class="space-y-3">
                @forelse($stats['reservations']['by_status'] as $status => $count)
                    @php
                        $statusLabels = [
                            'en_attente' => 'En attente',
                            'confirmee' => 'Confirmée',
                            'en_cours' => 'En cours',
                            'terminee' => 'Terminée',
                            'litige' => 'Litige',
                            'annulea' => 'Annulée',
                            'refusee' => 'Refusée',
                        ];
                        $statusColors = [
                            'en_attente' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'confirmee' => 'bg-blue-100 text-blue-800 border-blue-300',
                            'en_cours' => 'bg-purple-100 text-purple-800 border-purple-300',
                            'terminee' => 'bg-green-100 text-green-800 border-green-300',
                            'litige' => 'bg-red-100 text-red-800 border-red-300',
                            'annulea' => 'bg-slate-100 text-slate-800 border-slate-300',
                            'refusee' => 'bg-red-100 text-red-800 border-red-300',
                        ];
                        $percentage = $stats['reservations']['total'] > 0 ? ($count / $stats['reservations']['total'] * 100) : 0;
                    @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold border {{ $statusColors[$status] ?? 'bg-slate-100' }}">
                                {{ $statusLabels[$status] ?? $status }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-32 bg-slate-200 rounded-full h-2">
                                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-slate-900">{{ $count }} ({{ round($percentage, 1) }}%)</span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm">Aucune réservation pour cette période</p>
                @endforelse
            </div>
        </div>

        {{-- Reclamations by Status --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Réclamations par statut</h3>
            <div class="space-y-3">
                @forelse($stats['reclamations']['by_status'] as $status => $count)
                    @php
                        $recStatusLabels = [
                            'en_attente' => 'En attente',
                            'en_cours' => 'En cours',
                            'approuvee' => 'Approuvée',
                            'rejetee' => 'Rejetée',
                            'fermee' => 'Fermée',
                        ];
                        $recStatusColors = [
                            'en_attente' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'en_cours' => 'bg-purple-100 text-purple-800 border-purple-300',
                            'approuvee' => 'bg-green-100 text-green-800 border-green-300',
                            'rejetee' => 'bg-red-100 text-red-800 border-red-300',
                            'fermee' => 'bg-slate-100 text-slate-800 border-slate-300',
                        ];
                        $percentage = $stats['reclamations']['total'] > 0 ? ($count / $stats['reclamations']['total'] * 100) : 0;
                    @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold border {{ $recStatusColors[$status] ?? 'bg-slate-100' }}">
                                {{ $recStatusLabels[$status] ?? $status }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-32 bg-slate-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-slate-900">{{ $count }} ({{ round($percentage, 1) }}%)</span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm">Aucune réclamation pour cette période</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Reclamations by Type --}}
    @if($stats['reclamations']['by_type'])
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Réclamations par type de problème</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse($stats['reclamations']['by_type'] as $type => $count)
                    @php
                        $typeLabels = [
                            'endommage' => 'Endommagé',
                            'manquant' => 'Manquant',
                            'malfonctionnant' => 'Malfonctionnant',
                            'autre' => 'Autre',
                        ];
                        $typeColors = [
                            'endommage' => 'from-red-50 to-red-100 border-red-200 text-red-700',
                            'manquant' => 'from-orange-50 to-orange-100 border-orange-200 text-orange-700',
                            'malfonctionnant' => 'from-yellow-50 to-yellow-100 border-yellow-200 text-yellow-700',
                            'autre' => 'from-slate-50 to-slate-100 border-slate-200 text-slate-700',
                        ];
                    @endphp
                    <div class="bg-gradient-to-br] {{ $typeColors[$type] ?? 'from-slate-50 to-slate-100 border-slate-200 text-slate-700' }} rounded-lg border p-4 text-center">
                        <p class="font-semibold text-lg">{{ $count }}</p>
                        <p class="text-xs font-medium mt-1">{{ $typeLabels[$type] ?? $type }}</p>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm col-span-full">Aucun incident pour cette période</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Top Equipment Rankings --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Équipements les plus demandés (par nombre de réservations)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 px-4 font-semibold text-slate-700" style="font-family: Outfit, sans-serif">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700" style="font-family: Outfit, sans-serif">Équipement</th>
                        <th class="text-center py-3 px-4 font-semibold text-slate-700" style="font-family: Outfit, sans-serif">Réservations</th>
                        <th class="text-right py-3 px-4 font-semibold text-slate-700" style="font-family: Outfit, sans-serif">Revenu (TND)</th>
                        <th class="text-right py-3 px-4 font-semibold text-slate-700" style="font-family: Outfit, sans-serif">Prix/jour (TND)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['equipment_rankings_by_reservations'] ?? [] as $index => $equipment)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors" data-equipment-id="{{ $equipment['id'] }}">
                            <td class="py-3 px-4 text-slate-600 font-medium">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 text-slate-900 font-medium">{{ $equipment['name'] }} <span class="text-xs text-slate-500">(ID: {{ $equipment['id'] }})</span></td>
                            <td class="py-3 px-4 text-center text-slate-700">
                                <span class="inline-block px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">
                                    {{ $equipment['reservation_count'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-slate-900 font-semibold">{{ number_format($equipment['revenue'], 2, ',', ' ') }}</td>
                            <td class="py-3 px-4 text-right text-slate-600">{{ number_format($equipment['price_per_day'], 2, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-3 px-4 text-center text-slate-500">Aucun équipement loué pour cette période</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Equipment by Revenue --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Équipements générant le plus de revenus</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 px-4 font-semibold text-slate-700" style="font-family: Outfit, sans-serif">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700" style="font-family: Outfit, sans-serif">Équipement</th>
                        <th class="text-center py-3 px-4 font-semibold text-slate-700" style="font-family: Outfit, sans-serif">Réservations</th>
                        <th class="text-right py-3 px-4 font-semibold text-slate-700" style="font-family: Outfit, sans-serif">Revenu (TND)</th>
                        <th class="text-right py-3 px-4 font-semibold text-slate-700" style="font-family: Outfit, sans-serif">Prix/jour (TND)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['equipment_rankings'] as $index => $equipment)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors" data-equipment-id="{{ $equipment['id'] }}">
                            <td class="py-3 px-4 text-slate-600 font-medium">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 text-slate-900 font-medium">{{ $equipment['name'] }} <span class="text-xs text-slate-500">(ID: {{ $equipment['id'] }})</span></td>
                            <td class="py-3 px-4 text-center text-slate-700">
                                <span class="inline-block px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">
                                    {{ $equipment['reservation_count'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-slate-900 font-semibold">{{ number_format($equipment['revenue'], 2, ',', ' ') }}</td>
                            <td class="py-3 px-4 text-right text-slate-600">{{ number_format($equipment['price_per_day'], 2, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500 text-sm">Aucun équipement loué durant cette période</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Additional Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider" style="font-family: Outfit, sans-serif">Durée moyenne de location</p>
            @if($stats['reservations']['avg_duration_days'] > 0)
                <p class="text-3xl font-bold text-slate-900 mt-3" style="font-family: Outfit, sans-serif">{{ $stats['reservations']['avg_duration_days'] }}</p>
                <p class="text-sm text-slate-600 mt-2" style="font-family: Outfit, sans-serif">jours</p>
            @elseif($stats['reservations']['total'] == 0)
                <p class="text-3xl font-bold text-slate-400 mt-3" style="font-family: Outfit, sans-serif">—</p>
                <p class="text-sm text-slate-500 mt-2" style="font-family: Outfit, sans-serif">Aucune réservation</p>
            @elseif($stats['reservations']['completed'] == 0)
                <p class="text-3xl font-bold text-slate-400 mt-3" style="font-family: Outfit, sans-serif">—</p>
                <p class="text-sm text-slate-500 mt-2" style="font-family: Outfit, sans-serif">Aucune réservation terminée</p>
            @else
                <p class="text-3xl font-bold text-slate-400 mt-3" style="font-family: Outfit, sans-serif">0</p>
                <p class="text-sm text-slate-500 mt-2" style="font-family: Outfit, sans-serif">jours</p>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider" style="font-family: Outfit, sans-serif">Taux de réclamation</p>
            <p class="text-3xl font-bold text-slate-900 mt-3" style="font-family: Outfit, sans-serif">{{ $stats['reclamations']['rate'] }}<span class="text-lg">%</span></p>
            <p class="text-sm text-slate-600 mt-2" style="font-family: Outfit, sans-serif">Réclamations / Réservations</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider" style="font-family: Outfit, sans-serif">Revenu moyen par réservation</p>
            <p class="text-3xl font-bold text-slate-900 mt-3" style="font-family: Outfit, sans-serif">
                {{ $stats['reservations']['total'] > 0 ? number_format($stats['revenue']['total'] / $stats['reservations']['total'], 2, ',', ' ') : '0' }}
            </p>
            <p class="text-sm text-slate-600 mt-2" style="font-family: Outfit, sans-serif">TND</p>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Reservations by Status Chart --}}
        @if($stats['reservations']['by_status'])
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Réservations par statut (graphique)</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="reservationStatusChart"></canvas>
                </div>
            </div>
        @endif

        {{-- Reclamations by Status Chart --}}
        @if($stats['reclamations']['by_status'])
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Réclamations par statut (graphique)</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="reclamationStatusChart"></canvas>
                </div>
            </div>
        @endif

        {{-- Revenue by Status Chart --}}
        @if($stats['revenue']['by_status'])
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Paiements par statut</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="paymentStatusChart"></canvas>
                </div>
            </div>
        @endif

        {{-- Top Equipment Chart --}}
        @if($stats['equipment_rankings'])
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Équipements par revenu généré</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="equipmentRevenueChart"></canvas>
                </div>
            </div>
        @endif

        {{-- Incidents by Type Chart --}}
        @if($stats['reclamations']['by_type'])
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Incidents par type</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="incidentTypeChart"></canvas>
                </div>
            </div>
        @endif

        {{-- Top Equipment by Reservations Chart --}}
        @if($stats['equipment_rankings_by_reservations'])
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Équipements les plus demandés</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="equipmentReservationsChart"></canvas>
                </div>
            </div>
        @endif
    </div>

    {{-- Back to Reports --}}
    <div class="flex justify-center pt-4">
        <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-2 px-6 py-2.5 bg-slate-200 text-slate-900 rounded-lg text-sm font-600 hover:bg-slate-300 transition-colors" style="font-family: Outfit, sans-serif">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Générer un autre rapport
        </a>
    </div>

    {{-- Export Options --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6">
        <form action="{{ route('admin.reports.export.csv') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            @if($period === 'custom')
                <input type="hidden" name="start_date" value="{{ \Carbon\Carbon::parse($stats['period']['start'])->format('Y-m-d') }}">
                <input type="hidden" name="end_date" value="{{ \Carbon\Carbon::parse($stats['period']['end'])->format('Y-m-d') }}">
            @endif
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-green-600 text-white rounded-lg text-sm font-600 hover:bg-green-700 transition-colors" style="font-family: Outfit, sans-serif">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2m0 0v-8m0 8H3m18 0h3" />
                </svg>
                Télécharger en CSV
            </button>
        </form>

        <form action="{{ route('admin.reports.export.pdf') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            @if($period === 'custom')
                <input type="hidden" name="start_date" value="{{ \Carbon\Carbon::parse($stats['period']['start'])->format('Y-m-d') }}">
                <input type="hidden" name="end_date" value="{{ \Carbon\Carbon::parse($stats['period']['end'])->format('Y-m-d') }}">
            @endif
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg text-sm font-600 hover:bg-red-700 transition-colors" style="font-family: Outfit, sans-serif">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Télécharger en PDF
            </button>
        </form>
    </div>
</div>

{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js could not be loaded. Charts will not be displayed.');
    } else {
        Chart.defaults.font.family = 'Outfit, sans-serif';
        Chart.defaults.color = '#64748b';

        // Colors palette
        const statusColors = {
            'en_attente': '#fbbf24',
            'confirmee': '#60a5fa',
            'en_cours': '#a78bfa',
            'terminee': '#34d399',
            'litige': '#f87171',
            'annulea': '#cbd5e1',
            'refusee': '#ef4444',
            'approuvee': '#34d399',
            'rejetee': '#f87171',
            'fermee': '#94a3b8',
            'en_cours_rec': '#a78bfa'
        };

        const incidentColors = {
            'endommage': '#ef4444',
            'manquant': '#f97316',
            'malfonctionnant': '#eab308',
            'autre': '#64748b'
        };

        // Reservation Status Chart
        @if($stats['reservations']['by_status'])
            const reservationStatusData = @json($stats['reservations']['by_status']);
            const reservationLabels = Object.keys(reservationStatusData).map(status => {
                const labels = {
                    'en_attente': 'En attente',
                    'confirmee': 'Confirmée',
                    'en_cours': 'En cours',
                    'terminee': 'Terminée',
                    'litige': 'Litige',
                    'annulea': 'Annulée',
                    'refusee': 'Refusée'
                };
                return labels[status] || status;
            });
            const reservationValues = Object.values(reservationStatusData);
            const reservationChartColors = Object.keys(reservationStatusData).map(status => statusColors[status] || '#94a3b8');

            new Chart(document.getElementById('reservationStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: reservationLabels,
                    datasets: [{
                        data: reservationValues,
                        backgroundColor: reservationChartColors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        @endif

        // Reclamation Status Chart
        @if($stats['reclamations']['by_status'])
            const reclamationStatusData = @json($stats['reclamations']['by_status']);
            const reclamationLabels = Object.keys(reclamationStatusData).map(status => {
                const labels = {
                    'en_attente': 'En attente',
                    'en_cours': 'En cours',
                    'approuvee': 'Approuvée',
                    'rejetee': 'Rejetée',
                    'fermee': 'Fermée'
                };
                return labels[status] || status;
            });
            const reclamationValues = Object.values(reclamationStatusData);
            const reclamationChartColors = Object.keys(reclamationStatusData).map(status => statusColors[status] || '#94a3b8');

            new Chart(document.getElementById('reclamationStatusChart'), {
                type: 'pie',
                data: {
                    labels: reclamationLabels,
                    datasets: [{
                        data: reclamationValues,
                        backgroundColor: reclamationChartColors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        @endif

        // Payment Status Chart
        @if($stats['revenue']['by_status'])
            const paymentStatusData = @json($stats['revenue']['by_status']);
            const paymentLabels = Object.keys(paymentStatusData).map(status => {
                const labels = {
                    'pending': 'En attente',
                    'paid': 'Payé',
                    'failed': 'Échoué',
                    'refunded': 'Remboursé'
                };
                return labels[status] || status;
            });
            const paymentValues = Object.values(paymentStatusData);
            const paymentChartColors = ['#fbbf24', '#34d399', '#ef4444', '#60a5fa'];

            new Chart(document.getElementById('paymentStatusChart'), {
                type: 'bar',
                data: {
                    labels: paymentLabels,
                    datasets: [{
                        label: 'Nombre de paiements',
                        data: paymentValues,
                        backgroundColor: paymentChartColors,
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif

        // Equipment Revenue Chart
        @if($stats['equipment_rankings'])
            const equipmentRevenueData = @json($stats['equipment_rankings']);
            const equipmentRevenueLabels = equipmentRevenueData.map(e => e.name + ' (ID: ' + e.id + ')');
            const equipmentRevenueValues = equipmentRevenueData.map(e => e.revenue);

            new Chart(document.getElementById('equipmentRevenueChart'), {
                type: 'bar',
                data: {
                    labels: equipmentRevenueLabels,
                    datasets: [{
                        label: 'Revenu (TND)',
                        data: equipmentRevenueValues,
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif

        // Incident Type Chart
        @if($stats['reclamations']['by_type'])
            const incidentTypeData = @json($stats['reclamations']['by_type']);
            const incidentLabels = Object.keys(incidentTypeData).map(type => {
                const labels = {
                    'endommage': 'Endommagé',
                    'manquant': 'Manquant',
                    'malfonctionnant': 'Malfonctionnant',
                    'autre': 'Autre'
                };
                return labels[type] || type;
            });
            const incidentValues = Object.values(incidentTypeData);
            const incidentChartColors = Object.keys(incidentTypeData).map(type => incidentColors[type] || '#94a3b8');

            new Chart(document.getElementById('incidentTypeChart'), {
                type: 'bar',
                data: {
                    labels: incidentLabels,
                    datasets: [{
                        label: 'Nombre d\'incidents',
                        data: incidentValues,
                        backgroundColor: incidentChartColors,
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif

        // Equipment Reservations Chart
        @if($stats['equipment_rankings_by_reservations'])
            const equipmentReservationsData = @json($stats['equipment_rankings_by_reservations']);
            const equipmentReservationsLabels = equipmentReservationsData.map(e => e.name + ' (ID: ' + e.id + ')');
            const equipmentReservationsValues = equipmentReservationsData.map(e => e.reservation_count);

            new Chart(document.getElementById('equipmentReservationsChart'), {
                type: 'bar',
                data: {
                    labels: equipmentReservationsLabels,
                    datasets: [{
                        label: 'Nombre de réservations',
                        data: equipmentReservationsValues,
                        backgroundColor: '#8b5cf6',
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif
    }
</script>

@endsection
