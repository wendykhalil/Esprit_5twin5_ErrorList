@extends('layouts.backend')

@section('title', 'Interventions Techniques - SolarShare Admin')

@section('content')

<div class="space-y-6">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl" style="font-family: Outfit, sans-serif">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl" style="font-family: Outfit, sans-serif">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Interventions Techniques</h2>
            <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">
                Suivi et arbitrage des demandes d'intervention technique entre clients et prestataires
            </p>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Total --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Total interventions</span>
                <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm font-bold">📋</span>
            </div>
            <p class="text-2xl font-bold text-slate-900 mt-2" style="font-family: Outfit, sans-serif">{{ $kpis->total ?? 0 }}</p>
        </div>

        {{-- Pending --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider" style="font-family: Outfit, sans-serif">En attente</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center text-sm font-bold">⏳</span>
            </div>
            <p class="text-2xl font-bold text-amber-900 mt-2" style="font-family: Outfit, sans-serif">{{ $kpis->pending_count ?? 0 }}</p>
        </div>

        {{-- In Progress --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider" style="font-family: Outfit, sans-serif">En cours</span>
                <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center text-sm font-bold">⚙️</span>
            </div>
            <p class="text-2xl font-bold text-purple-900 mt-2" style="font-family: Outfit, sans-serif">{{ $kpis->in_progress_count ?? 0 }}</p>
        </div>

        {{-- Completed --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-green-600 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Terminées</span>
                <span class="w-8 h-8 rounded-lg bg-green-50 text-green-700 flex items-center justify-center text-sm font-bold">✓</span>
            </div>
            <p class="text-2xl font-bold text-green-900 mt-2" style="font-family: Outfit, sans-serif">{{ $kpis->completed_count ?? 0 }}</p>
        </div>

        {{-- Completed Estimated Amount --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Montant estimé (terminées)</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm font-bold">TND</span>
            </div>
            <p class="text-2xl font-bold text-emerald-900 mt-2" style="font-family: Outfit, sans-serif">
                {{ number_format((float) ($kpis->total_completed_estimated_amount ?? 0), 2) }}
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.service-requests.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                {{-- Search --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1" style="font-family: Outfit, sans-serif">Recherche libre</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Titre, client, prestataire, adresse..."
                        class="w-full px-3 py-2 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-800 bg-white"
                        style="font-family: Outfit, sans-serif"
                    >
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1" style="font-family: Outfit, sans-serif">Statut</label>
                    <select name="status" class="w-full px-3 py-2 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-800 bg-white" style="font-family: Outfit, sans-serif">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente (pending)</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Acceptée (accepted)</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours (in_progress)</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminée (completed)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée (rejected)</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée (cancelled)</option>
                    </select>
                </div>

                {{-- Provider Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1" style="font-family: Outfit, sans-serif">Prestataire</label>
                    <select name="service_provider_id" class="w-full px-3 py-2 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-800 bg-white" style="font-family: Outfit, sans-serif">
                        <option value="">Tous les prestataires</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" {{ request('service_provider_id') == $provider->id ? 'selected' : '' }}>
                                {{ $provider->user->name ?? 'N/A' }} ({{ $provider->specialty }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div>
                        <input
                            type="date"
                            name="date_from"
                            value="{{ request('date_from') }}"
                            class="px-3 py-1.5 text-xs font-medium border border-slate-200 rounded-lg text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400"
                            style="font-family: Outfit, sans-serif"
                            title="Date d'intervention min"
                        >
                    </div>
                    <span class="text-xs text-slate-400">à</span>
                    <div>
                        <input
                            type="date"
                            name="date_to"
                            value="{{ request('date_to') }}"
                            class="px-3 py-1.5 text-xs font-medium border border-slate-200 rounded-lg text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400"
                            style="font-family: Outfit, sans-serif"
                            title="Date d'intervention max"
                        >
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-semibold hover:bg-amber-600 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                        Filtrer
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'service_provider_id', 'date_from', 'date_to']))
                        <a href="{{ route('admin.service-requests.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors" style="font-family: Outfit, sans-serif">
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Interventions Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Réf. / Titre</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Client</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Prestataire</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell" style="font-family: Outfit, sans-serif">Équipement</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell" style="font-family: Outfit, sans-serif">Date souhaitée</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Devis estimé</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Statut</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($serviceRequests as $req)
                        @php
                            $statusBadges = [
                                'pending' => ['bg-yellow-100 text-yellow-800', 'En attente'],
                                'accepted' => ['bg-blue-100 text-blue-800', 'Acceptée'],
                                'in_progress' => ['bg-purple-100 text-purple-800', 'En cours'],
                                'completed' => ['bg-green-100 text-green-800', 'Terminée'],
                                'rejected' => ['bg-red-100 text-red-800', 'Rejetée'],
                                'cancelled' => ['bg-slate-200 text-slate-800', 'Annulée'],
                            ];
                            $badge = $statusBadges[$req->status] ?? ['bg-gray-100 text-gray-800', $req->status];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-4">
                                <span class="font-mono font-semibold text-slate-500 text-xs">#{{ $req->id }}</span>
                                <p class="font-bold text-slate-900 text-sm mt-0.5 line-clamp-1" style="font-family: Outfit, sans-serif">{{ $req->title }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900" style="font-family: Outfit, sans-serif">{{ $req->user->name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $req->user->email ?? '' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900" style="font-family: Outfit, sans-serif">{{ $req->serviceProvider->user->name ?? 'N/A' }}</p>
                                <p class="text-xs text-emerald-600 mt-0.5">{{ $req->serviceProvider->specialty ?? '' }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600 hidden md:table-cell text-xs">
                                @if($req->equipment)
                                    <span class="font-medium text-slate-800">{{ $req->equipment->name }}</span>
                                    <span class="block text-slate-400">{{ $req->equipment->brand ?? '' }}</span>
                                @else
                                    <span class="text-slate-400 italic">Non spécifié</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-700 hidden sm:table-cell text-xs whitespace-nowrap">
                                {{ $req->requested_date ? $req->requested_date->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-900 text-xs whitespace-nowrap">
                                @if($req->estimated_price !== null)
                                    {{ number_format((float) $req->estimated_price, 2) }} TND
                                @else
                                    <span class="text-slate-400 font-normal italic">En attente</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge[0] }}">
                                    {{ $badge[1] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a
                                    href="{{ route('admin.service-requests.show', $req) }}"
                                    class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-amber-500 hover:text-white text-slate-700 rounded-lg text-xs font-semibold transition-all shadow-sm"
                                    style="font-family: Outfit, sans-serif"
                                >
                                    Consulter
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400" style="font-family: Outfit, sans-serif">
                                <div class="text-3xl mb-2">🔍</div>
                                <p class="text-base font-semibold text-slate-700">Aucune intervention trouvée</p>
                                <p class="text-xs text-slate-400 mt-1">Modifiez vos filtres ou effectuez une autre recherche.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($serviceRequests->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $serviceRequests->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
