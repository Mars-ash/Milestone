<?php

namespace Tests;

use App\Models\User;

abstract class PrototypeTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // No business DB fixtures enter the preview adapter. This is a session identity only.
        $user = new User(['name' => 'Admin Fiktif', 'email' => 'preview@example.test']);
        $user->id = 1;
        $user->admin_slot = true;
        $this->actingAs($user);
    }
}
