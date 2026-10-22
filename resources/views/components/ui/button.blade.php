@props([
    'variant' => 'primary', // primary, secondary, danger, outline, ghost, text, icon
    'size' => 'md',        // sm, md, lg
    'type' => 'button',
    'disabled' => false,
])

@php
    $classMap = [
        'primary' => 'btn primary',
        'secondary' => 'btn',
        'outline' => 'btn',
        'danger' => 'btn danger',
        'danger-outline' => 'btn danger',
        'ghost' => 'btn ghost',
        'text' => 'btn text',
        'icon' => 'icon-btn',
    ];

    $classes = $classMap[$variant] ?? 'btn';
    if ($size === 'sm') {
        $classes .= ' sm';
    }
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    @disabled($disabled)
>
    {{ $slot }}
</button>
