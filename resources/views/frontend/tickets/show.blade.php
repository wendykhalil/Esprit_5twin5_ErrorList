@extends('layouts.frontend')

@section('title', $ticket->reference.' - Support')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <a href="{{ route('support.tickets.index') }}" class="text-sm text-green-700 hover:text-green-800">← Mes tickets</a>

        <div class="bg-white border border-green-100 rounded-xl p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-mono text-gray-500">{{ $ticket->reference }}</p>
                    <h1 class="text-xl font-bold text-gray-900 mt-1">{{ $ticket->subject }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    <x-support.priority-badge :priority="$ticket->priority" />
                    <x-support.status-badge :status="$ticket->status" />
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        <div>
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Conversation</h2>
            <x-support.thread :ticket="$ticket" />
        </div>

        @if($ticket->acceptsReplies())
            <form method="POST" action="{{ route('support.tickets.replies.store', $ticket) }}" class="bg-white border border-green-100 rounded-xl p-5 space-y-3" novalidate>
                @csrf
                <label class="block text-sm font-medium text-gray-700">Votre réponse</label>
                <textarea name="body" rows="4" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('body') border-red-500 @enderror">{{ old('body') }}</textarea>
                @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">Répondre</button>
            </form>
        @endif
    </div>
</div>
@endsection
