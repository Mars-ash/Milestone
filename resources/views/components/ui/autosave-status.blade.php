@props([
    'status' => 'idle', // idle, saving, saved, unsaved, error
    'time' => null,
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-xs select-none']) }}>
    @if($status === 'saving')
        <svg class="animate-spin h-3.5 w-3.5 text-[#2459A6]" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span class="text-[#2459A6] font-medium">Menyimpan draft...</span>
    @elseif($status === 'saved')
        <svg class="h-3.5 w-3.5 text-[#0E6B38]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
        <span class="text-[#0E6B38]">Draft tersimpan{{ $time ? ' pukul ' . $time : '' }}</span>
    @elseif($status === 'unsaved')
        <span class="h-2 w-2 rounded-full bg-[#8A5B00]"></span>
        <span class="text-[#8A5B00]">Draft belum tersimpan</span>
    @elseif($status === 'error')
        <svg class="h-3.5 w-3.5 text-[#C0392B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-[#C0392B] font-medium">Gagal menyimpan draft</span>
    @endif
</div>
