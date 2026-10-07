@extends('layouts.frontend')

@section('title', 'Support - SolarShare')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-green-900" style="font-family: Fraunces, Georgia, serif">Support</h1>
                <p class="text-sm text-gray-600 mt-1">Vos demandes d'assistance ({{ $tickets->total() }})</p>
            </div>
            <a href="{{ route('support.tickets.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                Nouveau ticket
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        <div class="bg-white border border-green-100 rounded-xl overflow-hidden shadow-sm">
            @forelse($tickets as $ticket)
                <a href="{{ route('support.tickets.show', $ticket) }}" class="block px-5 py-4 border-b border-green-50 hover:bg-green-50/50 transition-colors">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-mono text-gray-500">{{ $ticket->reference }}</p>
                            <p class="font-semibold text-gray-900 mt-1">{{ $ticket->subject }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-support.priority-badge :priority="$ticket->priority" />
                            <x-support.status-badge :status="$ticket->status" />
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-gray-500 text-sm">Aucun ticket pour le moment.</div>
            @endforelse
        </div>

        {{ $tickets->links() }}
    </div>
</div>
@endsection
