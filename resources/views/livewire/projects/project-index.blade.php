<div>
    <div class="page-head">
        <div>
            <h1>Project Master</h1>
        </div>
        <button
            type="button"
            wire:click="openCreate"
            class="btn primary"
            data-testid="add-project-btn"
        >
            <x-ui.icon name="plus" />
            Tambah project
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar" style="margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        <div style="flex: 1; min-width: 240px;">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                class="input"
                placeholder="Cari JO No, Part No, atau Part Name..."
                aria-label="Cari project"
            >
        </div>
        @if(count($customers) > 0)
            <div style="min-width: 160px;">
                <select wire:model.live="customer" class="input" aria-label="Filter customer">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if(count($models) > 0)
            <div style="min-width: 140px;">
                <select wire:model.live="model" class="input" aria-label="Filter model">
                    <option value="">Semua Model</option>
                    @foreach($models as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <section class="panel">
        <div class="panel-head">
            <h2>{{ $projects->total() }} project</h2>
        </div>

        @if($projects->isEmpty())
            <div class="empty">
                <x-ui.icon name="search" />
                <h3>Tidak ada project ditemukan</h3>
                <p>Coba ubah kata kunci pencarian atau bersihkan filter.</p>
            </div>
        @else
            <table class="data-table project-table" data-testid="project-table">
                <thead>
                    <tr>
                        <th scope="col" style="width:35%">Project / Part</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Model</th>
                        <th scope="col">T0 Target</th>
                        <th scope="col">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $p)
                        @php
                            $url = '?tab=projects&detail=' . $p->id;
                            $t0Formatted = $p->t0_target ? \App\Support\UiFormat::date($p->t0_target->format('Y-m-d')) : 'Belum diisi';
                        @endphp
                        <tr
                            class="clickable-row"
                            data-testid="project-row"
                            data-id="{{ $p->id }}"
                            onclick="if (!event.target.closest('a, button, input, label')) { if (event.ctrlKey || event.metaKey) { window.open('{{ $url }}', '_blank'); } else { window.location.href='{{ $url }}'; } }"
                        >
                            <td class="part-col">
                                <span class="row-title" data-testid="project-detail-link">{{ $p->part_name }}</span>
                                <span class="row-sub">{{ $p->part_no }} / <span class="jo-code">{{ $p->jo_no }}</span></span>
                            </td>
                            <td class="project-customer" data-label="Customer">{{ $p->customer ?: '—' }}</td>
                            <td class="project-model" data-label="Model"><span class="model-code">{{ $p->model ?: '—' }}</span></td>
                            <td class="project-target" data-label="T0 Target">{{ $t0Formatted }}</td>
                            <td class="project-count" data-label="Progress">{{ $p->progresses_count }} kegiatan</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($projects->hasPages())
                <div class="table-foot">
                    <span>Menampilkan {{ $projects->firstItem() }}–{{ $projects->lastItem() }} dari {{ $projects->total() }} project</span>
                    <div>
                        {{ $projects->links() }}
                    </div>
                </div>
            @endif
        @endif
    </section>

    @if($showFormModal)
        <livewire:projects.project-form :projectId="$editingProjectId" :wire:key="'project-form-modal-'.($editingProjectId ?? 'new')" />
    @endif
</div>
