
@extends('layouts.backend')

@section('title', 'Détails équipement - SolarShare Admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" style="font-family: Outfit, sans-serif">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">
                Détails de l'équipement
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Consultez les informations de cet équipement depuis le back-office.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.equipments') }}"
               class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Retour
            </a>

            <a href="{{ route('admin.equipments.edit', $equipment) }}"
               class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-semibold hover:bg-amber-600 transition-colors">
                Modifier
            </a>
        </div>
    </div>

    {{-- Equipment overview --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0">

            <div class="bg-slate-100 min-h-72">
                @if($equipment->image)
                    <img
                        src="{{ asset('storage/' . $equipment->image) }}"
                        alt="{{ $equipment->name }}"
                        class="w-full h-full max-h-96 object-cover"
                    >
                @else
                    <div class="h-72 flex items-center justify-center text-slate-400">
                        Aucune image disponible
                    </div>
                @endif
            </div>

            <div class="p-6 space-y-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">
                        Équipement #{{ $equipment->id }}
                    </p>
                    <h3 class="text-2xl font-bold text-slate-900 mt-2">
                        {{ $equipment->name }}
                    </h3>
                    <p class="text-sm text-slate-500 mt-2">
                        {{ $equipment->category?->name ?? 'Sans catégorie' }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $equipment->availability ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $equipment->availability ? 'Disponible' : 'Indisponible' }}
                    </span>

                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                        {{ ucfirst($equipment->status ?? 'Non défini') }}
                    </span>
                </div>

                <div class="border-t border-slate-200 pt-4">
                    <p class="text-sm text-slate-500">Prix par jour</p>
                    <p class="text-3xl font-bold text-amber-600 mt-1">
                        {{ number_format((float) $equipment->price_per_day, 2, ',', ' ') }} DT
                    </p>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Marque</span>
                        <span class="font-semibold text-slate-900 text-right">
                            {{ $equipment->brand ?: 'Non renseignée' }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Localisation</span>
                        <span class="font-semibold text-slate-900 text-right">
                            {{ $equipment->location ?: 'Non renseignée' }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Propriétaire</span>
                        <span class="font-semibold text-slate-900 text-right">
                            {{ $equipment->user?->name ?? 'Non renseigné' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Description --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4">
            Description
        </h3>

        <p class="text-sm text-slate-600 leading-7 whitespace-pre-line">
            {{ $equipment->description ?: 'Aucune description disponible.' }}
        </p>
    </div>

    {{-- Administration information --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-5">
            Informations administratives
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <p class="text-sm text-slate-500">Identifiant</p>
                <p class="font-semibold text-slate-900 mt-1">
                    #{{ $equipment->id }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Catégorie</p>
                <p class="font-semibold text-slate-900 mt-1">
                    {{ $equipment->category?->name ?? 'Non renseignée' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Date de création</p>
                <p class="font-semibold text-slate-900 mt-1">
                    {{ $equipment->created_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">Dernière modification</p>
                <p class="font-semibold text-slate-900 mt-1">
                    {{ $equipment->updated_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('admin.equipments') }}"
           class="flex-1 text-center px-5 py-3 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
            Retour à la liste
        </a>

        <a href="{{ route('admin.equipments.edit', $equipment) }}"
           class="flex-1 text-center px-5 py-3 bg-amber-500 text-white rounded-lg text-sm font-semibold hover:bg-amber-600 transition-colors">
            Modifier l'équipement
        </a>
    </div>

</div>
@endsection
