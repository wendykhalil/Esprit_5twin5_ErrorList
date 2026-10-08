@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <a href="{{ route('reservations.show', $inspection->reservation) }}" class="text-sm font-semibold text-green-700 hover:text-green-800">← Retour à la réservation</a>

        @if (session('success'))
            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800" role="alert">{{ session('success') }}</div>
        @endif

        @php
            $typeLabels = ['remise' => 'Remise', 'retour' => 'Retour'];
            $stateLabels = ['neuf' => 'Neuf', 'bon' => 'Bon', 'use' => 'Usé', 'endommage' => 'Endommagé'];
            $stateClasses = match ($inspection->etat) {
                'neuf', 'bon' => 'bg-green-100 text-green-700',
                'use' => 'bg-amber-100 text-amber-700',
                'endommage' => 'bg-red-100 text-red-700',
                default => 'bg-slate-100 text-slate-700',
            };
        @endphp

        <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-green-600">SolarShare</p>
                <h1 class="mt-2 text-4xl font-bold tracking-tight text-slate-900">Détail de l'inspection</h1>
                <p class="mt-2 text-slate-500">{{ $inspection->reservation->equipment_label }}</p>
            </div>
            <a href="{{ route('inspections.edit', $inspection) }}" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700">Modifier</a>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[1.4fr_0.8fr]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-bold text-slate-900">Informations de l'inspection</h2>
                <dl class="mt-6 divide-y divide-slate-100">
                    <div class="flex items-center justify-between gap-4 py-4">
                        <dt class="text-sm text-slate-500">Équipement</dt>
                        <dd class="text-right text-sm font-semibold text-slate-800">{{ $inspection->reservation->equipment_label }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-4">
                        <dt class="text-sm text-slate-500">Période de réservation</dt>
                        <dd class="text-right text-sm font-semibold text-slate-800">{{ $inspection->reservation->date_debut->format('d/m/Y') }} → {{ $inspection->reservation->date_fin->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-4">
                        <dt class="text-sm text-slate-500">Type</dt>
                        <dd class="text-right text-sm font-semibold text-slate-800">{{ $typeLabels[$inspection->type] ?? ucfirst($inspection->type) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-4">
                        <dt class="text-sm text-slate-500">État</dt>
                        <dd><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $stateClasses }}">{{ $stateLabels[$inspection->etat] ?? ucfirst($inspection->etat) }}</span></dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl bg-green-900 p-6 text-white shadow-sm sm:p-8">
                <p class="text-sm font-medium text-white">Batterie</p>
                <p class="mt-3 text-4xl font-bold">{{ $inspection->niveau_batterie !== null ? $inspection->niveau_batterie : '-' }}<span class="text-xl font-medium">{{ $inspection->niveau_batterie !== null ? ' %' : '' }}</span></p>
                <p class="mt-4 text-sm leading-6 text-white">Inspection enregistrée le {{ $inspection->date_inspection->format('d/m/Y H:i') }}.</p>
            </section>
        </div>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-lg font-bold text-slate-900">Observations</h2>
            <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $inspection->observations ?: 'Aucune observation enregistrée.' }}</p>
        </section>
    </div>
</div>
@endsection
