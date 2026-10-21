<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReopenSupportTicketRequest;
use App\Http\Requests\StoreSupportTicketRequest;
use App\Http\Requests\StoreTicketReplyRequest;
use App\Http\Requests\StoreTicketSatisfactionRequest;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(): View
    {
        $tickets = SupportTicket::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('frontend.tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        $this->authorize('create', SupportTicket::class);

        return view('frontend.tickets.create');
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $data = [
            'user_id' => $request->user()->id,
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
            'priority' => $request->validated('priority') ?? 'normal',
        ];

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request
                ->file('attachment')
                ->store('support-tickets', 'public');
        }

        $similar = SupportTicket::findSimilarOpen(
            $request->user()->id,
            $data['subject'],
            $data['message']
        );

        $ticket = SupportTicket::create($data);
        $ticket->recordEvent('created', $request->user()->id);

        $redirect = redirect()
            ->route('support.tickets.show', $ticket)
            ->with('success', 'Votre ticket a été créé. Référence : '.$ticket->reference);

        if ($similar !== null) {
            $redirect->with('warning', 'Une demande ouverte similaire existe déjà : '.$similar->reference.'.');
        }

        return $redirect;
    }

    public function show(SupportTicket $supportTicket): View
    {
        $this->authorize('view', $supportTicket);

        $supportTicket->load([
            'user',
            'publicReplies' => fn ($query) => $query->with('author')->orderBy('created_at'),
        ]);

        return view('frontend.tickets.show', [
            'ticket' => $supportTicket,
        ]);
    }

    public function storeReply(StoreTicketReplyRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('reply', $supportTicket);

        $body = $request->validated('body');
        $duplicate = $supportTicket->hasDuplicatePublicBody($body);

        $reply = $supportTicket->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $body,
            'is_internal' => false,
            'attachment' => $request->hasFile('attachment')
                ? $request->file('attachment')->store('support-replies', 'public')
                : null,
        ]);

        $redirect = back()->with('success', 'Votre réponse a été envoyée.');

        if ($duplicate) {
            $redirect->with('warning', 'Ce texte est déjà présent dans la conversation.');
        }

        return $redirect;
    }

    public function acceptSolution(SupportTicket $supportTicket, TicketReply $reply): RedirectResponse
    {
        $this->authorize('acceptSolution', $supportTicket);

        DB::transaction(function () use ($supportTicket, $reply) {
            $ticket = SupportTicket::query()->whereKey($supportTicket->id)->lockForUpdate()->firstOrFail();
            $lockedReply = TicketReply::query()->whereKey($reply->id)->lockForUpdate()->firstOrFail();

            if (
                $ticket->user_id !== auth()->id()
                || $ticket->isClosed()
                || $lockedReply->is_internal
                || $lockedReply->support_ticket_id !== $ticket->id
            ) {
                abort(403);
            }

            $ticket->accepted_reply_id = $lockedReply->id;
            $ticket->status = 'resolved';
            $ticket->syncClosedTimestampForStatus('resolved');
            $ticket->save();
            $ticket->recordEvent('solution_accepted', auth()->id(), [
                'reply_id' => $lockedReply->id,
            ]);
        });

        return back()->with('success', 'Cette réponse est enregistrée comme solution. Le ticket est résolu.');
    }

    public function reopen(ReopenSupportTicketRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        DB::transaction(function () use ($request, $supportTicket) {
            $ticket = SupportTicket::query()->whereKey($supportTicket->id)->lockForUpdate()->firstOrFail();

            if ($ticket->user_id !== $request->user()->id || $ticket->status !== 'resolved') {
                abort(403);
            }

            $ticket->replies()->create([
                'user_id' => $request->user()->id,
                'body' => $request->validated('motif'),
                'is_internal' => false,
            ]);

            $ticket->accepted_reply_id = null;
            $ticket->status = 'open';
            $ticket->syncClosedTimestampForStatus('open');
            $ticket->reopen_count = (int) $ticket->reopen_count + 1;
            $ticket->last_reopened_at = now();
            $ticket->satisfaction_score = null;
            $ticket->satisfaction_comment = null;
            $ticket->satisfaction_rated_at = null;
            $ticket->save();
            $ticket->recordEvent('reopened', $request->user()->id, [
                'reopen_count' => $ticket->reopen_count,
            ]);
        });

        return back()->with('success', 'Le ticket a été rouvert.');
    }

    public function rate(StoreTicketSatisfactionRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $supportTicket->update([
            'satisfaction_score' => (int) $request->validated('score'),
            'satisfaction_comment' => $request->validated('comment'),
            'satisfaction_rated_at' => now(),
        ]);

        $supportTicket->recordEvent('satisfaction', $request->user()->id, [
            'score' => (int) $request->validated('score'),
        ]);

        return back()->with('success', 'Merci, votre avis a été enregistré.');
    }
}
