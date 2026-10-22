<?php

namespace App\Policies;

use App\Models\CorrectiveAction;
use App\Models\Feedback;
use App\Models\NextAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\User;

class AdminPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_slot === true;
    }

    public function view(User $user, $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, $record): bool
    {
        $progress = match (true) {
            $record instanceof Progress => $record,
            $record instanceof CorrectiveAction => $record->problem?->progress,
            $record instanceof Problem, $record instanceof NextAction,
            $record instanceof Feedback => $record->progress,
            default => null,
        };

        return $this->viewAny($user) && ($progress === null || $progress->status === 'Open');
    }

    public function close(User $user, $record): bool
    {
        return $this->viewAny($user);
    }

    public function reopen(User $user, $record): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, $record): bool
    {
        return false;
    }
}
