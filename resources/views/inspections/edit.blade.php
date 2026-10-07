@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <h1>Modifier l'inspection #{{ $inspection->id }}</h1>
    <form action="{{ route('inspections.update', $inspection) }}" method="POST">
        @csrf @method('PUT')
        @include('inspections._form')
        <button class="btn btn-primary">Enregistrer les modifications</button>
        <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
