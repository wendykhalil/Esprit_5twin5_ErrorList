@extends('layouts.backend')

@section('title', $ticket->reference.' - Support Admin')

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
    $eventLabels = [
        'created' => 'Ticket créé',
        'status_changed' => 'Statut modifié',
        'priority_changed' => 'Priorité modifiée',
        'solution_accepted' => 'Solution acceptée',
        'reopened' => 'Ticket rouvert',
        'sla_escalated' => 'Échéance dépassée',
        'reminded' => 'Relance interne',
        'satisfaction' => 'Avis client',
        'ai_analysis_completed' => 'Analyse IA enregistrée',
        'ai_analysis_failed' => 'Analyse IA échouée',
        'ai_analysis_discarded' => 'Analyse IA écartée',
        'ai_draft_sent' => 'Réponse publique envoyée',
    ];
    $categoryLabels = [
        'compte' => 'Compte',
        'paiement' => 'Paiement',
        'reservation' => 'Réservation',
        'equipement' => 'Équipement',
        'technique' => 'Technique',
        'autre' => 'Autre',
    ];
@endphp

<div class="space-y-5">
    <a href="{{ route('admin.tickets.index') }}" class="inline-flex text-sm font-medium text-amber-700 hover:text-amber-800">← Retour à la liste</a>

    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="font-mono text-xs font-semibold text-slate-500">{{ $ticket->reference }}</p>
                <h2 class="mt-1 text-xl font-semibold text-slate-900" style="font-family: Outfit, sans-serif">{{ $ticket->subject }}</h2>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $ticket->user->name }} · {{ $ticket->user->email }}
                    <span class="text-slate-300">·</span>
                    Ouvert le {{ $ticket->created_at->format('d/m/Y H:i') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Première réponse attendue avant {{ $ticket->first_response_due_at?->format('d/m/Y H:i') ?? '—' }}
                    @if($ticket->first_response_at)
                        · reçue le {{ $ticket->first_response_at->format('d/m/Y H:i') }}
                    @elseif($ticket->sla_breached_at)
                        · échéance dépassée
                    @endif
                    @if($ticket->resolved_at)
                        · résolu le {{ $ticket->resolved_at->format('d/m/Y H:i') }}
                    @endif
                    @if($ticket->status === 'closed' && $ticket->closed_at)
                        · fermé le {{ $ticket->closed_at->format('d/m/Y H:i') }}
                    @endif
                </p>
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

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            <h3 class="text-sm font-semibold text-slate-800">Conversation</h3>
            <x-support.thread :ticket="$ticket" :show-internal="true" />

            @if($ticket->acceptsReplies())
                <form method="POST" action="{{ route('admin.tickets.replies.store', $ticket) }}" enctype="multipart/form-data" class="space-y-3 rounded-xl border border-slate-200 bg-white p-5" novalidate>
                    @csrf
                    <div>
                        <label for="reply-body" class="block text-sm font-medium text-slate-800">Répondre</label>
                        <p class="mt-0.5 text-xs text-slate-500">Une note interne reste invisible pour le client.</p>
                    </div>
                    <textarea id="reply-body" name="body" rows="5" class="w-full rounded-lg border px-3 py-2 text-sm text-slate-800 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30 @error('body') border-red-500 @else border-slate-200 @enderror">{{ old('body') }}</textarea>
                    @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf" class="block w-full text-sm text-slate-600" />
                    @error('attachment')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_internal" value="1" class="rounded border-slate-300 text-amber-600" @checked(old('is_internal')) />
                            Note interne
                        </label>
                        <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600">Envoyer</button>
                    </div>
                </form>
            @else
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Ticket fermé : changez le statut pour autoriser une nouvelle réponse.
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h3 class="text-sm font-semibold text-slate-800">Assistant IA</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500">Suggestion uniquement. La priorité, le statut et la conversation ne changent qu'après votre validation.</p>
                @php $analysisCurrent = $ticket->ai_analyzed_at && $ticket->aiAnalysisIsCurrent(); @endphp
                <form method="POST" action="{{ route('admin.tickets.analyze', $ticket) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-amber-600">
                        {{ $ticket->ai_analyzed_at && ! $analysisCurrent ? 'Relancer l\'analyse' : 'Analyser le ticket' }}
                    </button>
                </form>
                @if($ticket->ai_analyzed_at)
                    <div class="mt-4 space-y-3 text-sm">
                        @if(! $analysisCurrent)
                            <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-amber-900">Cette analyse ne correspond plus à la conversation publique. Relancez-la avant de l'utiliser.</p>
                        @endif
                        <p><span class="font-medium text-slate-700">Catégorie :</span> {{ $categoryLabels[$ticket->ai_category] ?? $ticket->ai_category }}</p>
                        <p><span class="font-medium text-slate-700">Priorité suggérée :</span> {{ $priorityLabels[$ticket->ai_suggested_priority] ?? $ticket->ai_suggested_priority }}</p>
                        @if($ticket->ai_priority_reason)
                            <p><span class="font-medium text-slate-700">Justification :</span> {{ $ticket->ai_priority_reason }}</p>
                        @endif
                        <p class="leading-6 text-slate-600 whitespace-pre-wrap">{{ $ticket->ai_summary }}</p>
                        <div>
                            <p class="font-medium text-slate-700">Informations manquantes</p>
                            @if($ticket->ai_missing_information === null)
                                <p class="text-slate-500">Non évaluées pour cette analyse.</p>
                            @elseif($ticket->ai_missing_information === [])
                                <p class="text-slate-500">Aucune information manquante signalée.</p>
                            @else
                                <ul class="mt-1 list-disc space-y-1 pl-4 text-slate-600">
                                    @foreach($ticket->ai_missing_information as $question)
                                        <li>{{ $question }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <p class="text-slate-500">
                            @if($ticket->ai_confidence === null)
                                Confiance non évaluée
                            @else
                                Confiance estimée par le modèle : {{ (int) round($ticket->ai_confidence * 100) }} %
                            @endif
                        </p>
                        <label for="ai-draft" class="block text-xs font-medium text-slate-600">Brouillon modifiable</label>
                        <textarea id="ai-draft" rows="6" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30">{{ $ticket->ai_suggested_reply }}</textarea>
                        <div class="flex flex-col gap-2">
                            @if($ticket->acceptsReplies())
                                <button type="button" id="use-ai-draft" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Copier dans la réponse</button>
                            @endif
                            <button type="button" id="use-ai-priority" data-priority="{{ $ticket->ai_suggested_priority }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Préremplir la priorité</button>
                            <p class="text-xs text-slate-400">Le préremplissage ne modifie pas le ticket. Confirmez ensuite avec Mettre à jour.</p>
                        </div>
                        <p class="text-xs text-slate-400">Analysé le {{ $ticket->ai_analyzed_at->format('d/m/Y H:i') }}. Rien n'est envoyé tant que vous ne validez pas.</p>
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-800">Gestion du ticket</h3>
                <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="ticket-status" class="mb-1 block text-xs font-medium text-slate-600">Statut</label>
                        <select id="ticket-status" name="status" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-10 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30">
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $ticket->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="ticket-priority" class="mb-1 block text-xs font-medium text-slate-600">Priorité</label>
                        <select id="ticket-priority" name="priority" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-10 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30">
                            @foreach($priorityLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', $ticket->priority) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">Mettre à jour</button>
                </form>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h3 class="text-sm font-semibold text-slate-800">Journal</h3>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    @forelse($ticket->events as $event)
                        @php
                            $eventLabel = $eventLabels[$event->type] ?? $event->type;
                            $eventDetail = null;

                            if ($event->type === 'ai_analysis_completed' && ($event->payload['stale'] ?? false)) {
                                $eventLabel = 'Analyse IA obsolète';
                                $eventDetail = 'Suggestion conservée, à ne pas utiliser telle quelle';
                            } elseif ($event->type === 'ai_analysis_completed') {
                                $eventDetail = 'Suggestion uniquement';
                            } elseif ($event->type === 'ai_analysis_failed') {
                                $eventDetail = 'Le ticket n\'a pas été modifié';
                            } elseif ($event->type === 'ai_analysis_discarded') {
                                $eventDetail = 'Un résultat plus récent a été conservé';
                            } elseif ($event->type === 'ai_draft_sent') {
                                $eventDetail = 'Texte réellement envoyé, identique au brouillon';
                            } elseif ($event->type === 'priority_changed') {
                                $eventDetail = ($event->payload['matches_ai_suggestion'] ?? false)
                                    ? 'Décision de l\'administrateur, identique à la suggestion'
                                    : 'Décision de l\'administrateur';
                            }
                        @endphp
                        <li>
                            <span class="font-medium text-slate-800">{{ $eventLabel }}</span>
                            @if($eventDetail)
                                <span class="text-slate-500">· {{ $eventDetail }}</span>
                            @endif
                            <span class="text-xs text-slate-400">· {{ $event->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                    @empty
                        <li class="text-slate-400">Aucun événement enregistré.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('use-ai-draft')?.addEventListener('click', function () {
        const draft = document.getElementById('ai-draft');
        const body = document.getElementById('reply-body');
        if (!draft || !body) {
            return;
        }
        body.value = draft.value;
        body.focus();
    });
    document.getElementById('use-ai-priority')?.addEventListener('click', function () {
        const select = document.getElementById('ticket-priority');
        if (select && this.dataset.priority) {
            select.value = this.dataset.priority;
        }
    });
</script>
@endsection
