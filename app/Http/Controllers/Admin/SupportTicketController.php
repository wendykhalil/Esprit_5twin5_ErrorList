<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminTicketReplyRequest;
use App\Http\Requests\Admin\UpdateSupportTicketRequest;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Services\SupportTicketAnalysisException;
use App\Services\SupportTicketAssistant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $priority = $request->string('priority')->trim()->toString();

        $tickets = SupportTicket::query()
            ->with('user')
            ->withCount('replies')
            ->byStatus($status !== '' ? $status : null)
            ->byPriority($priority !== '' ? $priority : null)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('reference', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $supportStats = SupportTicket::query()
            ->toBase()
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as open_count', ['open'])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as progress_count', ['in_progress'])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as resolved_count', ['resolved'])
            ->selectRaw('sum(case when priority = ? then 1 else 0 end) as high_count', ['high'])
            ->first();

        return view('backend.tickets.index', [
            'tickets' => $tickets,
            'search' => $search,
            'status' => $status,
            'priority' => $priority,
            'supportStats' => $supportStats,
            'delayStats' => $this->delaySnapshot(),
        ]);
    }

    /**
     * @return array{first_response: string, resolution: string, closure: string, reopen_rate: int}
     */
    private function delaySnapshot(): array
    {
        $rows = SupportTicket::query()->get([
            'created_at',
            'first_response_at',
            'resolved_at',
            'closed_at',
            'status',
            'reopen_count',
        ]);

        $total = $rows->count();
        $reopened = $rows->where('reopen_count', '>', 0)->count();

        return [
            'first_response' => $this->averageDelayLabel($rows, 'first_response_at'),
            'resolution' => $this->averageDelayLabel($rows, 'resolved_at'),
            'closure' => $this->averageDelayLabel($rows->where('status', 'closed'), 'closed_at'),
            'reopen_rate' => $total === 0 ? 0 : (int) round(($reopened / $total) * 100),
        ];
    }

    /**
     * @param  Collection<int, SupportTicket>  $rows
     */
    private function averageDelayLabel($rows, string $column): string
    {
        $samples = $rows->filter(fn (SupportTicket $ticket) => $ticket->{$column} !== null);

        if ($samples->isEmpty()) {
            return '—';
        }

        $minutes = (int) round($samples->avg(
            fn (SupportTicket $ticket) => abs($ticket->created_at->diffInMinutes($ticket->{$column}))
        ));
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        if ($hours === 0) {
            return $rest.' min';
        }

        return $hours.' h '.$rest.' min';
    }

    public function show(SupportTicket $supportTicket): View
    {
        $this->authorize('view', $supportTicket);

        $supportTicket->load([
            'user',
            'events.user',
            'replies' => fn ($query) => $query->with('author')->orderBy('created_at'),
        ]);

        return view('backend.tickets.show', [
            'ticket' => $supportTicket,
        ]);
    }

    public function storeReply(StoreAdminTicketReplyRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('reply', $supportTicket);

        $body = $request->validated('body');
        $internal = (bool) $request->boolean('is_internal');
        $duplicate = ! $internal && $supportTicket->hasDuplicatePublicBody($body);

        $supportTicket->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $body,
            'is_internal' => $internal,
            'attachment' => $request->hasFile('attachment')
                ? $request->file('attachment')->store('support-replies', 'public')
                : null,
        ]);

        if (! $internal && $supportTicket->first_response_at === null) {
            $supportTicket->first_response_at = now();
        }

        $supportTicket->markInProgressIfOpen();

        if ($supportTicket->isDirty('first_response_at')) {
            $supportTicket->save();
        }

        if (
            ! $internal
            && filled($supportTicket->ai_suggested_reply)
            && TicketReply::normalizeBody($body) === TicketReply::normalizeBody((string) $supportTicket->ai_suggested_reply)
        ) {
            $supportTicket->recordEvent('ai_draft_sent', $request->user()->id, [
                'matches_suggestion' => true,
                'analysis_current' => $supportTicket->aiAnalysisIsCurrent(),
            ]);
        }

        $redirect = back()->with('success', 'Réponse enregistrée.');

        if ($duplicate) {
            $redirect->with('warning', 'Ce texte est déjà présent dans la conversation.');
        }

        return $redirect;
    }

    public function update(UpdateSupportTicketRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('update', $supportTicket);

        $data = $request->validated();
        $previousStatus = $supportTicket->status;
        $previousPriority = $supportTicket->priority;

        $supportTicket->status = $data['status'];
        $supportTicket->priority = $data['priority'];
        $supportTicket->syncClosedTimestampForStatus($data['status']);

        if ($previousPriority !== $data['priority'] && $supportTicket->first_response_at === null) {
            $supportTicket->first_response_due_at = $supportTicket->created_at
                ->copy()
                ->addHours(SupportTicket::SLA_HOURS[$data['priority']] ?? SupportTicket::SLA_HOURS['normal']);
        }

        $supportTicket->save();

        if ($previousStatus !== $supportTicket->status) {
            $supportTicket->recordEvent('status_changed', $request->user()->id, [
                'from' => $previousStatus,
                'to' => $supportTicket->status,
            ]);
        }

        if ($previousPriority !== $supportTicket->priority) {
            $supportTicket->recordEvent('priority_changed', $request->user()->id, [
                'from' => $previousPriority,
                'to' => $supportTicket->priority,
                'source' => 'admin',
                'matches_ai_suggestion' => $supportTicket->aiAnalysisIsCurrent()
                    && $supportTicket->ai_suggested_priority === $supportTicket->priority,
            ]);
        }

        return back()->with('success', 'Ticket mis à jour.');
    }

    public function analyze(SupportTicket $supportTicket, SupportTicketAssistant $assistant): RedirectResponse
    {
        $this->authorize('analyze', $supportTicket);

        $seenAnalysis = $supportTicket->ai_analyzed_at?->format('Y-m-d H:i:s');
        $seenGeneration = (int) $supportTicket->ai_analysis_generation;

        try {
            $suggestion = $assistant->analyze($supportTicket);
        } catch (SupportTicketAnalysisException $exception) {
            $supportTicket->recordEvent('ai_analysis_failed', auth()->id(), [
                'reason' => $exception->reason,
            ]);

            return back()->with('error', $exception->getMessage());
        }

        $outcome = DB::transaction(function () use ($supportTicket, $suggestion, $seenAnalysis, $seenGeneration) {
            $locked = SupportTicket::query()->whereKey($supportTicket->id)->lockForUpdate()->firstOrFail();
            $locked->refresh();

            $currentAnalysis = $locked->ai_analyzed_at?->format('Y-m-d H:i:s');

            if ((int) $locked->ai_analysis_generation !== $seenGeneration || $currentAnalysis !== $seenAnalysis) {
                $locked->recordEvent('ai_analysis_discarded', auth()->id(), [
                    'reason' => 'newer_result',
                ]);

                return 'superseded';
            }

            $stale = ! hash_equals($suggestion['context_hash'], $locked->publicContextFingerprint());

            $locked->update([
                'ai_category' => $suggestion['category'],
                'ai_suggested_priority' => $suggestion['priority'],
                'ai_priority_reason' => $suggestion['priority_reason'],
                'ai_summary' => $suggestion['summary'],
                'ai_suggested_reply' => $suggestion['suggested_reply'],
                'ai_missing_information' => $suggestion['missing_information'],
                'ai_confidence' => $suggestion['confidence'],
                'ai_context_hash' => $suggestion['context_hash'],
                'ai_analysis_generation' => $seenGeneration + 1,
                'ai_analyzed_at' => now(),
            ]);

            $locked->recordEvent('ai_analysis_completed', auth()->id(), [
                'category' => $suggestion['category'],
                'suggested_priority' => $suggestion['priority'],
                'stale' => $stale,
                'confidence' => $suggestion['confidence'],
                'missing_count' => count($suggestion['missing_information']),
            ]);

            return $stale ? 'stale' : 'current';
        });

        if ($outcome === 'superseded') {
            return back()->with('warning', 'Une analyse plus récente est déjà enregistrée. Celle-ci n\'a pas remplacé le résultat affiché.');
        }

        if ($outcome === 'stale') {
            return back()->with('warning', 'Le ticket a évolué pendant l\'analyse. Ces suggestions sont obsolètes : relancez l\'analyse.');
        }

        return back()->with('success', 'Analyse enregistrée. Relisez le brouillon avant de l\'envoyer.');
    }
}
