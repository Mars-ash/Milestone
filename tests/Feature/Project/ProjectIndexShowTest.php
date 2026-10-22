<?php

namespace Tests\Feature\Project;

use App\Livewire\Projects\ProjectIndex;
use App\Livewire\Projects\ProjectShow;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectIndexShowTest extends TestCase
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

    public function test_project_index_renders_and_lists_projects(): void
    {
        $project = Project::factory()->create([
            'jo_no' => '0012/NM/2026',
            'part_no' => '0091-XYZ-01',
            'part_name' => 'Bracket Assembly',
            'customer' => 'Customer A',
            'model' => 'K2SJ',
        ]);

        Progress::factory()->create(['project_id' => $project->id]);

        Livewire::test(ProjectIndex::class)
            ->assertSee('0012/NM/2026')
            ->assertSee('0091-XYZ-01')
            ->assertSee('Bracket Assembly')
            ->assertSee('Customer A')
            ->assertSee('K2SJ')
            ->assertSee('1 kegiatan');
    }

    public function test_search_by_jo_with_leading_zeros(): void
    {
        Project::factory()->create([
            'jo_no' => '0007/NM/2026',
            'part_no' => 'PART-0007',
            'part_name' => 'Zero Target Part',
        ]);

        Project::factory()->create([
            'jo_no' => '1007/NM/2026',
            'part_no' => 'PART-1007',
            'part_name' => 'Non Zero Target Part',
        ]);

        Livewire::test(ProjectIndex::class)
            ->set('search', '0007')
            ->assertSee('0007/NM/2026')
            ->assertDontSee('1007/NM/2026');
    }

    public function test_filter_by_customer_and_model(): void
    {
        Project::factory()->create([
            'jo_no' => 'JO-CUST-A',
            'part_no' => 'P-CUST-A',
            'part_name' => 'Part A',
            'customer' => 'Toyota',
            'model' => 'Innova',
        ]);

        Project::factory()->create([
            'jo_no' => 'JO-CUST-B',
            'part_no' => 'P-CUST-B',
            'part_name' => 'Part B',
            'customer' => 'Honda',
            'model' => 'Civic',
        ]);

        Livewire::test(ProjectIndex::class)
            ->set('customer', 'Toyota')
            ->assertSee('JO-CUST-A')
            ->assertDontSee('JO-CUST-B')
            ->set('customer', '')
            ->set('model', 'Civic')
            ->assertSee('JO-CUST-B')
            ->assertDontSee('JO-CUST-A');
    }

    public function test_project_show_displays_identity_and_spec_and_progress_list(): void
    {
        $project = Project::factory()->create([
            'jo_no' => '0015/NM/2026',
            'part_no' => '0042-DEF-02',
            'part_name' => 'Cover Handle',
            'customer' => 'Customer B',
            'model' => 'HK09',
            'material' => 'SPCC',
            'material_thickness_raw' => '1,5',
            'material_width_raw' => '120.0',
            't0_target' => '2026-10-25',
        ]);

        $prog = Progress::factory()->create([
            'project_id' => $project->id,
            'activity_date' => '2026-10-15',
            'activity' => 'Trial',
            'status' => 'Open',
        ]);

        Livewire::test(ProjectShow::class, ['projectId' => $project->id])
            ->assertSee('0015/NM/2026')
            ->assertSee('0042-DEF-02')
            ->assertSee('Cover Handle')
            ->assertSee('Customer B')
            ->assertSee('HK09')
            ->assertSee('SPCC')
            ->assertSee('1,5')
            ->assertSee('Trial')
            // No project status badge or percentage completion
            ->assertDontSee('Status Project')
            ->assertDontSee('% Selesai');
    }

    public function test_project_show_renders_empty_state_when_not_found(): void
    {
        Livewire::test(ProjectShow::class, ['projectId' => 999999])
            ->assertSee('Project tidak ditemukan');
    }
}
