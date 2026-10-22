<div
    x-data="{
        focus(selector) {
            this.$nextTick(() => requestAnimationFrame(() => this.$el.querySelector(selector)?.focus()));
        }
    }"
    x-on:editor-focus.window="focus($event.detail.selector)"
    x-on:beforeunload.window="if ($wire.saveStatus === 'unsaved' || $wire.saveStatus === 'saving') { $event.preventDefault(); $event.returnValue = ''; }"
>
    <div class="page-head">
        <div>
            <a href="?tab=progresses" class="back">
                <x-ui.icon name="left" />
                Kembali ke daftar
            </a>
            <h1>{{ $progressId ? 'Edit Progress' : 'Input Progress' }}</h1>
        </div>
    </div>

    <!-- Error Summary Component -->
    <x-ui.error-summary :errors="$errors" />

    @if($progress?->legacy_incomplete)
        <div class="note" style="margin-bottom: 16px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px; border-radius: 6px;" data-testid="legacy-incomplete-notice">
            <strong>Data Legacy Incomplete:</strong> Record ini berasal dari data lama yang belum lengkap. Lengkapi JO No, tanggal aktivitas, dan Activity sebelum menyimpan.
        </div>
    @endif

    @if($hasConflict)
        <div class="note" style="margin-bottom: 16px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px;" role="alert" data-testid="conflict-notice">
            <strong>Konflik Versi Terdeteksi:</strong> Data ini telah diubah pada sesi lain atau tab lain. Isian kamu saat ini dipertahankan, namun simpan otomatis ditahan agar tidak menimpa perubahan tersebut.
            <button type="button" wire:click="saveDraft" class="btn" style="margin-left: 12px; font-size: 12px; padding: 4px 8px;">Coba simpan ulang</button>
        </div>
    @endif

    @if($errors->has('save') || $errors->has('remove'))
        <div class="field-error" style="margin-bottom:16px;" role="alert">
            {{ $errors->first('save') ?: $errors->first('remove') }}
        </div>
    @endif

    <!-- SECTION 00: Project Selection & Read-only Master Identity -->
    <livewire:components.project-picker :selectedId="$projectId" :readOnly="$isClosed" />

    <!-- 5 Section Work Sheet -->
    <div class="panel entry-sheet">
        <!-- SECTION 01: Aktivitas & Rincian Trial -->
        <section class="entry-section" id="entry-activity" aria-labelledby="heading-activity">
            <div class="entry-section-head">
                <span class="section-index" aria-hidden="true">01</span>
                <h2 id="heading-activity" tabindex="-1">Aktivitas</h2>
            </div>
            <x-progress.activity-fields />
        </section>

        <!-- SECTION 02: Catatan & Lampiran -->
        <section class="entry-section" id="entry-files" aria-labelledby="heading-files">
            <div class="entry-section-head">
                <span class="section-index" aria-hidden="true">02</span>
                <h2 id="heading-files" tabindex="-1">Catatan &amp; lampiran</h2>
            </div>
            <div class="field full">
                <label class="field-label" for="f-remark">Remark Progress</label>
                <textarea
                    id="f-remark"
                    rows="2"
                    wire:model.live.debounce.300ms="remark"
                    placeholder="Catatan tambahan kegiatan"
                ></textarea>
            </div>

            <!-- Dokumen Baru Progress (P04-T07) -->
            <x-progress.documents
                ownerKind="progress"
                :documents="$documents"
                :isClosed="$isClosed"
            />

            <!-- Dokumen Lama Progress (P04-T07) -->
            <x-progress.legacy-documents
                :legacyDocuments="$legacyDocuments"
                title="Arsip Dokumen Lama Kegiatan"
            />
        </section>

        <!-- SECTION 03: Problem & Corrective Action -->
        <section class="entry-section" id="entry-problems" aria-labelledby="heading-problems">
            <div class="entry-section-head">
                <span class="section-index" aria-hidden="true">03</span>
                <h2 id="heading-problems" tabindex="-1">Problem &amp; Corrective Action</h2>
            </div>
            <x-progress.problems :problems="$problems" :isClosed="$isClosed" />
        </section>

        <!-- SECTION 04: Next Action -->
        <section class="entry-section" id="entry-nextActions" aria-labelledby="heading-nextActions">
            <div class="entry-section-head">
                <span class="section-index" aria-hidden="true">04</span>
                <h2 id="heading-nextActions" tabindex="-1">Next Action</h2>
            </div>
            <x-progress.next-actions :nextActions="$nextActions" :isClosed="$isClosed" />
        </section>

        <!-- SECTION 05: Feedback Customer -->
        <section class="entry-section" id="entry-feedback" aria-labelledby="heading-feedback">
            <div class="entry-section-head">
                <span class="section-index" aria-hidden="true">05</span>
                <h2 id="heading-feedback" tabindex="-1">Feedback Customer</h2>
            </div>
            <x-progress.feedback :feedbacks="$feedbacks" :isClosed="$isClosed" />
        </section>
    </div>

    <!-- Fixed Savebar di Bagian Bawah -->
    <footer class="savebar">
        <div class="save-state">
            <x-progress.save-state :status="$saveStatus" :time="$lastSavedAt" />
            @if($errors->any() && !$errors->has('save'))
                <div class="save-indicator error" role="status" style="margin-left: 8px;">
                    <x-ui.icon name="info" />
                    <span>Lengkapi field wajib yang belum terisi.</span>
                </div>
            @endif
        </div>
        <div class="savebar-actions">
            @if($draftId && !$isClosed)
                <button type="button" class="btn" wire:click="discardDraft" data-testid="discard-draft-btn" style="color: #c5221f;">
                    <x-ui.icon name="trash" />
                    Buang draft
                </button>
            @endif
            <a href="?tab=progresses" class="btn">Kembali ke daftar</a>
            @unless($isClosed)
                <button type="button" class="btn primary" wire:click="save" data-testid="save-progress-btn">
                    Simpan progress
                </button>
            @endunless
        </div>
    </footer>

    @if($pendingRemoval)
        <x-ui.reference-dialog id="remove-row-dialog" title="Hapus baris baru?" cancel="cancelRemove">
            <p>Baris ini belum diterapkan ke progress. Isian pada baris ini akan dihapus dari form.</p>
            <x-slot:footer>
                <button type="button" class="btn" wire:click="cancelRemove">Batal</button>
                <button type="button" class="btn danger" wire:click="confirmRemove" data-testid="confirm-remove-row-btn">Hapus baris</button>
            </x-slot:footer>
        </x-ui.reference-dialog>
    @endif

    @if($activePhoto)
        <x-ui.reference-dialog id="editor-photo-dialog" title="Foto problem" cancel="closeLightbox">
            <img class="full-photo" src="{{ $activePhoto['preview_url'] ?? '' }}" alt="{{ $activePhoto['caption'] ?: ($activePhoto['original_name'] ?? 'Foto problem') }}" style="max-width: 100%; height: auto; border-radius: 4px; display: block; margin: 0 auto;" data-testid="lightbox-full-img">
            <p style="margin-top: 8px; font-weight: 500;" data-testid="lightbox-caption">{{ $activePhoto['caption'] ?: ($activePhoto['original_name'] ?? '') }}</p>
            @if(!empty($activePhoto['download_url']))
                <div style="margin-top: 12px; text-align: right;">
                    <a href="{{ $activePhoto['download_url'] }}" class="btn" target="_blank" rel="noopener" data-testid="lightbox-download-link">
                        Unduh Foto Asli
                    </a>
                </div>
            @endif
        </x-ui.reference-dialog>
    @endif
</div>