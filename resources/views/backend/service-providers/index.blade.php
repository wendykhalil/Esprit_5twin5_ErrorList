@extends('layouts.backend')

@section('title', 'Prestataires - SolarShare Admin')

@section('content')

<div class="space-y-5">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl" style="font-family: Outfit, sans-serif">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Prestataires</h2>
            <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">{{ $serviceProviders->total() }} {{ $serviceProviders->total() > 1 ? 'prestataires trouvés' : 'prestataire trouvé' }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form method="GET" action="{{ route('admin.service-providers.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="specialty" value="{{ request('specialty') }}" placeholder="Spécialité..." class="px-3 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white flex-1" style="font-family: Outfit, sans-serif">
            <input type="text" name="location" value="{{ request('location') }}" placeholder="Localisation..." class="px-3 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white flex-1" style="font-family: Outfit, sans-serif">
            <select name="status" class="px-3 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white" style="font-family: Outfit, sans-serif">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvé</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeté</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-semibold hover:bg-amber-600 transition-colors" style="font-family: Outfit, sans-serif">Filtrer</button>
            <a href="{{ route('admin.service-providers.index') }}" class="px-4 py-2.5 border border-slate-200 text-slate-600 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors text-center" style="font-family: Outfit, sans-serif">Réinitialiser</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Utilisateur</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Spécialité</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell" style="font-family: Outfit, sans-serif">Localisation</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell" style="font-family: Outfit, sans-serif">Tarif horaire</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Disponibilité</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Statut</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($serviceProviders as $provider)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4 font-medium text-slate-800" style="font-family: Outfit, sans-serif">{{ $provider->user->name ?? 'N/A' }}</td>
                            <td class="px-5 py-4 text-slate-600" style="font-family: Outfit, sans-serif">{{ $provider->specialty }}</td>
                            <td class="px-5 py-4 text-slate-600 hidden sm:table-cell" style="font-family: Outfit, sans-serif">{{ $provider->location }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-800 hidden sm:table-cell" style="font-family: Outfit, sans-serif">{{ number_format((float) $provider->hourly_rate, 2) }} TND/h</td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $provider->availability ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $provider->availability ? 'Disponible' : 'Indisponible' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if($provider->status === 'approved')
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Approuvé</span>
                                @elseif($provider->status === 'rejected')
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">Rejeté</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">En attente</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.service-providers.show', $provider) }}" class="inline-block p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Voir">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400" style="font-family: Outfit, sans-serif">Aucun prestataire trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($serviceProviders->hasPages())
        <div class="mt-4">{{ $serviceProviders->links() }}</div>
    @endif
</div>

@endsection
