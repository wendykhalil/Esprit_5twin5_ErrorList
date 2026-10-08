@props(['ticket'])

@if($ticket->hasAttachment())
    <div class="mt-3">
        <p class="text-xs font-medium text-slate-500 mb-2">Pièce jointe</p>
        <a href="{{ $ticket->attachmentUrl() }}" target="_blank" rel="noopener noreferrer" class="inline-block group">
            <img
                src="{{ $ticket->attachmentUrl() }}"
                alt="Pièce jointe du ticket {{ $ticket->reference }}"
                class="max-w-full sm:max-w-md rounded-lg border border-slate-200 shadow-sm group-hover:opacity-95 transition-opacity"
            />
        </a>
        <p class="text-xs text-slate-400 mt-1">Cliquez pour ouvrir l'image en grand.</p>
    </div>
@endif
