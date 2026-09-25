@props(['label', 'value', 'icon', 'trend' => null, 'accent' => 'solar'])

@php
    $accentClasses = [
        'solar' => ['icon' => 'bg-amber-500', 'bg' => 'bg-amber-50'],
        'eco' => ['icon' => 'bg-green-600', 'bg' => 'bg-green-50'],
        'blue' => ['icon' => 'bg-blue-500', 'bg' => 'bg-blue-50'],
        'purple' => ['icon' => 'bg-violet-500', 'bg' => 'bg-violet-50'],
    ];
    
    $colors = $accentClasses[$accent] ?? $accentClasses['solar'];
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start justify-between gap-3">
        <div class="{{ $colors['icon'] }} w-11 h-11 rounded-xl flex items-center justify-center text-white flex-shrink-0">
            {!! $icon !!}
        </div>
        @if($trend)
            <span class="text-xs font-600 px-2 py-1 rounded-full {{ $trend['positive'] ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}" style="font-family: Outfit, sans-serif">
                {{ $trend['positive'] ? '↑' : '↓' }} {{ $trend['value'] }}
            </span>
        @endif
    </div>
    <div class="mt-4">
        <p class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">{{ $value }}</p>
        <p class="text-sm text-slate-500 mt-0.5" style="font-family: Outfit, sans-serif">{{ $label }}</p>
    </div>
</div>
