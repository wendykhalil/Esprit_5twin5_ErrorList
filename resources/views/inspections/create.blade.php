@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('inspections.index') }}" class="text-sm font-semibold text-green-700 hover:text-green-800">← Retour aux inspections</a>
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-green-600">Nouvelle inspection</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Constater l'état d'un équipement</h1>
            <p class="mt-2 mb-8 text-sm text-slate-500">Renseignez les informations de l'inspection ci-dessous.</p>
            <form action="{{ route('inspections.store') }}" method="POST" class="space-y-8">
                @csrf
                @include('inspections._form')
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('inspections.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Annuler</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-700">Enregistrer l'inspection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
