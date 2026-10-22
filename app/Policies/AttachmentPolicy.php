<?php

namespace App\Policies;

use App\Models\User;

class AttachmentPolicy extends AdminPolicy
{
    public function view(User $user, $record): bool
    {
        return parent::view($user, $record) && (int) $record->uploaded_by === (int) $user->id
            && ($record->draft_id === null || (int) $record->draft?->admin_id === (int) $user->id);
    }

    public function download(User $user, $record): bool
    {
        return $this->view($user, $record) && $record->state === 'attached';
    }

    public function update(User $user, $record): bool
    {
        $progress = $record->progress ?? $record->problem?->progress ?? $record->feedback?->progress ?? $record->draft?->progress;

        return $this->view($user, $record) && ($progress === null || $progress->status === 'Open');
    }
}
