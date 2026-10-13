@props([
    'feedbacks' => [],
])

<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-[#E8ECF0]">
        <div>
            <h3 class="text-sm font-bold text-[#18212B] flex items-center gap-2">
                <span>Riwayat Feedback Customer (D08, FR09)</span>
                <span class="text-xs font-semibold bg-[#EAF7EE] text-[#0E6B38] px-2 py-0.5 rounded">
                    {{ count($feedbacks) }} Tanggapan
                </span>
            </h3>
            <p class="text-xs text-[#536170] mt-0.5">
                Setiap entri feedback memiliki tanggal, isi uraian, PIC customer bebas, dan lampiran terkait (D08).
            </p>
        </div>

        <button
            type="button"
            wire:click="addFeedback"
            class="px-3.5 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] sm:min-h-0 flex items-center justify-center gap-1.5 shrink-0"
            data-testid="add-feedback-btn"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            + Tambah Feedback
        </button>
    </div>

    @if(empty($feedbacks))
        <div class="p-6 bg-white rounded-lg border border-dashed border-[#D8DEE6] text-center space-y-2">
            <svg class="w-8 h-8 text-[#8C9BAE] mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <p class="text-xs font-medium text-[#18212B]">Belum ada feedback dari customer yang dicatat.</p>
            <p class="text-[11px] text-[#536170]">
                Klik "+ Tambah Feedback" bila customer memberikan catatan hasil evaluasi sampel atau konfirmasi dokumen.
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($feedbacks as $fbIdx => $fb)
                <div
                    wire:key="feedback-{{ $fb['id'] }}"
                    class="bg-white rounded-lg border border-[#D8DEE6] p-4 space-y-3 shadow-2xs hover:border-[#B5C2D4] transition-colors"
                >
                    <div class="flex items-center justify-between pb-1.5 border-b border-[#F0F2F5]">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-[#EAF7EE] text-[#0E6B38] text-[11px] font-bold flex items-center justify-center shrink-0">
                                {{ $fbIdx + 1 }}
                            </span>
                            <span class="text-xs font-bold text-[#18212B]">
                                Feedback Customer #{{ $fbIdx + 1 }}
                            </span>
                            <span class="text-[10px] font-mono text-[#8C9BAE]">
                                ({{ $fb['id'] }})
                            </span>
                        </div>

                        <button
                            type="button"
                            wire:click="removeFeedback('{{ $fb['id'] }}')"
                            class="text-xs text-[#C5221F] hover:text-[#9A1613] font-medium flex items-center gap-1"
                            title="Hapus feedback ini"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <!-- Tanggal Feedback -->
                        <div class="sm:col-span-4">
                            <label class="block text-[11px] font-semibold text-[#536170] mb-1">
                                Tanggal Tanggapan
                            </label>
                            <input
                                type="date"
                                wire:model="feedbacks.{{ $fbIdx }}.date"
                                class="w-full text-xs rounded border border-[#D8DEE6] bg-white px-3 py-2 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                            />
                        </div>

                        <!-- PIC Customer (Teks Bebas, D02, D08) -->
                        <div class="sm:col-span-8">
                            <label class="block text-[11px] font-semibold text-[#536170] mb-1">
                                PIC Customer / Evaluator (Teks Bebas)
                            </label>
                            <input
                                type="text"
                                wire:model="feedbacks.{{ $fbIdx }}.pic"
                                placeholder="Contoh: Mr. Tanaka / QA Inspector..."
                                class="w-full text-xs rounded border border-[#D8DEE6] bg-white px-3 py-2 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                            />
                        </div>

                        <!-- Isi Feedback -->
                        <div class="sm:col-span-12">
                            <label class="block text-[11px] font-semibold text-[#536170] mb-1">
                                Uraian Isi Tanggapan Customer
                            </label>
                            <textarea
                                wire:model="feedbacks.{{ $fbIdx }}.content"
                                rows="2"
                                placeholder="Catatan evaluasi sampel dari customer, approval bersyarat, atau poin perbaikan..."
                                class="w-full text-xs rounded border border-[#D8DEE6] bg-white px-3 py-2 text-[#18212B] focus:outline-2 focus:outline-[#2459A6]"
                            ></textarea>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
