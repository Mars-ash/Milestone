<?php

namespace Tests\Feature\Import;

use App\Models\CorrectiveAction;
use App\Models\Feedback;
use App\Models\ImportRecord;
use App\Models\LegacyDocumentMetadata;
use App\Models\NextAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\Project;
use App\Services\Import\AccessImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_reimporting_same_dataset_does_not_duplicate_any_records(): void
    {
        $importer = new AccessImporter();

        // 1st run
        $report1 = $importer->run();
        $this->assertSame(51, $report1['projects']['imported']);
        $this->assertSame(266, $report1['progresses']['imported']);

        $projCount1 = Project::count();
        $progCount1 = Progress::count();
        $probCount1 = Problem::count();
        $caCount1 = CorrectiveAction::count();
        $naCount1 = NextAction::count();
        $fbCount1 = Feedback::count();
        $docCount1 = LegacyDocumentMetadata::count();
        $importRecordCount1 = ImportRecord::count();

        $this->assertSame(51, $projCount1);
        $this->assertSame(266, $progCount1);
        $this->assertGreaterThan(0, $probCount1);
        $this->assertGreaterThan(0, $caCount1);
        $this->assertGreaterThan(0, $naCount1);
        $this->assertGreaterThan(0, $fbCount1);
        $this->assertGreaterThan(0, $docCount1);

        // 2nd run
        $report2 = $importer->run();
        $this->assertSame(0, $report2['projects']['imported']);
        $this->assertSame(51, $report2['projects']['updated']);
        $this->assertSame(0, $report2['progresses']['imported']);
        $this->assertSame(266, $report2['progresses']['updated']);

        // Assert all counts strictly identical
        $this->assertSame($projCount1, Project::count());
        $this->assertSame($progCount1, Progress::count());
        $this->assertSame($probCount1, Problem::count());
        $this->assertSame($caCount1, CorrectiveAction::count());
        $this->assertSame($naCount1, NextAction::count());
        $this->assertSame($fbCount1, Feedback::count());
        $this->assertSame($docCount1, LegacyDocumentMetadata::count());
        $this->assertSame($importRecordCount1, ImportRecord::count());
    }
}
