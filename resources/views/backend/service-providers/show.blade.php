@extends('layouts.backend')

@section('title', 'Détails du Prestataire - SolarShare Admin')

@section('content')

<div class="space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.service-providers.index') }}" class="p-2 bg-white rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Détails du profil prestataire</h2>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl" style="font-family: Outfit, sans-serif">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Utilisateur</p>
                <p class="font-medium text-slate-800" style="font-family: Outfit, sans-serif">{{ $serviceProvider->user->name ?? 'N/A' }} ({{ $serviceProvider->user->email ?? 'N/A' }})</p>
            </div>
            <div>
                <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Statut</p>
                @if($serviceProvider->status === 'approved')
                    <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Approuvé</span>
                @elseif($serviceProvider->status === 'rejected')
                    <span class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">Rejeté</span>
                @else
                    <span class="px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">En attente</span>
                @endif
            </div>
            <div>
                <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Spécialité</p>
                <p class="font-medium text-slate-800" style="font-family: Outfit, sans-serif">{{ $serviceProvider->specialty }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Localisation</p>
                <p class="font-medium text-slate-800" style="font-family: Outfit, sans-serif">{{ $serviceProvider->location }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Expérience (années)</p>
                <p class="font-medium text-slate-800" style="font-family: Outfit, sans-serif">{{ $serviceProvider->experience_years }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Taux horaire (TND)</p>
                <p class="font-medium text-slate-800" style="font-family: Outfit, sans-serif">{{ $serviceProvider->hourly_rate ? number_format((float) $serviceProvider->hourly_rate, 2) : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Disponibilité</p>
                <span class="px-2 py-1 rounded text-xs font-medium {{ $serviceProvider->availability ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $serviceProvider->availability ? 'Disponible' : 'Indisponible' }}
                </span>
            </div>
            <div>
                <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Téléphone</p>
                <p class="font-medium text-slate-800" style="font-family: Outfit, sans-serif">{{ $serviceProvider->phone ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Description</p>
                <p class="text-slate-700 whitespace-pre-wrap" style="font-family: Outfit, sans-serif">{{ $serviceProvider->description ?? 'Aucune description fournie.' }}</p>
            </div>
        </div>
        
        <div class="mt-8 flex gap-3 border-t border-slate-100 pt-6">
            @if($serviceProvider->status !== 'approved')
                <form method="POST" action="{{ route('admin.service-providers.approve', $serviceProvider) }}" onsubmit="return confirm('Confirmer l\'approbation de ce profil ?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-semibold hover:bg-green-600 transition-colors" style="font-family: Outfit, sans-serif">
                        Approuver
                    </button>
                </form>
            @endif

            @if($serviceProvider->status !== 'rejected')
                <form method="POST" action="{{ route('admin.service-providers.reject', $serviceProvider) }}" onsubmit="return confirm('Confirmer le rejet de ce profil ?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors" style="font-family: Outfit, sans-serif">
                        Rejeter
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

@endsection
