<div>
    @if(!$progress)
        <div class="empty">
            <h2>Progress tidak ditemukan</h2>
            <p style="margin-top: 8px;">Data progress kegiatan tidak tersedia atau telah dihapus.</p>
            <a href="?tab=dashboard" class="btn" style="margin-top: 16px;">Kembali ke dashboard</a>
        </div>
    @else
        @php
            $isClosed = strtolower($progress['status'] ?? 'open') === 'closed';
            $problems = $progress['problems'] ?? [];
            $nextActions = $progress['next_actions'] ?? [];
            $feedbacks = $progress['feedbacks'] ?? [];
            $attachments = $progress['attachments'] ?? [];
            $activityDate = !empty($progress['activity_date']) ? \App\Support\UiFormat::date($progress['activity_date']) : '—';
            $docNo = ($project['doc_no'] ?? $project['no_doc'] ?? $project['doc'] ?? '') ?: '—';
        @endphp

        <a href="?tab=dashboard" class="back">
            <x-ui.icon name="left" />
            Kembali ke daftar
        </a>

        <div class="page-head editor-head">
            <div>
                <div class="eyebrow">{{ $project['jo_no'] ?? 'JO Belum Dipilih' }} · {{ $activityDate }}</div>
                <div class="heading-inline">
                    <h1>{{ $progress['activity'] ?? 'Aktivitas' }}</h1>
                    <span class="status {{ strtolower($progress['status'] ?? 'open') }}">
                        {{ $progress['status'] ?? 'Open' }}
                    </span>
                </div>
                <p class="subtitle">
                    {{ $project['part_name'] ?? '—' }} <span class="slash">/</span> {{ $project['part_no'] ?? '—' }}
                </p>
            </div>

            <div class="actions">
                @if(!$isClosed)
                    <button type="button" class="btn" wire:click="openConfirmClose" data-testid="close-progress-btn">
                        <x-ui.icon name="check" />
                        Close progress
                    </button>
                    <a href="?tab=form&id={{ $progress['id'] }}" class="btn primary" data-testid="edit-progress-btn">
                        <x-ui.icon name="edit" />
                        {{ $hasDraft ? 'Lanjutkan draft' : 'Edit progress' }}
                    </a>
                @else
                    <button type="button" class="btn primary" wire:click="reopenProgress" data-testid="reopen-progress-btn">
                        <x-ui.icon name="reopen" />
                        Reopen progress
                    </button>
                @endif
            </div>
        </div>

        <div class="detail-layout">
            <section class="panel detail-body">
                <!-- 01: Aktivitas -->
                <div class="detail-block">
                    <h2>Aktivitas</h2>
                    <p class="detail-text">{{ !empty($progress['notes']) ? $progress['notes'] : (!empty($progress['description']) ? $progress['description'] : 'Belum ada uraian.') }}</p>
                </div>

                <!-- 02: Lampiran kegiatan -->
                <div class="detail-block">
                    <h2>Lampiran kegiatan</h2>
                    @if(!empty($attachments))
                        <div class="file-list">
                            @foreach($attachments as $att)
                                <div class="file-row">
                                    <x-ui.icon name="file" />
                                    <div>
                                        <strong>{{ $att['name'] ?? 'dokumen' }}</strong>
                                        <small>{{ max(1, round(($att['size'] ?? 1024) / 1024)) }} KB</small>
                                    </div>
                                    <span class="file-kind">Simulasi lampiran</span>
                                    @if(!empty($att['preview_url']) || !empty($att['path']))
                                        <a href="{{ $att['preview_url'] ?? $att['path'] }}" target="_blank" rel="noopener" class="btn text sm">Preview</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="detail-text subtle">Belum ada dokumen.</p>
                    @endif
                </div>

                <!-- 03: Problem & Corrective Action -->
                <div class="detail-block">
                    <h2>
                        Problem & Corrective Action
                        <span class="tab-count">{{ count($problems) }}</span>
                    </h2>
                    @forelse($problems as $i => $prob)
                        <div class="detail-problem">
                            <h3>Problem {{ $i + 1 }}</h3>
                            <p class="detail-text">{{ !empty($prob['description']) ? $prob['description'] : 'Uraian belum diisi' }}</p>

                            @foreach($prob['corrective_actions'] ?? [] as $n => $act)
                                <div class="detail-action">
                                    <div>
                                        <span style="color:#8490a0">{{ $n + 1 }}.</span>
                                        {{ !empty($act['description']) ? $act['description'] : 'Uraian belum diisi' }}
                                        <small>PIC {{ !empty($act['pic']) ? $act['pic'] : 'belum diisi' }}</small>
                                    </div>
                                    <div class="date">
                                        {{ !empty($act['actual_date']) ? \App\Support\UiFormat::date($act['actual_date']) : 'Actual date belum diisi' }}
                                    </div>
                                </div>
                            @endforeach

                            @if(!empty($prob['photos']))
                                <div class="photo-strip">
                                    @foreach($prob['photos'] as $ph)
                                        <button
                                            type="button"
                                            class="icon-btn photo-thumb"
                                            wire:click="openPhotoById('{{ $prob['id'] }}', '{{ $ph['id'] }}')"
                                            aria-label="Perbesar {{ $ph['filename'] ?? 'foto' }}"
                                        >
                                            <img
                                                class="thumb"
                                                src="{{ $ph['path'] ?? 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%23e2e8f0%22/><text x=%2250%22 y=%2250%22 font-size=%2212%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 fill=%22%2364748b%22>Foto</text></svg>' }}"
                                                alt="{{ $ph['filename'] ?? 'Foto' }}"
                                            >
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="detail-text subtle">Belum ada problem.</p>
                    @endforelse
                </div>

                <!-- 04: Next Action -->
                <div class="detail-block">
                    <h2>
                        Next Action
                        <span class="tab-count">{{ count($nextActions) }}</span>
                    </h2>
                    @forelse($nextActions as $i => $act)
                        <div class="detail-action">
                            <div>
                                <span style="color:#8490a0">{{ $i + 1 }}.</span>
                                {{ !empty($act['description']) ? $act['description'] : 'Uraian belum diisi' }}
                                <small>PIC {{ !empty($act['pic']) ? $act['pic'] : 'belum diisi' }}</small>
                            </div>
                            <div class="date">
                                {{ !empty($act['actual_date']) ? \App\Support\UiFormat::date($act['actual_date']) : 'Actual date belum diisi' }}
                            </div>
                        </div>
                    @empty
                        <p class="detail-text subtle">Belum ada next action.</p>
                    @endforelse
                </div>

                <!-- 05: Feedback Customer -->
                <div class="detail-block">
                    <h2>
                        Feedback Customer
                        <span class="tab-count">{{ count($feedbacks) }}</span>
                    </h2>
                    @forelse($feedbacks as $fb)
                        <div class="detail-action">
                            <div>
                                {{ !empty($fb['content']) ? $fb['content'] : 'Isi feedback belum diisi' }}
                                <small>{{ !empty($fb['pic']) ? $fb['pic'] : 'PIC belum diisi' }}</small>
                                @if(!empty($fb['files']))
                                    <div class="file-list">
                                        @foreach($fb['files'] as $doc)
                                            <div class="file-row">
                                                <x-ui.icon name="file" />
                                                <div>
                                                    <strong>{{ $doc['name'] ?? 'dokumen' }}</strong>
                                                    <small>{{ max(1, round(($doc['size'] ?? 1024) / 1024)) }} KB</small>
                                                </div>
                                                <span class="file-kind">Simulasi lampiran</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="date">
                                {{ !empty($fb['date']) ? \App\Support\UiFormat::date($fb['date']) : 'Belum diisi' }}
                            </div>
                        </div>
                    @empty
                        <p class="detail-text subtle">Belum ada feedback.</p>
                    @endforelse
                </div>
            </section>

            <aside class="detail-aside">
                <!-- Identitas Project -->
                <section class="panel">
                    <h3>Identitas project</h3>
                    <div class="key-value">
                        <span>Customer</span>
                        <strong>{{ $project['customer'] ?? '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Model</span>
                        <strong>{{ $project['model'] ?? '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>No Doc</span>
                        <strong>{{ $docNo }}</strong>
                    </div>
                    <div class="divider" style="margin: 17px 0;"></div>
                    <div class="key-value">
                        <span>PIC Customer</span>
                        <strong>{{ ($progress['pic_customer'] ?? null) ?: '—' }}</strong>
                    </div>
                    @if(!empty($project['id']))
                        <a class="btn text sm" href="?tab=projects&detail={{ $project['id'] }}">
                            Lihat project
                            <x-ui.icon name="arrow" />
                        </a>
                    @endif
                </section>

                <!-- Rincian kegiatan -->
                <section class="panel">
                    <h3>Rincian kegiatan</h3>
                    <div class="key-value">
                        <span>Process Trial</span>
                        <strong>{{ ($progress['process_trial'] ?? null) ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Mesin</span>
                        <strong>{{ ($progress['machine'] ?? null) ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>DH · Die High</span>
                        <strong>{{ ($progress['die_high_raw'] ?? null) ?: (($progress['dh'] ?? null) ?: '—') }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Material</span>
                        <strong>{{ ($progress['material_actual_trial'] ?? null) ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Qty OK</span>
                        <strong>{{ ($progress['qty_ok_raw'] ?? null) ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Qty NG</span>
                        <strong>{{ ($progress['qty_ng_raw'] ?? null) ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Qty Process</span>
                        <strong>{{ ($progress['qty_process_raw'] ?? null) ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Qty Sample</span>
                        <strong>{{ ($progress['qty_sample_raw'] ?? null) ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Ket Event</span>
                        <strong>{{ ($progress['event_note'] ?? null) ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Remark</span>
                        <strong>{{ ($progress['remark'] ?? null) ?: '—' }}</strong>
                    </div>

                    @if($hasDraft)
                        <div class="note">
                            Ada draft perubahan. Data di halaman ini adalah versi yang terakhir diterapkan.{{ $isClosed ? ' Reopen untuk melanjutkan draft.' : '' }}
                        </div>
                    @endif
                </section>
            </aside>
        </div>

        @if($confirmingClose)
            <x-ui.reference-dialog id="close-progress-dialog" title="Close progress?" cancel="cancelConfirmClose">
                <p>Progress ini akan menjadi Closed. Seluruh catatan, problem, dan tindakan tetap tersimpan.</p>
                <p>{{ $hasDraft ? 'Ada draft perubahan yang belum diterapkan. Close berlaku pada versi tersimpan dan draft tetap dipertahankan.' : 'Progress dapat di-Reopen jika perlu dilanjutkan atau dikoreksi.' }}</p>
                <x-slot:footer>
                    <button type="button" class="btn" wire:click="cancelConfirmClose">Kembali</button>
                    <button type="button" class="btn primary" wire:click="confirmCloseProgress">Close progress</button>
                </x-slot:footer>
            </x-ui.reference-dialog>
        @endif
        @if($activePhoto)
            <x-ui.reference-dialog id="detail-photo-dialog" title="Foto problem" cancel="closePhoto">
                <img class="full-photo" src="{{ $activePhoto['src'] }}" alt="{{ $activePhoto['name'] }}">
                <p>{{ $activePhoto['name'] }}</p>
            </x-ui.reference-dialog>
        @endif
    @endif
</div>
