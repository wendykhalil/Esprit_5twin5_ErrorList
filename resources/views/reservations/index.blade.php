@extends('layouts.frontend')

@section('content')
<div class="bg-slate-50 min-h-screen">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-[0.18em] text-green-600">SolarShare</p>
                <h1 class="text-4xl font-bold tracking-tight text-slate-900">Réservations</h1>
                <p class="mt-2 max-w-2xl text-slate-500">Consultez les réservations de matériel solaire et leur état.</p>
            </div>

            @if($reservations->isNotEmpty())
                <a href="{{ route('reservations.create') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    + Ajouter une réservation
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left">
                    <thead class="bg-slate-50">
                        <tr class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Équipement</th>
                            <th class="px-6 py-4">Période</th>
                            <th class="px-6 py-4">Statut</th>
                            <th class="px-6 py-4">Prix</th>
                            @auth
                                <th class="px-6 py-4 text-right">Actions</th>
                            @endauth
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($reservations as $reservation)
                        @php
                            $statusClasses = match ($reservation->statut) {
                                'confirmee', 'confirmée' => 'bg-green-100 text-green-700',
                                'annulee', 'annulée' => 'bg-red-100 text-red-700',
                                default => 'bg-amber-100 text-amber-700',
                            };
                        @endphp
                        <tr class="transition hover:bg-green-50/40">
                            <td class="whitespace-nowrap px-6 py-5 text-sm font-medium text-slate-800">{{ $reservation->equipment_label }}</td>
                            <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">
                                {{ $reservation->date_debut->format('d/m/Y') }} <span class="text-slate-400">→</span> {{ $reservation->date_fin->format('d/m/Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-5">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ ucfirst(str_replace('_', ' ', $reservation->statut)) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-5 text-sm font-semibold text-slate-800">{{ number_format($reservation->prix_total, 2) }} DT</td>
                            @auth
                                <td class="whitespace-nowrap px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('reservations.show', $reservation) }}" class="rounded-lg px-3 py-2 text-xs font-semibold text-green-700 hover:bg-green-50">Voir</a>
                                        @if($reservation->statut === 'en_attente')
                                            <a href="{{ route('reservations.edit', $reservation) }}" class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">Modifier</a>
                                        @endif
                                        @if(in_array($reservation->statut, ['en_attente', 'confirmee']))
                                            <form action="{{ route('reservations.cancel', $reservation) }}" method="POST" style="display: inline;" onsubmit="return confirm('Annuler cette réservation ?');">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="rounded-lg px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">Annuler</button>
                                            </form>
                                        @endif
                                        @if($reservation->statut === 'en_attente')
                                            <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette réservation ?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-100">Supprimer</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @endauth
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <p class="text-lg font-semibold text-slate-800">Vous n'avez pas encore de réservation.</p>
                                <p class="mt-1 text-sm text-slate-500">Choisissez un équipement pour commencer.</p>
                                <a href="{{ route('reservations.create') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700">
                                    Réserver un équipement
                                </a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if ($reservations->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
