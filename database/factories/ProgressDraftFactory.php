<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProgressDraftFactory extends Factory
{
    public function definition(): array
    {
        return ['id' => (string) Str::uuid(), 'admin_id' => User::factory(), 'payload' => ['schema_version' => 1], 'state' => 'active'];
    }
}
