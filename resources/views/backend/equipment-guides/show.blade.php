@extends('layouts.backend')

@section('title', 'Détails du guide - SolarShare Admin')

@section('content')
<div class="w-full space-y-5 pb-8" style="font-family: Outfit, sans-serif">
    {{-- Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-600">SolarShare / Administration / Guides d'utilisation</p>
            <h1 class="mt-2 break-words text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $equipmentGuide->title }}</h1>
            <p class="mt-1 text-sm text-slate-500">Fiche du guide #{{ $equipmentGuide->id }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.equipment-guides.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">← Retour</a>
            <a href="{{ route('admin.equipment-guides.edit', $equipmentGuide) }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-amber-600">Modifier le guide</a>
        </div>
    </div>

    {{-- Summary --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-900 px-5 py-5 text-white sm:px-7">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-400">Guide d'utilisation #{{ $equipmentGuide->id }}</p>
            <h2 class="mt-2 break-words text-xl font-bold sm:text-2xl">{{ $equipmentGuide->title }}</h2>
            <p class="mt-1 text-sm text-slate-300">{{ $equipmentGuide->equipment?->name ?? 'Équipement indisponible' }}</p>
        </div>
        <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-3 sm:p-6">
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Publication</p>
                <div class="mt-3">
                    @if($equipmentGuide->status === 'published')
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Publié</span>
                    @elseif($equipmentGuide->status === 'pending')
                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>En attente</span>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Brouillon</span>
                    @endif
                </div>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Difficulté</p>
                <p class="mt-3 text-sm font-bold text-slate-900">
                    @switch($equipmentGuide->difficulty_level)
                        @case('beginner') Débutant @break
                        @case('intermediate') Intermédiaire @break
                        @case('advanced') Avancé @break
                        @default {{ $equipmentGuide->difficulty_level ?: 'Non renseigné' }}
                    @endswitch
                </p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Équipement associé</p>
                <p class="mt-3 break-words text-sm font-bold text-slate-900">{{ $equipmentGuide->equipment?->name ?? 'Équipement indisponible' }}</p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-12">
        <div class="space-y-5 xl:col-span-8">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4 flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 font-bold text-amber-600">◎</span><h3 class="text-base font-bold text-slate-900">Contexte d'utilisation</h3></div>
                <p class="whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $equipmentGuide->usage_context ?: 'Aucun contexte renseigné.' }}</p>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4 flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 font-bold text-amber-600">≡</span><h3 class="text-base font-bold text-slate-900">Instructions d'utilisation</h3></div>
                <div class="whitespace-pre-line break-words rounded-xl bg-slate-50 p-5 text-sm leading-7 text-slate-700">{{ $equipmentGuide->instructions ?: 'Aucune instruction renseignée.' }}</div>
            </section>
            <section class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 shadow-sm sm:p-6">
                <div class="mb-4 flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 font-bold text-amber-700">!</span><h3 class="text-base font-bold text-amber-900">Précautions de sécurité</h3></div>
                @if($equipmentGuide->safety_precautions)
                    <div class="whitespace-pre-line break-words text-sm leading-7 text-amber-950">{{ $equipmentGuide->safety_precautions }}</div>
                @else
                    <p class="text-sm text-amber-800">Aucune précaution renseignée.</p>
                @endif
            </section>
        </div>
        <aside class="space-y-5 xl:col-span-4">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h3 class="mb-4 text-base font-bold text-slate-900">Vidéo explicative</h3>
                @if($equipmentGuide->video_url)
                    <div class="mb-4 flex h-28 items-center justify-center rounded-xl bg-slate-900 text-4xl text-white" aria-hidden="true">▶</div>
                    <a href="{{ $equipmentGuide->video_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-amber-600">Voir la vidéo ↗</a>
                    <p class="mt-3 break-all text-xs text-slate-500">{{ $equipmentGuide->video_url }}</p>
                @else
                    <div class="rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500">Aucune vidéo associée.</div>
                @endif
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h3 class="mb-4 text-base font-bold text-slate-900">Historique</h3>
                <div class="space-y-3">
                    <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Créé le</p><p class="mt-1 text-sm font-bold text-slate-900">{{ $equipmentGuide->created_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Dernière modification</p><p class="mt-1 text-sm font-bold text-slate-900">{{ $equipmentGuide->updated_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}</p></div>
                </div>
            </section>
            <section class="rounded-2xl bg-slate-900 p-5 text-white shadow-sm sm:p-6">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-400">Gestion du guide</p>
                <h3 class="mt-2 text-lg font-bold">Mettre à jour le contenu</h3>
                <p class="mt-2 text-sm leading-6 text-slate-300">Modifiez les instructions, les précautions et le statut de publication.</p>
                <a href="{{ route('admin.equipment-guides.edit', $equipmentGuide) }}" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-amber-600">Modifier le guide →</a>
            </section>
        </aside>
    </div>
</div>
@endsection
