@props(['count' => 0])

@if ($count > 0)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full']) }}>
        {{ $count > 99 ? '99+' : $count }}
    </span>
@endif
