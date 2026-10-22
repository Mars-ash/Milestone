<div>
    <div class="page-head">
        <div>
            <h1>Draft</h1>
        </div>
        <a href="?tab=form" class="btn primary">
            <x-ui.icon name="plus" />
            Tambah progress
        </a>
    </div>

    <div class="draft-cards">
        @forelse($drafts as $d)
            @php
                $proj = $d['project'] ?? null;
                $isEdit = !empty($d['progress_id']);
                $isClosed = $d['is_closed'] ?? false;
                $timeStr = !empty($d['updated_at']) ? \App\Support\UiFormat::time($d['updated_at']) : '—';
            @endphp
            <article class="draft-card" wire:key="draft-{{ $d['uuid'] }}">
                <div class="draft-card-icon">
                    <x-ui.icon name="draft" />
                </div>
                <div>
                    <span class="status draft">{{ $isEdit ? 'Draft perubahan' : 'Draft baru' }}</span>
                    <h3>
                        {{ $proj['part_name'] ?? 'JO belum dipilih' }}
                        @if(!empty($d['activity'])) · {{ $d['activity'] }} @endif
                    </h3>
                    <p>
                        {{ $proj['jo_no'] ?? 'Lengkapi identitas kegiatan' }} · Tersimpan {{ $timeStr }}{{ $isClosed ? ' · Progress Closed' : '' }}
                    </p>
                </div>
                <div class="actions">
                    <button
                        type="button"
                        class="icon-btn"
                        wire:click="promptDiscard('{{ $d['uuid'] }}')"
                        aria-label="Buang draft {{ $proj['jo_no'] ?? 'baru' }}"
                        data-testid="discard-btn-{{ $d['uuid'] }}"
                    >
                        <x-ui.icon name="trash" />
                    </button>
                    @if($isClosed)
                        <a class="btn" href="?tab=progresses&detail={{ $d['progress_id'] }}" data-testid="view-closed-progress-btn-{{ $d['uuid'] }}">
                            Lihat progress
                            <x-ui.icon name="arrow" />
                        </a>
                    @else
                        <a class="btn" href="?tab=form&draft={{ $d['uuid'] }}" data-testid="resume-draft-btn-{{ $d['uuid'] }}">
                            Lanjutkan
                            <x-ui.icon name="arrow" />
                            <span class="sr-only">Lanjutkan Draft</span>
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="panel empty">
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
