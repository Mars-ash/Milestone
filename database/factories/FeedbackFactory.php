<?php

namespace Database\Factories;

use App\Models\Progress;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FeedbackFactory extends Factory
{
    public function definition(): array
    {
        return ['progress_id' => Progress::factory(), 'client_id' => (string) Str::uuid(), 'feedback_date' => null, 'content' => 'Feedback fiktif'];
    }
}
