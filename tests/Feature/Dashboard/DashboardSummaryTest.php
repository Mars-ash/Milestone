<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\DashboardOverview;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\Project;
use App\Models\User;
use App\Queries\DashboardSummary;
use App\Services\ProgressDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    /**
     * FR17, AT23: Tiga KPI ringkasan dashboard dihitung akurat dan idempoten:
     * - Open Project: jumlah project unik yang memiliki sedikitnya satu progress Open.
     * - Open Progress: seluruh progress berstatus Open.
     * - Closed Progress: seluruh progress berstatus Closed.
     * - Draft tidak dihitung ke Open maupun Closed.
     */
    public function test_at23_dashboard_three_kpis_accurate_and_exclude_drafts(): void
    {
        // Project 1: memiliki 2 progress Open dan 1 Closed
        $p1 = Project::factory()->create();
        Progress::factory()->create(['project_id' => $p1->id, 'status' => 'Open']);
        Progress::factory()->create(['project_id' => $p1->id, 'status' => 'Open']);
        Progress::factory()->create(['project_id' => $p1->id, 'status' => 'Closed']);

        // Project 2: memiliki 1 progress Closed saja (tidak ada Open)
        $p2 = Project::factory()->create();
        Progress::factory()->create(['project_id' => $p2->id, 'status' => 'Closed']);

        // Project 3: memiliki 1 progress Open
        $p3 = Project::factory()->create();
        Progress::factory()->create(['project_id' => $p3->id, 'status' => 'Open']);

        // Project 4: belum memiliki progress apapun
        Project::factory()->create();

        // Buat 3 Draft aktif (2 baru, 1 edit pada progress p1)
        app(ProgressDraftService::class)->saveDraft($this->admin, [
            'schema_version' => 1,
            'project_id' => $p1->id,
            'description' => 'Draft Baru 1',
        ]);
        app(ProgressDraftService::class)->saveDraft($this->admin, [
            'schema_version' => 1,
            'project_id' => $p2->id,
            'description' => 'Draft Baru 2',
        ]);

        $summary = app(DashboardSummary::class)->getSummary($this->admin);

        // Verifikasi KPI:
        // Open Projects: hanya p1 dan p3 yang memiliki minimal satu progress Open -> 2
        $this->assertSame(2, $summary['open_projects']);

        // Open Progress: total 3 progress Open (2 di p1, 1 di p3)
        $this->assertSame(3, $summary['open_progress']);

        // Closed Progress: total 2 progress Closed (1 di p1, 1 di p2)
        $this->assertSame(2, $summary['closed_progress']);

        // Draft Aktif: 2 draft
        $this->assertSame(2, $summary['active_drafts']);

        // Uji komponen Livewire DashboardOverview (FR17 & AT23: 3 kartu KPI ringkasan)
        Livewire::test(DashboardOverview::class)
            ->assertSeeHtml('data-testid="stat-open"')
            ->assertSeeHtml('03')
            ->assertSeeHtml('data-testid="stat-draft"')
            ->assertSeeHtml('02')
            ->assertSeeHtml('data-testid="stat-month"')
            ->assertSee('Progress Open')
            ->assertSee('Draft aktif')
            ->assertSee('Aktivitas bulan ini');
    }
}
