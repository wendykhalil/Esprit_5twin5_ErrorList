@extends('layouts.backend')

@section('title', 'Modifier réservation - SolarShare Admin')

@section('content')
<div class="mx-auto max-w-2xl space-y-5">
    <a href="{{ route('admin.reservations.show', $reservation) }}" class="text-sm font-600 text-amber-600 hover:text-amber-700">← Retour au détail</a>
    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="text-2xl font-bold text-slate-900">Modifier la réservation #{{ $reservation->id }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $reservation->equipment_label }} · {{ $reservation->user->name }}</p>
        <form action="{{ route('admin.reservations.update', $reservation) }}" method="POST" class="mt-6 space-y-6">
            @csrf @method('PUT')
            @include('backend.reservations._form')
            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.reservations.show', $reservation) }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-600 text-slate-600 hover:bg-slate-50">Annuler</a>
                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-600 text-white hover:bg-amber-600">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection