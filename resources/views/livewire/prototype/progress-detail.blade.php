<div class="space-y-6">
    @if(!$progress)
        <div class="bg-white p-8 rounded-lg border border-[#D8DEE6] text-center shadow-2xs">
            <h3 class="text-base font-bold text-[#18212B]">Catatan Progress Tidak Ditemukan</h3>
            <p class="text-xs text-[#536170] mt-1">Data progress dengan ID '{{ $progressId }}' tidak tersedia di sistem.</p>
            <div class="mt-4">
                <a href="?tab=progresses" class="px-4 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white">
                    Kembali ke Daftar Progress
                </a>
            </div>
        </div>
    @else
        @php
            $isClosed = strtolower($progress['status'] ?? 'open') === 'closed';
        @endphp

        <!-- Header Halaman Detail Progress -->
        <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] shadow-2xs">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-mono font-bold text-[#536170]">
                            {{ $progress['id'] }}
                        </span>
                        <x-ui.badge
                            :type="strtolower(str_replace(' ', '-', $progress['activity']))"
                            :label="$progress['activity']"
                        />
                        <x-ui.badge
                            :type="$isClosed ? 'closed' : 'open'"
                            :label="$progress['status']"
                        />
                        <span class="text-xs text-[#536170] bg-[#F5F6F8] px-2 py-0.5 rounded border border-[#D8DEE6]">
                            Revisi: {{ $progress['revision'] ?? 1 }}
                        </span>
                    </div>

                    <h1 class="text-xl sm:text-2xl font-bold text-[#18212B] mt-2 flex flex-wrap items-baseline gap-2">
                        <span>{{ $project['jo_no'] ?? 'JO Belum Terkait' }}</span>
                        <span class="text-base font-normal text-[#536170]">&bull; {{ $project['part_name'] ?? '-' }}</span>
                        <span class="text-sm font-mono text-[#8C9BAE]">({{ $project['part_no'] ?? '-' }})</span>
                    </h1>

                    <div class="text-xs text-[#536170] mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><strong>Tanggal Aktivitas:</strong> {{ $progress['activity_date'] ?? 'Belum ada tanggal' }}</span>
                        <span><strong>Customer:</strong> {{ $project['customer'] ?? '-' }}</span>
                        <span><strong>Model:</strong> {{ $project['model'] ?? '-' }}</span>
                    </div>
                </div>

                <!-- Tombol Aksi Status & Navigasi -->
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <a
                        href="?tab=progresses"
                        class="px-3.5 py-2 text-xs font-semibold rounded-md border border-[#D8DEE6] bg-white text-[#536170] hover:bg-[#F5F6F8] transition-colors min-h-[44px] flex items-center"
                    >
                        &larr; Kembali
                    </a>

                    <x-progress.status-actions
                        :status="$progress['status']"
                        :progressId="$progress['id']"
                    />
                </div>
            </div>
        </div>

        <!-- Notifikasi Flash -->
        @if($flashMessage)
            <div class="p-3.5 rounded-md bg-[#EAF7EE] border border-[#B6E6C3] text-[#0E6B38] text-xs font-medium flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $flashMessage }}</span>
                </div>
                <button wire:click="dismissFlash" class="text-[#0E6B38] hover:opacity-75 font-bold">&times;</button>
            </div>
        @endif

        <!-- Banner Peringatan Status Closed (AT21) -->
        @if($isClosed)
            <div class="p-4 rounded-lg bg-[#F5F6F8] border border-[#D8DEE6] flex items-start gap-3 shadow-2xs" data-testid="closed-warning-banner">
                <div class="w-8 h-8 rounded-full bg-[#E8ECF0] text-[#536170] flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-[#18212B]">Progress ini berstatus Closed (Read-Only)</h4>
                    <p class="text-xs text-[#536170] mt-0.5">
                        Sesuai aturan PRD D10 &amp; AT21, catatan progress yang telah ditutup tidak dapat diedit secara langsung. Seluruh isi catatan, problem, tindakan, dan foto tetap terjaga utuh. Untuk melakukan pengeditan data, klik tombol <strong>"Buka Kembali (Reopen Progress)"</strong> di atas.
                    </p>
                </div>
            </div>
        @endif

        <!-- Kartu 1: Identitas Project Terkini (FR02, AT03) -->
        @if($project)
            <x-progress.identity :project="$project" />
        @endif

        <!-- Kartu 2: Uraian Catatan Kegiatan & PIC Teks Bebas (FR05, AT06) -->
        <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] shadow-2xs space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#2459A6] pb-1 border-b border-[#E8ECF0]">
                Data Aktivitas &amp; Catatan Pelaksanaan
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-3 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] font-semibold text-[#536170] block">PIC Progress (Teks Bebas)</span>
                    <span class="text-xs font-bold text-[#18212B] mt-0.5 block">
                        {{ ($progress['pic_progress'] ?? null) ?: '-' }}
                    </span>
                </div>
                <div class="p-3 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] font-semibold text-[#536170] block">PIC Customer (Teks Bebas)</span>
                    <span class="text-xs font-bold text-[#18212B] mt-0.5 block">
                        {{ ($progress['pic_customer'] ?? null) ?: '-' }}
                    </span>
                </div>
                <div class="p-3 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] font-semibold text-[#536170] block">Tanggal Kegiatan</span>
                    <span class="text-xs font-bold text-[#18212B] mt-0.5 block">
                        {{ ($progress['activity_date'] ?? null) ?: '-' }}
                    </span>
                </div>
            </div>

            <div>
                <span class="text-xs font-semibold text-[#18212B] block mb-1">Uraian Catatan Kegiatan / Notes:</span>
                <div class="p-4 bg-[#F9FAFC] rounded-md border border-[#E8ECF0] text-xs text-[#18212B] whitespace-pre-wrap leading-relaxed">
                    {{ ($progress['notes'] ?? null) ?: '(Tidak ada uraian catatan kegiatan)' }}
                </div>
            </div>
        </div>

        <!-- Kartu 3: Rincian Parameter Mesin, DH, Qty (Bagian 16 PRD) -->
        <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] shadow-2xs space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#2459A6] pb-1 border-b border-[#E8ECF0]">
                Rincian Parameter Lapangan &amp; Trial (Tersedia di Semua Activity — FR04)
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-2.5 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] text-[#536170] block">Mesin</span>
                    <span class="font-bold text-[#18212B]">{{ ($progress['machine'] ?? null) ?: '-' }}</span>
                </div>
                <div class="p-2.5 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] text-[#536170] block">Die High (DH)</span>
                    <span class="font-bold text-[#18212B]">{{ ($progress['die_high_raw'] ?? null) ?: '-' }}</span>
                </div>
                <div class="p-2.5 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] text-[#536170] block">Process Trial</span>
                    <span class="font-bold text-[#18212B]">{{ ($progress['process_trial'] ?? null) ?: '-' }}</span>
                </div>
                <div class="p-2.5 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] text-[#536170] block">Material Actual</span>
                    <span class="font-bold text-[#18212B]">{{ ($progress['material_actual_trial'] ?? null) ?: '-' }}</span>
                </div>
                <div class="p-2.5 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] text-[#536170] block">Qty Material</span>
                    <span class="font-bold text-[#18212B]">{{ ($progress['qty_material_raw'] ?? null) ?: '-' }}</span>
                </div>
                <div class="p-2.5 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] text-[#536170] block">Qty Process</span>
                    <span class="font-bold text-[#18212B]">{{ ($progress['qty_process_raw'] ?? null) ?: '-' }}</span>
                </div>
                <div class="p-2.5 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] text-[#536170] block">Qty Sample</span>
                    <span class="font-bold text-[#18212B]">{{ ($progress['qty_sample_raw'] ?? null) ?: '-' }}</span>
                </div>
                <div class="p-2.5 bg-[#F9FAFC] rounded border border-[#E8ECF0]">
                    <span class="text-[11px] text-[#536170] block">Ket Event / Remark</span>
                    <span class="font-bold text-[#18212B]">
                        {{ ($progress['event_note'] ?? null) ?: (($progress['remark'] ?? null) ?: '-') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Kartu 4: Problem, Corrective Action, & Foto (D06, FR07) -->
        <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-1 border-b border-[#E8ECF0]">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#C5221F] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#C5221F]"></span>
                    Temuan Problem &amp; Tindakan Perbaikan ({{ count($progress['problems'] ?? []) }})
                </h3>
            </div>

            @if(empty($progress['problems']))
                <div class="p-4 bg-[#F9FAFC] rounded border border-dashed border-[#D8DEE6] text-center text-xs text-[#536170]">
                    Tidak ada temuan problem tercatat pada kegiatan ini.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($progress['problems'] as $idx => $prob)
                        <div class="p-4 bg-[#F9FAFC] rounded-lg border border-[#E8ECF0] space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-[#18212B]">
                                    Problem #{{ $idx + 1 }}: {{ $prob['description'] ?: '(Tanpa uraian temuan)' }}
                                </span>
                                <span class="text-[11px] font-mono text-[#8C9BAE]">{{ $prob['id'] }}</span>
                            </div>

                            <!-- Lampiran Foto Problem -->
                            @if(!empty($prob['photos']))
                                <div class="pt-2 border-t border-[#E8ECF0]">
                                    <span class="text-[11px] font-semibold text-[#536170] block mb-2">Lampiran Foto:</span>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach($prob['photos'] as $ph)
                                            <div class="p-2 bg-white rounded border border-[#D8DEE6] text-[11px] space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono text-[#2459A6] font-bold">{{ $ph['filename'] ?? 'foto.jpg' }}</span>
                                                    <span class="text-[10px] font-bold bg-[#FEF7E6] text-[#8A5B00] px-1 rounded border border-[#F8DDA4]">[SIMULASI]</span>
                                                </div>
                                                @if(!empty($ph['caption']))
                                                    <p class="text-[#536170] italic">{{ $ph['caption'] }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Tindakan Perbaikan (Corrective Action) -->
                            <div class="pt-2 border-t border-[#E8ECF0]">
                                <span class="text-[11px] font-semibold text-[#536170] block mb-1.5">
                                    Tindakan Perbaikan ({{ count($prob['corrective_actions'] ?? []) }}):
                                </span>
                                @if(empty($prob['corrective_actions']))
                                    <p class="text-xs text-[#8C9BAE] italic">Belum ada tindakan perbaikan.</p>
                                @else
                                    <div class="space-y-1.5">
                                        @foreach($prob['corrective_actions'] as $actIdx => $act)
                                            <div class="p-2.5 bg-white rounded border border-[#E8ECF0] flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                                <div>
                                                    <span class="font-semibold text-[#18212B]">{{ $actIdx + 1 }}. {{ $act['description'] ?: '-' }}</span>
                                                </div>
                                                <div class="flex items-center gap-3 text-[11px] text-[#536170] shrink-0">
                                                    <span>PIC: <strong class="text-[#18212B]">{{ $act['pic'] ?: '-' }}</strong></span>
                                                    <span>Tgl: {{ $act['actual_date'] ?: '-' }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Kartu 5: Next Action & Feedback Customer (D07, D08) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Next Action Independen -->
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] shadow-2xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#2459A6] pb-1 border-b border-[#E8ECF0] flex items-center justify-between">
                    <span>Next Action (Saudara Problem — D07)</span>
                    <span class="text-[11px] bg-[#EBF2FA] text-[#2459A6] px-2 py-0.5 rounded">
                        {{ count($progress['next_actions'] ?? []) }}
                    </span>
                </h3>

                @if(empty($progress['next_actions']))
                    <p class="text-xs text-[#536170] italic p-3 bg-[#F9FAFC] rounded text-center">
                        Tidak ada langkah lanjutan yang direncanakan.
                    </p>
                @else
                    <div class="space-y-2">
                        @foreach($progress['next_actions'] as $naIdx => $na)
                            <div class="p-3 bg-[#F9FAFC] rounded border border-[#E8ECF0] text-xs space-y-1">
                                <div class="font-semibold text-[#18212B]">{{ $naIdx + 1 }}. {{ $na['description'] ?? '-' }}</div>
                                <div class="flex items-center justify-between text-[11px] text-[#536170]">
                                    <span>PIC: <strong>{{ ($na['pic'] ?? null) ?: '-' }}</strong></span>
                                    <span>Tgl: {{ ($na['actual_date'] ?? null) ?: '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Feedback Customer Berulang -->
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] shadow-2xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#0E6B38] pb-1 border-b border-[#E8ECF0] flex items-center justify-between">
                    <span>Feedback Customer (D08)</span>
                    <span class="text-[11px] bg-[#EAF7EE] text-[#0E6B38] px-2 py-0.5 rounded">
                        {{ count($progress['feedbacks'] ?? []) }}
                    </span>
                </h3>

                @if(empty($progress['feedbacks']))
                    <p class="text-xs text-[#536170] italic p-3 bg-[#F9FAFC] rounded text-center">
                        Tidak ada feedback dari customer.
                    </p>
                @else
                    <div class="space-y-2">
                        @foreach($progress['feedbacks'] as $fbIdx => $fb)
                            <div class="p-3 bg-[#F9FAFC] rounded border border-[#E8ECF0] text-xs space-y-1">
                                <div class="flex items-center justify-between text-[11px] text-[#536170]">
                                    <span class="font-bold text-[#18212B]">{{ ($fb['pic'] ?? null) ?: 'Customer' }}</span>
                                    <span>{{ ($fb['date'] ?? null) ?: '-' }}</span>
                                </div>
                                <p class="text-[#18212B]">{{ $fb['content'] ?? '-' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
