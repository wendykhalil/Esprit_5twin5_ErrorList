
@extends('layouts.backend')

@section('title', 'Détails équipement - SolarShare Admin')

@section('content')

<div class="w-full space-y-5 pb-8" style="font-family: Outfit, sans-serif;">

    {{-- ================================================= --}}
    {{-- PAGE HEADER --}}
    {{-- ================================================= --}}

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">
                SolarShare / Administration / Équipements
            </p>

            <h1 class="text-2xl font-bold text-slate-900">
                Détails de l'équipement
            </h1>

            <p class="text-sm text-slate-500 mt-1">
                Fiche administrative #{{ $equipment->id }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">

            <a href="{{ route('admin.equipments') }}"
               class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                ← Retour
            </a>

            <a href="{{ route('admin.equipments.edit', $equipment) }}"
               class="inline-flex items-center justify-center px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                Modifier l'équipement
            </a>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- MAIN EQUIPMENT CARD --}}
    {{-- ================================================= --}}

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

        <div class="grid grid-cols-1 lg:grid-cols-2">

            {{-- IMAGE SECTION --}}
            <div class="bg-slate-50 border-b lg:border-b-0 lg:border-r border-slate-200 min-w-0">

                <div
                    class="relative flex items-center justify-center overflow-hidden p-4"
                    style="height: 340px; max-height: 340px;"
                >

                    @if($equipment->image)

                        <img
                            src="{{ asset('storage/' . $equipment->image) }}"
                            alt="{{ $equipment->name }}"
                            class="block object-contain"
                            style="
                                width: 100%;
                                height: 100%;
                                max-width: 100%;
                                max-height: 100%;
                                object-fit: contain;
                            "
                            loading="lazy"
                        >

                    @else

                        <div class="flex flex-col items-center justify-center gap-3 text-slate-400">

                            <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-3xl">
                                ☀️
                            </div>

                            <p class="text-sm font-medium">
                                Aucune image disponible
                            </p>

                        </div>

                    @endif

                </div>

            </div>


            {{-- EQUIPMENT INFORMATION --}}
            <div class="p-5 lg:p-6 flex flex-col justify-center gap-4 min-w-0">

                {{-- NAME AND CATEGORY --}}
                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-amber-600">
                        Équipement #{{ $equipment->id }}
                    </p>

                    <h2 class="text-xl lg:text-2xl font-bold text-slate-900 mt-2 break-words">
                        {{ $equipment->name }}
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        {{ $equipment->category?->name ?? 'Sans catégorie' }}
                    </p>

                </div>


                {{-- STATUS --}}
                <div class="flex flex-wrap items-center gap-2">

                    @if($equipment->availability)

                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">

                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>

                            Disponible

                        </span>

                    @else

                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-xs font-semibold">

                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>

                            Indisponible

                        </span>

                    @endif

                    <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                        {{ ucfirst($equipment->status ?? 'Non défini') }}
                    </span>

                </div>


                {{-- DAILY PRICE --}}
                <div class="rounded-xl bg-amber-50 border border-amber-100 px-4 py-3">

                    <p class="text-xs font-medium text-slate-600">
                        Tarif journalier
                    </p>

                    <div class="flex items-baseline flex-wrap gap-2 mt-1">

                        <span class="text-3xl font-bold text-amber-600">
                            {{ number_format((float) $equipment->price_per_day, 2, ',', ' ') }}
                        </span>

                        <span class="text-xs font-semibold text-amber-700">
                            TND / jour
                        </span>

                    </div>

                </div>


                {{-- BRAND AND LOCATION --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    <div class="rounded-xl border border-slate-200 p-3">

                        <p class="text-xs text-slate-500 mb-1">
                            Marque
                        </p>

                        <p class="text-sm font-bold text-slate-900 break-words">
                            {{ $equipment->brand ?: 'Non renseignée' }}
                        </p>

                    </div>

                    <div class="rounded-xl border border-slate-200 p-3">

                        <p class="text-xs text-slate-500 mb-1">
                            Localisation
                        </p>

                        <p class="text-sm font-bold text-slate-900 break-words">
                            {{ $equipment->location ?: 'Non renseignée' }}
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- DESCRIPTION AND OWNER --}}
    {{-- ================================================= --}}

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- DESCRIPTION --}}
        <div class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6">

            <div class="flex items-center gap-3 mb-4">

                <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 text-xl shrink-0">
                    ☰
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        Description
                    </h3>

                    <p class="text-xs text-slate-500 mt-1">
                        Présentation et caractéristiques de l'équipement
                    </p>
                </div>

            </div>

            <div class="text-sm text-slate-600 leading-7 whitespace-pre-line break-words">{{ $equipment->description ?: 'Aucune description disponible.' }}</div>

        </div>


        {{-- OWNER --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6">

            <h3 class="text-base font-bold text-slate-900 mb-4">
                Propriétaire
            </h3>

            <div class="flex items-center gap-3">

                <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-lg shrink-0">
                    {{ mb_strtoupper(mb_substr($equipment->user?->name ?? 'U', 0, 1)) }}
                </div>

                <div class="min-w-0">

                    <p class="text-sm font-bold text-slate-900 break-words">
                        {{ $equipment->user?->name ?? 'Non renseigné' }}
                    </p>

                    <p class="text-xs text-slate-500 mt-1">
                        Propriétaire de l'équipement
                    </p>

                </div>

            </div>

            <div class="border-t border-slate-100 mt-5 pt-4">

                <p class="text-xs text-slate-500">
                    Catégorie associée
                </p>

                <p class="text-sm font-semibold text-slate-800 mt-1">
                    {{ $equipment->category?->name ?? 'Sans catégorie' }}
                </p>

            </div>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- ADMINISTRATION INFORMATION --}}
    {{-- ================================================= --}}

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:p-6">

        <div class="mb-5">

            <h3 class="text-base font-bold text-slate-900">
                Informations administratives
            </h3>

            <p class="text-xs text-slate-500 mt-1">
                Identification et historique de l'équipement
            </p>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- EQUIPMENT ID --}}
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">

                <p class="text-xs text-slate-500 mb-2">
                    Identifiant
                </p>

                <p class="text-sm font-bold text-slate-900">
                    #{{ $equipment->id }}
                </p>

            </div>


            {{-- CATEGORY --}}
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">

                <p class="text-xs text-slate-500 mb-2">
                    Catégorie
                </p>

                <p class="text-sm font-bold text-slate-900 break-words">
                    {{ $equipment->category?->name ?? 'Non renseignée' }}
                </p>

            </div>


            {{-- CREATED DATE --}}
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">

                <p class="text-xs text-slate-500 mb-2">
                    Date de création
                </p>

                <p class="text-sm font-bold text-slate-900">
                    {{ $equipment->created_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}
                </p>

            </div>


            {{-- LAST UPDATE --}}
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">

                <p class="text-xs text-slate-500 mb-2">
                    Dernière modification
                </p>

                <p class="text-sm font-bold text-slate-900">
                    {{ $equipment->updated_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}
                </p>

            </div>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- FOOTER ACTIONS --}}
    {{-- ================================================= --}}

    <div class="flex flex-col sm:flex-row justify-end gap-3">

        <a href="{{ route('admin.equipments') }}"
           class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
            Retour à la liste
        </a>

        <a href="{{ route('admin.equipments.edit', $equipment) }}"
           class="inline-flex items-center justify-center px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold shadow-sm transition">
            Modifier l'équipement
        </a>

    </div>

</div>

@endsection
