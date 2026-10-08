@extends('layouts.backend')
@section('title', 'Ajouter une inspection - SolarShare Admin')
@section('content')
<div class="mx-auto max-w-3xl space-y-5">
    <a href="{{ route('admin.inspections.index') }}" class="text-sm font-600 text-amber-600 hover:text-amber-700">← Retour aux inspections</a>
    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="text-2xl font-bold text-slate-900">Ajouter une inspection</h2>
        <p class="mt-1 text-sm text-slate-500">Enregistrez le constat d'une réservation.</p>
        <form action="{{ route('admin.inspections.store') }}" method="POST" class="mt-6 space-y-6">@csrf @include('backend.inspections._form')<div class="flex justify-end gap-3 border-t border-slate-100 pt-5"><a href="{{ route('admin.inspections.index') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-600 text-slate-600 hover:bg-slate-50">Annuler</a><button class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-600 text-white hover:bg-amber-600">Enregistrer</button></div></form>
    </div>
</div>
@endsection