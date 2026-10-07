@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <h1>Modifier la réservation #{{ $reservation->id }}</h1>
    <form action="{{ route('reservations.update', $reservation) }}" method="POST">
        @csrf @method('PUT')
        @include('reservations._form')
        <button class="btn btn-primary">Enregistrer les modifications</button>
        <a href="{{ route('reservations.show', $reservation) }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
