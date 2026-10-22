<?php

namespace App\Livewire\Components;

use App\Models\Project;
use App\Queries\ProjectLookup;
use Livewire\Component;

class ProjectPicker extends Component
{
    public ?int $selectedId = null;

    public ?Project $project = null;

    public bool $readOnly = false;

    public ?string $name = 'projectId';

    public function mount(?int $selectedId = null, bool $readOnly = false, ?string $name = 'projectId'): void
    {
        $this->selectedId = $selectedId;
        $this->readOnly = $readOnly;
        $this->name = $name;
        $this->loadSelectedProject();
    }

    public function updatedSelectedId($value): void
    {
        $this->selectedId = $value ? (int) $value : null;
        $this->loadSelectedProject();
        $this->dispatch('project-selected', projectId: $this->selectedId, project: $this->project?->toArray());
    }

    public function loadSelectedProject(): void
    {
        $lookup = app(ProjectLookup::class);
        $this->project = $lookup->find($this->selectedId);
    }

    public function render()
    {
        $lookup = app(ProjectLookup::class);
        $projects = $lookup->allForPicker();

        return view('livewire.components.project-picker', [
            'projects' => $projects,
        ]);
    }
}
