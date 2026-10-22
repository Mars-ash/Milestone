<div>
@if(! $isDashboard)
    <div class="page-head">
        <div>
            <h1>Semua progress</h1>
        </div>
        <div class="actions">
            <a class="btn primary" href="?tab=form" data-testid="add-progress-btn">
                <x-ui.icon name="plus" />
                Tambah progress
            </a>
        </div>
    </div>
@endif

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>Daftar progress</h2>
            </div>
        </div>

        <div class="tabs" role="tablist" aria-label="Status progress">
            @foreach(['Open', 'Closed', 'Semua'] as $s)
                @php
                    $count = match($s) {
                        'Open' => $statusCounts['Open'] ?? 0,
                        'Closed' => $statusCounts['Closed'] ?? 0,
                        default => $statusCounts['all'] ?? 0,
                    };
                    $isActive = ($status === $s || ($s === 'Semua' && $status === 'all'));
                @endphp
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $isActive ? 'true' : 'false' }}"
                    class="tab {{ $isActive ? 'active' : '' }}"
                    wire:click="setStatusFilter('{{ $s }}')"
                    data-status-filter="{{ $s }}"
                >
                    {{ $s }}<span class="tab-count">{{ $count }}</span>
                </button>
            @endforeach
        </div>

        <div class="filters">
            <label class="search-box">
                <x-ui.icon name="search" />
                <span class="sr-only">Cari JO, Part No, atau nama part</span>
                <input
                    class="input"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari JO, Part No, nama part…"
                    data-testid="search-input"
                >
            </label>
            <label class="sr-only" for="activity-filter">Activity</label>
            <select id="activity-filter" class="filter-select" wire:model.live="activity" data-testid="activity-select">
                <option value="">Semua Activity</option>
                @foreach(['Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'] as $act)
                    <option value="{{ $act }}">{{ $act }}</option>
                @endforeach
            </select>
            <label class="sr-only" for="customer-filter">Customer</label>
            <select id="customer-filter" class="filter-select" wire:model.live="customer" data-testid="customer-select">
                <option value="">Semua customer</option>
                @foreach($customers as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>
            <button
                type="button"
                class="btn ghost"
                wire:click="clearFilters"
                aria-label="Bersihkan semua filter"
                data-testid="clear-filter-btn"
            >
                Reset
            </button>
        </div>

        <div class="list-meta">
            <span id="result-count">
                {{ $progresses->total() }} progress · {{ $status === 'all' || $status === 'Semua' ? 'Semua status' : $status }}
            </span>
            <button
                type="button"
                class="btn ghost sm"
                wire:click="toggleSort"
                style="padding:0;min-height:18px"
            >
                <x-ui.icon name="sort" />
                <span id="sort-label">{{ $sort === 'oldest' ? 'Terlama lebih dulu' : 'Terbaru lebih dulu' }}</span>
            </button>
        </div>

        <div id="table-area">
            @if($totalDbCount === 0)
                <div class="empty" data-testid="empty-state-container">
                    <x-ui.icon name="progress" />
                    <h3>Belum ada progress kegiatan</h3>
                    <p>Belum ada data progress yang tercatat dalam sistem. Tambahkan progress baru untuk mulai mendata.</p>
                    <a href="?tab=form" class="btn primary" style="margin-top: 12px;">Tambah progress</a>
                </div>
            @elseif($progresses->isEmpty())
                <div class="empty" data-testid="empty-state-container">
                    <x-ui.icon name="search" />
                    <h3>Tidak ada progress yang cocok</h3>
                    <p>Coba kata pencarian lain atau bersihkan filter untuk melihat kegiatan lainnya.</p>
                    <button type="button" class="btn" wire:click="clearFilters" data-testid="empty-clear-filter-btn">Bersihkan filter</button>
                </div>
            @else
                <table class="data-table" data-testid="progress-table">
                    <caption class="sr-only">Daftar kegiatan sesuai pencarian dan filter</caption>
                    <colgroup>
                        <col style="width:14%">
                        <col style="width:33%">
                        <col style="width:14%">
                        <col style="width:15%">
                        <col style="width:24%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Project / Part</th>
                            <th>Activity</th>
                            <th>PIC</th>
                            <th>Next Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($progresses as $prog)
                            @php
                                $proj = $prog->project;
                                $isOpen = ($prog->status === 'Open');
                                $url = '?tab=progresses&detail=' . $prog->id;
                                $dateStr = $prog->activity_date
                                    ? \App\Support\UiFormat::date($prog->activity_date->format('Y-m-d'))
                                    : 'Belum diisi';
                                $nextActions = $prog->nextActions;
                                $nextCount = $nextActions->count();
                                $firstNext = $nextCount > 0 ? $nextActions->first()->description : '';
                            @endphp
                            <tr
                                class="clickable-row"
                                data-testid="progress-row"
                                data-id="{{ $prog->id }}"
                                onclick="if (!event.target.closest('a, button, input, label')) { if (event.ctrlKey || event.metaKey) { window.open('{{ $url }}', '_blank'); } else { window.location.href='{{ $url }}'; } }"
                            >
                                <td class="date-col date-cell">
                                    {{ $dateStr }}
                                </td>
                                <td class="part-col">
                                    <span class="row-title" data-open-progress="{{ $prog->id }}">
                                        {{ $proj->part_name ?? '—' }}
                                    </span>
                                    <span class="row-sub">
                                        {{ $proj->part_no ?? '—' }}
                                        <span style="color:#c1c9d3">/</span>
                                        <span class="jo-code">{{ $proj->jo_no ?? '—' }}</span>
                                    </span>
                                </td>
                                <td class="activity-col">
                                    <span class="activity-label">{{ $prog->activity }}</span>
                                    <br>
                                    <span class="status {{ $isOpen ? 'open' : 'closed' }}">{{ $prog->status }}</span>
                                </td>
                                <td class="pic-col" data-label="PIC">
                                    <span class="pic-label">{{ $prog->pic_customer ?: '—' }}</span>
                                </td>
                                <td class="next-col">
                                    <span class="next-text">{{ $firstNext ?: 'Belum ada next action' }}</span>
                                    @if($nextCount > 1)
                                        <span class="more-count">+{{ $nextCount - 1 }} tindakan lainnya</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($progresses->hasPages())
                    <div class="table-foot">
                        <span>Menampilkan {{ $progresses->firstItem() }}–{{ $progresses->lastItem() }} dari {{ $progresses->total() }} progress</span>
                        <div>
                            {{ $progresses->links() }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </section>

    <div class="support-row" style="margin-top: 16px;">
        <a href="?tab=projects" class="btn text">
            Lihat Project Master <x-ui.icon name="arrow" />
        </a>
    </div>
</div>
