@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <h1>Nouvelle inspection</h1>
    <form action="{{ route('inspections.store') }}" method="POST">
        @csrf
        @include('inspections._form')
        <button class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('inspections.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
