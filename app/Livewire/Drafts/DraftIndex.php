<?php

namespace App\Livewire\Drafts;

use App\Services\ProgressDraftService;
use Livewire\Component;

class DraftIndex extends Component
{
    public ?string $confirmingDiscardUuid = null;

    public function promptDiscard(string $uuid): void
    {
        $this->confirmingDiscardUuid = $uuid;
    }

    public function cancelDiscard(): void
    {
        $this->confirmingDiscardUuid = null;
    }

    public function confirmDiscard(): void
    {
        if ($this->confirmingDiscardUuid) {
            $this->discardDraft($this->confirmingDiscardUuid);
        }
    }

    public function discardDraft(string $uuid): void
    {
        $admin = auth()->user();
        if ($admin) {
            app(ProgressDraftService::class)->discardDraft($admin, $uuid);
        }

        $this->confirmingDiscardUuid = null;
        $this->dispatch('ui-toast', message: 'Draft dibuang.');
    }

    public function render()
    {
        $admin = auth()->user();
        $drafts = $admin ? app(ProgressDraftService::class)->listActiveDrafts($admin) : collect();

        return view('livewire.drafts.draft-index', [
            'drafts' => $drafts,
        ]);
    }
}
