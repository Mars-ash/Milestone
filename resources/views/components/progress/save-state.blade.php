@props([
    'status' => 'unsaved', // saving, saved, unsaved, error
    'time' => null,
])

<div class="inline-flex items-center gap-2 text-xs select-none">
    @if($status === 'saving')
        <span class="inline-flex items-center gap-1.5 text-[#536170] bg-[#F5F6F8] px-2.5 py-1 rounded border border-[#D8DEE6] animate-pulse">
            <svg class="animate-spin w-3.5 h-3.5 text-[#2459A6]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="font-medium text-[11px]">Menyimpan draft...</span>
        </span>
    @elseif($status === 'saved')
        <span class="inline-flex items-center gap-1.5 text-[#0E6B38] bg-[#EAF7EE] px-2.5 py-1 rounded border border-[#B6E6C3]">
            <svg class="w-3.5 h-3.5 text-[#0E6B38]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium text-[11px]">Draft tersimpan {{ $time ? $time : now()->format('H:i') }}</span>
        </span>
    @elseif($status === 'error')
        <div class="inline-flex items-center gap-1.5 text-[#C5221F] bg-[#FCE8E6] px-2.5 py-1 rounded border border-[#F8D0CD]">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="font-medium text-[11px]">Gagal menyimpan draft</span>
            <button
                type="button"
                wire:click="saveDraft"
                class="ml-1 text-[11px] font-bold underline hover:no-underline text-[#C5221F]"
                title="Coba simpan draft ulang"
            >
                Coba lagi
            </button>
        </div>
    @else
        <span class="inline-flex items-center gap-1.5 text-[#536170] bg-[#F5F6F8] px-2.5 py-1 rounded border border-[#D8DEE6]">
            <span class="w-2 h-2 rounded-full bg-[#8A5B00]"></span>
            <span class="font-medium text-[11px]">Perubahan belum disimpan</span>
        </span>
    @endif
</div>
