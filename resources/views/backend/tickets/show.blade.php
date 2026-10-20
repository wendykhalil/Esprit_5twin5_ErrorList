@extends('layouts.backend')

@section('title', $ticket->reference.' - Support Admin')

@section('content')
<div class="space-y-6">
    <a href="{{ route('admin.tickets.index') }}" class="text-sm text-amber-700 hover:text-amber-800">← Retour à la liste</a>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-mono text-slate-500">{{ $ticket->reference }}</p>
                <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $ticket->subject }}</h2>
                <p class="text-sm text-slate-500 mt-1">Client : {{ $ticket->user->name }} ({{ $ticket->user->email }})</p>
            </div>
            <div class="flex items-center gap-2">
                <x-support.priority-badge :priority="$ticket->priority" />
                <x-support.status-badge :status="$ticket->status" />
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-800 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-800 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-4">
            <h3 class="text-sm font-semibold text-slate-700">Fil de discussion</h3>
            <x-support.thread :ticket="$ticket" :show-internal="true" />

            @if($ticket->acceptsReplies())
                <form method="POST" action="{{ route('admin.tickets.replies.store', $ticket) }}" class="bg-white border border-slate-200 rounded-xl p-5 space-y-3" novalidate>
                    @csrf
                    <label class="block text-sm font-medium text-slate-700">Répondre</label>
                    <textarea name="body" rows="4" class="w-full px-3 py-2 border rounded-lg text-sm @error('body') border-red-500 @else border-slate-200 @enderror">{{ old('body') }}</textarea>
                    @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_internal" value="1" class="rounded border-slate-300 text-amber-600" @checked(old('is_internal')) />
                        Note interne (invisible client)
                    </label>
                    <button type="submit" class="px-4 py-2 bg-amber-500 text-white text-sm rounded-lg hover:bg-amber-600">Envoyer</button>
                </form>
            @else
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm text-slate-600">
                    Ticket fermé : changez le statut (réouverture) pour autoriser une nouvelle réponse.
                </div>
            @endif
        </div>

        <div class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-slate-800 mb-1">Assistant IA</h3>
            <p class="text-xs text-slate-500 mb-4">Suggestion uniquement. La priorité, le statut et la conversation ne changent qu'après votre validation.</p>
            <form method="POST" action="{{ route('admin.tickets.analyze', $ticket) }}">
                @csrf
                <button type="submit" class="w-full px-4 py-2.5 bg-amber-500 text-white text-sm rounded-lg hover:bg-amber-600">Analyser le ticket</button>
            </form>
            @if($ticket->ai_analyzed_at)
                @php
                    $categoryLabels = [
                        'compte' => 'Compte',
                        'paiement' => 'Paiement',
                        'reservation' => 'Réservation',
                        'equipement' => 'Équipement',
                        'technique' => 'Technique',
                        'autre' => 'Autre',
                    ];
                @endphp
                <div class="mt-4 space-y-3 text-sm">
                    <p><span class="font-medium text-slate-700">Catégorie :</span> {{ $categoryLabels[$ticket->ai_category] ?? $ticket->ai_category }}</p>
                    <p><span class="font-medium text-slate-700">Priorité suggérée :</span> {{ $ticket->ai_suggested_priority }}</p>
                    <p class="text-slate-600 whitespace-pre-wrap">{{ $ticket->ai_summary }}</p>
                    <label for="ai-draft" class="block text-xs font-medium text-slate-600">Brouillon modifiable</label>
                    <textarea id="ai-draft" rows="6" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">{{ $ticket->ai_suggested_reply }}</textarea>
                    <div class="flex flex-col gap-2">
                        @if($ticket->acceptsReplies())
                            <button type="button" id="use-ai-draft" class="px-3 py-2 border border-slate-300 text-slate-700 text-sm rounded-lg hover:bg-slate-50">Copier dans la réponse</button>
                        @endif
                        <button type="button" id="use-ai-priority" data-priority="{{ $ticket->ai_suggested_priority }}" class="px-3 py-2 border border-slate-300 text-slate-700 text-sm rounded-lg hover:bg-slate-50">Préremplir la priorité</button>
                    </div>
                    <p class="text-xs text-slate-400">Analysé le {{ $ticket->ai_analyzed_at->format('d/m/Y H:i') }}. Rien n'est envoyé tant que vous ne validez pas.</p>
                </div>
            @endif
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 h-fit">
            <h3 class="text-sm font-semibold text-slate-800 mb-4">Gestion du ticket</h3>
            <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Statut</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                        @foreach(['open','in_progress','resolved','closed'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $ticket->status) === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Priorité</label>
                    <select name="priority" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                        @foreach(['low','normal','high'] as $p)
                            <option value="{{ $p }}" @selected(old('priority', $ticket->priority) === $p)>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full px-4 py-2.5 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800">Mettre à jour</button>
            </form>
        </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('use-ai-draft')?.addEventListener('click', function () {
        const draft = document.getElementById('ai-draft');
        const body = document.querySelector('textarea[name="body"]');
        if (!draft || !body) {
            return;
        }
        body.value = draft.value;
        body.focus();
    });
    document.getElementById('use-ai-priority')?.addEventListener('click', function () {
        const select = document.querySelector('select[name="priority"]');
        if (select && this.dataset.priority) {
            select.value = this.dataset.priority;
        }
    });
</script>
@endsection
