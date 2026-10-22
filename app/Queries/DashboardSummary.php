<?php

namespace App\Queries;

use App\Models\ProgressDraft;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DashboardSummary
{
    /**
     * Hitung ringkasan 3 KPI dashboard utama secara efisien tanpa N+1 (FR17, AT23).
     *
     * @return array{
     *     open_projects: int,
     *     open_progress: int,
     *     closed_progress: int,
     *     active_drafts: int
     * }
     */
    public function getSummary(?User $admin = null): array
    {
        $progressCounts = DB::table('progresses')
            ->selectRaw("
                COUNT(*) FILTER (WHERE status = 'Open') as open_count,
                COUNT(*) FILTER (WHERE status = 'Closed') as closed_count,
                COUNT(DISTINCT project_id) FILTER (WHERE status = 'Open') as open_projects_count
            ")
            ->first();

        $openProgress = (int) ($progressCounts->open_count ?? 0);
        $closedProgress = (int) ($progressCounts->closed_count ?? 0);
        $openProjects = (int) ($progressCounts->open_projects_count ?? 0);

        $draftsQuery = ProgressDraft::where('state', 'active');
        if ($admin) {
            $draftsQuery->where('admin_id', $admin->id);
        }
        $activeDrafts = $draftsQuery->count();

        $now = now('Asia/Jakarta');
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $monthCount = (int) DB::table('progresses')
            ->whereNotNull('activity_date')
            ->whereBetween('activity_date', [$startOfMonth, $endOfMonth])
            ->count();

        $monthLabel = $now->locale('id')->isoFormat('MMMM YYYY');

        return [
            'open_projects' => $openProjects,
            'open_progress' => $openProgress,
            'open_count' => $openProgress,
            'closed_progress' => $closedProgress,
            'active_drafts' => $activeDrafts,
            'draft_count' => $activeDrafts,
            'current_month_count' => $monthCount,
            'current_month_label' => $monthLabel,
        ];
    }
}
