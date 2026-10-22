<?php

namespace App\Actions;

use App\Data\ProgressResult;
use App\Exceptions\VersionConflict;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class CloseProgress
{
    public function handle(User $admin, int $progressId, int $expectedRevision): ProgressResult
    {
        return DB::transaction(function () use ($admin, $progressId, $expectedRevision) {
            $progress = Progress::lockForUpdate()->findOrFail($progressId);
            Gate::forUser($admin)->authorize('close', $progress);
            if ($progress->revision !== $expectedRevision) {
                throw new VersionConflict;
            }
            if ($progress->status !== 'Closed') {
                $progress->status = 'Closed';
                $progress->revision++;
                $progress->save();
            }

            return new ProgressResult($progress, 'closed', 'Progress ditutup.');
        });
    }
}
