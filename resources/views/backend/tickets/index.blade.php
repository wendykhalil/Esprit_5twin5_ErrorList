@extends('layouts.backend')

@section('title', 'Support - SolarShare Admin')

@section('content')
<div class="space-y-5">
    <div>
        <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Support</h2>
        <p class="text-slate-500 text-sm mt-0.5">{{ $tickets->total() }} tickets</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-800 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="grid grid-cols-1 lg:grid-cols-4 gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Référence ou sujet…"
                   class="lg:col-span-2 w-full px-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400" />
            <select name="status" class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-lg">
                <option value="">Tous statuts</option>
                @foreach(['open','in_progress','resolved','closed'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <select name="priority" class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-lg">
                <option value="">Toutes priorités</option>
                @foreach(['low','normal','high'] as $p)
                    <option value="{{ $p }}" @selected($priority === $p)>{{ $p }}</option>
                @endforeach
            </select>
            <div class="lg:col-span-4 flex gap-2">
                <button type="submit" class="px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm">Filtrer</button>
                <a href="{{ route('admin.tickets.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-lg text-sm">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="text-left px-4 py-3">Réf.</th>
                    <th class="text-left px-4 py-3">Client</th>
                    <th class="text-left px-4 py-3">Sujet</th>
                    <th class="text-left px-4 py-3">Priorité</th>
                    <th class="text-left px-4 py-3">Statut</th>
                    <th class="text-left px-4 py-3">Réponses</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs">
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-amber-700 hover:underline">{{ $ticket->reference }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $ticket->user->name }}</td>
                        <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($ticket->subject, 50) }}</td>
                        <td class="px-4 py-3"><x-support.priority-badge :priority="$ticket->priority" /></td>
                        <td class="px-4 py-3"><x-support.status-badge :status="$ticket->status" /></td>
                        <td class="px-4 py-3 text-slate-500">{{ $ticket->replies_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Aucun ticket.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tickets->links() }}
</div>
@endsection
