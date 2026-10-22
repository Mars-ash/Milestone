<?php

namespace App\Services;

use App\Exceptions\FeatureNotReady;
use App\Models\ProgressDraft;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class AttachmentFinalizer
{
    /** Only server-generated IDs/keys. Fase 4 verifies bytes, checksum and ownership before promotion. */
    public function finalize(User $admin, ProgressDraft $draft, array $attachmentIds): void
    {
        Gate::forUser($admin)->authorize('update', $draft);
        throw new FeatureNotReady('Finalisasi lampiran privat (Fase 4)');
    }
}
