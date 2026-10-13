@props([
    'problemId',
    'problemIndex',
    'actions' => [],
])

<div class="mt-4 pt-3 border-t border-[#E8ECF0] space-y-3">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-[#2459A6]">
                Tindakan Perbaikan (Corrective Action — D06)
            </span>
            <span class="text-[11px] text-[#536170] bg-[#F5F6F8] px-2 py-0.5 rounded border border-[#D8DEE6]">
                {{ count($actions) }} Tindakan
            </span>
        </div>
        <button
            type="button"
            wire:click="addCorrectiveAction('{{ $problemId }}')"
            class="px-2.5 py-1 text-xs font-semibold rounded border border-[#D8DEE6] bg-white text-[#2459A6] hover:bg-[#F0F4FA] transition-colors flex items-center gap-1 min-h-[36px]"
            data-testid="add-action-btn-{{ $problemId }}"
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            + Tambah Tindakan
        </button>
    </div>

    @if(empty($actions))
        <div class="p-3 bg-[#F9FAFC] rounded border border-dashed border-[#D8DEE6] text-center text-xs text-[#536170]">
            Belum ada tindakan perbaikan untuk temuan ini. Klik "+ Tambah Tindakan" untuk mencatat corrective action.
        </div>
    @else
        <div class="space-y-3">
            @foreach($actions as $aIdx => $action)
                <div
                    wire:key="action-{{ $problemId }}-{{ $action['id'] }}"
                    class="p-3 bg-[#F9FAFC] rounded-md border border-[#E8ECF0] space-y-2.5 transition-colors"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[#18212B] flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#2459A6]"></span>
                            Tindakan {{ $problemIndex + 1 }}.{{ $aIdx + 1 }}
                        </span>
                        <button
                            type="button"
                            wire:click="removeCorrectiveAction('{{ $problemId }}', '{{ $action['id'] }}')"
                            class="text-xs text-[#C5221F] hover:text-[#9A1613] font-medium flex items-center gap-1"
                            title="Hapus tindakan ini"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <!-- Uraian Tindakan -->
                        <div class="sm:col-span-6">
                            <label class="block text-[11px] font-semibold text-[#536170] mb-1">
                                Uraian Tindakan Perbaikan
                            </label>
                            <input
                                type="text"
                                wire:model="problems.{{ $problemIndex }}.corrective_actions.{{ $aIdx }}.description"
                                placeholder="Contoh: Re-grinding clearance punch R2,5 mm..."
                                class="w-full text-xs rounded border border-[#D8DEE6] bg-white px-3 py-2 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                            />
                        </div>

                        <!-- PIC Tindakan (Teks Bebas, D02, D08) -->
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-[#536170] mb-1">
                                PIC Tindakan (Teks Bebas)
                            </label>
                            <input
                                type="text"
                                wire:model="problems.{{ $problemIndex }}.corrective_actions.{{ $aIdx }}.pic"
                                placeholder="Nama PIC / Tim..."
                                class="w-full text-xs rounded border border-[#D8DEE6] bg-white px-3 py-2 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                            />
                        </div>

                        <!-- Actual Date (D09: No Target Date, tanpa default hari ini) -->
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-[#536170] mb-1">
                                Tanggal Aktual (Opsional)
                            </label>
                            <input
                                type="date"
                                wire:model="problems.{{ $problemIndex }}.corrective_actions.{{ $aIdx }}.actual_date"
                                class="w-full text-xs rounded border border-[#D8DEE6] bg-white px-3 py-2 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                            />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
