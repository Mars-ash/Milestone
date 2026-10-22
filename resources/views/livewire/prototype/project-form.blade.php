<div class="bg-white rounded-lg border border-[#D8DEE6] shadow-2xs overflow-hidden">
    <!-- Header Form -->
    <div class="px-5 py-4 border-b border-[#D8DEE6] flex items-center justify-between bg-white">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-[#18212B]">
                {{ $projectId ? 'Edit Master Project' : 'Tambah Master Project Baru' }}
            </h2>
            <p class="text-xs text-[#536170] mt-0.5">
                Satu JO No untuk satu Part No dan sebaliknya. Keduanya wajib unik (D01).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button
                wire:click="cancel"
                type="button"
                class="px-3.5 py-1.5 text-xs font-semibold rounded-md border border-[#D8DEE6] bg-white text-[#536170] hover:bg-[#F5F6F8] transition-colors min-h-[38px]"
            >
                Batal
            </button>
            <button
                wire:click="save"
                type="button"
                class="px-4 py-1.5 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[38px] flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Master
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
        <!-- BAGIAN 1: Identitas Utama (Wajib & Unik) -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#2459A6] mb-3 pb-1 border-b border-[#E8ECF0]">
                1. Identitas Utama Project
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- JO No -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">
                        JO No <span class="text-[#C5221F]">*</span>
                    </label>
                    <input
                        wire:model="jo_no"
                        type="text"
                        placeholder="Contoh: 0012/NM/2026"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border @error('jo_no') border-[#C5221F] @else border-[#D8DEE6] @enderror rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] min-h-[40px]"
                        data-testid="input-jo-no"
                    />
                    @error('jo_no')
                        <p class="text-[11px] text-[#C5221F] mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-[#536170] mt-0.5">Wajib dan unik. Nol di depan tetap utuh.</p>
                </div>

                <!-- Part No -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">
                        Part No <span class="text-[#C5221F]">*</span>
                    </label>
                    <input
                        wire:model="part_no"
                        type="text"
                        placeholder="Contoh: 0091-XYZ-01"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border @error('part_no') border-[#C5221F] @else border-[#D8DEE6] @enderror rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] min-h-[40px]"
                        data-testid="input-part-no"
                    />
                    @error('part_no')
                        <p class="text-[11px] text-[#C5221F] mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-[#536170] mt-0.5">Wajib dan unik. Pasangan 1-to-1 dengan JO No.</p>
                </div>

                <!-- Part Name -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">
                        Part Name <span class="text-[#C5221F]">*</span>
                    </label>
                    <input
                        wire:model="part_name"
                        type="text"
                        placeholder="Contoh: BRACKET FRONT LH"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border @error('part_name') border-[#C5221F] @else border-[#D8DEE6] @enderror rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] min-h-[40px]"
                        data-testid="input-part-name"
                    />
                    @error('part_name')
                        <p class="text-[11px] text-[#C5221F] mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-[#536170] mt-0.5">Wajib untuk data baru. Otomatis mengisi progress.</p>
                </div>

                <!-- Customer -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Customer</label>
                    <input
                        wire:model="customer"
                        type="text"
                        placeholder="Nama customer"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- Model -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Model</label>
                    <input
                        wire:model="model"
                        type="text"
                        placeholder="Model produk/kendaraan"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- No Doc -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">No Doc</label>
                    <input
                        wire:model="no_doc"
                        type="text"
                        placeholder="Nomor dokumen master (teks)"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- Date Receive -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Date Receive</label>
                    <input
                        wire:model="date_receive"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>
            </div>
        </div>

        <!-- BAGIAN 2: Spesifikasi Material & Proses Cetak -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#2459A6] mb-3 pb-1 border-b border-[#E8ECF0]">
                2. Spesifikasi Material & Parameter Tooling (D15 Raw Text)
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Material -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Material</label>
                    <input
                        wire:model="material"
                        type="text"
                        placeholder="Contoh: SPCC, SECC"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- Spec -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Spec</label>
                    <input
                        wire:model="spec"
                        type="text"
                        placeholder="Spesifikasi teknis"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- Thickness Raw -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Material Thickness</label>
                    <input
                        wire:model="material_thickness_raw"
                        type="text"
                        placeholder="Contoh: 1,5 mm"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                    <p class="text-[11px] text-[#536170] mt-0.5">Menerima format teks koma/titik utuh.</p>
                </div>

                <!-- Width Raw -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Material Width</label>
                    <input
                        wire:model="material_width_raw"
                        type="text"
                        placeholder="Contoh: 120.0 mm"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- Pitch Raw -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Material Pitch</label>
                    <input
                        wire:model="material_pitch_raw"
                        type="text"
                        placeholder="Contoh: 45.0 mm"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- Process Master -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Process Master</label>
                    <input
                        wire:model="process"
                        type="text"
                        placeholder="Proses master (terpisah dari trial)"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- Die High (DH) -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Die High (DH)</label>
                    <input
                        wire:model="dh"
                        type="text"
                        placeholder="Contoh: 120,5 mm"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- Size -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Size</label>
                    <input
                        wire:model="size"
                        type="text"
                        placeholder="Contoh: 250 x 180"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- Qty -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Qty</label>
                    <input
                        wire:model="qty"
                        type="text"
                        placeholder="Contoh: 1.500 pcs"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>
            </div>
        </div>

        <!-- BAGIAN 3: Jadwal Master Manual (FR03, AT04, D14) -->
        <div>
            <div class="flex items-center justify-between mb-3 pb-1 border-b border-[#E8ECF0]">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#2459A6]">
                    3. Jadwal & Milestone Master (Manual & Independen)
                </h3>
                <span class="text-[11px] text-[#536170]">
                    *Semua tanggal diisi manual oleh admin; tidak di-trigger otomatis oleh kegiatan progress.
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- T0 Target & T0 Actual -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">T0 Target Date</label>
                    <input
                        wire:model="t0_target"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-t0-target"
                    />
                    <p class="text-[11px] text-[#536170] mt-0.5">Target pengiriman sampel pertama.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">T0 Actual Date</label>
                    <input
                        wire:model="t0_actual"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-t0-actual"
                    />
                    <p class="text-[11px] text-[#536170] mt-0.5">Aktual pengiriman sampel pertama (manual).</p>
                </div>

                <!-- Event 1 (PP1) -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Event 1 (PP1)</label>
                    <input
                        wire:model="event_1"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-event-1"
                    />
                </div>

                <!-- Event 2 (PP2) -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Event 2 (PP2)</label>
                    <input
                        wire:model="event_2"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-event-2"
                    />
                </div>

                <!-- Event 3 (MP) -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Event 3 (MP)</label>
                    <input
                        wire:model="event_3"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                        data-testid="input-event-3"
                    />
                </div>

                <!-- Plan Start & Finish -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Plan Start</label>
                    <input
                        wire:model="plan_start"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Plan Finish</label>
                    <input
                        wire:model="plan_finish"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <!-- Actual Start & Finish -->
                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Actual Start</label>
                    <input
                        wire:model="actual_start"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#18212B] mb-1">Actual Finish</label>
                    <input
                        wire:model="actual_finish"
                        type="date"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] min-h-[40px]"
                    />
                </div>
            </div>

            <!-- Catatan / Remark -->
            <div class="mt-4">
                <label class="block text-xs font-semibold text-[#18212B] mb-1">Remark Project</label>
                <textarea
                    wire:model="remark"
                    rows="3"
                    placeholder="Catatan umum mengenai project ini..."
                    class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-[#D8DEE6] rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6]"
                ></textarea>
            </div>
        </div>

        <!-- Tombol Aksi Bawah -->
        <div class="pt-4 border-t border-[#E8ECF0] flex items-center justify-between">
            <span class="text-xs text-[#536170]">
                *23 kolom master aktif disimpan sesuai acuan PRD Bagian 15.
            </span>
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
                    class="px-5 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] flex items-center gap-1.5"
                    data-testid="submit-project-btn"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Master Project
                </button>
            </div>
        </div>
    </form>
</div>
