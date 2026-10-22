@props([
    'title' => 'Tidak ada data',
    'description' => null,
    'actionText' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'w-full py-12 px-4 text-center rounded-lg border border-dashed border-[#D8DEE6] bg-[#FAFAFC] my-4']) }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#EBF2FA] text-[#2459A6] mb-3">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
    </div>

    <h3 class="text-sm font-semibold text-[#18212B] tracking-tight">{{ $title }}</h3>

    @if($description)
        <p class="mt-1 text-xs text-[#536170] max-w-sm mx-auto">{{ $description }}</p>
    @endif

    @if($actionText && $actionUrl)
        <div class="mt-4">
            <a href="{{ $actionUrl }}" class="inline-flex items-center justify-center font-medium rounded-md text-xs px-3 py-2 bg-[#2459A6] text-white hover:bg-[#1D4685] transition-colors shadow-xs">
                {{ $actionText }}
            </a>
        </div>
    @elseif($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
