<?php

namespace Tests\Feature\Project;

use App\Livewire\Components\ProjectPicker;
use App\Models\Project;
use App\Models\User;
use App\Queries\ProjectLookup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectPickerTest extends TestCase
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

    public function test_project_lookup_search_and_find(): void
    {
        $p1 = Project::factory()->create([
            'jo_no' => '0012/NM/2026',
            'part_no' => '0091-XYZ-01',
            'part_name' => 'Cover Handle Front',
            'customer' => 'Customer A',
        ]);

        $p2 = Project::factory()->create([
            'jo_no' => '0013/NM/2026',
            'part_no' => '0092-XYZ-02',
            'part_name' => 'Bracket Base',
            'customer' => 'Customer B',
        ]);

        $lookup = new ProjectLookup;

        // Search by JO
        $results = $lookup->search('0012');
        $this->assertCount(1, $results);
        $this->assertSame($p1->id, $results->first()->id);

        // Search by Part No
        $results = $lookup->search('0092');
        $this->assertCount(1, $results);
        $this->assertSame($p2->id, $results->first()->id);

        // Search by Part Name
        $results = $lookup->search('bracket');
        $this->assertCount(1, $results);
        $this->assertSame($p2->id, $results->first()->id);

        // Find by ID
        $found = $lookup->find($p1->id);
        $this->assertNotNull($found);
        $this->assertSame('Cover Handle Front', $found->part_name);
    }

    public function test_project_picker_displays_selected_project_identity(): void
    {
        $project = Project::factory()->create([
            'jo_no' => '0014/NM/2026',
            'part_no' => '0094-ABC-01',
            'part_name' => 'Door Panel Assembly',
            'customer' => 'Customer C',
            'model' => 'K2SJ',
            'no_doc' => 'DOC-2026-99',
        ]);

        Livewire::test(ProjectPicker::class, ['selectedId' => $project->id])
            ->assertSee('0014/NM/2026')
            ->assertSee('Door Panel Assembly')
            ->assertSee('0094-ABC-01')
            ->assertSee('Customer C')
            ->assertSee('K2SJ')
            ->assertSee('DOC-2026-99');
    }

    /**
     * AT03: Koreksi Part Name/Customer/Model/No Doc master terlihat pada progress lama dan draft yang dibuka.
     */
    public function test_at03_master_update_reflected_immediately(): void
    {
        $project = Project::factory()->create([
            'jo_no' => '0015/NM/2026',
            'part_no' => '0095-ABC-02',
            'part_name' => 'Initial Part Name',
            'customer' => 'Initial Customer',
            'model' => 'Model V1',
            'no_doc' => 'DOC-V1',
        ]);

        $component = Livewire::test(ProjectPicker::class, ['selectedId' => $project->id]);
        $component->assertSee('Initial Part Name');
        $component->assertSee('Initial Customer');

        // Update Project master in DB
        $project->update([
            'part_name' => 'Updated Part Name Realtime',
            'customer' => 'Updated Customer Corp',
            'model' => 'Model V2',
            'no_doc' => 'DOC-V2',
        ]);

        // When picker re-renders or calls loadSelectedProject, it reflects updated master without snapshot
        $component->call('loadSelectedProject')
            ->assertSee('Updated Part Name Realtime')
            ->assertSee('Updated Customer Corp')
            ->assertSee('Model V2')
            ->assertSee('DOC-V2')
            ->assertDontSee('Initial Part Name');
    }

    public function test_project_picker_handles_missing_or_null_project(): void
    {
        Livewire::test(ProjectPicker::class, ['selectedId' => null])
            ->assertSee('Pilih JO No')
            ->assertSee('—');

        Livewire::test(ProjectPicker::class, ['selectedId' => 999999])
            ->assertSee('—');
    }
}
