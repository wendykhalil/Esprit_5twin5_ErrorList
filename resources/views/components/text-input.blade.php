@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'disabled:opacity-50 disabled:cursor-not-allowed']) }}>
