<?php

namespace Tests\Feature\Report;

use App\Models\CorrectiveAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrialReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->create();
    }

    public function test_guests_cannot_access_trial_report(): void
    {
        $project = Project::factory()->create();
        $progress = Progress::factory()->create([
            'project_id' => $project->id,
            'activity' => 'Trial',
        ]);

        $response = $this->get(route('progresses.trial-report', $progress));
        $response->assertRedirect(route('login'));
    }

    public function test_non_trial_activity_returns_404(): void
    {
        $project = Project::factory()->create();
        $progress = Progress::factory()->create([
            'project_id' => $project->id,
            'activity' => 'Sent Sample',
        ]);

        $response = $this->actingAs($this->admin)->get(route('progresses.trial-report', $progress));
        $response->assertNotFound();
    }

    public function test_trial_report_renders_successfully_with_data_and_wet_signatures(): void
    {
        $project = Project::factory()->create([
            'part_name' => 'BRACKET FRONT ENGINE',
            'part_no' => 'BF-2026-X1',
            'customer' => 'PT ASTRA HONDA',
            'model' => 'K1A',
        ]);

        $progress = Progress::factory()->create([
            'project_id' => $project->id,
            'activity' => 'Trial',
            'activity_date' => '2026-10-21',
            'machine' => 'KOMATSU 200T',
            'tonage_raw' => '200T',
            'qty_ok_raw' => '450 pcs',
            'qty_ng_raw' => '50 pcs',
        ]);

        $problem = Problem::factory()->create([
            'progress_id' => $progress->id,
            'description' => 'Burr pada lubang pierce D8',
        ]);

        CorrectiveAction::factory()->create([
            'problem_id' => $problem->id,
            'description' => 'Grinding punch D8 dan cek clearance',
            'actual_date' => '2026-10-22',
            'pic' => 'Andi Tooling',
        ]);

        $response = $this->actingAs($this->admin)->get(route('progresses.trial-report', [
            'progress' => $progress,
            'tonage' => '250T',
            'qty_ok' => '480 pcs',
            'qty_ng' => '20 pcs',
        ]));

        $response->assertOk();
        $response->assertSee('TRIAL REPORT');
        $response->assertSee('BRACKET FRONT ENGINE');
        $response->assertSee('BF-2026-X1');
        $response->assertSee('PT ASTRA HONDA');
        $response->assertSee('KOMATSU 200T');
        $response->assertSee('250T');
        $response->assertSee('480 pcs');
        $response->assertSee('20 pcs');
        $response->assertSee('Burr pada lubang pierce D8');
        $response->assertSee('Grinding punch D8 dan cek clearance');
        $response->assertSee('Andi Tooling');
        $response->assertSee('21/10/2026');

        // Verify wet signature section has headers but empty names
        $response->assertSee('Prepared');
        $response->assertSee('Checked');
        $response->assertSee('Approved');
        $response->assertSee('trial-date-table');
        $response->assertSee('has-data');
        $response->assertSee('empty-row');

        // Check that tonage, qty ok, qty ng got updated on progress
        $progress->refresh();
        $this->assertEquals('250T', $progress->tonage_raw);
        $this->assertEquals('480 pcs', $progress->qty_ok_raw);
        $this->assertEquals('20 pcs', $progress->qty_ng_raw);
    }
}
