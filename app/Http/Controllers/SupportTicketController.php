<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportTicketRequest;
use App\Http\Requests\StoreTicketReplyRequest;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
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
        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
            'priority' => $request->validated('priority') ?? 'normal',
        ]);

        return redirect()
            ->route('support.tickets.show', $ticket)
            ->with('success', 'Votre ticket a été créé. Référence : '.$ticket->reference);
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

        $supportTicket->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'is_internal' => false,
        ]);

        return back()->with('success', 'Votre réponse a été envoyée.');
    }
}
