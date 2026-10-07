@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <h1>Inspection #{{ $inspection->id }}</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <dl class="row">
        <dt class="col-sm-3">Réservation</dt>
        <dd class="col-sm-9"><a href="{{ route('reservations.show', $inspection->reservation) }}">#{{ $inspection->reservation_id }}</a> ({{ $inspection->reservation->user->name }})</dd>
        <dt class="col-sm-3">Équipement</dt><dd class="col-sm-9">{{ $inspection->reservation->equipement->nom ?? '-' }}</dd>
        <dt class="col-sm-3">Type</dt><dd class="col-sm-9">{{ ucfirst($inspection->type) }}</dd>
        <dt class="col-sm-3">État</dt><dd class="col-sm-9">{{ ucfirst($inspection->etat) }}</dd>
        <dt class="col-sm-3">Batterie</dt><dd class="col-sm-9">{{ $inspection->niveau_batterie !== null ? $inspection->niveau_batterie.' %' : '-' }}</dd>
        <dt class="col-sm-3">Observations</dt><dd class="col-sm-9">{{ $inspection->observations ?: '-' }}</dd>
        <dt class="col-sm-3">Date</dt><dd class="col-sm-9">{{ $inspection->date_inspection->format('d/m/Y H:i') }}</dd>
    </dl>

    <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-warning btn-sm">Modifier</a>
    <a href="{{ route('inspections.index') }}" class="btn btn-secondary btn-sm">Retour à la liste</a>
</div>
@endsection
