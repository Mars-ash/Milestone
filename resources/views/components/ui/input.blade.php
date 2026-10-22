@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'required' => false,
    'error' => null,
    'helper' => null,
    'placeholder' => '',
])

@php
    $inputId = $id ?? $name ?? 'input-' . uniqid();
    $hasError = !empty($error);
@endphp

<div class="field">
    @if($label)
        <label for="{{ $inputId }}" class="field-label">
            {{ $label }}@if($required)<span class="required">*</span>@endif
        </label>
    @endif

    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        placeholder="{{ $placeholder }}"
        @if($required) required aria-required="true" @endif
        @if($hasError) aria-invalid="true" aria-describedby="error-{{ $inputId }}" @endif
        {{ $attributes->merge(['class' => 'input' . ($hasError ? ' input-error' : '')]) }}
    />

    @if($hasError)
        <div class="field-error" id="error-{{ $inputId }}">{{ $error }}</div>
    @elseif($helper)
        <small>{{ $helper }}</small>
    @endif
</div>
