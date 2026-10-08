@props(['priority'])

@php
    $styles = [
        'low' => 'bg-slate-100 text-slate-700 border-slate-200',
        'normal' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'high' => 'bg-red-100 text-red-800 border-red-200',
    ];
    $labels = [
        'low' => 'Basse',
        'normal' => 'Normale',
        'high' => 'Haute',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border '.($styles[$priority] ?? $styles['normal'])]) }}>
    {{ $labels[$priority] ?? $priority }}
</span>
