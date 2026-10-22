<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgressFactory extends Factory
{
    public function definition(): array
    {
        return ['project_id' => Project::factory(), 'activity_date' => '2026-10-15', 'activity' => 'Trial', 'status' => 'Open', 'revision' => 1];
    }
}
