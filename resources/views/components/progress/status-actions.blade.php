@props([
    'status' => 'Open',
    'progressId',
])

@php
    $isClosed = strtolower($status) === 'closed';
@endphp

<div class="flex flex-wrap items-center gap-2 select-none">
    @if(!$isClosed)
        <!-- Mode Open: Tombol Edit & Tombol Close -->
        <a
            href="?tab=form&id={{ $progressId }}"
            class="px-4 py-2 text-xs font-semibold rounded-md border border-[#D8DEE6] bg-white text-[#2459A6] hover:bg-[#F0F4FA] transition-colors min-h-[44px] flex items-center gap-1.5 shadow-2xs"
            data-testid="edit-progress-btn"
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
            Edit Progress
        </a>

        <button
            type="button"
            wire:click="closeProgress"
            wire:confirm="Tutup catatan progress ini? Progress yang Closed akan berstatus read-only sampai di-Reopen kembali."
            class="px-4 py-2 text-xs font-semibold rounded-md border border-[#F8D0CD] text-[#C5221F] bg-[#FCE8E6] hover:bg-[#F8D0CD] transition-colors min-h-[44px] flex items-center gap-1.5 shadow-2xs"
            data-testid="close-progress-btn"
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Tutup Progress (Close)
        </button>
    @else
        <!-- Mode Closed: Tombol Reopen Aktif & Peringatan Edit Diblokir (D10, AT21) -->
        <button
            type="button"
            wire:click="reopenProgress"
            class="px-4 py-2 text-xs font-semibold rounded-md bg-[#0E6B38] text-white hover:bg-[#0A532B] transition-colors min-h-[44px] flex items-center gap-1.5 shadow-2xs"
            data-testid="reopen-progress-btn"
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Buka Kembali (Reopen Progress)
        </button>

        <span
            class="px-3 py-2 text-xs font-medium rounded-md bg-[#F0F2F5] text-[#8C9BAE] border border-[#D8DEE6] cursor-not-allowed flex items-center gap-1.5 min-h-[44px]"
            title="Progress ini berstatus Closed. Klik 'Reopen Progress' untuk mengaktifkan kembali mode edit."
            data-testid="disabled-edit-btn"
        >
            <svg class="w-3.5 h-3.5 text-[#8C9BAE]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Edit Dinonaktifkan (Closed)
        </span>
    @endif
</div>
