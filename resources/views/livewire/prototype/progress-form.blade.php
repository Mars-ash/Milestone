<div class="bg-white rounded-lg border border-[#D8DEE6] shadow-2xs overflow-hidden">
    <!-- Header Form -->
    <div class="px-5 py-4 border-b border-[#D8DEE6] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-[#2459A6]">
                    Form Progress Kegiatan (FR04: Satu Bentuk untuk Semua Activity)
                </span>
                <span class="text-xs text-[#536170]">
                    {{ $progressId ? "Edit {$progressId}" : 'Entri Baru' }}
                </span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-[#18212B] mt-0.5">
                {{ $progressId ? 'Edit Catatan Kegiatan' : 'Catat Kegiatan Progress Baru' }}
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="hidden sm:block mr-2">
                <x-ui.autosave-status :status="$autosaveStatus" :time="$lastSavedTime" />
            </div>

            <button
                wire:click="saveDraft"
                type="button"
                class="px-3.5 py-2 text-xs font-semibold rounded-md border border-[#D8DEE6] bg-white text-[#536170] hover:bg-[#F5F6F8] transition-colors min-h-[44px] flex items-center gap-1.5"
                data-testid="save-draft-btn"
            >
                <svg class="w-4 h-4 text-[#8A5B00]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Simpan Draft
            </button>
            <button
                wire:click="cancel"
                type="button"
                class="px-3.5 py-2 text-xs font-semibold rounded-md border border-[#D8DEE6] bg-white text-[#536170] hover:bg-[#F5F6F8] transition-colors min-h-[44px]"
            >
                Batal
            </button>
            <button
                wire:click="save"
                type="button"
                class="px-5 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] flex items-center gap-1.5"
                data-testid="save-progress-btn"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Progress
            </button>
        </div>
    </div>

    <!-- Notifikasi Sukses -->
    @if($successMessage)
        <div class="m-5 p-3 rounded-md bg-[#EAF7EE] border border-[#B6E6C3] text-[#0E6B38] text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ $successMessage }}</span>
        </div>
    @endif

    <form wire:submit.prevent="save" class="p-5 sm:p-6 space-y-6">
        <!-- 1. IDENTITAS PROJECT & AUTOFILL (FR02, AT03) -->
        <div class="space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#2459A6] pb-1 border-b border-[#E8ECF0]">
                1. Pemilihan Project & Identitas Terkini
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Dropdown JO No Searchable -->
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">
                        Pilih JO No <span class="text-[#C5221F]">*</span>
                    </label>
                    <select
                        wire:model.live="projectId"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border @error('projectId') border-[#C5221F] @else border-[#D8DEE6] @enderror rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] min-h-[42px]"
                        data-testid="select-jo-no"
                    >
                        <option value="">-- Pilih Nomor JO --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p['id'] }}">
                                {{ $p['jo_no'] }} — {{ $p['part_name'] }} ({{ $p['part_no'] }})
                            </option>
                        @endforeach
                    </select>
                    @error('projectId')
                        <p class="text-[11px] text-[#C5221F] mt-1" data-testid="error-project-id">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-[#536170] mt-0.5">
                        Memilih JO akan otomatis mengisi identitas terkini di samping secara hanya-baca.
                    </p>
                </div>

                <!-- 5 Field Identitas Hanya-Baca Autofill (Component Reusable) -->
                <div class="md:col-span-7">
                    <x-progress.identity :project="$selectedProject" />
                </div>
            </div>
        </div>

        <!-- 2. PILIHAN 5 ACTIVITY (FR04, AT05) -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between pb-1 border-b border-[#E8ECF0]">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#2459A6]">
                    2. Jenis Activity (5 Pilihan Tetap Sesuai D03)
                </h2>
                <span class="text-[11px] text-[#536170]">
                    *Pergantian Activity tidak akan menghapus data rincian atau field lainnya.
                </span>
            </div>

            <div class="flex flex-wrap gap-2" data-testid="activity-selector">
                @foreach(['Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'] as $actOption)
                    <button
                        type="button"
                        wire:click="setActivity('{{ $actOption }}')"
                        class="px-4 py-2 text-xs font-semibold rounded-md border transition-colors flex items-center gap-1.5 min-h-[42px] {{ $activity === $actOption ? 'bg-[#2459A6] text-white border-[#2459A6] shadow-2xs' : 'bg-white text-[#536170] border-[#D8DEE6] hover:bg-[#F5F6F8]' }}"
                        data-testid="activity-pill-{{ Str::slug($actOption) }}"
                    >
                        @if($activity === $actOption)
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                        {{ $actOption }}
                    </button>
                @endforeach
            </div>
            @error('activity')
                <p class="text-[11px] text-[#C5221F] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- 3. DATA UTAMA AKTIVITAS & PIC (FR05, AT06) -->
        <div class="space-y-3 pt-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#2459A6] pb-1 border-b border-[#E8ECF0]">
                3. Data Pelaksanaan & Catatan Kegiatan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Tanggal Aktivitas -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">
                        Tanggal Aktivitas <span class="text-[#C5221F]">*</span>
                    </label>
                    <input
                        wire:model="activityDate"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border @error('activityDate') border-[#C5221F] @else border-[#D8DEE6] @enderror rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] min-h-[42px]"
                        data-testid="input-activity-date"
                    />
                    @error('activityDate')
                        <p class="text-[11px] text-[#C5221F] mt-1" data-testid="error-activity-date">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-[#536170] mt-0.5">Tanggal kegiatan dilaksanakan.</p>
                </div>

                <!-- PIC Progress (Teks Bebas) -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">
                        PIC Progress (Teks Bebas)
                    </label>
                    <input
                        wire:model="picProgress"
                        type="text"
                        placeholder="Contoh: Ahmad Fauzi"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[42px]"
                        data-testid="input-pic-progress"
                    />
                    <p class="text-[11px] text-[#536170] mt-0.5">Nama bebas tanpa relasi akun (FR05).</p>
                </div>

                <!-- PIC Customer (Teks Bebas) -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">
                        PIC Customer (Teks Bebas)
                    </label>
                    <input
                        wire:model="picCustomer"
                        type="text"
                        placeholder="Contoh: Irwan Customer"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[42px]"
                        data-testid="input-pic-customer"
                    />
                </div>
            </div>

            <!-- Uraian Catatan Kegiatan (Textarea Luas) -->
            <div class="mt-3">
                <label class="block text-xs font-semibold text-[#18212B] mb-1">
                    Uraian Catatan Kegiatan
                </label>
                <textarea
                    wire:model="notes"
                    rows="4"
                    placeholder="Tuliskan uraian hasil kegiatan, observasi pelaksanaan, atau temuan umum..."
                    class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6]"
                    data-testid="textarea-notes"
                ></textarea>
                <p class="text-[11px] text-[#536170] mt-0.5">Nyaman untuk uraian teks panjang.</p>
            </div>
        </div>

        <!-- 4. RINCIAN TRIAL & LAPANGAN (D15 RAW TEXT - TERSEDIA UNTUK SEMUA ACTIVITY) -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between pb-1 border-b border-[#E8ECF0]">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#2459A6]">
                    4. Rincian Parameter Lapangan & Trial (Tersedia untuk Semua Activity)
                </h2>
                <span class="text-[11px] text-[#536170]">
                    *D15: Raw text mempertahankan koma dan titik (contoh 1,5 dan 1.5).
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <!-- Process Trial -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Process Trial</label>
                    <input
                        wire:model="processTrial"
                        type="text"
                        placeholder="Contoh: Bending 2"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-process-trial"
                    />
                </div>

                <!-- Mesin -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Mesin</label>
                    <input
                        wire:model="machine"
                        type="text"
                        placeholder="Contoh: Press 200T"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-machine"
                    />
                </div>

                <!-- DH (Die High) -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">
                        DH (Die High)
                    </label>
                    <input
                        wire:model="dieHighRaw"
                        type="text"
                        placeholder="Contoh: 120,5 mm"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-dh"
                    />
                    <p class="text-[11px] text-[#536170] mt-0.5">Input raw teks bebas.</p>
                </div>

                <!-- Material Actual Trial -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Material Actual Trial</label>
                    <input
                        wire:model="materialActualTrial"
                        type="text"
                        placeholder="Contoh: SPCC t=1.5 mm"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-material-actual"
                    />
                </div>

                <!-- Qty Material Raw -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Qty Material</label>
                    <input
                        wire:model="qtyMaterialRaw"
                        type="text"
                        placeholder="Contoh: 25 lembar"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-qty-material"
                    />
                </div>

                <!-- Qty Process Raw -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Qty Process</label>
                    <input
                        wire:model="qtyProcessRaw"
                        type="text"
                        placeholder="Contoh: 20 pcs"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-qty-process"
                    />
                </div>

                <!-- Qty Sample Raw -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Qty Sample</label>
                    <input
                        wire:model="qtySampleRaw"
                        type="text"
                        placeholder="Contoh: 5 pcs"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-qty-sample"
                    />
                </div>

                <!-- Ket Event -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Ket Event</label>
                    <input
                        wire:model="eventNote"
                        type="text"
                        placeholder="Catatan event (terpisah)"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-event-note"
                    />
                </div>
            </div>

            <!-- Remark Progress -->
            <div class="mt-3">
                <label class="block text-xs font-semibold text-[#18212B] mb-1">Remark Progress</label>
                <input
                    wire:model="remark"
                    type="text"
                    placeholder="Catatan remark progress..."
                    class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    data-testid="input-remark"
                />
            </div>
        </div>

        <!-- 5. NAVIGASI BAGIAN LANJUTAN (Problem, Next Action, Feedback, Lampiran) -->
        <div class="pt-2 space-y-3">
            <div class="flex items-center justify-between pb-1 border-b border-[#E8ECF0]">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#2459A6]">
                    5. Tindak Lanjut & Lampiran (FR04, UI05)
                </h2>
                <span class="text-[11px] text-[#536170]">
                    *Dapat diisi saat ini atau dilengkapi kemudian.
                </span>
            </div>

            <!-- Navigasi Sub-Tab Bagian (UI05) -->
            <div class="flex border-b border-[#D8DEE6] gap-2 overflow-x-auto text-xs">
                <button
                    type="button"
                    wire:click="setSection('problems')"
                    class="px-3.5 py-2 font-medium border-b-2 transition-colors flex items-center gap-1.5 whitespace-nowrap {{ $activeSection === 'problems' ? 'border-[#2459A6] text-[#2459A6] font-bold' : 'border-transparent text-[#536170] hover:text-[#18212B]' }}"
                >
                    Problem & Corrective Action
                    <span class="text-[10px] bg-[#F0F2F5] px-1.5 py-0.5 rounded text-[#536170]">
                        {{ count($problems) }}
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="setSection('next_actions')"
                    class="px-3.5 py-2 font-medium border-b-2 transition-colors flex items-center gap-1.5 whitespace-nowrap {{ $activeSection === 'next_actions' ? 'border-[#2459A6] text-[#2459A6] font-bold' : 'border-transparent text-[#536170] hover:text-[#18212B]' }}"
                >
                    Next Action
                    <span class="text-[10px] bg-[#F0F2F5] px-1.5 py-0.5 rounded text-[#536170]">
                        {{ count($nextActions) }}
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="setSection('feedback')"
                    class="px-3.5 py-2 font-medium border-b-2 transition-colors flex items-center gap-1.5 whitespace-nowrap {{ $activeSection === 'feedback' ? 'border-[#2459A6] text-[#2459A6] font-bold' : 'border-transparent text-[#536170] hover:text-[#18212B]' }}"
                >
                    Feedback Customer
                    <span class="text-[10px] bg-[#F0F2F5] px-1.5 py-0.5 rounded text-[#536170]">
                        {{ count($feedbacks) }}
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="setSection('attachments')"
                    class="px-3.5 py-2 font-medium border-b-2 transition-colors flex items-center gap-1.5 whitespace-nowrap {{ $activeSection === 'attachments' ? 'border-[#2459A6] text-[#2459A6] font-bold' : 'border-transparent text-[#536170] hover:text-[#18212B]' }}"
                >
                    Lampiran Foto & Dokumen
                    <span class="text-[10px] bg-[#F0F2F5] px-1.5 py-0.5 rounded text-[#536170]">
                        {{ count($attachments) }}
                    </span>
                </button>
            </div>

            <!-- Konten Bagian Aktif -->
            <div class="p-4 bg-[#F8FAFC] border border-[#E8ECF0] rounded-md text-xs">
                @if($activeSection === 'problems')
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#18212B]">Daftar Problem & Corrective Action</span>
                            <span class="text-[11px] text-[#536170]">{{ count($problems) }} entri tercatat</span>
                        </div>
                        <p class="text-[11px] text-[#536170]">
                            Banyak problem per progress; banyak corrective action per problem (P01-T07).
                        </p>
                    </div>
                @elseif($activeSection === 'next_actions')
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#18212B]">Daftar Next Action</span>
                            <span class="text-[11px] text-[#536170]">{{ count($nextActions) }} tindakan tercatat</span>
                        </div>
                        <p class="text-[11px] text-[#536170]">
                            Next Action berdiri pada level progress terpisah dari problem (D07).
                        </p>
                    </div>
                @elseif($activeSection === 'feedback')
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#18212B]">Feedback Customer</span>
                            <span class="text-[11px] text-[#536170]">{{ count($feedbacks) }} tanggapan tercatat</span>
                        </div>
                        <p class="text-[11px] text-[#536170]">
                            Tanggapan dan persetujuan dari pihak customer (D08).
                        </p>
                    </div>
                @elseif($activeSection === 'attachments')
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#18212B]">Lampiran Foto & File</span>
                            <span class="text-[11px] text-[#536170]">{{ count($attachments) }} file terlampir</span>
                        </div>
                        <p class="text-[11px] text-[#536170]">
                            Semua lampiran diberi label [SIMULASI] pada mode prototype.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tombol Aksi Bawah -->
        <div class="pt-4 border-t border-[#E8ECF0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-xs text-[#536170]">
                *Simpan Baru hanya mensyaratkan JO No, Tanggal Aktivitas, dan Activity yang valid.
            </div>

            <div class="flex items-center gap-2">
                <button
                    wire:click="cancel"
                    type="button"
                    class="px-4 py-2 text-xs font-semibold rounded-md border border-[#D8DEE6] bg-white text-[#536170] hover:bg-[#F5F6F8] transition-colors min-h-[44px]"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-6 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] flex items-center gap-1.5"
                    data-testid="submit-bottom-btn"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Progress
                </button>
            </div>
        </div>
    </form>
</div>
