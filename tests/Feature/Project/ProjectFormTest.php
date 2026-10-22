<?php

namespace Tests\Feature\Project;

use App\Actions\SaveProject;
use App\Livewire\Projects\ProjectForm;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectFormTest extends TestCase
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
     * AT01: JO yang sudah ada ditolak pada project baru; tidak muncul project kedua.
     */
    public function test_at01_duplicate_jo_no_is_rejected_on_create(): void
    {
        Project::factory()->create([
            'jo_no' => '0012/NM/2026',
            'part_no' => 'PART-ORIG-01',
            'part_name' => 'Original Part',
        ]);

        Livewire::test(ProjectForm::class)
            ->set('jo_no', '0012/NM/2026')
            ->set('part_no', 'PART-NEW-02')
            ->set('part_name', 'New Part Name')
            ->call('save')
            ->assertHasErrors(['jo_no']);

        $this->assertSame(1, Project::count());
    }

    /**
     * AT02: Part No yang sudah dipakai JO lain ditolak; nol di depan tetap utuh.
     */
    public function test_at02_duplicate_part_no_is_rejected_and_leading_zeros_preserved(): void
    {
        Project::factory()->create([
            'jo_no' => 'JO-ORIG-01',
            'part_no' => '0091-XYZ-01',
            'part_name' => 'Original Part',
        ]);

        Livewire::test(ProjectForm::class)
            ->set('jo_no', 'JO-NEW-02')
            ->set('part_no', '0091-XYZ-01')
            ->set('part_name', 'New Part')
            ->call('save')
            ->assertHasErrors(['part_no']);

        $this->assertSame(1, Project::count());

        // Test creating a new valid project with leading zeros
        Livewire::test(ProjectForm::class)
            ->set('jo_no', '0007/NM/2026')
            ->set('part_no', '0042-ABC-99')
            ->set('part_name', 'Part with Leading Zero')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, Project::count());
        $created = Project::where('jo_no', '0007/NM/2026')->first();
        $this->assertNotNull($created);
        $this->assertSame('0007/NM/2026', $created->jo_no);
        $this->assertSame('0042-ABC-99', $created->part_no);
    }

    /**
     * AT04: T0 Target/Actual, jadwal, dan Event 1 sampai 3 tersimpan manual.
     */
    public function test_at04_manual_dates_and_events_saved(): void
    {
        Livewire::test(ProjectForm::class)
            ->set('jo_no', 'JO-DATES-01')
            ->set('part_no', 'PART-DATES-01')
            ->set('part_name', 'Part with Dates')
            ->set('date_receive', '2026-10-01')
            ->set('t0_target', '2026-10-10')
            ->set('t0_actual', '2026-10-12')
            ->set('plan_start', '2026-10-02')
            ->set('plan_finish', '2026-10-20')
            ->set('actual_start', '2026-10-03')
            ->set('actual_finish', '2026-10-21')
            ->set('event_1', '2026-11-01')
            ->set('event_2', '2026-11-15')
            ->set('event_3', '2026-12-01')
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::where('jo_no', 'JO-DATES-01')->firstOrFail();
        $this->assertSame('2026-10-01', $project->date_receive->format('Y-m-d'));
        $this->assertSame('2026-10-10', $project->t0_target->format('Y-m-d'));
        $this->assertSame('2026-10-12', $project->t0_actual->format('Y-m-d'));
        $this->assertSame('2026-10-02', $project->plan_start->format('Y-m-d'));
        $this->assertSame('2026-10-20', $project->plan_finish->format('Y-m-d'));
        $this->assertSame('2026-10-03', $project->actual_start->format('Y-m-d'));
        $this->assertSame('2026-10-21', $project->actual_finish->format('Y-m-d'));
        $this->assertSame('2026-11-01', $project->event_1->format('Y-m-d'));
        $this->assertSame('2026-11-15', $project->event_2->format('Y-m-d'));
        $this->assertSame('2026-12-01', $project->event_3->format('Y-m-d'));
    }

    /**
     * AT06: Raw numeric text values preserved as text (1,5 and 1.5).
     */
    public function test_at06_raw_numeric_values_preserved_as_text(): void
    {
        Livewire::test(ProjectForm::class)
            ->set('jo_no', 'JO-RAW-01')
            ->set('part_no', 'PART-RAW-01')
            ->set('part_name', 'Part with Raw Text')
            ->set('material_thickness_raw', '1,5')
            ->set('material_width_raw', '12.5')
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::where('jo_no', 'JO-RAW-01')->firstOrFail();
        $this->assertSame('1,5', $project->material_thickness_raw);
        $this->assertSame('12.5', $project->material_width_raw);
    }

    /**
     * Edit project: mengedit project yang sudah ada mempertahankan JO dan Part No sendiri tanpa error.
     */
    public function test_edit_project_allows_same_jo_and_part_no(): void
    {
        $project = Project::factory()->create([
            'jo_no' => 'JO-EDIT-01',
            'part_no' => 'PART-EDIT-01',
            'part_name' => 'Original Part Name',
        ]);

        Livewire::test(ProjectForm::class, ['projectId' => $project->id])
            ->assertSet('jo_no', 'JO-EDIT-01')
            ->assertSet('part_no', 'PART-EDIT-01')
            ->set('part_name', 'Updated Part Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Updated Part Name', $project->fresh()->part_name);
        $this->assertSame('JO-EDIT-01', $project->fresh()->jo_no);
        $this->assertSame('PART-EDIT-01', $project->fresh()->part_no);
    }

    /**
     * Required field validation.
     */
    public function test_required_fields_validation(): void
    {
        Livewire::test(ProjectForm::class)
            ->set('jo_no', '')
            ->set('part_no', '')
            ->set('part_name', '')
            ->call('save')
            ->assertHasErrors(['jo_no', 'part_no', 'part_name']);
    }

    /**
     * Action SaveProject: directly test Action and race condition handling.
     */
    public function test_save_project_action_handles_uniqueness(): void
    {
        $action = new SaveProject;
        $p1 = $action->handle([
            'jo_no' => 'JO-ACTION-01',
            'part_no' => 'PART-ACTION-01',
            'part_name' => 'Action Part 1',
        ]);

        $this->assertInstanceOf(Project::class, $p1);
        $this->assertDatabaseHas('projects', ['jo_no' => 'JO-ACTION-01']);

        // Duplicate JO
        $this->expectException(ValidationException::class);
        $action->handle([
            'jo_no' => 'JO-ACTION-01',
            'part_no' => 'PART-ACTION-02',
            'part_name' => 'Action Part 2',
        ]);
    }

    /**
     * Process dropdown options and saving selected process.
     */
    public function test_process_dropdown_options_and_saving(): void
    {
        $comp = Livewire::test(ProjectForm::class)
            ->assertSeeHtml('<select')
            ->assertSeeHtml('id="master-process"');

        foreach (Project::PROCESS_OPTIONS as $option) {
            $comp->assertSee($option);
        }

        $comp->set('jo_no', 'JO-PROC-01')
            ->set('part_no', 'PART-PROC-01')
            ->set('part_name', 'Part Process Test')
            ->set('process', 'Stamping Progressive')
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::where('jo_no', 'JO-PROC-01')->firstOrFail();
        $this->assertSame('Stamping Progressive', $project->process);
    }
}
