<?php

namespace App\Livewire\Progresses;

use App\Models\Progress;
use App\Models\Project;
use App\Queries\DashboardSummary;
use App\Queries\ProgressFilterQuery;
use Livewire\Component;
use Livewire\WithPagination;

class ProgressIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'all'; // 'Open', 'Closed', 'all'

    public string $activity = '';

    public string $customer = '';

    public string $pic = '';

    public string $picRole = 'all'; // 'all', 'corrective_action', 'next_action', 'feedback', 'customer'

    public string $sort = 'newest'; // 'newest', 'oldest'

    public bool $advanced = false;

    public ?string $from = null;

    public ?string $to = null;

    public bool $isDashboard = false;

    public int $perPage = 15;

    public function mount(?bool $isDashboard = null, ?string $initialStatus = null): void
    {
        if ($isDashboard !== null) {
            $this->isDashboard = $isDashboard;
        }

        if ($this->isDashboard) {
            $this->status = $initialStatus ?? 'Open';
            $this->sort = 'oldest';
        } elseif ($initialStatus !== null) {
            $this->status = $initialStatus;
        }
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'activity' => ['except' => ''],
        'customer' => ['except' => ''],
        'pic' => ['except' => ''],
        'picRole' => ['except' => 'all'],
        'sort' => ['except' => 'newest'],
        'from' => ['except' => null],
        'to' => ['except' => null],
        'page' => ['except' => 1],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingActivity(): void
    {
        $this->resetPage();
    }

    public function updatingCustomer(): void
    {
        $this->resetPage();
    }

    public function updatingPic(): void
    {
        $this->resetPage();
    }

    public function updatingPicRole(): void
    {
        $this->resetPage();
    }

    public function updatingFrom(): void
    {
        $this->resetPage();
    }

    public function updatingTo(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->status = ($status === 'Semua') ? 'all' : $status;
        $this->resetPage();
    }

    public function toggleSort(): void
    {
        $this->sort = ($this->sort === 'newest') ? 'oldest' : 'newest';
        $this->resetPage();
    }

    public function toggleAdvanced(): void
    {
        $this->advanced = ! $this->advanced;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = 'all';
        $this->activity = '';
        $this->customer = '';
        $this->pic = '';
        $this->picRole = 'all';
        $this->from = null;
        $this->to = null;
        $this->sort = 'newest';
        $this->resetPage();
    }

    public function render(ProgressFilterQuery $filterQuery, DashboardSummary $summaryQuery)
    {
        $totalDbCount = Progress::count();
        $summary = $summaryQuery->getSummary(auth()->user());

        $statusCounts = [
            'all' => $totalDbCount,
            'Open' => $summary['open_progress'],
            'Closed' => $summary['closed_progress'],
        ];

        $filters = [
            'search' => $this->search,
            'status' => $this->status,
            'activity' => $this->activity,
            'customer' => $this->customer,
            'pic' => $this->pic,
            'pic_role' => $this->picRole,
            'from' => $this->from,
            'to' => $this->to,
            'sort' => $this->sort,
        ];

        $progresses = $filterQuery->build($filters)->paginate($this->perPage);

        $customers = Project::whereNotNull('customer')
            ->where('customer', '!=', '')
            ->distinct()
            ->orderBy('customer')
            ->pluck('customer');

        return view('livewire.progresses.progress-index', [
            'progresses' => $progresses,
            'customers' => $customers,
            'statusCounts' => $statusCounts,
            'totalDbCount' => $totalDbCount,
        ]);
    }
}
