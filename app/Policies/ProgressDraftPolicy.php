<?php

namespace App\Policies;

use App\Models\User;

class ProgressDraftPolicy extends AdminPolicy
{
    public function view(User $user, $record): bool
    {
        return parent::view($user, $record) && (int) $record->admin_id === (int) $user->id;
    }

    public function update(User $user, $record): bool
    {
        return $this->view($user, $record) && $record->state === 'active';
    }

    public function delete(User $user, $record): bool
    {
        return $this->update($user, $record);
    }
}
