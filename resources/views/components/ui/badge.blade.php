@props([
    'type' => 'open', // open, closed, draft, or activity name
    'label' => null,
])

@php
    $typeLower = strtolower(trim((string) ($type ?? '')));
    $labelText = $label ?? $slot;
@endphp

@if(in_array($typeLower, ['open', 'closed', 'draft'], true))
    <span {{ $attributes->merge(['class' => 'status ' . ($typeLower === 'closed' ? 'closed' : ($typeLower === 'draft' ? 'draft' : ''))]) }}>
        {{ $labelText }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'activity-label']) }}>
        {{ $labelText }}
    </span>
@endif
