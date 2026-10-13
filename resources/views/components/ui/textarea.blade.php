@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'rows' => 3,
    'required' => false,
    'error' => null,
    'helper' => null,
])

@php
    $textareaId = $id ?? $name ?? 'textarea-' . uniqid();
    $hasError = !empty($error);
@endphp

<div class="w-full space-y-1">
    @if($label)
        <label for="{{ $textareaId }}" class="block text-xs font-semibold text-[#18212B] tracking-wide">
            {{ $label }}
            @if($required)
                <span class="text-[#C0392B] ml-0.5" title="Wajib diisi">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <textarea
            id="{{ $textareaId }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'w-full rounded-md px-3 py-2 text-sm bg-white border transition-colors ' .
                ($hasError
                    ? 'border-[#C0392B] text-[#C0392B] focus:border-[#C0392B] focus:ring-1 focus:ring-[#C0392B]'
                    : 'border-[#D8DEE6] text-[#18212B] focus:border-[#2459A6] focus:ring-1 focus:ring-[#2459A6]') .
                ' placeholder-[#8C9BAE] disabled:bg-[#F5F6F8] disabled:text-[#8C9BAE]'
            ]) }}
        >{{ $slot }}</textarea>
    </div>

    @if($hasError)
        <p class="text-xs text-[#C0392B] font-medium mt-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            {{ $error }}
        </p>
    @elseif($helper)
        <p class="text-xs text-[#536170] mt-1">{{ $helper }}</p>
    @endif
</div>
