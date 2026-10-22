<?php

namespace Tests\Fixtures;

use App\Models\CorrectiveAction;
use App\Models\Feedback;
use App\Models\NextAction;
use App\Models\Problem;
use App\Models\Progress;

class FoundationGraph
{
    public static function create(): Progress
    {
        $progress = Progress::factory()->create();
        foreach ([0, 1] as $i) {
            $problem = Problem::factory()->create(['progress_id' => $progress->id, 'position' => $i]);
            foreach ([0, 1] as $j) {
                CorrectiveAction::factory()->create(['problem_id' => $problem->id, 'position' => $j, 'pic' => "PIC $i-$j Fiktif", 'actual_date' => $j === 0 ? null : '2026-10-15']);
            }
        }
        NextAction::factory()->count(3)->create(['progress_id' => $progress->id]);
        Feedback::factory()->count(2)->create(['progress_id' => $progress->id]);

        return $progress->fresh(['project', 'problems.correctiveActions', 'nextActions', 'feedbacks']);
    }
}
