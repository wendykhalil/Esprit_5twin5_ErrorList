@props(['ticket', 'showInternal' => false, 'canAccept' => false])

@php
    $replies = $showInternal
        ? $ticket->replies
        : ($ticket->relationLoaded('publicReplies') ? $ticket->publicReplies : $ticket->publicReplies()->with('author')->orderBy('created_at')->get());
@endphp

<div class="space-y-3">
    <article class="flex overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="w-1 shrink-0 bg-emerald-500"></div>
        <div class="min-w-0 flex-1 p-4">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $ticket->user->name }}</p>
                    <p class="text-xs text-slate-500">Message initial · {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Client</span>
            </div>
            <p class="text-sm leading-6 text-slate-700 whitespace-pre-wrap">{{ $ticket->message }}</p>
            <x-support.ticket-attachment :ticket="$ticket" />
        </div>
    </article>

    @foreach($replies as $reply)
        @php
            $isInternal = $showInternal && $reply->is_internal;
            $isSupport = ! $isInternal && $reply->author->isAdmin();
        @endphp
        <article @class([
            'flex overflow-hidden rounded-xl border',
            'border-amber-200 bg-amber-50/70' => $isInternal,
            'border-slate-200 bg-slate-50' => $isSupport,
            'border-slate-200 bg-white' => ! $isInternal && ! $isSupport,
        ])>
            <div @class([
                'w-1 shrink-0',
                'bg-amber-500' => $isInternal,
                'bg-slate-800' => $isSupport,
                'bg-emerald-500' => ! $isInternal && ! $isSupport,
            ])></div>
            <div class="min-w-0 flex-1 p-4">
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $reply->author->name }}</p>
                        <p class="text-xs text-slate-500">{{ $reply->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if((int) $ticket->accepted_reply_id === (int) $reply->id)
                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-800">Solution</span>
                        @endif
                        @if($isInternal)
                            <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">Note interne</span>
                        @elseif($isSupport)
                            <span class="rounded-full bg-slate-200 px-2 py-1 text-xs font-medium text-slate-800">Support</span>
                        @else
                            <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Client</span>
                        @endif
                    </div>
                </div>
                <p class="text-sm leading-6 text-slate-700 whitespace-pre-wrap">{{ $reply->body }}</p>
                @if($reply->hasAttachment())
                    <div class="mt-3">
                        @if($reply->attachmentIsPdf())
                            <a href="{{ $reply->attachmentUrl() }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-slate-700 underline">Document PDF</a>
                        @else
                            <a href="{{ $reply->attachmentUrl() }}" target="_blank" rel="noopener noreferrer" class="inline-block">
                                <img src="{{ $reply->attachmentUrl() }}" alt="Pièce jointe" class="max-w-full sm:max-w-md rounded-lg border border-slate-200" />
                            </a>
                        @endif
                    </div>
                @endif
                @if($canAccept && ! $reply->is_internal && (int) $ticket->accepted_reply_id !== (int) $reply->id)
                    <form method="POST" action="{{ route('support.tickets.replies.accept', [$ticket, $reply]) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">Accepter comme solution</button>
                    </form>
                @endif
            </div>
        </article>
    @endforeach
</div>
