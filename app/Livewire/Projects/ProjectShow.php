<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class ProjectShow extends Component
{
    public int $projectId;

    public ?Project $project = null;

    public bool $showEditModal = false;

    protected $listeners = [
        'project-saved' => 'refreshProject',
        'close-project-dialog' => 'closeEditModal',
    ];

    public function mount(int $projectId): void
    {
        $this->projectId = $projectId;
        $this->loadProject();
    }

    public function loadProject(): void
    {
        $this->project = Project::with(['progresses' => function ($q) {
            $q->with('nextActions')->orderByDesc('activity_date')->orderByDesc('id');
        }])->find($this->projectId);
    }

    public function refreshProject(): void
    {
        $this->loadProject();
        $this->showEditModal = false;
    }

    public function openEdit(): void
    {
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    public function render()
    {
        return view('livewire.projects.project-show', [
            'project' => $this->project,
            'progresses' => $this->project?->progresses ?? collect(),
        ]);
    }
}
