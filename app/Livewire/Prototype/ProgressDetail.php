<?php

namespace App\Livewire\Prototype;

use App\Prototype\FakeStore;
use Livewire\Component;

class ProgressDetail extends Component
{
    public string $progressId;
    public ?array $progress = null;
    public ?array $project = null;
    public ?string $flashMessage = null;

    public function mount(?string $progressId = null): void
    {
        $this->progressId = $progressId ?? request()->query('detail') ?? request()->query('id') ?? '';
        $this->loadData();
    }

    public function loadData(): void
    {
        if (! empty($this->progressId)) {
            $this->progress = FakeStore::findProgress($this->progressId);
            if ($this->progress && ! empty($this->progress['project_id'])) {
                $this->project = FakeStore::findProject($this->progress['project_id']);
            }
        }
    }

    /**
     * Tutup progress secara manual (FR16, AT20, D10).
     * Tidak memerlukan syarat kelengkapan data.
     */
    public function closeProgress(): void
    {
        if ($this->progressId) {
            $updated = FakeStore::closeProgress($this->progressId);
            if ($updated) {
                $this->progress = $updated;
                $this->flashMessage = "Progress #{$this->progressId} berhasil ditutup (Closed). Mode edit dinonaktifkan.";
                $this->dispatch('progress-closed', progressId: $this->progressId);
            }
        }
    }

    /**
     * Buka kembali progress yang berstatus Closed (FR16, AT22, D10).
     * Menjaga seluruh isi data tanpa reset.
     */
    public function reopenProgress(): void
    {
        if ($this->progressId) {
            $updated = FakeStore::reopenProgress($this->progressId);
            if ($updated) {
                $this->progress = $updated;
                $this->flashMessage = "Progress #{$this->progressId} berhasil dibuka kembali (Open). Mode edit telah aktif kembali.";
                $this->dispatch('progress-reopened', progressId: $this->progressId);
            }
        }
    }

    public function dismissFlash(): void
    {
        $this->flashMessage = null;
    }

    public function render()
    {
        return view('livewire.prototype.progress-detail', [
            'progress' => $this->progress,
            'project' => $this->project,
        ]);
    }
}
