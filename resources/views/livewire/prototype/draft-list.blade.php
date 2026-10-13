<div class="bg-white rounded-lg border border-[#D8DEE6] overflow-hidden shadow-2xs">
    <!-- Header Draft List -->
    <div class="px-5 py-4 border-b border-[#D8DEE6] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-[#8A5B00] bg-[#FEF7E6] px-2.5 py-0.5 rounded border border-[#F8DDA4]">
                    D11: Draft Mandiri
                </span>
                <span class="text-xs text-[#536170]">
                    {{ count($drafts) }} Draft Tersimpan
                </span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-[#18212B] mt-0.5">
                Daftar Draft Isian Kerja
            </h1>
            <p class="text-xs text-[#536170] mt-0.5">
                Isian kerja yang belum diterapkan ke data progress tersimpan. Tidak masuk hitungan Open/Closed (PRD D11).
            </p>
        </div>

        <a
            href="?tab=form"
            class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] sm:min-h-0"
        >
            + Form Baru
        </a>
    </div>

    <!-- Notifikasi Flash -->
    @if($flashMessage)
        <div class="m-5 p-3 rounded-md bg-[#EAF7EE] border border-[#B6E6C3] text-[#0E6B38] text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ $flashMessage }}</span>
            </div>
            <button wire:click="dismissFlash" class="text-[#0E6B38] hover:opacity-75 font-bold">&times;</button>
        </div>
    @endif

    <!-- Daftar Draft -->
    <div class="divide-y divide-[#D8DEE6]">
        @forelse($drafts as $d)
            @php $proj = $d['project'] ?? null; @endphp
            <div
                wire:key="draft-item-{{ $d['uuid'] }}"
                class="p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-[#F9FAFC] transition-colors"
                data-testid="draft-item-{{ $d['uuid'] }}"
            >
                <div class="space-y-1.5 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-sm text-[#2459A6]">
                            {{ $proj['jo_no'] ?? (!empty($d['project_id']) ? $d['project_id'] : 'JO Belum Dipilih') }}
                        </span>
                        <x-ui.badge
                            :type="($d['type'] ?? 'new') === 'new' ? 'draft' : 'neutral'"
                            :label="($d['type'] ?? 'new') === 'new' ? 'Draft Baru' : 'Draft Perubahan'"
                        />
                        <x-ui.badge :type="strtolower(str_replace(' ', '-', $d['activity'] ?? 'trial'))" :label="$d['activity'] ?? 'Trial'" />
                        @if(!empty($d['progress_id']))
                            <span class="text-[11px] text-[#536170] bg-[#F0F2F5] px-2 py-0.5 rounded">
                                Mengedit: {{ $d['progress_id'] }}
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-[#18212B]">
                        <strong>Part:</strong> {{ $proj['part_name'] ?? '-' }}
                        <span class="font-mono text-[#536170]">({{ $proj['part_no'] ?? '-' }})</span>
                        @if(!empty($proj['customer']))
                            &bull; <strong>Customer:</strong> {{ $proj['customer'] }}
                        @endif
                    </div>

                    <p class="text-xs text-[#536170] max-w-3xl line-clamp-2">
                        {{ !empty($d['notes']) ? $d['notes'] : '(Belum ada catatan aktivitas)' }}
                    </p>

                    <div class="flex flex-wrap items-center gap-3 text-[11px] text-[#8C9BAE]">
                        <span>Terakhir diperbarui: {{ \Illuminate\Support\Carbon::parse($d['updated_at'] ?? now())->format('d M Y, H:i') }}</span>
                        @if(!empty($d['pic_progress']))
                            <span>&bull; PIC: <strong class="text-[#536170]">{{ $d['pic_progress'] }}</strong></span>
                        @endif
                        @if(!empty($d['problems']))
                            <span>&bull; <strong class="text-[#C5221F]">{{ count($d['problems']) }} Problem</strong></span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto justify-end">
                    <button
                        type="button"
                        wire:click="confirmDiscard('{{ $d['uuid'] }}')"
                        class="px-3 py-2 text-xs font-semibold rounded border border-[#F8D0CD] text-[#C5221F] bg-[#FCE8E6] hover:bg-[#F8D0CD] transition-colors min-h-[44px] flex items-center gap-1"
                        data-testid="discard-draft-btn-{{ $d['uuid'] }}"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Buang Draft
                    </button>

                    <a
                        href="?tab=form&draft={{ $d['uuid'] }}"
                        class="px-4 py-2 text-xs font-semibold rounded bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] flex items-center gap-1"
                        data-testid="resume-draft-btn-{{ $d['uuid'] }}"
                    >
                        <span>Lanjutkan Draft</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-[#FEF7E6] text-[#8A5B00] mx-auto flex items-center justify-center border border-[#F8DDA4]">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-[#18212B]">Tidak Ada Draft Tersimpan</h3>
                <p class="text-xs text-[#536170] max-w-sm mx-auto">
                    Semua isian kerja telah disimpan final menjadi data progress tersimpan atau belum ada draft yang dibuat.
                </p>
                <div class="pt-2">
                    <a href="?tab=form" class="px-4 py-2 text-xs font-semibold rounded bg-[#2459A6] text-white hover:bg-[#1D4785]">
                        Buat Isian Kerja Baru
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modal Konfirmasi Buang Draft -->
    @if($confirmingDiscardUuid)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-5 space-y-4 shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#FCE8E6] text-[#C5221F] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[#18212B]">Konfirmasi Buang Draft</h3>
                        <p class="text-xs text-[#536170] mt-0.5">
                            Apakah Anda yakin ingin membuang draft ini? Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>

                <div class="bg-[#F9FAFC] p-3 rounded text-[11px] text-[#536170] border border-[#E8ECF0]">
                    *Data progress resmi tersimpan (bila ini draft perubahan) akan tetap utuh pada versi terakhirnya.
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button
                        type="button"
                        wire:click="cancelDiscard"
                        class="px-4 py-2 text-xs font-semibold rounded border border-[#D8DEE6] bg-white text-[#536170] hover:bg-[#F5F6F8]"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="discardDraft('{{ $confirmingDiscardUuid }}')"
                        class="px-4 py-2 text-xs font-semibold rounded bg-[#C5221F] text-white hover:bg-[#9A1613] shadow-2xs"
                        data-testid="confirm-discard-btn"
                    >
                        Ya, Buang Draft
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
