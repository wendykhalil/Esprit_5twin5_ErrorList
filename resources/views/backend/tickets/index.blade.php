@extends('layouts.backend')

@section('title', 'Support - SolarShare Admin')

@section('content')
@php
    $statusLabels = [
        'open' => 'Ouvert',
        'in_progress' => 'En cours',
        'resolved' => 'Résolu',
        'closed' => 'Fermé',
    ];
    $priorityLabels = [
        'low' => 'Basse',
        'normal' => 'Normale',
        'high' => 'Haute',
    ];
    $displayed = $tickets->total();
@endphp

<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Back-office</p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900" style="font-family: Outfit, sans-serif">Support</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $displayed }} {{ $displayed > 1 ? 'tickets affichés' : 'ticket affiché' }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-medium text-slate-500">Ouverts</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ (int) ($supportStats->open_count ?? 0) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-medium text-slate-500">En cours</p>
            <p class="mt-1 text-2xl font-semibold text-amber-700">{{ (int) ($supportStats->progress_count ?? 0) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-medium text-slate-500">Résolus</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-700">{{ (int) ($supportStats->resolved_count ?? 0) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-medium text-slate-500">Priorité haute</p>
            <p class="mt-1 text-2xl font-semibold text-red-700">{{ (int) ($supportStats->high_count ?? 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-medium text-slate-500">Première réponse</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $delayStats['first_response'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-medium text-slate-500">Résolution</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $delayStats['resolution'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-medium text-slate-500">Fermeture</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $delayStats['closure'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-medium text-slate-500">Taux de réouverture</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $delayStats['reopen_rate'] }} %</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.tickets.index') }}" class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-5">
                <label for="ticket-search" class="mb-1.5 block text-xs font-medium text-slate-600">Recherche</label>
                <input
                    id="ticket-search"
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Référence ou sujet"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-800 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30"
                />
            </div>
            <div class="lg:col-span-3">
                <label for="ticket-status" class="mb-1.5 block text-xs font-medium text-slate-600">Statut</label>
                <select id="ticket-status" name="status" class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm text-slate-800 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30">
                    <option value="">Tous les statuts</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label for="ticket-priority" class="mb-1.5 block text-xs font-medium text-slate-600">Priorité</label>
                <select id="ticket-priority" name="priority" class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm text-slate-800 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30">
                    <option value="">Toutes</option>
                    @foreach($priorityLabels as $value => $label)
                        <option value="{{ $value }}" @selected($priority === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 lg:col-span-2">
                <button type="submit" class="flex-1 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-amber-600">Filtrer</button>
                <a href="{{ route('admin.tickets.index') }}" class="flex-1 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Réinitialiser</a>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-[760px] w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Référence</th>
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Sujet</th>
                        <th class="px-4 py-3">Priorité</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3">Réponses</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="inline-flex rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-800 hover:bg-amber-50 hover:text-amber-800">{{ $ticket->reference }}</a>
                            </td>
                            <td class="max-w-[140px] truncate px-4 py-3 text-slate-700" title="{{ $ticket->user->name }}">{{ $ticket->user->name }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-slate-800" title="{{ $ticket->subject }}">{{ $ticket->subject }}</td>
                            <td class="px-4 py-3"><x-support.priority-badge :priority="$ticket->priority" /></td>
                            <td class="px-4 py-3"><x-support.status-badge :status="$ticket->status" /></td>
                            <td class="px-4 py-3 text-slate-600">{{ $ticket->replies_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="inline-flex rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">Aucun ticket ne correspond à ces critères.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($tickets->hasPages())
        <div class="text-sm text-slate-600">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
