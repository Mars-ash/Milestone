@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'error' => null,
    'helper' => null,
    'placeholder' => null,
])

@php
    $selectId = $id ?? $name ?? 'select-' . uniqid();
    $hasError = !empty($error);
@endphp

<div class="field">
    @if($label)
        <label for="{{ $selectId }}" class="field-label">
            {{ $label }}@if($required)<span class="required">*</span>@endif
        </label>
    @endif

    <select
        id="{{ $selectId }}"
        name="{{ $name }}"
        @if($required) required aria-required="true" @endif
        @if($hasError) aria-invalid="true" aria-describedby="error-{{ $selectId }}" @endif
        {{ $attributes->merge(['class' => $hasError ? 'input-error' : '']) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    @if($hasError)
        <div class="field-error" id="error-{{ $selectId }}">{{ $error }}</div>
    @elseif($helper)
        <small>{{ $helper }}</small>
    @endif
</div>
