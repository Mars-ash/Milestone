<div>
    <div class="page-head">
        <div>
            <h1>Draft</h1>
        </div>
        <a href="?tab=form" class="btn primary" data-testid="new-progress-btn">
            <x-ui.icon name="plus" />
            Tambah progress
        </a>
    </div>

    <div class="draft-cards">
        @forelse($drafts as $d)
            @php
                $isEdit = !empty($d->progress_id);
                $isClosed = ($d->progress?->status === 'Closed');
                $payload = $d->payload ?? [];
                $projectId = $payload['project_id'] ?? $d->progress?->project_id;
                $project = $projectId ? \App\Models\Project::find($projectId) : null;
                $activity = $payload['activity'] ?? ($d->progress?->activity ?? 'Trial');
                $timeStr = $d->updated_at ? $d->updated_at->format('H:i') : '—';
                $url = $isClosed ? '?tab=progresses&detail=' . $d->progress_id : '?tab=form&draft=' . $d->id;
            @endphp
            <article
                class="draft-card clickable-row"
                wire:key="draft-{{ $d->id }}"
                data-testid="draft-card-{{ $d->id }}"
                onclick="if (!event.target.closest('a, button, input, label')) { if (event.ctrlKey || event.metaKey) { window.open('{{ $url }}', '_blank'); } else { window.location.href='{{ $url }}'; } }"
            >
                <div class="draft-card-icon">
                    <x-ui.icon name="draft" />
                </div>
                <div>
                    <span class="status draft">{{ $isEdit ? 'Draft perubahan' : 'Draft baru' }}</span>
                    <h3>
                        {{ $project?->part_name ?? 'JO belum dipilih' }}
                        @if(!empty($activity)) · {{ $activity }} @endif
                    </h3>
                    <p>
                        {{ $project?->jo_no ?? 'Lengkapi identitas kegiatan' }} · Tersimpan {{ $timeStr }}{{ $isClosed ? ' · Progress Closed' : '' }}
                    </p>
                </div>
                <div class="actions">
                    <button
                        type="button"
                        class="icon-btn"
                        wire:click="promptDiscard('{{ $d->id }}')"
                        aria-label="Buang draft {{ $project?->jo_no ?? 'baru' }}"
                        data-testid="discard-btn-{{ $d->id }}"
                    >
                        <x-ui.icon name="trash" />
                    </button>
                    @if($isClosed)
                        <a class="btn" href="?tab=progresses&detail={{ $d->progress_id }}" data-testid="view-closed-progress-btn-{{ $d->id }}">
                            Lihat progress
                            <x-ui.icon name="arrow" />
                        </a>
                    @else
                        <a class="btn" href="?tab=form&draft={{ $d->id }}" data-testid="resume-draft-btn-{{ $d->id }}">
                            Lanjutkan
                            <x-ui.icon name="arrow" />
                            <span class="sr-only">Lanjutkan Draft</span>
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="panel empty" data-testid="empty-drafts-notice">
                <h3>Semua draft sudah ditangani</h3>
                <p>Draft baru muncul saat kamu mulai mengisi atau mengedit progress.</p>
            </div>
        @endforelse
    </div>

    @if($confirmingDiscardUuid)
        <x-ui.reference-dialog id="discard-draft-dialog" title="Buang draft?" cancel="cancelDiscard">
            <p>Isian draft akan dihapus. Progress yang sudah tersimpan tetap utuh.</p>
            <x-slot:footer>
                <button type="button" class="btn" wire:click="cancelDiscard">Batal</button>
                <button type="button" class="btn danger" wire:click="confirmDiscard" data-testid="confirm-discard-btn">Buang draft</button>
            </x-slot:footer>
        </x-ui.reference-dialog>
    @endif
</div>
