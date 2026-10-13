@props([
    'variant' => 'primary', // primary, secondary, danger, outline, ghost
    'size' => 'md',        // sm, md, lg
    'type' => 'button',
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 disabled:cursor-not-allowed select-none';

    $sizes = [
        'sm' => 'text-xs px-2.5 py-1.5 h-8 gap-1.5',
        'md' => 'text-sm px-4 py-2 min-h-[40px] sm:min-h-[38px] gap-2',
        'lg' => 'text-base px-5 py-2.5 min-h-[44px] gap-2.5',
    ];

    $variants = [
        'primary' => 'bg-[#2459A6] text-white hover:bg-[#1D4685] active:bg-[#173768] focus-visible:outline-[#2459A6] shadow-xs',
        'secondary' => 'bg-white text-[#18212B] border border-[#D8DEE6] hover:bg-[#F5F6F8] hover:border-[#B0BCCB] focus-visible:outline-[#2459A6] shadow-xs',
        'danger' => 'bg-[#C0392B] text-white hover:bg-[#962D22] focus-visible:outline-[#C0392B] shadow-xs',
        'danger-outline' => 'bg-white text-[#C0392B] border border-[#C0392B] hover:bg-[#FDF2F1] focus-visible:outline-[#C0392B]',
        'outline' => 'bg-transparent text-[#2459A6] border border-[#2459A6] hover:bg-[#EBF2FA] focus-visible:outline-[#2459A6]',
        'ghost' => 'bg-transparent text-[#536170] hover:text-[#18212B] hover:bg-[#F0F2F5] focus-visible:outline-[#2459A6]',
    ];

    $classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    @disabled($disabled)
>
    {{ $slot }}
</button>
