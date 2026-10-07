@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <h1>Nouvelle réservation</h1>
    <form action="{{ route('reservations.store') }}" method="POST">
        @csrf
        @include('reservations._form')
        <button class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('reservations.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
