@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Réservations</h1>
        <a href="{{ route('reservations.create') }}" class="btn btn-primary">Ajouter une réservation</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>#</th><th>Utilisateur</th><th>Équipement</th><th>Du</th><th>Au</th><th>Statut</th><th>Prix</th><th></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($reservations as $reservation)
            <tr>
                <td>{{ $reservation->id }}</td>
                <td>{{ $reservation->user->name }}</td>
                <td>{{ $reservation->equipement->nom ?? '#'.$reservation->equipement_id }}</td>
                <td>{{ $reservation->date_debut->format('d/m/Y') }}</td>
                <td>{{ $reservation->date_fin->format('d/m/Y') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $reservation->statut)) }}</td>
                <td>{{ number_format($reservation->prix_total, 2) }} DT</td>
                <td class="d-flex gap-1">
                    <a href="{{ route('reservations.show', $reservation) }}" class="btn btn-sm btn-info">Voir</a>
                    <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-sm btn-warning">Modifier</a>
                    <form action="{{ route('reservations.destroy', $reservation) }}" method="POST"
                          onsubmit="return confirm('Supprimer cette réservation ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8">Aucune réservation pour le moment.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $reservations->links() }}
</div>
@endsection
