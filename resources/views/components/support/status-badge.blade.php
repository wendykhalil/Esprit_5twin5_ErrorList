@props(['status'])

@php
    $styles = [
        'open' => 'bg-blue-100 text-blue-800 border-blue-200',
        'in_progress' => 'bg-amber-100 text-amber-800 border-amber-200',
        'resolved' => 'bg-green-100 text-green-800 border-green-200',
        'closed' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];
    $labels = [
        'open' => 'Ouvert',
        'in_progress' => 'En cours',
        'resolved' => 'Résolu',
        'closed' => 'Fermé',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border '.($styles[$status] ?? $styles['open'])]) }}>
    {{ $labels[$status] ?? $status }}
</span>
