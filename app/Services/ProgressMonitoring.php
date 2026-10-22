<?php

namespace App\Services;

use App\Exceptions\FeatureNotReady;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

final class ProgressMonitoring
{
    /** Fase 5: paginated saved progresses, current project identity, separate active draft count. */
    public function list(User $admin, array $filters = []): LengthAwarePaginator
    {
        Gate::forUser($admin)->authorize('viewAny', Progress::class);
        throw new FeatureNotReady('Monitoring PostgreSQL (Fase 5)');
    }
}
