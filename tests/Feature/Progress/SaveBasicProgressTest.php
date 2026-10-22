<?php

namespace Tests\Feature\Progress;

use App\Actions\SaveProgress;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SaveBasicProgressTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->create();
        $this->project = Project::factory()->create([
            'jo_no' => '0020/NM/2026',
            'part_no' => '0020-PART-01',
            'part_name' => 'Bracket Arm',
        ]);
        $this->actingAs($this->admin);
    }

    public function test_create_basic_progress_sets_open_status_and_revision_1(): void
    {
        $action = new SaveProgress;
        $progress = $action->saveBasic($this->admin, [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'description' => 'First trial activity',
            'pic_customer' => 'Mr. Tanaka',
            'machine' => 'Press 300T',
            'die_high_raw' => '1,5',
            'qty_material_raw' => '100.5',
        ]);

        $this->assertInstanceOf(Progress::class, $progress);
        $this->assertSame('Open', $progress->status);
        $this->assertSame(1, $progress->revision);
        $this->assertSame('First trial activity', $progress->description);
        $this->assertSame('1,5', $progress->die_high_raw);
        $this->assertSame('100.5', $progress->qty_material_raw);
        $this->assertDatabaseHas('progresses', [
            'id' => $progress->id,
            'project_id' => $this->project->id,
            'activity' => 'Trial',
            'status' => 'Open',
        ]);
    }

    public function test_final_validation_requires_three_mandatory_fields(): void
    {
        $action = new SaveProgress;

        // Missing project_id
        try {
            $action->saveBasic($this->admin, [
                'activity_date' => '2026-10-19',
                'activity' => 'Trial',
            ]);
            $this->fail('Missing project_id should fail');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('project_id', $e->errors());
        }

        // Missing activity_date
        try {
            $action->saveBasic($this->admin, [
                'project_id' => $this->project->id,
                'activity' => 'Trial',
            ]);
            $this->fail('Missing activity_date should fail');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('activity_date', $e->errors());
        }

        // Missing activity
        try {
            $action->saveBasic($this->admin, [
                'project_id' => $this->project->id,
                'activity_date' => '2026-10-19',
            ]);
            $this->fail('Missing activity should fail');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('activity', $e->errors());
        }
    }

    public function test_invalid_activity_rejected(): void
    {
        $action = new SaveProgress;

        $this->expectException(ValidationException::class);
        $action->saveBasic($this->admin, [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Invalid Activity Type',
        ]);
    }

    public function test_edit_open_progress_updates_scalar_fields(): void
    {
        $action = new SaveProgress;
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-15',
            'activity' => 'Trial',
            'status' => 'Open',
            'description' => 'Original description',
        ]);

        $updated = $action->saveBasic($this->admin, [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-16',
            'activity' => 'Sent Sample',
            'description' => 'Updated description',
        ], $progress);

        $this->assertSame('Open', $updated->status);
        $this->assertSame('Sent Sample', $updated->activity);
        $this->assertSame('2026-10-16', $updated->activity_date->format('Y-m-d'));
        $this->assertSame('Updated description', $updated->description);
    }

    public function test_edit_closed_progress_is_rejected(): void
    {
        $action = new SaveProgress;
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'Closed',
        ]);

        $this->expectException(AuthorizationException::class);
        $action->saveBasic($this->admin, [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'description' => 'Trying to edit closed',
        ], $progress);

        $this->assertSame('Closed', $progress->fresh()->status);
    }
}
