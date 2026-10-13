<div class="space-y-6">
    <!-- Header Aksi Cepat & Navigasi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[#18212B] tracking-tight">Dashboard & Daftar Kerja</h1>
            <p class="text-xs sm:text-sm text-[#536170] mt-0.5">
                Pantau status kegiatan new model, tindak lanjut Open, dan kelanjutan draft tersimpan.
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if(count($drafts) > 0)
                <a
                    href="?tab=drafts"
                    class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-semibold rounded-md border border-[#F8DDA4] bg-[#FEF7E6] text-[#8A5B00] hover:bg-[#FDF0D0] transition-colors min-h-[44px] sm:min-h-0"
                    title="Lanjutkan pengisian draft yang belum disimpan final"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Lanjutkan Draft ({{ count($drafts) }})
                </a>
            @endif

            <a
                href="?tab=form"
                class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] sm:min-h-0"
            >
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Progress
            </a>
        </div>
    </div>

    <!-- 3 Angka Ringkas Global (FR17, AT23, UI03) -->
    <div class="bg-white p-4 sm:p-5 rounded-lg border border-[#D8DEE6] shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-3 mb-3 border-b border-[#E8ECF0]">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-[#2459A6]">Ringkasan Keseluruhan</span>
                <span class="text-[11px] text-[#536170] bg-[#F0F4FA] px-2 py-0.5 rounded font-medium">
                    Periode: {{ $globalStats['current_month_label'] }}
                </span>
            </div>
            <span class="text-[11px] text-[#536170]">
                *Angka ringkasan bersifat global dan tidak terpengaruh filter daftar kerja di bawah.
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <!-- 1. Progress Open Tersimpan -->
            <div class="p-3.5 sm:p-4 rounded-md bg-[#F4FAF6] border border-[#B6E6C3] flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-[#0E6B38] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#0E6B38]"></span>
                        Progress Open
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-[#0E6B38] mt-1 tracking-tight" data-testid="stat-open">
                        {{ $globalStats['open_count'] }}
                    </div>
                    <div class="text-[11px] text-[#536170] mt-0.5">Memerlukan tindak lanjut aktif</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-[#E0F2E9] flex items-center justify-center text-[#0E6B38] shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <!-- 2. Draft Aktif -->
            <div class="p-3.5 sm:p-4 rounded-md bg-[#FEFBF2] border border-[#F8DDA4] flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-[#8A5B00] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#8A5B00]"></span>
                        Draft Tersimpan
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-[#8A5B00] mt-1 tracking-tight" data-testid="stat-draft">
                        {{ $globalStats['draft_count'] }}
                    </div>
                    <div class="text-[11px] text-[#536170] mt-0.5">Isian kerja belum final (D11)</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-[#FDF0D0] flex items-center justify-center text-[#8A5B00] shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>

            <!-- 3. Aktivitas Bulan Berjalan -->
            <div class="p-3.5 sm:p-4 rounded-md bg-[#F0F5FA] border border-[#B3CCE9] flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-[#2459A6] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#2459A6]"></span>
                        Aktivitas Bulan Berjalan
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-[#2459A6] mt-1 tracking-tight" data-testid="stat-month">
                        {{ $globalStats['current_month_count'] }}
                    </div>
                    <div class="text-[11px] text-[#536170] mt-0.5">
                        {{ $globalStats['current_month_label'] }} (Open & Closed)
                    </div>
                </div>
                <div class="w-10 h-10 rounded-full bg-[#E0ECF8] flex items-center justify-center text-[#2459A6] shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Filter & Pencarian Instan (FR18, AT24) -->
    <div class="bg-white p-4 sm:p-5 rounded-lg border border-[#D8DEE6] shadow-2xs space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#2459A6]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#18212B]">Filter & Pencarian Daftar Kerja</h3>
            </div>
            @if($isFiltered)
                <button
                    wire:click="clearFilters"
                    type="button"
                    class="text-xs font-medium text-[#C5221F] hover:text-[#9B1A17] flex items-center gap-1 min-h-[36px] px-2 rounded hover:bg-[#FDF2F2] transition-colors"
                    data-testid="clear-filter-btn"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Bersihkan Filter
                </button>
            @endif
        </div>

        <!-- Baris 1: Pencarian Keyword, Status, Pengurutan -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <div class="md:col-span-6">
                <label class="block text-xs font-semibold text-[#536170] mb-1">Pencarian Cepat</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8C9BAE]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Cari JO, Part No, Part Name, Customer, Catatan, Masalah..."
                        class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-[#FFFFFF] border border-[#D8DEE6] rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] transition-colors min-h-[42px]"
                        data-testid="search-input"
                    />
                </div>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-[#536170] mb-1">Status Kegiatan</label>
                <select
                    wire:model.live="status"
                    class="w-full px-3 py-2 text-xs sm:text-sm bg-[#FFFFFF] border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] transition-colors min-h-[42px]"
                    data-testid="status-select"
                >
                    <option value="Open">Open (Tindak Lanjut)</option>
                    <option value="Closed">Closed (Ditutup)</option>
                    <option value="all">Semua Status</option>
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-[#536170] mb-1">Urutan Tanggal</label>
                <select
                    wire:model.live="sort"
                    class="w-full px-3 py-2 text-xs sm:text-sm bg-[#FFFFFF] border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] transition-colors min-h-[42px]"
                    data-testid="sort-select"
                >
                    <option value="oldest">Terlama Lebih Dahulu (Default)</option>
                    <option value="newest">Terbaru Lebih Dahulu</option>
                </select>
            </div>
        </div>

        <!-- Baris 2: Customer, Activity, Filter PIC & Peran PIC -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-1 border-t border-[#F0F2F5]">
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-[#536170] mb-1">Customer</label>
                <select
                    wire:model.live="customer"
                    class="w-full px-3 py-2 text-xs bg-[#FFFFFF] border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] transition-colors min-h-[38px]"
                    data-testid="customer-select"
                >
                    <option value="">Semua Customer</option>
                    @foreach($customers as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-[#536170] mb-1">Activity</label>
                <select
                    wire:model.live="activity"
                    class="w-full px-3 py-2 text-xs bg-[#FFFFFF] border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] transition-colors min-h-[38px]"
                    data-testid="activity-select"
                >
                    <option value="">Semua Activity</option>
                    <option value="Trial">Trial</option>
                    <option value="Sent Sample">Sent Sample</option>
                    <option value="Send Document">Send Document</option>
                    <option value="Audit">Audit</option>
                    <option value="Others">Others</option>
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-[#536170] mb-1">Peran PIC</label>
                <select
                    wire:model.live="picRole"
                    class="w-full px-3 py-2 text-xs bg-[#FFFFFF] border border-[#D8DEE6] rounded-md text-[#18212B] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] transition-colors min-h-[38px]"
                    data-testid="pic-role-select"
                >
                    <option value="progress">PIC Progress (Default)</option>
                    <option value="corrective_action">PIC Corrective Action</option>
                    <option value="next_action">PIC Next Action</option>
                    <option value="feedback">PIC Feedback Customer</option>
                    <option value="all">Semua Peran PIC</option>
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-[#536170] mb-1">Nama PIC (Teks Bebas)</label>
                <input
                    wire:model.live.debounce.300ms="pic"
                    type="text"
                    placeholder="Ketik nama PIC..."
                    class="w-full px-3 py-2 text-xs bg-[#FFFFFF] border border-[#D8DEE6] rounded-md text-[#18212B] placeholder-[#8C9BAE] focus:outline-none focus:ring-1 focus:ring-[#2459A6] focus:border-[#2459A6] transition-colors min-h-[38px]"
                    data-testid="pic-input"
                />
            </div>
        </div>
    </div>

    <!-- Daftar Kerja: Tabel Desktop & Kartu Mobile (FR18, UI08) -->
    <div class="bg-white rounded-lg border border-[#D8DEE6] overflow-hidden shadow-2xs">
        <div class="px-5 py-4 border-b border-[#D8DEE6] flex items-center justify-between bg-white">
            <div class="flex items-center gap-2">
                <h2 class="text-sm sm:text-base font-bold text-[#18212B]">
                    Daftar Kerja
                    <span class="text-xs font-normal text-[#536170]">
                        ({{ count($progresses) }} progress ditampilkan)
                    </span>
                </h2>
                @if($isFiltered)
                    <span class="text-[11px] font-medium bg-[#FEF7E6] text-[#8A5B00] border border-[#F8DDA4] px-2 py-0.5 rounded">
                        Filter Aktif
                    </span>
                @endif
            </div>

            <div class="text-xs text-[#536170]">
                Urutan: <span class="font-medium text-[#18212B]">{{ $sort === 'newest' ? 'Terbaru' : 'Terlama' }}</span>
            </div>
        </div>

        @if(count($progresses) > 0)
            <!-- DESKTOP TABLE VIEW (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm text-[#18212B]" data-testid="progress-table">
                    <thead class="bg-[#F5F6F8] border-b border-[#D8DEE6] text-xs font-semibold text-[#536170]">
                        <tr>
                            <th class="px-4 py-3 w-32">Tanggal</th>
                            <th class="px-4 py-3 w-44">JO & Part No</th>
                            <th class="px-4 py-3">Part Name & Customer</th>
                            <th class="px-4 py-3 w-32">Activity</th>
                            <th class="px-4 py-3 w-36">PIC Progress</th>
                            <th class="px-4 py-3">Next Action</th>
                            <th class="px-4 py-3 w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D8DEE6] bg-white">
                        @foreach($progresses as $prog)
                            @php
                                $proj = $prog['project'] ?? \App\Prototype\FakeStore::findProject($prog['project_id']);
                                $nextCount = count($prog['next_actions'] ?? []);
                                $firstNext = $nextCount > 0 ? $prog['next_actions'][0]['description'] : null;
                            @endphp
                            <tr class="hover:bg-[#F9FAFC] transition-colors" data-testid="progress-row" data-id="{{ $prog['id'] }}">
                                <!-- Tanggal (FR18: label Tanggal belum tersedia jika null) -->
                                <td class="px-4 py-3 font-medium whitespace-nowrap">
                                    @if(!empty($prog['activity_date']))
                                        <div class="text-[#18212B]">{{ \Illuminate\Support\Carbon::parse($prog['activity_date'])->format('d M Y') }}</div>
                                        <div class="text-[11px] text-[#536170]">{{ $prog['id'] }}</div>
                                    @else
                                        <span class="text-[#8C9BAE] italic">Tanggal belum tersedia</span>
                                        <div class="text-[11px] text-[#536170]">{{ $prog['id'] }}</div>
                                    @endif
                                </td>

                                <!-- JO & Part No -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-bold text-[#2459A6]">{{ $proj['jo_no'] ?? '-' }}</div>
                                    <div class="font-mono text-xs text-[#536170]">{{ $proj['part_no'] ?? '-' }}</div>
                                </td>

                                <!-- Part Name & Customer -->
                                <td class="px-4 py-3">
                                    <div class="font-medium text-[#18212B]">{{ $proj['part_name'] ?? '-' }}</div>
                                    <div class="text-xs text-[#536170]">{{ $proj['customer'] ?? '-' }}</div>
                                </td>

                                <!-- Activity & Status -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-ui.badge :variant="match($prog['activity']) {
                                        'Trial' => 'primary',
                                        'Sent Sample' => 'info',
                                        'Send Document' => 'warning',
                                        'Audit' => 'success',
                                        default => 'neutral',
                                    }">
                                        {{ $prog['activity'] }}
                                    </x-ui.badge>
                                    <div class="mt-1">
                                        @if(strtolower($prog['status']) === 'open')
                                            <span class="inline-flex items-center text-[11px] font-semibold text-[#0E6B38]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#0E6B38] mr-1"></span>
                                                Open
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-[11px] font-medium text-[#8C9BAE]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#8C9BAE] mr-1"></span>
                                                Closed
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- PIC Progress -->
                                <td class="px-4 py-3">
                                    <div class="text-xs font-medium text-[#18212B]">
                                        {{ ($prog['pic_progress'] ?? null) ?: '-' }}
                                    </div>
                                </td>

                                <!-- Next Action Ringkas -->
                                <td class="px-4 py-3">
                                    @if($nextCount > 0)
                                        <div class="text-xs text-[#18212B] line-clamp-2">
                                            <span class="font-semibold text-[#2459A6]">[{{ $nextCount }} tindakan]</span>
                                            {{ $firstNext }}
                                        </div>
                                    @else
                                        <span class="text-xs text-[#8C9BAE]">-</span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <a
                                        href="?tab=progresses&id={{ $prog['id'] }}"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded border border-[#D8DEE6] bg-white text-[#2459A6] hover:bg-[#F0F4FA] hover:border-[#B3CCE9] transition-colors"
                                    >
                                        Buka Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- MOBILE CARD VIEW (< 768px / 390px responsive) -->
            <div class="md:hidden divide-y divide-[#E8ECF0]" data-testid="progress-cards">
                @foreach($progresses as $prog)
                    @php
                        $proj = $prog['project'] ?? \App\Prototype\FakeStore::findProject($prog['project_id']);
                        $nextCount = count($prog['next_actions'] ?? []);
                        $firstNext = $nextCount > 0 ? $prog['next_actions'][0]['description'] : null;
                    @endphp
                    <div class="p-4 space-y-2.5 bg-white hover:bg-[#F9FAFC] transition-colors" data-testid="progress-card-item">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm text-[#2459A6]">{{ $proj['jo_no'] ?? '-' }}</span>
                                <span class="text-xs font-mono text-[#536170] ml-1.5">{{ $proj['part_no'] ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <x-ui.badge :variant="match($prog['activity']) {
                                    'Trial' => 'primary',
                                    'Sent Sample' => 'info',
                                    'Send Document' => 'warning',
                                    'Audit' => 'success',
                                    default => 'neutral',
                                }">
                                    {{ $prog['activity'] }}
                                </x-ui.badge>
                                @if(strtolower($prog['status']) === 'open')
                                    <span class="text-[11px] font-bold text-[#0E6B38] bg-[#EAF7EE] px-1.5 py-0.5 rounded">
                                        Open
                                    </span>
                                @else
                                    <span class="text-[11px] font-medium text-[#536170] bg-[#F0F2F5] px-1.5 py-0.5 rounded">
                                        Closed
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-[#18212B]">{{ $proj['part_name'] ?? '-' }}</div>
                            <div class="text-xs text-[#536170]">{{ $proj['customer'] ?? '-' }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs pt-1 text-[#536170]">
                            <div>
                                <span class="text-[11px] block text-[#8C9BAE]">Tanggal:</span>
                                <span class="font-medium text-[#18212B]">
                                    {{ !empty($prog['activity_date']) ? \Illuminate\Support\Carbon::parse($prog['activity_date'])->format('d M Y') : 'Tanggal belum tersedia' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[11px] block text-[#8C9BAE]">PIC Progress:</span>
                                <span class="font-medium text-[#18212B]">{{ ($prog['pic_progress'] ?? null) ?: '-' }}</span>
                            </div>
                        </div>

                        @if($nextCount > 0)
                            <div class="text-xs bg-[#F5F8FC] p-2 rounded border border-[#E0ECF8] text-[#18212B]">
                                <span class="font-semibold text-[#2459A6]">Next Action ({{ $nextCount }}):</span>
                                <span class="text-[#536170]">{{ $firstNext }}</span>
                            </div>
                        @endif

                        <div class="pt-2 flex justify-end">
                            <a
                                href="?tab=progresses&id={{ $prog['id'] }}"
                                class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2.5 text-xs font-semibold rounded border border-[#2459A6] text-[#2459A6] bg-white hover:bg-[#F0F4FA] transition-colors min-h-[44px]"
                            >
                                Buka Detail Progress
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- DIFERENSIASI EMPTY STATE (AT25) -->
            <div class="p-8 sm:p-12 text-center" data-testid="empty-state-container">
                @if($isFiltered)
                    <!-- Empty State: Filter Tidak Menemukan Hasil -->
                    <div class="max-w-md mx-auto space-y-3" data-testid="empty-filtered">
                        <div class="w-12 h-12 mx-auto rounded-full bg-[#FEF7E6] text-[#8A5B00] flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-[#18212B]">Tidak ada hasil yang cocok dengan filter</h3>
                        <p class="text-xs sm:text-sm text-[#536170]">
                            Tidak ditemukan kegiatan dengan kriteria pencarian atau filter yang dipilih. Coba sesuaikan kata kunci atau bersihkan filter.
                        </p>
                        <div class="pt-2">
                            <button
                                wire:click="clearFilters"
                                type="button"
                                class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors min-h-[44px]"
                                data-testid="empty-clear-filter-btn"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Bersihkan Filter
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Empty State: Default Open Kosong -->
                    <div class="max-w-md mx-auto space-y-3" data-testid="empty-default-open">
                        <div class="w-12 h-12 mx-auto rounded-full bg-[#EAF7EE] text-[#0E6B38] flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-[#18212B]">Tidak ada progress Open</h3>
                        <p class="text-xs sm:text-sm text-[#536170]">
                            Semua kegiatan telah ditutup (Closed) atau belum ada progress yang dicatat untuk pasangan JO & Part No yang aktif.
                        </p>
                        <div class="pt-2">
                            <a
                                href="?tab=form"
                                class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors min-h-[44px]"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Progress Baru
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
