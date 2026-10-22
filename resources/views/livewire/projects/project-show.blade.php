<div>
    @if(!$project)
        <div class="empty">
            <h2>Project tidak ditemukan</h2>
            <p>Data project master tidak tersedia atau telah dihapus.</p>
            <a href="?tab=projects" class="btn">Kembali ke Project Master</a>
        </div>
    @else
        <a href="?tab=projects" class="back">
            <x-ui.icon name="left" />
            Project Master
        </a>

        <div class="page-head">
            <div>
                <div class="eyebrow">{{ $project->jo_no }} · {{ $project->part_no }}</div>
                <h1>{{ $project->part_name }}</h1>
                <p class="subtitle">{{ $project->customer ?: '—' }} / {{ $project->model ?: '—' }}</p>
            </div>
            <button type="button" wire:click="openEdit" class="btn" data-testid="edit-project-btn">
                <x-ui.icon name="edit" />
                Edit project
            </button>
        </div>

        <div class="detail-layout" style="margin-bottom:23px">
            <section class="panel detail-body">
                <h2>Jadwal project</h2>
                <div class="form-grid" style="margin-top:18px">
                    @php
                        $formatDate = fn($v) => !empty($v) ? \App\Support\UiFormat::date($v instanceof \DateTimeInterface ? $v->format('Y-m-d') : $v) : 'Belum diisi';
                    @endphp

                    <div class="key-value">
                        <span>T0 Target</span>
                        <strong data-testid="detail-t0-target">{{ $formatDate($project->t0_target) }}</strong>
                    </div>
                    <div class="key-value">
                        <span>T0 Actual</span>
                        <strong data-testid="detail-t0-actual">{{ $formatDate($project->t0_actual) }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Event 1</span>
                        <strong>{{ $formatDate($project->event_1) }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Event 2</span>
                        <strong>{{ $formatDate($project->event_2) }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Event 3</span>
                        <strong>{{ $formatDate($project->event_3) }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Date Receive</span>
                        <strong>{{ $formatDate($project->date_receive) }}</strong>
                    </div>
                </div>
            </section>

            <aside class="detail-aside" style="display:block">
                <section class="panel">
                    <h3>Spesifikasi</h3>
                    <div class="key-value">
                        <span>No Doc</span>
                        <strong>{{ $project->no_doc ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Material</span>
                        <strong>{{ $project->material ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Spec</span>
                        <strong>{{ $project->spec ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Thickness</span>
                        <strong>{{ $project->material_thickness_raw ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Width</span>
                        <strong>{{ $project->material_width_raw ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Pitch</span>
                        <strong>{{ $project->material_pitch_raw ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Process</span>
                        <strong>{{ $project->process ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Remark</span>
                        <strong>{{ $project->remark ?: '—' }}</strong>
                    </div>
                </section>
            </aside>
        </div>

        <section class="panel">
            <div class="panel-head">
                <h2>Histori progress</h2>
                <a href="?tab=form&project_id={{ $project->id }}" class="btn primary sm">
                    <x-ui.icon name="plus" />
                    Tambah progress
                </a>
            </div>

            @if($progresses->isEmpty())
                <div class="empty">
                    <x-ui.icon name="search" />
                    <h3>Belum ada histori progress</h3>
                    <p>Mulai tambahkan kegiatan progress untuk project ini.</p>
                </div>
            @else
                <table class="data-table">
                    <caption class="sr-only">Daftar progress project {{ $project->jo_no }}</caption>
                    <colgroup>
                        <col style="width:15%">
                        <col style="width:35%">
                        <col style="width:15%">
                        <col style="width:35%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Project / Part</th>
                            <th>Activity</th>
                            <th>Next Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($progresses as $prog)
                            @php
                                $isOpen = ($prog->status === 'Open');
                                $url = $isOpen ? '?tab=form&id=' . $prog->id : '?tab=progresses&detail=' . $prog->id;
                                $dateStr = $prog->activity_date ? \App\Support\UiFormat::date($prog->activity_date->format('Y-m-d')) : 'Belum diisi';
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
                                    <span class="row-title">
                                        {{ $project->part_name ?: '-' }}
                                    </span>
                                    <span class="row-sub">
                                        {{ $project->part_no ?: '-' }}
                                        <span style="color:#c1c9d3">/</span>
                                        <span class="jo-code">{{ $project->jo_no ?: '-' }}</span>
                                    </span>
                                </td>
                                <td class="activity-col">
                                    <span class="activity-label">{{ $prog->activity }}</span>
                                    <br><span class="status {{ $isOpen ? '' : 'closed' }}">{{ $prog->status }}</span>
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
                <div class="table-foot">
                    <span>Menampilkan {{ $progresses->count() }} progress</span>
                    <span>Halaman 1 dari 1</span>
                </div>
            @endif
        </section>

        @if($showEditModal)
            <livewire:projects.project-form :projectId="$project->id" :wire:key="'project-edit-modal-'.$project->id" />
        @endif
    @endif
</div>
