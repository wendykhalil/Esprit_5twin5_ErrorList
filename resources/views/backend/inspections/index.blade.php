@extends('layouts.backend')

@section('title', 'Inspections - SolarShare Admin')

@section('content')
<div class="space-y-5">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Inspections</h2>
            <p class="text-slate-500 text-sm mt-0.5">{{ $inspections->total() }} inspections trouvées</p>
        </div>
        <a href="{{ route('admin.inspections.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-600 text-white shadow-sm hover:bg-amber-600">+ Ajouter une inspection</a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="alert">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form method="GET" class="flex flex-col lg:flex-row gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par client ou équipement…" class="flex-1 px-4 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 placeholder-slate-400">
            <select name="type" class="px-3 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white">
                @foreach($types as $value => $label)<option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>@endforeach
            </select>
            <select name="etat" class="px-3 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white">
                @foreach($states as $value => $label)<option value="{{ $value }}" @selected($selectedState === $value)>{{ $label }}</option>@endforeach
            </select>
            <button type="submit" class="px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600">Filtrer</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 border-b border-slate-200">
                    <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase">#</th>
                    <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase">Client</th>
                    <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase">Équipement</th>
                    <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase">Type</th>
                    <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase">État</th>
                    <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase">Batterie</th>
                    <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase">Date</th>
                    <th class="text-right px-5 py-3.5 text-xs font-600 text-slate-500 uppercase">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($inspections as $inspection)
                    @php
                        $typeLabels = ['remise' => 'Remise', 'retour' => 'Retour'];
                        $stateLabels = ['neuf' => 'Neuf', 'bon' => 'Bon', 'use' => 'Usé', 'endommage' => 'Endommagé'];
                        $stateClasses = match ($inspection->etat) {
                            'neuf', 'bon' => 'bg-green-50 text-green-700 border-green-200',
                            'use' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'endommage' => 'bg-red-50 text-red-600 border-red-200',
                            default => 'bg-slate-100 text-slate-600 border-slate-200',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4 text-slate-400 font-mono text-xs">#{{ str_pad($inspection->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-5 py-4 font-500 text-slate-800">{{ $inspection->reservation->user->name }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $inspection->reservation->equipment_label }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $typeLabels[$inspection->type] ?? ucfirst($inspection->type) }}</td>
                        <td class="px-5 py-4"><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-600 {{ $stateClasses }}">{{ $stateLabels[$inspection->etat] ?? ucfirst($inspection->etat) }}</span></td>
                        <td class="px-5 py-4 text-slate-600">{{ $inspection->niveau_batterie !== null ? $inspection->niveau_batterie.' %' : '-' }}</td>
                        <td class="px-5 py-4 text-slate-500 text-xs">{{ $inspection->date_inspection->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4"><div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.inspections.show', $inspection) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Voir" aria-label="Voir l'inspection"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></a>
                            <a href="{{ route('admin.inspections.edit', $inspection) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Modifier" aria-label="Modifier l'inspection"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg></a>
                            <form action="{{ route('admin.inspections.destroy', $inspection) }}" method="POST" onsubmit="return confirm('Supprimer cette inspection ?')">@csrf @method('DELETE')<button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Supprimer" aria-label="Supprimer l'inspection"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397"/></svg></button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-12 text-slate-400">Aucune inspection trouvée.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($inspections->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $inspections->links() }}</div>@endif
    </div>
</div>
@endsection