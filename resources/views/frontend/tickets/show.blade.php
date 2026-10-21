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
        @if(session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('warning') }}</div>
        @endif

        <div>
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Conversation</h2>
            <x-support.thread :ticket="$ticket" :can-accept="auth()->id() === $ticket->user_id && ! $ticket->isClosed()" />
        </div>

        @if($ticket->status === 'resolved' && $ticket->satisfaction_rated_at === null)
            <form method="POST" action="{{ route('support.tickets.satisfaction', $ticket) }}" class="bg-white border border-green-100 rounded-xl p-5 space-y-3" novalidate>
                @csrf
                <label for="satisfaction-score" class="block text-sm font-medium text-gray-700">Cette solution vous convient-elle ?</label>
                <select id="satisfaction-score" name="score" class="w-full rounded-lg border-gray-300">
                    <option value="">Choisir une note</option>
                    @for($score = 1; $score <= 5; $score++)
                        <option value="{{ $score }}" @selected((int) old('score') === $score)>{{ $score }} / 5</option>
                    @endfor
                </select>
                @error('score')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <textarea name="comment" rows="3" placeholder="Commentaire facultatif" class="w-full rounded-lg border-gray-300">{{ old('comment') }}</textarea>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">Envoyer l'avis</button>
            </form>
        @elseif($ticket->satisfaction_score)
            <p class="text-sm text-gray-600">Votre avis : {{ $ticket->satisfaction_score }} / 5.</p>
        @endif

        @if($ticket->status === 'resolved')
            <form method="POST" action="{{ route('support.tickets.reopen', $ticket) }}" class="bg-white border border-amber-200 rounded-xl p-5 space-y-3" novalidate>
                @csrf
                <label class="block text-sm font-medium text-gray-700">Le problème persiste</label>
                <p class="text-xs text-gray-500">Décrivez ce qui n'est pas résolu. Ce motif sera ajouté à la conversation et le ticket sera rouvert.</p>
                <textarea name="motif" rows="4" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('motif') border-red-500 @enderror">{{ old('motif') }}</textarea>
                @error('motif')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700">Rouvrir le ticket</button>
            </form>
        @endif

        @if($ticket->acceptsReplies() && $ticket->status !== 'resolved')
            <form method="POST" action="{{ route('support.tickets.replies.store', $ticket) }}" enctype="multipart/form-data" class="bg-white border border-green-100 rounded-xl p-5 space-y-3" novalidate>
                @csrf
                <label class="block text-sm font-medium text-gray-700">Votre réponse</label>
                <textarea name="body" rows="4" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('body') border-red-500 @enderror">{{ old('body') }}</textarea>
                @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf" class="block w-full text-sm text-gray-600" />
                @error('attachment')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">Répondre</button>
            </form>
        @endif
    </div>
</div>
@endsection
