<div>
    <div class="page-head">
        <div>
            <h1>Aktivitas New Model</h1>
        </div>
        <div class="actions">
            <a class="btn" href="?tab=drafts" data-testid="drafts-link-btn">
                <x-ui.icon name="draft" />
                Lanjutkan draft <span class="tab-count">{{ $summary['active_drafts'] }}</span>
            </a>
            <a class="btn primary" href="?tab=form" data-testid="add-progress-btn">
                <x-ui.icon name="plus" />
                Tambah progress
            </a>
        </div>
    </div>

    <div class="summary-caption">
        <span>RINGKASAN KESELURUHAN</span>
        <span>Semua project</span>
    </div>

    <section class="summary" aria-label="Ringkasan keseluruhan">
        <div class="stat">
            <div class="stat-number" data-testid="stat-open">{{ str_pad($summary['open_progress'], 2, '0', STR_PAD_LEFT) }}</div>
            <div>
                <div class="stat-label">Progress Open</div>
                <div class="stat-meta">Perlu ditinjau &amp; dilanjutkan</div>
            </div>
        </div>

        <div class="stat">
            <div class="stat-number" data-testid="stat-draft">{{ str_pad($summary['active_drafts'], 2, '0', STR_PAD_LEFT) }}</div>
            <div>
                <div class="stat-label">Draft aktif</div>
                <div class="stat-meta">Belum diterapkan ke progress</div>
            </div>
        </div>

        <div class="stat">
            <div class="stat-number" data-testid="stat-month">{{ str_pad($summary['current_month_count'], 2, '0', STR_PAD_LEFT) }}</div>
            <div>
                <div class="stat-label">Aktivitas bulan ini</div>
                <div class="stat-meta">{{ $summary['current_month_label'] }}</div>
            </div>
        </div>
    </section>
</div>
