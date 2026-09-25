@props(['status'])

@php
    $statusStyles = [
        'En attente' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-400'],
        'En cours' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500'],
        'Terminée' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
        'Annulée' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'border' => 'border-red-200', 'dot' => 'bg-red-500'],
        'Disponible' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
        'Indisponible' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-500', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'],
        'Actif' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
        'Inactif' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-500', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'],
        'Client' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500'],
        'Propriétaire' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
        'Administrateur' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'border' => 'border-violet-200', 'dot' => 'bg-violet-500'],
    ];
    
    $style = $statusStyles[$status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'];
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-600 {{ $style['bg'] }} {{ $style['text'] }} border {{ $style['border'] }}" style="font-family: Outfit, sans-serif">
    <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
    {{ $status }}
</span>
