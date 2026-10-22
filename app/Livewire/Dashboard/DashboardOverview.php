<?php

namespace App\Livewire\Dashboard;

use App\Queries\DashboardSummary;
use Livewire\Component;

class DashboardOverview extends Component
{
    public function render(DashboardSummary $summaryQuery)
    {
        $admin = auth()->user();
        $summary = $summaryQuery->getSummary($admin);

        return view('livewire.dashboard.dashboard-overview', [
            'summary' => $summary,
        ]);
    }
}
