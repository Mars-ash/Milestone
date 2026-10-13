<div>
    <!-- Slot Header Actions: Reset Button -->
    <x-slot:headerActions>
        <x-ui.button
            wire:click="resetData"
            variant="secondary"
            size="sm"
            class="text-xs text-[#8A5B00] border-[#F8DDA4] hover:bg-[#FEF7E6]"
            title="Reset data ke fixture fiktif awal"
        >
            <svg class="w-3.5 h-3.5 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Reset Fixture
        </x-ui.button>
    </x-slot:headerActions>

    <!-- Notifikasi Flash -->
    @if($flashMessage)
        <div class="mb-5 p-3.5 rounded-md bg-[#EAF7EE] border border-[#B6E6C3] text-[#0E6B38] text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ $flashMessage }}</span>
            </div>
            <button wire:click="dismissFlash" class="text-[#0E6B38] hover:opacity-75 text-sm font-bold">&times;</button>
        </div>
    @endif

    <!-- Ringkasan Global Shell untuk Tab Non-Dashboard -->
    @if($activeTab !== 'dashboard')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg border border-[#D8DEE6] shadow-2xs">
                <div class="text-xs font-medium text-[#536170]">Total Project Master</div>
                <div class="text-2xl font-bold text-[#18212B] mt-1">{{ $stats['total_projects'] ?? 0 }}</div>
                <div class="text-[11px] text-[#536170] mt-0.5">Pasangan unik JO & Part No</div>
            </div>

            <div class="bg-white p-4 rounded-lg border border-[#D8DEE6] shadow-2xs">
                <div class="text-xs font-medium text-[#0E6B38] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#0E6B38]"></span>
                    Progress Open
                </div>
                <div class="text-2xl font-bold text-[#0E6B38] mt-1">{{ $stats['open_count'] ?? 0 }}</div>
                <div class="text-[11px] text-[#536170] mt-0.5">Memerlukan tindak lanjut</div>
            </div>

            <div class="bg-white p-4 rounded-lg border border-[#D8DEE6] shadow-2xs">
                <div class="text-xs font-medium text-[#536170] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#8C9BAE]"></span>
                    Progress Closed
                </div>
                <div class="text-2xl font-bold text-[#18212B] mt-1">{{ $stats['closed_count'] ?? 0 }}</div>
                <div class="text-[11px] text-[#536170] mt-0.5">Ditutup manual oleh admin</div>
            </div>

            <div class="bg-white p-4 rounded-lg border border-[#D8DEE6] shadow-2xs">
                <div class="text-xs font-medium text-[#8A5B00] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#8A5B00]"></span>
                    Draft Tersimpan
                </div>
                <div class="text-2xl font-bold text-[#8A5B00] mt-1">{{ $stats['draft_count'] ?? 0 }}</div>
                <div class="text-[11px] text-[#536170] mt-0.5">Isian kerja belum final (D11)</div>
            </div>
        </div>
    @endif

    <!-- Tampilan Tab Berdasarkan State ($activeTab) -->

    @if($activeTab === 'dashboard')
        <!-- TAB: DASHBOARD (P01-T04) -->
        <livewire:prototype.dashboard />

    @elseif($activeTab === 'projects')
        <!-- TAB: PROJECT MASTER (P01-T05) -->
        @if(request()->query('action') === 'new')
            <!-- Mode Tambah Project Baru -->
            <livewire:prototype.project-form />
        @elseif(request()->query('edit'))
            <!-- Mode Edit Project -->
            <livewire:prototype.project-form :projectId="request()->query('edit')" />
        @elseif(request()->query('detail'))
            <!-- Mode Detail Project -->
            <livewire:prototype.project-detail :projectId="request()->query('detail')" />
        @else
            <!-- Mode Daftar Project Master -->
            <div class="bg-white rounded-lg border border-[#D8DEE6] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-[#D8DEE6] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-[#18212B]">Daftar Project Master</h2>
                        <p class="text-xs text-[#536170] mt-0.5">Satu JO No untuk satu Part No dan sebaliknya. Keduanya wajib unik (D01).</p>
                    </div>
                    <a
                        href="?tab=projects&action=new"
                        class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] sm:min-h-0"
                        data-testid="add-project-btn"
                    >
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Project
                    </a>
                </div>

                <!-- Table Desktop (>= 768px) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm text-[#18212B]" data-testid="project-table">
                        <thead class="bg-[#F5F6F8] border-b border-[#D8DEE6] text-xs font-semibold text-[#536170] uppercase">
                            <tr>
                                <th class="px-4 py-3 w-36">JO No</th>
                                <th class="px-4 py-3 w-36">Part No</th>
                                <th class="px-4 py-3">Part Name</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Model</th>
                                <th class="px-4 py-3 w-32">T0 Target / Act</th>
                                <th class="px-4 py-3 w-36 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D8DEE6] bg-white">
                            @foreach($projects as $p)
                                <tr class="hover:bg-[#F9FAFC] transition-colors" data-testid="project-row" data-id="{{ $p['id'] }}">
                                    <td class="px-4 py-3 font-bold text-[#2459A6] whitespace-nowrap">{{ $p['jo_no'] }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-[#536170] whitespace-nowrap">{{ $p['part_no'] }}</td>
                                    <td class="px-4 py-3 font-medium text-[#18212B]">{{ $p['part_name'] }}</td>
                                    <td class="px-4 py-3 text-[#536170]">{{ $p['customer'] ?: '-' }}</td>
                                    <td class="px-4 py-3 text-xs text-[#536170]">{{ $p['model'] ?: '-' }}</td>
                                    <td class="px-4 py-3 text-xs whitespace-nowrap">
                                        <div>Plan: {{ !empty($p['t0_target']) ? \Illuminate\Support\Carbon::parse($p['t0_target'])->format('d M Y') : (!empty($p['t0_target_date']) ? \Illuminate\Support\Carbon::parse($p['t0_target_date'])->format('d M Y') : '-') }}</div>
                                        <div class="text-[#0E6B38]">Act: {{ !empty($p['t0_actual']) ? \Illuminate\Support\Carbon::parse($p['t0_actual'])->format('d M Y') : (!empty($p['t0_actual_date']) ? \Illuminate\Support\Carbon::parse($p['t0_actual_date'])->format('d M Y') : '-') }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a
                                                href="?tab=projects&detail={{ $p['id'] }}"
                                                class="px-2.5 py-1 text-xs font-semibold rounded border border-[#D8DEE6] text-[#2459A6] hover:bg-[#F0F4FA]"
                                            >
                                                Detail
                                            </a>
                                            <a
                                                href="?tab=projects&edit={{ $p['id'] }}"
                                                class="px-2.5 py-1 text-xs font-semibold rounded border border-[#D8DEE6] text-[#536170] hover:bg-[#F5F6F8]"
                                            >
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Summary (< 768px / 390px) -->
                <div class="block sm:hidden divide-y divide-[#D8DEE6]">
                    @foreach($projects as $p)
                        <div class="p-4 bg-white space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-[#2459A6]">{{ $p['jo_no'] }}</span>
                                <span class="text-xs font-mono text-[#536170]">{{ $p['part_no'] }}</span>
                            </div>
                            <div class="text-sm font-semibold text-[#18212B]">{{ $p['part_name'] }}</div>
                            <div class="text-xs text-[#536170]">Customer: {{ $p['customer'] ?: '-' }} &bull; Model: {{ $p['model'] ?: '-' }}</div>
                            <div class="text-xs text-[#536170] flex justify-between bg-[#F5F6F8] p-2 rounded">
                                <span>T0 Plan: {{ $p['t0_target'] ?? $p['t0_target_date'] ?? '-' }}</span>
                                <span class="text-[#0E6B38]">T0 Act: {{ $p['t0_actual'] ?? $p['t0_actual_date'] ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-end gap-2 pt-1">
                                <a
                                    href="?tab=projects&detail={{ $p['id'] }}"
                                    class="px-3 py-1.5 text-xs font-semibold rounded border border-[#D8DEE6] text-[#2459A6] bg-white min-h-[44px] flex items-center"
                                >
                                    Detail
                                </a>
                                <a
                                    href="?tab=projects&edit={{ $p['id'] }}"
                                    class="px-3 py-1.5 text-xs font-semibold rounded border border-[#D8DEE6] text-[#536170] bg-white min-h-[44px] flex items-center"
                                >
                                    Edit
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @elseif($activeTab === 'form')
        <!-- TAB: INPUT PROGRESS (P01-T06: 1 Form Semua Activity) -->
        <livewire:prototype.progress-form :projectId="request()->query('project_id')" />

    @elseif($activeTab === 'progresses')
        <!-- TAB: PROGRESS LIST -->
        <div class="bg-white rounded-lg border border-[#D8DEE6] overflow-hidden shadow-2xs">
            <div class="px-5 py-4 border-b border-[#D8DEE6] flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#18212B]">Seluruh Riwayat Progress</h2>
                    <p class="text-xs text-[#536170] mt-0.5">Mencakup 5 Activity dan status Open/Closed.</p>
                </div>
            </div>

            <!-- Table Desktop -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm text-[#18212B]">
                    <thead class="bg-[#F5F6F8] border-b border-[#D8DEE6] text-xs font-semibold text-[#536170] uppercase">
                        <tr>
                            <th class="px-4 py-3">JO No / Part</th>
                            <th class="px-4 py-3">Activity</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Uraian Catatan</th>
                            <th class="px-4 py-3">Detail Temuan</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D8DEE6]">
                        @foreach($progresses as $prog)
                            @php $proj = \App\Prototype\FakeStore::findProject($prog['project_id']); @endphp
                            <tr class="hover:bg-[#F9FAFC] transition-colors">
                                <td class="px-4 py-3 min-h-[44px]">
                                    <div class="font-bold text-[#2459A6]">{{ $proj['jo_no'] ?? '-' }}</div>
                                    <div class="text-xs text-[#536170]">{{ $proj['part_name'] ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :type="$prog['activity']" :label="$prog['activity']" />
                                </td>
                                <td class="px-4 py-3 text-xs text-[#536170] whitespace-nowrap">
                                    {{ $prog['activity_date'] }}
                                </td>
                                <td class="px-4 py-3 text-xs max-w-xs text-[#18212B]">
                                    {{ Str::limit($prog['notes'], 120) }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <div class="space-y-0.5">
                                        @if(!empty($prog['problems']))
                                            <div class="text-[#C0392B] font-medium">{{ count($prog['problems']) }} Problem</div>
                                        @endif
                                        @if(!empty($prog['next_actions']))
                                            <div class="text-[#2459A6]">{{ count($prog['next_actions']) }} Next Action</div>
                                        @endif
                                        @if(!empty($prog['feedbacks']))
                                            <div class="text-[#536170]">{{ count($prog['feedbacks']) }} Feedback</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :type="$prog['status']" :label="$prog['status']" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Summary (< 768px / 390px) -->
            <div class="block sm:hidden divide-y divide-[#D8DEE6]">
                @foreach($progresses as $prog)
                    @php $proj = \App\Prototype\FakeStore::findProject($prog['project_id']); @endphp
                    <div class="p-4 bg-white space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-[#2459A6]">{{ $proj['jo_no'] ?? '-' }}</span>
                            <div class="flex items-center gap-1.5">
                                <x-ui.badge :type="$prog['activity']" :label="$prog['activity']" />
                                <x-ui.badge :type="$prog['status']" :label="$prog['status']" />
                            </div>
                        </div>
                        <div class="text-xs text-[#536170]">{{ $proj['part_name'] ?? '-' }} &bull; {{ $prog['activity_date'] }}</div>
                        <div class="text-xs text-[#18212B] bg-[#F5F6F8] p-2 rounded">
                            {{ Str::limit($prog['notes'], 100) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    @elseif($activeTab === 'drafts')
        <!-- TAB: DRAFT (P01-T08: Manajemen Draft Mandiri) -->
        <livewire:prototype.draft-list />

    @elseif($activeTab === 'components')
        <!-- TAB: PUSTAKA KOMPONEN UI -->
        <div class="space-y-8">
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6]">
                <h2 class="text-base font-bold text-[#18212B] mb-1">Pustaka Komponen UI Terstandardisasi</h2>
                <p class="text-xs text-[#536170]">Komponen dasar yang mematuhi PRD Bagian 6 (UI01–UI12), kontras teks, keyboard accessibility, dan responsivitas mobile 390px.</p>
            </div>

            <!-- Bagian 1: Tombol -->
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] space-y-4">
                <h3 class="text-xs font-bold text-[#536170] uppercase tracking-wider">1. Tombol (Buttons)</h3>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button variant="primary">Simpan (Primary)</x-ui.button>
                    <x-ui.button variant="secondary">Tambah Problem (Secondary)</x-ui.button>
                    <x-ui.button variant="outline">Lihat Detail (Outline)</x-ui.button>
                    <x-ui.button variant="danger">Close (Danger Action)</x-ui.button>
                    <x-ui.button variant="ghost">Batal (Ghost)</x-ui.button>
                    <x-ui.button variant="primary" disabled>Disabled</x-ui.button>
                </div>
            </div>

            <!-- Bagian 2: Badges -->
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] space-y-4">
                <h3 class="text-xs font-bold text-[#536170] uppercase tracking-wider">2. Label & Status Badges (UI04)</h3>
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge type="open" label="Open" />
                    <x-ui.badge type="closed" label="Closed" />
                    <x-ui.badge type="draft" label="Draft" />
                    <x-ui.badge type="trial" label="Trial" />
                    <x-ui.badge type="sent-sample" label="Sent Sample" />
                    <x-ui.badge type="send-document" label="Send Document" />
                    <x-ui.badge type="audit" label="Audit" />
                    <x-ui.badge type="others" label="Others" />
                    <x-ui.badge type="simulation" label="[SIMULASI]" />
                </div>
            </div>

            <!-- Bagian 3: Form Inputs -->
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] space-y-4">
                <h3 class="text-xs font-bold text-[#536170] uppercase tracking-wider">3. Field Input Berlabel & Pesan Error (UI06, UI07)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-ui.input
                        label="JO No (Dropdown / Input)"
                        name="sample_jo"
                        value="0012/NM/2026"
                        required
                        helper="Format JO teks mempertahankan angka nol di depan"
                    />
                    <x-ui.input
                        label="PIC Tindakan (Teks Bebas)"
                        name="sample_pic"
                        value="Budi Santoso (Fiktif)"
                        helper="Nama PIC bebas, bukan akun user"
                    />
                    <x-ui.input
                        label="Die High (DH)"
                        name="sample_dh"
                        value="120,5 mm"
                        error="Pilih JO sebelum menyimpan progress"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <x-ui.select label="Activity (D03)" name="sample_activity" required>
                        <option value="Trial">Trial</option>
                        <option value="Sent Sample">Sent Sample</option>
                        <option value="Send Document">Send Document</option>
                        <option value="Audit">Audit</option>
                        <option value="Others">Others</option>
                    </x-ui.select>

                    <x-ui.textarea label="Uraian Kegiatan / Notes" name="sample_notes" rows="2">
                        Trial mold di mesin press. Ditemukan burry 1,5 mm pada flange kiri.
                    </x-ui.textarea>
                </div>
            </div>

            <!-- Bagian 4: Status Autosave -->
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] space-y-4">
                <h3 class="text-xs font-bold text-[#536170] uppercase tracking-wider">4. Indikator Autosave Status (UI07)</h3>
                <div class="flex flex-wrap items-center gap-6 p-3 bg-[#F5F6F8] rounded-md">
                    <x-ui.autosave-status status="saving" />
                    <x-ui.autosave-status status="saved" time="14:25" />
                    <x-ui.autosave-status status="unsaved" />
                    <x-ui.autosave-status status="error" />
                </div>
            </div>

            <!-- Bagian 5: Thumbnail & Lightbox -->
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] space-y-4">
                <h3 class="text-xs font-bold text-[#536170] uppercase tracking-wider">5. Thumbnail Foto Simulasi (UI10)</h3>
                <div class="flex flex-wrap items-center gap-4">
                    <x-ui.thumbnail
                        src="/images/simulasi1.jpg"
                        filename="burry_flange.jpg"
                        caption="Burry 1,5 mm pada flange sisi kiri"
                        :isSimulation="true"
                    />
                    <x-ui.thumbnail
                        src="/images/simulasi2.jpg"
                        filename="cmm_profile.png"
                        caption="Grafik profil dimensi CMM"
                        :isSimulation="true"
                    />
                </div>
            </div>

            <!-- Bagian 6: Modal Dialog -->
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] space-y-4">
                <h3 class="text-xs font-bold text-[#536170] uppercase tracking-wider">6. Dialog Modal Aksesibel (Keyboard Esc & Trap)</h3>
                <div>
                    <x-ui.button
                        variant="secondary"
                        size="sm"
                        x-data
                        x-on:click="$dispatch('open-modal', 'demo-modal')"
                    >
                        Buka Demo Modal Konfirmasi
                    </x-ui.button>

                    <x-ui.modal name="demo-modal" title="Konfirmasi Tindakan">
                        <p class="text-xs text-[#536170] mb-4">
                            Apakah Anda yakin ingin melakukan tindakan ini? Modal ini dapat ditutup dengan tombol Escape atau tombol Batal.
                        </p>
                        <div class="flex justify-end gap-2">
                            <x-ui.button
                                variant="secondary"
                                size="sm"
                                x-on:click="$dispatch('close-modal', 'demo-modal')"
                            >
                                Batal
                            </x-ui.button>
                            <x-ui.button
                                variant="primary"
                                size="sm"
                                x-on:click="$dispatch('close-modal', 'demo-modal')"
                            >
                                Konfirmasi
                            </x-ui.button>
                        </div>
                    </x-ui.modal>
                </div>
            </div>
        </div>
    @endif
</div>
