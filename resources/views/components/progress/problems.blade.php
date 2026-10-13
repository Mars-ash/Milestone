@props([
    'problems' => [],
])

<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-[#E8ECF0]">
        <div>
            <h3 class="text-sm font-bold text-[#18212B] flex items-center gap-2">
                <span>Daftar Problem &amp; Tindakan Perbaikan (D06, FR07)</span>
                <span class="text-xs font-semibold bg-[#FCE8E6] text-[#C5221F] px-2 py-0.5 rounded">
                    {{ count($problems) }} Temuan
                </span>
            </h3>
            <p class="text-xs text-[#536170] mt-0.5">
                Satu progress dapat memiliki banyak problem; setiap problem memiliki banyak tindakan perbaikan dan lampiran foto (D06).
            </p>
        </div>

        <button
            type="button"
            wire:click="addProblem"
            class="px-3.5 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] sm:min-h-0 flex items-center justify-center gap-1.5 shrink-0"
            data-testid="add-problem-btn"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            + Tambah Problem
        </button>
    </div>

    @if(empty($problems))
        <div class="p-6 bg-white rounded-lg border border-dashed border-[#D8DEE6] text-center space-y-2">
            <svg class="w-8 h-8 text-[#8C9BAE] mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-xs font-medium text-[#18212B]">Belum ada data temuan / problem tercatat.</p>
            <p class="text-[11px] text-[#536170]">
                Problem bersifat opsional untuk simpan awal. Klik tombol di atas jika ada temuan ketidaksesuaian/burry/dimensi.
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($problems as $pIdx => $prob)
                <div
                    wire:key="problem-{{ $prob['id'] }}"
                    class="bg-white rounded-lg border border-[#D8DEE6] p-4 sm:p-5 space-y-3 shadow-2xs hover:border-[#B5C2D4] transition-colors"
                    data-testid="problem-card-{{ $pIdx }}"
                >
                    <!-- Header Kartu Problem -->
                    <div class="flex items-center justify-between pb-2 border-b border-[#E8ECF0]">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#EBF2FA] text-[#2459A6] text-xs font-bold flex items-center justify-center shrink-0">
                                {{ $pIdx + 1 }}
                            </span>
                            <span class="text-sm font-bold text-[#18212B]">
                                Problem #{{ $pIdx + 1 }}
                            </span>
                            <span class="text-[11px] font-mono text-[#8C9BAE]">
                                ({{ $prob['id'] }})
                            </span>
                        </div>

                        <button
                            type="button"
                            wire:click="removeProblem('{{ $prob['id'] }}')"
                            class="px-2.5 py-1 text-xs font-medium rounded border border-[#F8D0CD] text-[#C5221F] bg-[#FCE8E6] hover:bg-[#F8D0CD] transition-colors flex items-center gap-1 min-h-[36px]"
                            title="Hapus temuan problem ini beserta tindakannya"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus Problem
                        </button>
                    </div>

                    <!-- Uraian Problem / Temuan -->
                    <div>
                        <label class="block text-xs font-semibold text-[#18212B] mb-1">
                            Uraian Temuan / Deskripsi Problem <span class="text-[#536170] font-normal">(D06)</span>
                        </label>
                        <textarea
                            wire:model="problems.{{ $pIdx }}.description"
                            rows="2"
                            placeholder="Contoh: Ditemukan burry 1,5 mm pada sisi flange kiri OP10..."
                            class="w-full text-xs rounded border border-[#D8DEE6] bg-white px-3 py-2 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                        ></textarea>
                    </div>

                    <!-- Sub-komponen Galeri Foto per Problem -->
                    <x-progress.gallery
                        :problemId="$prob['id']"
                        :problemIndex="$pIdx"
                        :photos="$prob['photos'] ?? []"
                    />

                    <!-- Sub-komponen Corrective Actions per Problem -->
                    <x-progress.corrective-actions
                        :problemId="$prob['id']"
                        :problemIndex="$pIdx"
                        :actions="$prob['corrective_actions'] ?? []"
                    />
                </div>
            @endforeach
        </div>
    @endif
</div>
