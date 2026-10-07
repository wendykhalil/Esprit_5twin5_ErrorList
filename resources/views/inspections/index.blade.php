@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Inspections</h1>
        <a href="{{ route('inspections.create') }}" class="btn btn-primary">Ajouter une inspection</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr><th>#</th><th>Réservation</th><th>Équipement</th><th>Type</th><th>État</th><th>Batterie</th><th>Date</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($inspections as $inspection)
            <tr>
                <td>{{ $inspection->id }}</td>
                <td><a href="{{ route('reservations.show', $inspection->reservation) }}">#{{ $inspection->reservation_id }}</a></td>
                <td>{{ $inspection->reservation->equipement->nom ?? '-' }}</td>
                <td>{{ ucfirst($inspection->type) }}</td>
                <td>{{ ucfirst($inspection->etat) }}</td>
                <td>{{ $inspection->niveau_batterie !== null ? $inspection->niveau_batterie.' %' : '-' }}</td>
                <td>{{ $inspection->date_inspection->format('d/m/Y H:i') }}</td>
                <td class="d-flex gap-1">
                    <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-sm btn-info">Voir</a>
                    <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-sm btn-warning">Modifier</a>
                    <form action="{{ route('inspections.destroy', $inspection) }}" method="POST"
                          onsubmit="return confirm('Supprimer cette inspection ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8">Aucune inspection pour le moment.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $inspections->links() }}
</div>
@endsection
