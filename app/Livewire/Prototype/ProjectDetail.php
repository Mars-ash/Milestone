<?php

namespace App\Livewire\Prototype;

use App\Prototype\FakeStore;
use Livewire\Component;

class ProjectDetail extends PreviewComponent
{
    public string $projectId;
    public ?array $project = null;
    public array $progresses = [];

    public function mount(string $projectId): void
    {
        $this->projectId = $projectId;
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->project = FakeStore::findProject($this->projectId);
        if ($this->project) {
            $this->progresses = FakeStore::getProgresses(['project_id' => $this->projectId, 'status' => 'all']);
        }
    }

    public function render()
    {
        return view('livewire.prototype.project-detail');
    }
}
