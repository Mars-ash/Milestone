<?php

namespace Database\Factories;

use App\Models\Problem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CorrectiveActionFactory extends Factory
{
    public function definition(): array
    {
        return ['problem_id' => Problem::factory(), 'client_id' => (string) Str::uuid(), 'pic' => 'Budi Fiktif', 'actual_date' => null];
    }
}
