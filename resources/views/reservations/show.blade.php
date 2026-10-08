@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <a href="{{ route('reservations.index') }}" class="text-sm font-semibold text-green-700 hover:text-green-800">← Retour aux réservations</a>

        @if (session('success'))
            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800">{{ session('success') }}</div>
        @endif

        <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-green-600">Détail de la réservation</p>
                <h1 class="mt-2 text-4xl font-bold tracking-tight text-slate-900">Ma réservation</h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('reservations.edit', $reservation) }}" class="inline-flex items-center rounded-xl bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700">Modifier</a>
            </div>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[1.4fr_0.8fr]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-bold text-slate-900">Informations générales</h2>
                <dl class="mt-6 divide-y divide-slate-100">
                    <div class="flex items-center justify-between gap-4 py-4">
                        <dt class="text-sm text-slate-500">Équipement</dt>
                        <dd class="text-right text-sm font-semibold text-slate-800">{{ $reservation->equipment_label }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-4">
                        <dt class="text-sm text-slate-500">Période</dt>
                        <dd class="text-right text-sm font-semibold text-slate-800">{{ $reservation->date_debut->format('d/m/Y') }} → {{ $reservation->date_fin->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-4">
                        <dt class="text-sm text-slate-500">Statut</dt>
                        <dd><span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">{{ ucfirst(str_replace('_', ' ', $reservation->statut)) }}</span></dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl bg-green-900 p-6 text-white shadow-sm sm:p-8">
                <p class="text-sm font-medium text-white">Prix total</p>
                <p class="mt-3 text-4xl font-bold">{{ number_format($reservation->prix_total, 2) }} <span class="text-xl font-medium">DT</span></p>
                <p class="mt-4 text-sm leading-6 text-white">Montant calculé selon la durée et le tarif journalier de l’équipement.</p>
            </section>
        </div>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Inspections</h2>
                    <p class="mt-1 text-sm text-slate-500">Suivi de l’état de l’équipement.</p>
                </div>
                <a href="{{ route('inspections.create', ['reservation_id' => $reservation->id]) }}" class="inline-flex items-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700">Ajouter une inspection</a>
            </div>
            <div class="mt-6 divide-y divide-slate-100">
                @forelse ($reservation->inspections as $inspection)
                    <div class="flex flex-wrap items-center justify-between gap-4 py-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ ucfirst($inspection->type) }}</p>
                            <p class="mt-1 text-sm text-slate-500">État : {{ $inspection->etat }}</p>
                        </div>
                        <div class="text-right text-sm text-slate-500">
                            <p>{{ !is_null($inspection->niveau_batterie) ? 'Batterie '.$inspection->niveau_batterie.' %' : 'Batterie non renseignée' }}</p>
                            <p class="mt-1">{{ $inspection->date_inspection->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('inspections.show', $inspection) }}" class="rounded-lg px-3 py-2 text-xs font-semibold text-green-700 hover:bg-green-50">Voir</a>
                            <a href="{{ route('inspections.edit', $inspection) }}" class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">Modifier</a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-sm text-slate-500">Aucune inspection enregistrée.</p>
                        <a href="{{ route('inspections.create', ['reservation_id' => $reservation->id]) }}" class="mt-4 inline-flex items-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700">Ajouter une inspection</a>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
