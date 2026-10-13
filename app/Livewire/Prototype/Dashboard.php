<?php

namespace App\Livewire\Prototype;

use App\Prototype\FakeStore;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Search text filter across JO, Part, Customer, Notes, Problems.
     */
    public string $search = '';

    /**
     * Status filter: 'Open' (default), 'Closed', or 'all'.
     */
    public string $status = 'Open';

    /**
     * Activity filter: '', 'Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'.
     */
    public string $activity = '';

    /**
     * Customer filter: exact customer name or empty for all.
     */
    public string $customer = '';

    /**
     * Free text search for PIC name.
     */
    public string $pic = '';

    /**
     * Role of the PIC to search: 'progress' (default), 'corrective_action', 'next_action', 'feedback', 'all'.
     */
    public string $picRole = 'progress';

    /**
     * Sort order: 'oldest' (default) or 'newest'.
     */
    public string $sort = 'oldest';

    /**
     * Optional custom year-month override for global stats testing (e.g. '2026-10').
     */
    public ?string $yearMonth = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'Open'],
        'activity' => ['except' => ''],
        'customer' => ['except' => ''],
        'pic' => ['except' => ''],
        'picRole' => ['except' => 'progress'],
        'sort' => ['except' => 'oldest'],
    ];

    /**
     * Reset all filters back to default state (FR18, AT24).
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = 'Open';
        $this->activity = '';
        $this->customer = '';
        $this->pic = '';
        $this->picRole = 'progress';
        $this->sort = 'oldest';
    }

    /**
     * Determine if current filter differs from default state.
     */
    public function isFiltered(): bool
    {
        return $this->search !== ''
            || $this->status !== 'Open'
            || $this->activity !== ''
            || $this->customer !== ''
            || $this->pic !== ''
            || $this->picRole !== 'progress'
            || $this->sort !== 'oldest';
    }

    public function render()
    {
        // 3 Global summary numbers - immune to table filters (FR17 & AT23)
        $globalStats = FakeStore::getGlobalStats($this->yearMonth);

        // Filter options
        $customers = FakeStore::getCustomers();

        // Filtered work list
        $filters = [
            'search' => $this->search,
            'status' => $this->status,
            'activity' => $this->activity,
            'customer' => $this->customer,
            'pic' => $this->pic,
            'pic_role' => $this->picRole,
            'sort' => $this->sort,
        ];

        $progresses = FakeStore::getProgresses($filters);
        $drafts = FakeStore::getDrafts();

        return view('livewire.prototype.dashboard', [
            'globalStats' => $globalStats,
            'customers' => $customers,
            'progresses' => $progresses,
            'drafts' => $drafts,
            'isFiltered' => $this->isFiltered(),
        ]);
    }
}
