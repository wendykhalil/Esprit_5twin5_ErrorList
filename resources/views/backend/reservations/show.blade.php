@extends('layouts.backend')

@section('title', 'Détail réservation - SolarShare Admin')

@section('content')
<div class="space-y-5">
    <a href="{{ route('admin.reservations.index') }}" class="text-sm font-600 text-amber-600 hover:text-amber-700">← Retour aux réservations</a>
    <div class="flex items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Réservation #{{ $reservation->id }}</h2>
            <p class="text-sm text-slate-500 mt-1">Détail de la réservation et de ses inspections.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.inspections.create', ['reservation_id' => $reservation->id]) }}" class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-600 text-white hover:bg-amber-600">Ajouter une inspection</a>
            <a href="{{ route('admin.reservations.edit', $reservation) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-600 text-slate-700 hover:bg-slate-50">Modifier</a>
        </div>
    </div>
    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <dl class="divide-y divide-slate-100">
                <div class="flex justify-between gap-4 py-3"><dt class="text-sm text-slate-500">Client</dt><dd class="text-sm font-600 text-slate-800">{{ $reservation->user->name }}</dd></div>
                <div class="flex justify-between gap-4 py-3"><dt class="text-sm text-slate-500">Équipement</dt><dd class="text-sm font-600 text-slate-800">{{ $reservation->equipment_label }}</dd></div>
                <div class="flex justify-between gap-4 py-3"><dt class="text-sm text-slate-500">Période</dt><dd class="text-sm font-600 text-slate-800">{{ $reservation->date_debut->format('d/m/Y') }} → {{ $reservation->date_fin->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between gap-4 py-3"><dt class="text-sm text-slate-500">Prix total</dt><dd class="text-sm font-600 text-slate-800">{{ number_format($reservation->prix_total, 2) }} TND</dd></div>
            </dl>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h3 class="font-bold text-slate-800">Inspections</h3>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse($reservation->inspections as $inspection)
                    <div class="flex justify-between gap-4 py-3 text-sm"><a href="{{ route('admin.inspections.show', $inspection) }}" class="text-amber-600 hover:text-amber-700">{{ ucfirst($inspection->type) }} · {{ ucfirst($inspection->etat) }}</a><span class="text-slate-500">{{ $inspection->date_inspection->format('d/m/Y H:i') }}</span></div>
                @empty
                    <p class="py-4 text-sm text-slate-500">Aucune inspection.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection