<div x-data x-on:open-trial-report.window="window.open($event.detail.url, '_blank')">
    @if(!$progress)
        <div class="empty">
            <h2>Progress tidak ditemukan</h2>
            <p style="margin-top: 8px;">Data progress kegiatan tidak tersedia atau telah dihapus.</p>
            <a href="?tab=progresses" class="btn" style="margin-top: 16px;">Kembali ke daftar</a>
        </div>
    @else
        @php
            $isClosed = ($progress->status === 'Closed');
            $problems = $progress->problems ?? collect();
            $nextActions = $progress->nextActions ?? collect();
            $feedbacks = $progress->feedbacks ?? collect();
            $attachments = $progress->attachments ?? collect();
            $legacyDocs = $progress->legacyDocuments ?? collect();
            $activityDate = $progress->activity_date
                ? \App\Support\UiFormat::date($progress->activity_date->format('Y-m-d'))
                : '—';
            $docNo = $project?->no_doc ?: '—';
        @endphp

        <a href="?tab=progresses" class="back">
            <x-ui.icon name="left" />
            Kembali ke daftar
        </a>

        @error('status')
            <div class="field-error" style="margin: 16px 0;" role="alert">
                {{ $message }}
            </div>
        @enderror

        @if($activeDraft)
            <div class="note" style="margin: 16px 0; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 12px; border-radius: 6px;" data-testid="unapplied-draft-notice">
                <strong>Draft Tersimpan:</strong> Terdapat draft perubahan aktif yang belum diterapkan pada progress ini.
                <a href="?tab=form&draft={{ $activeDraft->id }}" class="btn" style="margin-left: 12px; font-size: 12px; padding: 4px 8px;" data-testid="resume-active-draft-btn">Lanjutkan draft</a>
            </div>
        @endif

        <div class="page-head editor-head">
            <div>
                <div class="eyebrow">{{ $project->jo_no ?? 'JO Belum Dipilih' }} · {{ $activityDate }}</div>
                <div class="heading-inline">
                    <h1>{{ $progress->activity }}</h1>
                    <span class="status {{ strtolower($progress->status) }}">
                        {{ $progress->status }}
                    </span>
                </div>
                <p class="subtitle">
                    {{ $project->part_name ?? '—' }} <span class="slash">/</span> {{ $project->part_no ?? '—' }}
                </p>
            </div>

            <div class="actions">
                @if($progress->activity === 'Trial')
                    <button type="button" class="btn" wire:click="openPrintDialog" data-testid="print-trial-report-btn">
                        <x-ui.icon name="file" />
                        Cetak Trial Report
                    </button>
                @endif
                @if(!$isClosed)
                    <a href="?tab=form&id={{ $progress->id }}" class="btn primary" data-testid="edit-progress-btn">
                        <x-ui.icon name="edit" />
                        Edit progress
                    </a>
                    <button type="button" class="btn" wire:click="promptClose" data-testid="close-progress-btn">
                        <x-ui.icon name="lock" />
                        Close progress
                    </button>
                @else
                    <button type="button" class="btn primary" wire:click="promptReopen" data-testid="reopen-progress-btn">
                        <x-ui.icon name="refresh" />
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
                    <p class="detail-text">{{ !empty($progress->description) ? $progress->description : 'Belum ada uraian.' }}</p>
                </div>

                <!-- 02: Lampiran kegiatan -->
                <div class="detail-block">
                    <h2>Lampiran kegiatan</h2>
                    @php
                        $directAttachments = $attachments->where('problem_id', null)->where('feedback_id', null);
                        $hasAnyDoc = ($directAttachments->isNotEmpty() || $legacyDocs->isNotEmpty());
                    @endphp
                    @if($hasAnyDoc)
                        <div class="file-list">
                            @foreach($directAttachments as $att)
                                <div class="file-row">
                                    <x-ui.icon name="file" />
                                    <div style="min-width: 0; flex: 1;">
                                        <strong style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block;">{{ $att->original_name }}</strong>
                                        <small>{{ max(1, round($att->size_bytes / 1024)) }} KB</small>
                                    </div>
                                    <div style="display: flex; gap: 6px; flex-shrink: 0;">
                                        <a href="{{ route('attachments.preview', $att) }}" class="btn ghost" style="font-size: 11px; padding: 4px 8px;" target="_blank" rel="noopener">Preview</a>
                                        <a href="{{ route('attachments.download', $att) }}" class="btn" style="font-size: 11px; padding: 4px 8px;" target="_blank" rel="noopener">Unduh</a>
                                    </div>
                                </div>
                            @endforeach
                            @foreach($legacyDocs as $ld)
                                <div class="file-row">
                                    <x-ui.icon name="file" />
                                    <div>
                                        <strong>{{ $ld->name }}</strong>
                                        <small>Arsip legacy</small>
                                    </div>
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
                        <span class="tab-count">{{ $problems->count() }}</span>
                    </h2>
                    @forelse($problems as $i => $prob)
                        <div class="detail-problem">
                            <h3>Problem {{ $i + 1 }}</h3>
                            <p class="detail-text">{{ !empty($prob->description) ? $prob->description : 'Uraian belum diisi' }}</p>

                            @foreach($prob->correctiveActions ?? [] as $n => $act)
                                <div class="detail-action">
                                    <div>
                                        <span style="color:#8490a0">{{ $n + 1 }}.</span>
                                        {{ !empty($act->description) ? $act->description : 'Uraian belum diisi' }}
                                        <small>PIC {{ !empty($act->pic) ? $act->pic : 'belum diisi' }}</small>
                                    </div>
                                    <div class="date">
                                        {{ $act->actual_date ? \App\Support\UiFormat::date($act->actual_date->format('Y-m-d')) : 'Actual date belum diisi' }}
                                    </div>
                                </div>
                            @endforeach

                            @php
                                $photos = $prob->attachments->where('kind', 'photo');
                            @endphp
                            @if($photos->isNotEmpty())
                                <div class="photo-strip">
                                    @foreach($photos as $ph)
                                        <button
                                            type="button"
                                            class="icon-btn photo-thumb"
                                            wire:click="openPhoto('{{ route('attachments.download', $ph) }}', '{{ $ph->original_name }}')"
                                            aria-label="Perbesar {{ $ph->original_name }}"
                                        >
                                            <img
                                                class="thumb"
                                                src="{{ route('attachments.download', $ph) }}"
                                                alt="{{ $ph->original_name }}"
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
                        <span class="tab-count">{{ $nextActions->count() }}</span>
                    </h2>
                    @forelse($nextActions as $i => $act)
                        <div class="detail-action">
                            <div>
                                <span style="color:#8490a0">{{ $i + 1 }}.</span>
                                {{ !empty($act->description) ? $act->description : 'Uraian belum diisi' }}
                                <small>PIC {{ !empty($act->pic) ? $act->pic : 'belum diisi' }}</small>
                            </div>
                            <div class="date">
                                {{ $act->actual_date ? \App\Support\UiFormat::date($act->actual_date->format('Y-m-d')) : 'Actual date belum diisi' }}
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
                        <span class="tab-count">{{ $feedbacks->count() }}</span>
                    </h2>
                    @forelse($feedbacks as $fb)
                        <div class="detail-action">
                            <div>
                                {{ !empty($fb->content) ? $fb->content : 'Isi feedback belum diisi' }}
                                <small>PIC {{ !empty($fb->pic) ? $fb->pic : 'belum diisi' }}</small>
                                @php
                                    $fbDocs = $fb->attachments ?? collect();
                                    $fbLegacy = $fb->legacyDocuments ?? collect();
                                @endphp
                                @if($fbDocs->isNotEmpty() || $fbLegacy->isNotEmpty())
                                    <div class="file-list">
                                        @foreach($fbDocs as $doc)
                                            <div class="file-row">
                                                <x-ui.icon name="file" />
                                                <div style="min-width: 0; flex: 1;">
                                                    <strong style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block;">{{ $doc->original_name }}</strong>
                                                    <small>{{ max(1, round($doc->size_bytes / 1024)) }} KB</small>
                                                </div>
                                                <div style="display: flex; gap: 6px; flex-shrink: 0;">
                                                    <a href="{{ route('attachments.preview', $doc) }}" class="btn ghost" style="font-size: 11px; padding: 4px 8px;" target="_blank" rel="noopener">Preview</a>
                                                    <a href="{{ route('attachments.download', $doc) }}" class="btn" style="font-size: 11px; padding: 4px 8px;" target="_blank" rel="noopener">Unduh</a>
                                                </div>
                                            </div>
                                        @endforeach
                                        @foreach($fbLegacy as $ldoc)
                                            <div class="file-row">
                                                <x-ui.icon name="file" />
                                                <div>
                                                    <strong>{{ $ldoc->name }}</strong>
                                                    <small>Arsip legacy</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="date">
                                {{ $fb->feedback_date ? \App\Support\UiFormat::date($fb->feedback_date->format('Y-m-d')) : 'Belum diisi' }}
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
                        <strong>{{ $project->customer ?? '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Model</span>
                        <strong>{{ $project->model ?? '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>No Doc</span>
                        <strong>{{ $docNo }}</strong>
                    </div>
                    <div class="divider" style="margin: 17px 0;"></div>
                    <div class="key-value">
                        <span>PIC Customer</span>
                        <strong>{{ $progress->pic_customer ?: '—' }}</strong>
                    </div>
                    @if($project)
                        <a class="btn text sm" href="?tab=projects&detail={{ $project->id }}">
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
                        <strong>{{ $progress->process_trial ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Mesin</span>
                        <strong>{{ $progress->machine ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Tonage</span>
                        <strong>{{ $progress->tonage_raw ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Die High</span>
                        <strong>{{ $progress->die_high_raw ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Material</span>
                        <strong>{{ $progress->material_actual_trial ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Qty Process</span>
                        <strong>{{ $progress->qty_process_raw ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Qty Sample</span>
                        <strong>{{ $progress->qty_sample_raw ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Qty OK</span>
                        <strong>{{ $progress->qty_ok_raw ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Qty NG</span>
                        <strong>{{ $progress->qty_ng_raw ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Ket Event</span>
                        <strong>{{ $progress->event_note ?: '—' }}</strong>
                    </div>
                    <div class="key-value">
                        <span>Remark</span>
                        <strong>{{ $progress->remark ?: '—' }}</strong>
                    </div>
                </section>
            </aside>
        </div>

        @if($activePhoto)
            <x-ui.reference-dialog id="detail-photo-dialog" title="Foto problem" cancel="closePhoto">
                <img class="full-photo" src="{{ $activePhoto['src'] }}" alt="{{ $activePhoto['name'] }}">
                <p>{{ $activePhoto['name'] }}</p>
            </x-ui.reference-dialog>
        @endif

        @if($confirmingClose)
            <x-ui.reference-dialog id="close-progress-dialog" title="Tutup progress kegiatan?" cancel="cancelClose">
                <p>Status akan diubah menjadi Closed. Form edit dan autosave akan dikunci sampai dibuka kembali (Reopen).</p>
                <x-slot:footer>
                    <button type="button" class="btn" wire:click="cancelClose">Batal</button>
                    <button type="button" class="btn danger" wire:click="confirmClose" data-testid="confirm-close-btn">Ya, Tutup Progress</button>
                </x-slot:footer>
            </x-ui.reference-dialog>
        @endif

        @if($confirmingReopen)
            <x-ui.reference-dialog id="reopen-progress-dialog" title="Buka kembali progress kegiatan?" cancel="cancelReopen">
                <p>Status akan dikembalikan menjadi Open. Semua isi data tetap utuh dan mode edit akan aktif kembali.</p>
                <x-slot:footer>
                    <button type="button" class="btn" wire:click="cancelReopen">Batal</button>
                    <button type="button" class="btn primary" wire:click="confirmReopen" data-testid="confirm-reopen-btn">Ya, Reopen Progress</button>
                </x-slot:footer>
            </x-ui.reference-dialog>
        @endif
        @if($showPrintDialog)
            <x-ui.reference-dialog id="print-trial-dialog" title="Cetak Trial Report" cancel="cancelPrintDialog">
                <p style="margin-bottom: 16px; font-size: 13px; color: var(--muted, #64748b);">
                    Masukkan data Tonage, Qty OK, dan Qty NG sebelum mencetak report trial.
                </p>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="field">
                        <label for="print-tonage" style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 4px;">Tonage</label>
                        <input id="print-tonage" type="text" class="input" wire:model="printTonage" placeholder="Contoh: 200T" data-testid="print-tonage-input" />
                    </div>
                    <div class="field">
                        <label for="print-qty-ok" style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 4px;">Qty OK</label>
                        <input id="print-qty-ok" type="text" class="input" wire:model="printQtyOk" placeholder="Contoh: 100 pcs" data-testid="print-qty-ok-input" />
                    </div>
                    <div class="field">
                        <label for="print-qty-ng" style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 4px;">Qty NG</label>
                        <input id="print-qty-ng" type="text" class="input" wire:model="printQtyNg" placeholder="Contoh: 5 pcs" data-testid="print-qty-ng-input" />
                    </div>
                </div>
                <x-slot:footer>
                    <button type="button" class="btn" wire:click="cancelPrintDialog">Batal</button>
                    <button type="button" class="btn primary" wire:click="confirmPrint" data-testid="confirm-print-btn">
                        <x-ui.icon name="file" />
                        Cetak / Print PDF
                    </button>
                </x-slot:footer>
            </x-ui.reference-dialog>
        @endif
    @endif
</div>

