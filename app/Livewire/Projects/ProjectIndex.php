<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $customer = '';

    public string $model = '';

    public int $perPage = 15;

    public ?int $editingProjectId = null;

    public bool $showFormModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'customer' => ['except' => ''],
        'model' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'project-saved' => '$refresh',
        'close-project-dialog' => 'closeFormModal',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCustomer(): void
    {
        $this->resetPage();
    }

    public function updatingModel(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->editingProjectId = null;
        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->editingProjectId = $id;
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->editingProjectId = null;
    }

    public function render()
    {
        $query = Project::query()->withCount('progresses');

        if (filled($this->search)) {
            $term = '%'.strtolower(trim($this->search)).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(jo_no) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(part_no) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(part_name) LIKE ?', [$term]);
            });
        }

        if (filled($this->customer)) {
            $query->where('customer', $this->customer);
        }

        if (filled($this->model)) {
            $query->where('model', $this->model);
        }

        $projects = $query->orderBy('jo_no')->paginate($this->perPage);

        $customers = Project::whereNotNull('customer')
            ->where('customer', '!=', '')
            ->distinct()
            ->orderBy('customer')
            ->pluck('customer');

        $models = Project::whereNotNull('model')
            ->where('model', '!=', '')
            ->distinct()
            ->orderBy('model')
            ->pluck('model');

        return view('livewire.projects.project-index', [
            'projects' => $projects,
            'customers' => $customers,
            'models' => $models,
        ]);
    }
}
