@props([
    'nextActions' => [],
])

<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-[#E8ECF0]">
        <div>
            <h3 class="text-sm font-bold text-[#18212B] flex items-center gap-2">
                <span>Daftar Next Action (Langkah Lanjutan — D07, FR08)</span>
                <span class="text-xs font-semibold bg-[#EBF2FA] text-[#2459A6] px-2 py-0.5 rounded">
                    {{ count($nextActions) }} Tindakan
                </span>
            </h3>
            <p class="text-xs text-[#536170] mt-0.5">
                Next Action berada pada progress secara independen (saudara problem, bukan anak dari problem — D07).
            </p>
        </div>

        <button
            type="button"
            wire:click="addNextAction"
            class="px-3.5 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] sm:min-h-0 flex items-center justify-center gap-1.5 shrink-0"
            data-testid="add-next-action-btn"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            + Tambah Next Action
        </button>
    </div>

    @if(empty($nextActions))
        <div class="p-6 bg-white rounded-lg border border-dashed border-[#D8DEE6] text-center space-y-2">
            <svg class="w-8 h-8 text-[#8C9BAE] mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-xs font-medium text-[#18212B]">Belum ada langkah lanjutan (Next Action) tercatat.</p>
            <p class="text-[11px] text-[#536170]">
                Klik "+ Tambah Next Action" untuk merencanakan tindak lanjut progress tanpa harus terikat pada satu problem tertentu.
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($nextActions as $naIdx => $action)
                <div
                    wire:key="next-action-{{ $action['id'] }}"
                    class="bg-white rounded-lg border border-[#D8DEE6] p-4 space-y-2.5 shadow-2xs hover:border-[#B5C2D4] transition-colors"
                >
                    <div class="flex items-center justify-between pb-1.5 border-b border-[#F0F2F5]">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-[#EBF2FA] text-[#2459A6] text-[11px] font-bold flex items-center justify-center shrink-0">
                                {{ $naIdx + 1 }}
                            </span>
                            <span class="text-xs font-bold text-[#18212B]">
                                Next Action #{{ $naIdx + 1 }}
                            </span>
                            <span class="text-[10px] font-mono text-[#8C9BAE]">
                                ({{ $action['id'] }})
                            </span>
                        </div>

                        <button
                            type="button"
                            wire:click="removeNextAction('{{ $action['id'] }}')"
                            class="text-xs text-[#C5221F] hover:text-[#9A1613] font-medium flex items-center gap-1"
                            title="Hapus next action ini"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <!-- Uraian Next Action -->
                        <div class="sm:col-span-6">
                            <label class="block text-[11px] font-semibold text-[#536170] mb-1">
                                Rencana Langkah Lanjutan
                            </label>
                            <input
                                type="text"
                                wire:model="nextActions.{{ $naIdx }}.description"
                                placeholder="Contoh: Jadwalkan Trial 2 setelah modifikasi mold selesai..."
                                class="w-full text-xs rounded border border-[#D8DEE6] bg-white px-3 py-2 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                            />
                        </div>

                        <!-- PIC Next Action (Teks Bebas, D02, D08) -->
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-[#536170] mb-1">
                                PIC Tindakan
                            </label>
                            <input
                                type="text"
                                wire:model="nextActions.{{ $naIdx }}.pic"
                                placeholder="Nama PIC..."
                                class="w-full text-xs rounded border border-[#D8DEE6] bg-white px-3 py-2 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                            />
                        </div>

                        <!-- Tanggal Aktual (D09: No Target Date, tanpa default hari ini) -->
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-[#536170] mb-1">
                                Tanggal Aktual (Opsional)
                            </label>
                            <input
                                type="date"
                                wire:model="nextActions.{{ $naIdx }}.actual_date"
                                class="w-full text-xs rounded border border-[#D8DEE6] bg-white px-3 py-2 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                            />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
