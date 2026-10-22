@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'rows' => 2,
    'required' => false,
    'error' => null,
    'helper' => null,
    'placeholder' => '',
])

@php
    $textareaId = $id ?? $name ?? 'textarea-' . uniqid();
    $hasError = !empty($error);
@endphp

<div class="field">
    @if($label)
        <label for="{{ $textareaId }}" class="field-label">
            {{ $label }}@if($required)<span class="required">*</span>@endif
        </label>
    @endif

    <textarea
        id="{{ $textareaId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required aria-required="true" @endif
        @if($hasError) aria-invalid="true" aria-describedby="error-{{ $textareaId }}" @endif
        {{ $attributes->merge(['class' => $hasError ? 'input-error' : '']) }}
    >{{ $slot }}</textarea>

    @if($hasError)
        <div class="field-error" id="error-{{ $textareaId }}">{{ $error }}</div>
    @elseif($helper)
        <small>{{ $helper }}</small>
    @endif
</div>
