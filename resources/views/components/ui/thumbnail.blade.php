@props([
    'src',
    'caption' => null,
    'filename' => null,
    'isSimulation' => true,
])

@php
    $modalId = 'preview-' . md5($src . ($filename ?? ''));
@endphp

<div x-data="{ open: false }" class="group relative inline-block rounded-md border border-[#D8DEE6] bg-white p-1.5 shadow-2xs hover:border-[#2459A6] transition-colors">
    <div class="relative w-24 h-24 sm:w-28 sm:h-28 overflow-hidden rounded bg-[#F5F6F8] flex items-center justify-center cursor-pointer" @click="open = true">
        <div class="text-[#8C9BAE] flex flex-col items-center justify-center p-2 text-center">
            <svg class="w-6 h-6 mb-1 text-[#536170]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-[10px] leading-tight text-[#536170] truncate max-w-[80px]">
                {{ $filename ?? 'Foto' }}
            </span>
        </div>

        @if($isSimulation)
            <span class="absolute top-1 left-1 bg-[#FFF8E1] text-[#795548] border border-[#FFE082] text-[9px] font-semibold px-1 rounded select-none">
                SIMULASI
            </span>
        @endif
    </div>

    @if($caption)
        <p class="mt-1 text-[11px] text-[#536170] leading-tight truncate max-w-[110px]" title="{{ $caption }}">
            {{ $caption }}
        </p>
    @endif

    <!-- Simple Lightbox Dialog -->
    <div
        x-show="open"
        x-cloak
        style="display: none;"
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-[#18212B]/70 p-4"
    >
        <div class="bg-white rounded-lg border border-[#D8DEE6] p-4 max-w-lg w-full shadow-lg relative" @click.outside="open = false">
            <div class="flex items-center justify-between pb-2 border-b border-[#D8DEE6] mb-3">
                <span class="text-xs font-bold text-[#18212B]">{{ $filename ?? 'Preview Foto' }}</span>
                <button type="button" @click="open = false" class="text-[#536170] hover:text-[#18212B] text-sm font-bold">&times;</button>
            </div>
            <div class="h-56 bg-[#F5F6F8] rounded flex flex-col items-center justify-center text-center p-4">
                <svg class="w-12 h-12 text-[#536170] mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-xs text-[#536170] font-medium">[File Gambar Dummy Simulasi Prototype]</p>
                @if($caption)
                    <p class="text-xs text-[#18212B] mt-2 italic">"{{ $caption }}"</p>
                @endif
            </div>
            <div class="mt-3 text-right">
                <button type="button" @click="open = false" class="px-3 py-1.5 text-xs font-medium rounded bg-[#F5F6F8] text-[#18212B] border border-[#D8DEE6] hover:bg-[#EAECEF]">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
