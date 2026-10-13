<?php

namespace App\Livewire\Prototype;

use App\Prototype\FakeDraftStore;
use App\Prototype\FakeStore;
use Livewire\Component;

class DraftList extends Component
{
    public ?string $confirmingDiscardUuid = null;
    public ?string $flashMessage = null;

    public function confirmDiscard(string $uuid): void
    {
        $this->confirmingDiscardUuid = $uuid;
    }

    public function cancelDiscard(): void
    {
        $this->confirmingDiscardUuid = null;
    }

    public function discardDraft(string $uuid): void
    {
        FakeDraftStore::discard($uuid);
        $this->confirmingDiscardUuid = null;
        $this->flashMessage = 'Draft berhasil dibuang. Data tersimpan tidak terpengaruh.';
        $this->dispatch('draft-discarded', uuid: $uuid);
    }

    public function dismissFlash(): void
    {
        $this->flashMessage = null;
    }

    public function render()
    {
        $rawDrafts = FakeDraftStore::all();
        $drafts = [];

        foreach ($rawDrafts as $d) {
            $project = ! empty($d['project_id']) ? FakeStore::findProject($d['project_id']) : null;
            $drafts[] = array_merge($d, [
                'project' => $project,
            ]);
        }

        return view('livewire.prototype.draft-list', [
            'drafts' => $drafts,
        ]);
    }
}
