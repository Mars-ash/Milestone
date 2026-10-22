<?php

namespace App\Livewire\Progresses;

use App\Actions\CloseProgress;
use App\Actions\ReopenProgress;
use App\Exceptions\VersionConflict;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Services\ProgressDraftService;
use Livewire\Component;

class ProgressShow extends Component
{
    public int $progressId;

    public ?Progress $progress = null;

    public ?array $activePhoto = null;

    public bool $confirmingClose = false;

    public bool $confirmingReopen = false;

    public bool $showPrintDialog = false;

    public ?string $printTonage = '';

    public ?string $printQtyOk = '';

    public ?string $printQtyNg = '';

    public ?ProgressDraft $activeDraft = null;

    public function mount(int $progressId): void
    {
        $this->progressId = $progressId;
        $this->loadProgress();
    }

    public function loadProgress(): void
    {
        $this->progress = Progress::with([
            'project',
            'problems.correctiveActions',
            'problems.attachments',
            'nextActions',
            'feedbacks.attachments',
            'feedbacks.legacyDocuments',
            'attachments',
            'legacyDocuments',
        ])->find($this->progressId);

        $admin = auth()->user();
        if ($admin && $this->progress) {
            $this->activeDraft = app(ProgressDraftService::class)->getActiveEditDraft($admin, $this->progress->id);
        }
    }

    public function promptClose(): void
    {
        $this->confirmingClose = true;
    }

    public function cancelClose(): void
    {
        $this->confirmingClose = false;
    }

    public function closeProgress(CloseProgress $action): void
    {
        $this->confirmClose($action);
    }

    public function confirmClose(CloseProgress $action): void
    {
        $admin = auth()->user();
        if (! $admin || ! $this->progress) {
            return;
        }

        try {
            $action->handle($admin, $this->progress->id, $this->progress->revision);
            $this->confirmingClose = false;
            $this->loadProgress();
            $this->dispatch('ui-toast', message: 'Progress berhasil ditutup.');
        } catch (VersionConflict $e) {
            $this->addError('status', 'Terjadi konflik versi: data progress telah diubah di sesi lain.');
        }
    }

    public function promptReopen(): void
    {
        $this->confirmingReopen = true;
    }

    public function cancelReopen(): void
    {
        $this->confirmingReopen = false;
    }

    public function reopenProgress(ReopenProgress $action): void
    {
        $this->confirmReopen($action);
    }

    public function confirmReopen(ReopenProgress $action): void
    {
        $admin = auth()->user();
        if (! $admin || ! $this->progress) {
            return;
        }

        try {
            $action->handle($admin, $this->progress->id, $this->progress->revision);
            $this->confirmingReopen = false;
            $this->loadProgress();
            $this->dispatch('ui-toast', message: 'Progress dibuka kembali.');
        } catch (VersionConflict $e) {
            $this->addError('status', 'Terjadi konflik versi: data progress telah diubah di sesi lain.');
        }
    }

    public function openPhoto(string $src, string $name): void
    {
        $this->activePhoto = [
            'src' => $src,
            'name' => $name,
        ];
    }

    public function closePhoto(): void
    {
        $this->activePhoto = null;
    }

    public function openPrintDialog(): void
    {
        if (! $this->progress) {
            return;
        }

        $this->printTonage = $this->progress->tonage_raw ?? '';
        $this->printQtyOk = $this->progress->qty_ok_raw ?? '';
        $this->printQtyNg = $this->progress->qty_ng_raw ?? '';
        $this->showPrintDialog = true;
    }

    public function cancelPrintDialog(): void
    {
        $this->showPrintDialog = false;
    }

    public function confirmPrint(): void
    {
        if (! $this->progress) {
            return;
        }

        $this->progress->update([
            'tonage_raw' => $this->printTonage,
            'qty_ok_raw' => $this->printQtyOk,
            'qty_ng_raw' => $this->printQtyNg,
        ]);

        $this->showPrintDialog = false;

        $url = route('progresses.trial-report', [
            'progress' => $this->progress->id,
            'tonage' => $this->printTonage,
            'qty_ok' => $this->printQtyOk,
            'qty_ng' => $this->printQtyNg,
        ]);

        $this->dispatch('open-trial-report', url: $url);
    }

    public function render()
    {
        return view('livewire.progresses.progress-show', [
            'progress' => $this->progress,
            'project' => $this->progress?->project,
            'activeDraft' => $this->activeDraft,
        ]);
    }
}
