@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <h1>Réservation #{{ $reservation->id }}</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <dl class="row">
        <dt class="col-sm-3">Utilisateur</dt><dd class="col-sm-9">{{ $reservation->user->name }}</dd>
        <dt class="col-sm-3">Équipement</dt><dd class="col-sm-9">{{ $reservation->equipement->nom ?? '#'.$reservation->equipement_id }}</dd>
        <dt class="col-sm-3">Période</dt><dd class="col-sm-9">du {{ $reservation->date_debut->format('d/m/Y') }} au {{ $reservation->date_fin->format('d/m/Y') }}</dd>
        <dt class="col-sm-3">Statut</dt><dd class="col-sm-9">{{ ucfirst(str_replace('_', ' ', $reservation->statut)) }}</dd>
        <dt class="col-sm-3">Prix total</dt><dd class="col-sm-9">{{ number_format($reservation->prix_total, 2) }} DT</dd>
    </dl>

    <h2 class="h4 mt-4">Inspections</h2>
    <ul>
        @forelse ($reservation->inspections as $inspection)
            <li>
                <a href="{{ route('inspections.show', $inspection) }}">{{ ucfirst($inspection->type) }}</a>
                : {{ $inspection->etat }}
                @if (!is_null($inspection->niveau_batterie)) (batterie {{ $inspection->niveau_batterie }} %) @endif
            </li>
        @empty
            <li>Aucune inspection enregistrée.</li>
        @endforelse
    </ul>

    <a href="{{ route('inspections.create') }}" class="btn btn-outline-primary btn-sm">Ajouter une inspection</a>
    <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-warning btn-sm">Modifier</a>
    <a href="{{ route('reservations.index') }}" class="btn btn-secondary btn-sm">Retour à la liste</a>
</div>
@endsection
