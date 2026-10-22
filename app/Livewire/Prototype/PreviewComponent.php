<?php

namespace App\Livewire\Prototype;

use App\Prototype\PrototypeManager;
use Illuminate\Auth\AuthenticationException;
use Livewire\Component;

abstract class PreviewComponent extends Component
{
    // Boot runs on initial render AND every Livewire message, including direct actions.
    public function boot(): void
    {
        if (! auth()->check()) {
            throw new AuthenticationException;
        }
        abort_unless(auth()->user()->admin_slot === true, 403);
        abort_unless(PrototypeManager::isEnabled(), 404);
    }
}
