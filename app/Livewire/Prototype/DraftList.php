<?php

namespace App\Livewire\Prototype;

use App\Prototype\FakeDraftStore;
use App\Prototype\FakeStore;
use Livewire\Component;

class DraftList extends PreviewComponent
{
    public ?string $confirmingDiscardUuid = null;
    public ?string $flashMessage = null;

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
        FakeDraftStore::discard($uuid);
        $this->confirmingDiscardUuid = null;
        $this->dispatch('ui-toast', message: 'Draft dibuang.');
        $this->dispatch('draft-discarded', uuid: $uuid);
    }

    public function dismissFlash(): void
    {
        $this->flashMessage = null;
    }

    public function render()
    {
        $rawDrafts = FakeDraftStore::all();

        // Urutkan draft terbaru di atas
        usort($rawDrafts, function ($a, $b) {
            $ta = $a['updated_at'] ?? '';
            $tb = $b['updated_at'] ?? '';
            return strcmp($tb, $ta);
        });

        $drafts = [];
        foreach ($rawDrafts as $d) {
            $project = ! empty($d['project_id']) ? FakeStore::findProject($d['project_id']) : null;
            $isClosed = false;
            if (! empty($d['progress_id'])) {
                $linked = FakeStore::findProgress($d['progress_id']);
                $isClosed = strtolower($linked['status'] ?? 'open') === 'closed';
            }

            $drafts[] = array_merge($d, [
                'project' => $project,
                'is_closed' => $isClosed,
            ]);
        }

        return view('livewire.prototype.draft-list', [
            'drafts' => $drafts,
        ]);
    }
}
