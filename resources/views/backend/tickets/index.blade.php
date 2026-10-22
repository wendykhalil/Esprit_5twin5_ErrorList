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
    $fieldClass = 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30';
@endphp

<div class="space-y-4">
    <header class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-slate-900" style="font-family: Outfit, sans-serif">Support</h2>
            <p class="mt-0.5 text-sm text-slate-500">Gestion des demandes et suivi des tickets</p>
        </div>
    </header>

    <section aria-label="Indicateurs principaux" class="grid grid-cols-2 gap-2 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5">
            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Ouverts</p>
            <p class="mt-0.5 text-xl font-semibold tabular-nums text-slate-900">{{ (int) ($supportStats->open_count ?? 0) }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5">
            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">En cours</p>
            <p class="mt-0.5 text-xl font-semibold tabular-nums text-amber-700">{{ (int) ($supportStats->progress_count ?? 0) }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5">
            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Résolus</p>
            <p class="mt-0.5 text-xl font-semibold tabular-nums text-emerald-700">{{ (int) ($supportStats->resolved_count ?? 0) }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5">
            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Priorité haute</p>
            <p class="mt-0.5 text-xl font-semibold tabular-nums text-red-700">{{ (int) ($supportStats->high_count ?? 0) }}</p>
        </div>
    </section>

    <section aria-label="Délais et réouvertures" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 sm:grid-cols-4">
            <div class="min-w-0">
                <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Première réponse</dt>
                <dd class="truncate text-sm font-semibold text-slate-800">{{ $delayStats['first_response'] }}</dd>
            </div>
            <div class="min-w-0">
                <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Résolution</dt>
                <dd class="truncate text-sm font-semibold text-slate-800">{{ $delayStats['resolution'] }}</dd>
            </div>
            <div class="min-w-0">
                <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Fermeture</dt>
                <dd class="truncate text-sm font-semibold text-slate-800">{{ $delayStats['closure'] }}</dd>
            </div>
            <div class="min-w-0">
                <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Taux de réouverture</dt>
                <dd class="truncate text-sm font-semibold text-slate-800">{{ $delayStats['reopen_rate'] }} %</dd>
            </div>
        </dl>
    </section>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.tickets.index') }}" class="rounded-lg border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-end">
            <div class="min-w-0 lg:flex-1">
                <label for="ticket-search" class="mb-1 block text-xs font-medium text-slate-600">Recherche</label>
                <input
                    id="ticket-search"
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Référence ou sujet"
                    class="{{ $fieldClass }}"
                />
            </div>
            <div class="grid grid-cols-2 gap-2 sm:flex">
                <div class="min-w-[9.5rem]">
                    <label for="ticket-status" class="mb-1 block text-xs font-medium text-slate-600">Statut</label>
                    <select id="ticket-status" name="status" class="{{ $fieldClass }}">
                        <option value="">Tous les statuts</option>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[9.5rem]">
                    <label for="ticket-priority" class="mb-1 block text-xs font-medium text-slate-600">Priorité</label>
                    <select id="ticket-priority" name="priority" class="{{ $fieldClass }}">
                        <option value="">Toutes</option>
                        @foreach($priorityLabels as $value => $label)
                            <option value="{{ $value }}" @selected($priority === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex min-h-10 flex-1 items-center justify-center rounded-lg bg-amber-500 px-4 text-sm font-medium text-slate-950 hover:bg-amber-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500 sm:flex-none">Filtrer</button>
                <a href="{{ route('admin.tickets.index') }}" class="inline-flex min-h-10 flex-1 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500 sm:flex-none">Réinitialiser</a>
            </div>
            <p class="text-xs text-slate-500 lg:mb-2.5 lg:ml-auto lg:whitespace-nowrap">
                {{ $displayed }} {{ $displayed > 1 ? 'résultats' : 'résultat' }}
            </p>
        </div>
    </form>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-3 py-2">Référence</th>
                        <th scope="col" class="px-3 py-2">Client</th>
                        <th scope="col" class="px-3 py-2">Sujet</th>
                        <th scope="col" class="px-3 py-2">Priorité</th>
                        <th scope="col" class="px-3 py-2">Statut</th>
                        <th scope="col" class="px-3 py-2 text-center">Réponses</th>
                        <th scope="col" class="px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr class="border-t border-slate-100 hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-3 py-2">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="inline-flex rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-800 hover:bg-amber-50 hover:text-amber-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500">{{ $ticket->reference }}</a>
                            </td>
                            <td class="max-w-[10rem] truncate px-3 py-2 text-slate-700" title="{{ $ticket->user->name }}">{{ $ticket->user->name }}</td>
                            <td class="max-w-xs truncate px-3 py-2 text-slate-800" title="{{ $ticket->subject }}">{{ $ticket->subject }}</td>
                            <td class="whitespace-nowrap px-3 py-2"><x-support.priority-badge :priority="$ticket->priority" /></td>
                            <td class="whitespace-nowrap px-3 py-2"><x-support.status-badge :status="$ticket->status" /></td>
                            <td class="px-3 py-2 text-center tabular-nums text-slate-600">{{ $ticket->replies_count }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-right">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="inline-flex min-h-8 items-center rounded-md border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-10 text-center text-sm text-slate-500">Aucun ticket ne correspond à ces critères.</td>
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
