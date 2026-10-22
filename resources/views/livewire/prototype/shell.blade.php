@php
    $activeNav = match($activeTab) {
        'projects' => 'project',
        'progresses', 'form' => 'progress',
        'drafts' => 'draft',
        default => 'dashboard',
    };

    $label = match($activeTab) {
        'projects' => 'Project',
        'progresses', 'form' => 'Progress',
        'drafts' => 'Draft',
        'components' => 'Pustaka Komponen UI',
        default => 'Dashboard',
    };

    $isEditor = ($activeTab === 'form');
    $draftCount = count($drafts ?? []);
@endphp

<div>
    <button class="scrim" @click="mobileMenu = false" aria-label="Tutup navigasi"></button>
    <aside class="sidebar" aria-label="Navigasi utama">
        <a class="brand" href="{{ route('prototype.index', ['tab' => 'dashboard']) }}" @click="mobileMenu = false">
            <span>
                <span class="brand-name">New Model</span>
                <span class="brand-sub" style="display:block">PROGRESS TRACKER</span>
            </span>
        </a>
        <div class="nav-label">WORKSPACE</div>
        <nav class="nav">
            <a href="{{ route('prototype.index', ['tab' => 'dashboard']) }}"
                class="nav-item {{ $activeNav === 'dashboard' ? 'active' : '' }}"
                @if($activeNav === 'dashboard') aria-current="page" @endif
                @click="mobileMenu = false">
                <x-ui.icon name="dashboard" /><span>Dashboard</span>
            </a>
            <a href="{{ route('prototype.index', ['tab' => 'projects']) }}"
                class="nav-item {{ $activeNav === 'project' ? 'active' : '' }}"
                @if($activeNav === 'project') aria-current="page" @endif
                @click="mobileMenu = false">
                <x-ui.icon name="project" /><span>Project</span>
            </a>
            <a href="{{ route('prototype.index', ['tab' => 'progresses']) }}"
                class="nav-item {{ $activeNav === 'progress' ? 'active' : '' }}"
                @if($activeNav === 'progress') aria-current="page" @endif
                @click="mobileMenu = false">
                <x-ui.icon name="progress" /><span>Progress</span>
            </a>
            <a href="{{ route('prototype.index', ['tab' => 'drafts']) }}"
                class="nav-item {{ $activeNav === 'draft' ? 'active' : '' }}"
                @if($activeNav === 'draft') aria-current="page" @endif
                @click="mobileMenu = false">
                <x-ui.icon name="draft" /><span>Draft</span>
                <span class="nav-count">{{ $draftCount }}</span>
            </a>
        </nav>
    </aside>

    <div class="workspace">
        <header class="topbar">
            <div class="breadcrumb">
                <button
                    type="button"
                    class="icon-btn mobile-menu"
                    aria-label="Buka navigasi"
                    :aria-expanded="mobileMenu"
                    @click="mobileMenu = !mobileMenu"
                >
                    <x-ui.icon name="menu" />
                </button>
                <span>Workspace</span>
                <span class="slash">/</span>
                <strong>{{ $label }}</strong>
            </div>
            <div class="topbar-right">
                <span class="topbar-date">{{ now('Asia/Jakarta')->locale('id')->isoFormat('MMMM YYYY') }}</span>
            </div>
        </header>

        <main id="main" class="main {{ $isEditor ? 'editor-main' : '' }}" tabindex="-1">
            <!-- Notifikasi Flash -->
            @if($flashMessage)
                <div class="mb-5 p-3.5 rounded-md bg-[#EAF7EE] border border-[#B6E6C3] text-[#0E6B38] text-xs font-medium flex items-center justify-between" role="status">
                    <div class="flex items-center gap-2">
                        <x-ui.icon name="check" class="w-4 h-4 text-[#0E6B38]" />
                        <span>{{ $flashMessage }}</span>
                    </div>
                    <button wire:click="dismissFlash" class="text-[#0E6B38] hover:opacity-75 text-sm font-bold">&times;</button>
                </div>
            @endif

    <!-- Tampilan Tab Berdasarkan State ($activeTab) -->

    @if($activeTab === 'dashboard')
        <!-- TAB: DASHBOARD (P01-T04) -->
        <livewire:prototype.dashboard />

    @elseif($activeTab === 'projects')
        <!-- TAB: PROJECT MASTER (P01-T05) -->
        @if(request()->query('detail'))
            <!-- Mode Detail Project -->
            <livewire:prototype.project-detail :projectId="request()->query('detail')" />
        @else
            <!-- Mode Daftar Project Master -->
            <div class="page-head">
                <div>
                    <h1>Project Master</h1>
                </div>
                <a
                    href="?tab=projects&action=new"
                    class="btn primary"
                    data-testid="add-project-btn"
                >
                    <x-ui.icon name="plus" />
                    Tambah project
                </a>
            </div>

            <section class="panel">
                <div class="panel-head">
                    <h2>{{ count($projects) }} project</h2>
                </div>
                <table class="data-table project-table" data-testid="project-table">
                    <thead>
                        <tr>
                            <th scope="col" style="width:35%">Project / Part</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Model</th>
                            <th scope="col">T0 Target</th>
                            <th scope="col">Progress</th>
                            <th scope="col" style="width:50px"><span class="sr-only">Buka project</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $p)
                            @php
                                $t0Target = !empty($p['t0_target']) ? $p['t0_target'] : (!empty($p['t0_target_date']) ? $p['t0_target_date'] : null);
                                $t0TargetFormatted = !empty($t0Target) ? \App\Support\UiFormat::date($t0Target) : 'Belum diisi';
                                $progressCount = count(\App\Prototype\FakeStore::getProgresses(['project_id' => $p['id'], 'status' => 'all']));
                            @endphp
                            <tr data-testid="project-row" data-id="{{ $p['id'] }}">
                                <td class="part-col">
                                    <a class="row-title" href="?tab=projects&detail={{ $p['id'] }}" data-testid="project-detail-link">{{ $p['part_name'] }}</a>
                                    <span class="row-sub">{{ $p['part_no'] }} / <span class="jo-code">{{ $p['jo_no'] }}</span></span>
                                </td>
                                <td class="project-customer" data-label="Customer">{{ $p['customer'] ?: '—' }}</td>
                                <td class="project-model" data-label="Model"><span class="model-code">{{ $p['model'] ?: '—' }}</span></td>
                                <td class="project-target" data-label="T0 Target">{{ $t0TargetFormatted }}</td>
                                <td class="project-count" data-label="Progress">{{ $progressCount }} kegiatan</td>
                                <td class="go-col">
                                    <a href="?tab=projects&detail={{ $p['id'] }}" class="icon-btn" aria-label="Buka project {{ $p['jo_no'] }}">
                                        <x-ui.icon name="right" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

        @endif
        @if(request()->query('action') === 'new')
            <livewire:prototype.project-form />
        @elseif(request()->query('edit'))
            <livewire:prototype.project-form :projectId="request()->query('edit')" />
        @endif

    @elseif($activeTab === 'form')
        <!-- TAB: INPUT PROGRESS (P01-T06: 1 Form Semua Activity) -->
        <livewire:prototype.progress-form :projectId="request()->query('project_id')" :progressId="request()->query('id')" />

    @elseif($activeTab === 'progresses')
        @if(request()->query('detail') || request()->query('id'))
            <!-- Mode Detail Progress (P01-T09: Close & Reopen) -->
            <livewire:prototype.progress-detail :progressId="request()->query('detail') ?? request()->query('id')" />
        @else
            <!-- TAB: PROGRESS LIST (Semua progress) -->
            <livewire:prototype.dashboard :isDashboard="false" initialStatus="all" />
        @endif

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
        </main>
    </div>
</div>

