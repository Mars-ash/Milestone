@props([
    'problemId',
    'problemIndex',
    'photos' => [],
])

<div class="mt-4 pt-3 border-t border-[#E8ECF0] space-y-3" x-data="{ activeLightbox: null }">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-[#536170]">
                Lampiran Foto Temuan (D09, D12, D19)
            </span>
            <span class="text-[11px] text-[#536170] bg-[#F5F6F8] px-2 py-0.5 rounded border border-[#D8DEE6]">
                {{ count($photos) }} Foto
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="addSimulationPhoto('{{ $problemId }}')"
                class="px-2.5 py-1 text-xs font-semibold rounded border border-[#D8DEE6] bg-white text-[#536170] hover:bg-[#F0F4FA] hover:text-[#2459A6] transition-colors flex items-center gap-1 min-h-[36px]"
                data-testid="add-photo-btn-{{ $problemId }}"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                + Tambah Foto Simulasi
            </button>
        </div>
    </div>

    @if(empty($photos))
        <div class="p-3 bg-[#F9FAFC] rounded border border-dashed border-[#D8DEE6] text-center text-xs text-[#536170]">
            Belum ada foto dilampirkan pada temuan ini. Klik "+ Tambah Foto Simulasi" untuk mengunggah bukti visual.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($photos as $phIdx => $photo)
                <div
                    wire:key="photo-{{ $problemId }}-{{ $photo['id'] ?? $phIdx }}"
                    class="p-2.5 bg-[#F9FAFC] rounded-md border border-[#D8DEE6] space-y-2 flex flex-col justify-between"
                >
                    <div class="space-y-1.5">
                        <!-- Thumbnail Preview dengan Badge Simulasi (D12) -->
                        <div class="relative group rounded overflow-hidden bg-[#E8ECF0] aspect-video flex items-center justify-center cursor-pointer border border-[#D8DEE6]"
                             x-on:click="activeLightbox = '{{ $photo['filename'] ?? 'Foto Simulasi' }}'">
                            @if(!empty($photo['thumbnail']) || !empty($photo['path']))
                                <img
                                    src="{{ $photo['thumbnail'] ?? $photo['path'] }}"
                                    alt="{{ $photo['caption'] ?? $photo['filename'] ?? 'Foto' }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                />
                            @endif
                            <div class="flex flex-col items-center justify-center p-2 text-center text-[#536170] {{ (!empty($photo['thumbnail']) || !empty($photo['path'])) ? 'hidden' : '' }}">
                                <svg class="w-8 h-8 text-[#8C9BAE]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-[10px] mt-1 font-mono text-[#536170] truncate max-w-full">
                                    {{ $photo['filename'] ?? 'foto_temuan.jpg' }}
                                </span>
                            </div>

                            <!-- Badge [SIMULASI] (UI10, D12) -->
                            <span class="absolute top-1.5 left-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#FEF7E6] text-[#8A5B00] border border-[#F8DDA4]">
                                [SIMULASI]
                            </span>

                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                                Perbesar
                            </div>
                        </div>

                        <!-- Caption Input -->
                        <div>
                            <input
                                type="text"
                                wire:model="problems.{{ $problemIndex }}.photos.{{ $phIdx }}.caption"
                                value="{{ $photo['caption'] ?? '' }}"
                                placeholder="Keterangan foto / caption..."
                                class="w-full text-[11px] rounded border border-[#D8DEE6] bg-white px-2 py-1.5 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-[#E8ECF0] text-[11px]">
                        <span class="text-[#8C9BAE] font-mono truncate max-w-[120px]">
                            {{ $photo['filename'] ?? 'file_simulasi.jpg' }}
                        </span>
                        <button
                            type="button"
                            wire:click="removeSimulationPhoto('{{ $problemId }}', '{{ $photo['id'] ?? $phIdx }}')"
                            class="text-[#C5221F] hover:text-[#9A1613] font-medium"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Lightbox Modal Preview (UI10) -->
    <template x-if="activeLightbox">
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4"
            x-on:keydown.escape.window="activeLightbox = null"
            x-on:click.self="activeLightbox = null"
        >
            <div class="bg-white rounded-lg max-w-2xl w-full p-4 space-y-3 shadow-xl">
                <div class="flex items-center justify-between border-b border-[#D8DEE6] pb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#18212B]" x-text="activeLightbox"></span>
                        <span class="text-[10px] font-bold bg-[#FEF7E6] text-[#8A5B00] px-1.5 py-0.5 rounded border border-[#F8DDA4]">
                            [SIMULASI]
                        </span>
                    </div>
                    <button
                        type="button"
                        x-on:click="activeLightbox = null"
                        class="text-[#536170] hover:text-[#18212B] text-lg font-bold"
                    >
                        &times;
                    </button>
                </div>
                <div class="aspect-video bg-[#F5F6F8] rounded flex items-center justify-center border border-[#D8DEE6] text-[#536170] text-xs">
                    <div class="text-center p-4">
                        <svg class="w-12 h-12 text-[#8C9BAE] mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="font-semibold text-[#18212B]" x-text="activeLightbox"></p>
                        <p class="text-[11px] text-[#536170] mt-1">
                            Preview resolusi besar foto temuan trial (Simulasi PRD Bagian 6 &amp; UI10).
                        </p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button
                        type="button"
                        x-on:click="activeLightbox = null"
                        class="px-4 py-1.5 text-xs font-semibold rounded bg-[#2459A6] text-white hover:bg-[#1D4785]"
                    >
                        Tutup (Esc)
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
