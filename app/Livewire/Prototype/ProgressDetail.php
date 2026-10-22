<?php

namespace App\Livewire\Prototype;

use App\Prototype\FakeDraftStore;
use App\Prototype\FakeStore;
use Livewire\Component;

class ProgressDetail extends PreviewComponent
{
    public ?string $progressId = '';
    public ?array $progress = null;
    public ?array $project = null;
    public bool $hasDraft = false;
    public bool $confirmingClose = false;
    public ?array $activePhoto = null;
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

            // Cek apakah ada draft perubahan untuk progress ini
            $drafts = FakeDraftStore::all();
            $this->hasDraft = false;
            foreach ($drafts as $d) {
                if (($d['progress_id'] ?? '') === $this->progressId) {
                    $this->hasDraft = true;
                    break;
                }
            }
        }
    }

    public function openConfirmClose(): void
    {
        $this->confirmingClose = true;
    }

    public function cancelConfirmClose(): void
    {
        $this->confirmingClose = false;
    }

    /**
     * Tutup progress secara manual (FR16, AT20, D10).
     * Tanpa syarat tanggal action atau feedback.
     */
    public function confirmCloseProgress(): void
    {
        $this->closeProgress();
    }

    /**
     * Tutup progress secara manual (FR16, AT20, D10).
     * Tanpa syarat tanggal action atau feedback.
     */
    public function closeProgress(): void
    {
        if ($this->progressId) {
            $updated = FakeStore::closeProgress($this->progressId);
            if ($updated) {
                $this->progress = $updated;
                $this->dispatch('ui-toast', message: 'Progress ditutup. Reopen tersedia jika perlu dilanjutkan.');
                $this->dispatch('progress-closed', progressId: $this->progressId);
            }
        }
        $this->confirmingClose = false;
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
                $this->dispatch('ui-toast', message: 'Progress kembali Open. Seluruh catatan tetap tersimpan.');
                $this->dispatch('progress-reopened', progressId: $this->progressId);
            }
        }
    }

    public function openPhoto(string $src, string $name): void
    {
        $this->activePhoto = [
            'src' => $src,
            'name' => $name,
        ];
    }

    public function openPhotoById(string $problemId, string $photoId): void
    {
        foreach ($this->progress['problems'] ?? [] as $problem) {
            if (($problem['id'] ?? '') !== $problemId) continue;
            foreach ($problem['photos'] ?? [] as $photo) {
                if (($photo['id'] ?? '') !== $photoId) continue;
                $this->openPhoto($photo['path'] ?? $photo['src'] ?? '', $photo['filename'] ?? $photo['name'] ?? 'Foto problem');
                return;
            }
        }
    }

    public function closePhoto(): void
    {
        $this->activePhoto = null;
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
            'hasDraft' => $this->hasDraft,
        ]);
    }
}
