@extends('layouts.backend')

@section('title', 'Code ' . $compensationCode->code . ' - SolarShare Admin')

@section('content')

<div class="max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex justify-between items-start">
        <div>
            <a href="{{ route('admin.compensation-codes.index') }}" class="text-amber-500 hover:text-amber-600 font-600 mb-4 inline-flex items-center gap-2" style="font-family: Outfit, sans-serif">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Retour
            </a>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">{{ $compensationCode->code }}</h2>
        </div>
        <div class="flex gap-2">
            @if($compensationCode->uses_count === 0)
                <a
                    href="{{ route('admin.compensation-codes.edit', $compensationCode->id) }}"
                    class="px-4 py-2.5 bg-blue-500 text-white rounded-lg font-600 hover:bg-blue-600 transition-colors"
                    style="font-family: Outfit, sans-serif"
                >
                    Éditer
                </a>
            @endif
            <form
                action="{{ route('admin.compensation-codes.toggleActive', $compensationCode->id) }}"
                method="POST"
                class="inline"
            >
                @csrf
                @method('PATCH')
                <button
                    type="submit"
                    class="px-4 py-2.5 {{ $compensationCode->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white rounded-lg font-600 transition-colors"
                    style="font-family: Outfit, sans-serif"
                >
                    {{ $compensationCode->is_active ? 'Désactiver' : 'Activer' }}
                </button>
            </form>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6" style="font-family: Outfit, sans-serif">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6" style="font-family: Outfit, sans-serif">
            {{ session('error') }}
        </div>
    @endif

    {{-- Main Details --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Détails du Code</h3>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            {{-- Status --}}
            <div>
                <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Statut</p>
                <div class="flex items-center gap-2 mt-2">
                    @if($compensationCode->is_active)
                        <span class="inline-block px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-sm font-600" style="font-family: Outfit, sans-serif">Actif</span>
                    @else
                        <span class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded-full text-sm font-600" style="font-family: Outfit, sans-serif">Inactif</span>
                    @endif
                </div>
            </div>

            {{-- Type --}}
            <div>
                <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Type</p>
                <p class="mt-2 text-sm font-600 text-slate-900">
                    {{ $compensationCode->type === 'percentage' ? 'Pourcentage' : 'Montant Fixe' }}
                </p>
            </div>

            {{-- Value --}}
            <div>
                <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Valeur</p>
                <p class="mt-2 text-lg font-bold text-green-600">
                    @if($compensationCode->type === 'percentage')
                        {{ $compensationCode->value }}%
                    @else
                        {{ number_format($compensationCode->value, 2) }} TND
                    @endif
                </p>
            </div>

            {{-- Client --}}
            <div>
                <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Client</p>
                <p class="mt-2 font-600 text-slate-900">{{ $compensationCode->user?->name ?? 'N/A' }}</p>
                <p class="text-xs text-slate-500">{{ $compensationCode->user?->email ?? 'N/A' }}</p>
            </div>

            {{-- Max Uses --}}
            <div>
                <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Utilisations Max</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ $compensationCode->max_uses }}</p>
            </div>

            {{-- Uses Count --}}
            <div>
                <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Utilisée</p>
                <p class="mt-2 text-lg font-bold text-amber-600">{{ $compensationCode->uses_count }}</p>
                <div class="w-24 h-2 bg-slate-200 rounded-full mt-1 overflow-hidden">
                    <div class="h-full bg-amber-500" style="width: {{ min(($compensationCode->uses_count / $compensationCode->max_uses) * 100, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Validity Period --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Période de Validité</h3>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Valide À Partir De</p>
                <p class="mt-2 text-sm font-600 text-slate-900">{{ $compensationCode->valid_from->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Valide Jusqu'au</p>
                <p class="mt-2 text-sm font-600 text-slate-900">{{ $compensationCode->valid_until->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- Timestamps --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Informations</h3>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Créé Le</p>
                <p class="mt-2 text-sm font-600 text-slate-900">{{ $compensationCode->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Créé Par</p>
                <p class="mt-2 text-sm font-600 text-slate-900">{{ $compensationCode->createdBy?->name ?? 'N/A' }}</p>
            </div>
            @if($compensationCode->last_used_at)
                <div>
                    <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Dernière Utilisation</p>
                    <p class="mt-2 text-sm font-600 text-slate-900">{{ $compensationCode->last_used_at->format('d/m/Y H:i') }}</p>
                </div>
            @endif
            @if($compensationCode->reclamation)
                <div>
                    <p class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Issu De</p>
                    <a href="{{ route('admin.reclamations.show', $compensationCode->reclamation->id) }}" class="mt-2 text-sm font-600 text-amber-500 hover:text-amber-600">
                        Réclamation #{{ $compensationCode->reclamation->id }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Description --}}
    @if($compensationCode->description)
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Description</h3>
            <p class="text-slate-700">{{ $compensationCode->description }}</p>
        </div>
    @endif

    {{-- Delete Button (only if not used) --}}
    @if($compensationCode->uses_count === 0)
        <div class="bg-red-50 rounded-xl border border-red-200 p-6">
            <h3 class="text-lg font-bold text-red-900 mb-4" style="font-family: Outfit, sans-serif">Zone Dangereuse</h3>
            <p class="text-red-700 mb-4">Ce code n'a pas encore été utilisé et peut être supprimé.</p>
            <form
                action="{{ route('admin.compensation-codes.destroy', $compensationCode->id) }}"
                method="POST"
                onsubmit="return confirm('Êtes-vous sûr ? Cette action est irréversible.')"
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="px-4 py-2.5 bg-red-600 text-white rounded-lg font-600 hover:bg-red-700 transition-colors"
                    style="font-family: Outfit, sans-serif"
                >
                    Supprimer le Code
                </button>
            </form>
        </div>
    @endif
</div>

@endsection
