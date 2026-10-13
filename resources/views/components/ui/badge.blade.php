@props([
    'type' => 'neutral', // open, closed, draft, activity, info, danger
    'label' => null,
])

@php
    $typeKey = strtolower(str_replace(' ', '-', $type ?? 'neutral'));

    $styles = [
        // Status Progress (D11, UI04)
        'open' => 'bg-[#EAF7EE] text-[#0E6B38] border border-[#B6E6C3]',
        'closed' => 'bg-[#F0F2F5] text-[#536170] border border-[#D8DEE6]',
        'draft' => 'bg-[#FEF7E6] text-[#8A5B00] border border-[#F8DDA4]',

        // Activities (D03)
        'trial' => 'bg-[#EBF2FA] text-[#2459A6] border border-[#BBD5F3]',
        'sent-sample' => 'bg-[#F3EBF9] text-[#692994] border border-[#DFC3EE]',
        'send-document' => 'bg-[#EBF7F9] text-[#0D6B7A] border border-[#B6E4EC]',
        'audit' => 'bg-[#FEF1E8] text-[#9E3E0C] border border-[#F9CFBA]',
        'others' => 'bg-[#F0F2F5] text-[#394553] border border-[#D8DEE6]',

        // General
        'simulation' => 'bg-[#FFF8E1] text-[#795548] border border-[#FFE082]',
        'danger' => 'bg-[#FDF2F1] text-[#C0392B] border border-[#F5B7B1]',
        'neutral' => 'bg-[#F5F6F8] text-[#536170] border border-[#D8DEE6]',
    ];

    $class = $styles[$typeKey] ?? $styles['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium tracking-tight select-none ' . $class]) }}>
    {{ $label ?? $slot }}
</span>
