@props(['ticket', 'showInternal' => false])

@php
    $replies = $showInternal
        ? $ticket->replies
        : ($ticket->relationLoaded('publicReplies') ? $ticket->publicReplies : $ticket->publicReplies()->with('author')->orderBy('created_at')->get());
@endphp

<div class="space-y-4">
    <article class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ $ticket->user->name }}</p>
                <p class="text-xs text-slate-500">Message initial · {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-1 rounded">Client</span>
        </div>
        <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $ticket->message }}</p>
    </article>

    @foreach($replies as $reply)
        <article @class([
            'rounded-xl border p-4',
            'border-amber-200 bg-amber-50/60' => $showInternal && $reply->is_internal,
            'border-slate-200 bg-white' => ! ($showInternal && $reply->is_internal),
        ])>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $reply->author->name }}</p>
                    <p class="text-xs text-slate-500">{{ $reply->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($showInternal && $reply->is_internal)
                        <span class="text-xs font-medium text-amber-800 bg-amber-100 px-2 py-1 rounded">Note interne</span>
                    @elseif($reply->author->isAdmin())
                        <span class="text-xs font-medium text-slate-700 bg-slate-100 px-2 py-1 rounded">Support</span>
                    @else
                        <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-1 rounded">Client</span>
                    @endif
                </div>
            </div>
            <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $reply->body }}</p>
        </article>
    @endforeach
</div>
