<?php

namespace Database\Factories;

use App\Models\Progress;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NextActionFactory extends Factory
{
    public function definition(): array
    {
        return ['progress_id' => Progress::factory(), 'client_id' => (string) Str::uuid(), 'description' => 'Next action fiktif', 'actual_date' => null];
    }
}
