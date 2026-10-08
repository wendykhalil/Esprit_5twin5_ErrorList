<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminTicketReplyRequest;
use App\Http\Requests\Admin\UpdateSupportTicketRequest;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('backend.tickets.index', [
            'tickets' => $tickets,
            'search' => $search,
            'status' => $status,
            'priority' => $priority,
        ]);
    }

    public function show(SupportTicket $supportTicket): View
    {
        $this->authorize('view', $supportTicket);

        $supportTicket->load([
            'user',
            'replies' => fn ($query) => $query->with('author')->orderBy('created_at'),
        ]);

        return view('backend.tickets.show', [
            'ticket' => $supportTicket,
        ]);
    }

    public function storeReply(StoreAdminTicketReplyRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('reply', $supportTicket);

        $supportTicket->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'is_internal' => (bool) $request->boolean('is_internal'),
        ]);

        $supportTicket->markInProgressIfOpen();

        return back()->with('success', 'Réponse enregistrée.');
    }

    public function update(UpdateSupportTicketRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('update', $supportTicket);

        $data = $request->validated();

        if (in_array($data['status'], ['closed', 'resolved'], true)) {
            $data['closed_at'] = $supportTicket->closed_at ?? now();
        } else {
            $data['closed_at'] = null;
        }

        $supportTicket->update($data);

        return back()->with('success', 'Ticket mis à jour.');
    }
}
