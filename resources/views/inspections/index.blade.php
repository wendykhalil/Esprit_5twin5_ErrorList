@extends('layouts.frontend')

@section('content')
<div class="bg-slate-50 min-h-screen">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-[0.18em] text-green-600">SolarShare</p>
                <h1 class="text-4xl font-bold tracking-tight text-slate-900">Inspections</h1>
                <p class="mt-2 max-w-2xl text-slate-500">Suivez l’état des équipements lors de leur remise et de leur retour.</p>
            </div>

            @if($inspections->isNotEmpty())
                <a href="{{ route('inspections.create') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    + Ajouter une inspection
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
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">État</th>
                            <th class="px-6 py-4">Batterie</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($inspections as $inspection)
                        @php
                            $typeLabels = ['remise' => 'Remise', 'retour' => 'Retour'];
                            $stateLabels = ['neuf' => 'Neuf', 'bon' => 'Bon', 'use' => 'Usé', 'endommage' => 'Endommagé'];
                            $stateClasses = match ($inspection->etat) {
                                'neuf', 'bon' => 'bg-green-100 text-green-700',
                                'use' => 'bg-amber-100 text-amber-700',
                                'endommage' => 'bg-red-100 text-red-700',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp
                        <tr class="transition hover:bg-green-50/40">
                            <td class="whitespace-nowrap px-6 py-5 text-sm font-medium text-slate-800">{{ $inspection->reservation->equipment_label }}</td>
                            <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">{{ $typeLabels[$inspection->type] ?? ucfirst($inspection->type) }}</td>
                            <td class="whitespace-nowrap px-6 py-5">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $stateClasses }}">
                                    {{ $stateLabels[$inspection->etat] ?? ucfirst($inspection->etat) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">{{ $inspection->niveau_batterie !== null ? $inspection->niveau_batterie.' %' : '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">{{ $inspection->date_inspection->format('d/m/Y H:i') }}</td>
                            <td class="whitespace-nowrap px-6 py-5 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('inspections.show', $inspection) }}" class="rounded-lg px-3 py-2 text-xs font-semibold text-green-700 hover:bg-green-50">Voir</a>
                                    <a href="{{ route('inspections.edit', $inspection) }}" class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">Modifier</a>
                                    <form action="{{ route('inspections.destroy', $inspection) }}" method="POST" onsubmit="return confirm('Supprimer cette inspection ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <p class="text-lg font-semibold text-slate-800">Vous n'avez pas encore d'inspection.</p>
                                <p class="mt-1 text-sm text-slate-500">Ajoutez un constat lié à l’une de vos réservations.</p>
                                <a href="{{ route('inspections.create') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700">Ajouter une inspection</a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if ($inspections->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $inspections->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
