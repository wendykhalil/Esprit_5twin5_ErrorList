
@extends('layouts.backend')

@section('title', 'Détails du guide - SolarShare Admin')

@section('content')

<div class="py-6" style="font-family: Outfit, sans-serif">
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    {{ $equipmentGuide->title }}
                </h1>

                <p class="text-sm text-slate-500 mt-2">
                    Guide #{{ $equipmentGuide->id }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.equipment-guides.index') }}"
                   class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-white">
                    ← Retour
                </a>

                <a href="{{ route('admin.equipment-guides.edit', $equipmentGuide) }}"
                   class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-semibold">
                    Modifier
                </a>
            </div>
        </div>

        {{-- General information --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-5">
                Informations générales
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase mb-2">
                        Équipement associé
                    </p>
                    <p class="text-slate-800 font-medium">
                        {{ $equipmentGuide->equipment?->name ?? 'Équipement indisponible' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase mb-2">
                        Contexte d'utilisation
                    </p>
                    <p class="text-slate-800">
                        {{ $equipmentGuide->usage_context }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase mb-2">
                        Niveau de difficulté
                    </p>

                    <p class="text-slate-800">
                        @switch($equipmentGuide->difficulty_level)
                            @case('beginner')
                                Débutant
                                @break
                            @case('intermediate')
                                Intermédiaire
                                @break
                            @case('advanced')
                                Avancé
                                @break
                            @default
                                {{ $equipmentGuide->difficulty_level }}
                        @endswitch
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase mb-2">
                        Statut
                    </p>

                    @if($equipmentGuide->status === 'published')
                        <span class="inline-flex px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold">
                            Publié
                        </span>
                    @elseif($equipmentGuide->status === 'pending')
                        <span class="inline-flex px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">
                            En attente
                        </span>
                    @else
                        <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                            Brouillon
                        </span>
                    @endif
                </div>

            </div>
        </div>

        {{-- Instructions --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">
                Instructions d'utilisation
            </h2>

            <div class="text-sm text-slate-700 leading-7 whitespace-pre-line">{{ $equipmentGuide->instructions }}</div>
        </div>

        {{-- Safety precautions --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">
                Précautions de sécurité
            </h2>

            @if($equipmentGuide->safety_precautions)
                <div class="text-sm text-slate-700 leading-7 whitespace-pre-line">{{ $equipmentGuide->safety_precautions }}</div>
            @else
                <p class="text-sm text-slate-400">
                    Aucune précaution renseignée.
                </p>
            @endif
        </div>

        {{-- Video --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">
                Vidéo explicative
            </h2>

            @if($equipmentGuide->video_url)
                <a href="{{ $equipmentGuide->video_url }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="text-amber-600 font-semibold text-sm hover:underline break-all">
                    Voir la vidéo explicative ↗
                </a>
            @else
                <p class="text-sm text-slate-400">
                    Aucune vidéo associée.
                </p>
            @endif
        </div>

        {{-- Dates --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">
                Historique
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
                <div>
                    <p class="text-slate-500 mb-1">Date de création</p>
                    <p class="font-medium text-slate-800">
                        {{ $equipmentGuide->created_at?->format('d/m/Y à H:i') }}
                    </p>
                </div>

                <div>
                    <p class="text-slate-500 mb-1">Dernière modification</p>
                    <p class="font-medium text-slate-800">
                        {{ $equipmentGuide->updated_at?->format('d/m/Y à H:i') }}
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
