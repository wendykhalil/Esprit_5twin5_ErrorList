@extends('layouts.frontend')

@section('content')
<div class="min-h-[calc(100vh-4rem)] bg-slate-50 px-4 pt-24 pb-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('reservations.index') }}" class="text-sm font-semibold text-green-700 hover:text-green-800">← Retour aux réservations</a>
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-green-600">Nouvelle réservation</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Réserver un équipement</h1>
            <p class="mt-2 mb-8 text-sm text-slate-500">Renseignez les informations de la réservation ci-dessous.</p>
            <form action="{{ route('reservations.store') }}" method="POST" class="space-y-8">
                @csrf
                @include('reservations._form')
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('reservations.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Annuler</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-700">Enregistrer la réservation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
