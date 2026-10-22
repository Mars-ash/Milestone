<?php

namespace Tests\Feature\Progress;

use App\Livewire\Progresses\ProgressEditor;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ValidationTest extends TestCase
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
            'jo_no' => '0088/NM/2026',
            'part_no' => 'PN-0088',
            'part_name' => 'Reinforce Bracket',
        ]);
        $this->actingAs($this->admin);
    }

    public function test_minimal_valid_progress_saves_successfully(): void
    {
        Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-19')
            ->set('activity', 'Trial')
            // All other fields remain null/empty
            ->call('save')
            ->assertHasNoErrors();

        $saved = Progress::where('project_id', $this->project->id)->firstOrFail();
        $this->assertSame('2026-10-19', $saved->activity_date->format('Y-m-d'));
        $this->assertSame('Trial', $saved->activity);
        $this->assertSame('Open', $saved->status);
        $this->assertFalse($saved->legacy_incomplete);
    }

    public function test_validation_errors_displayed_and_user_inputs_preserved(): void
    {
        $component = Livewire::test(ProgressEditor::class)
            // Leave projectId and activityDate null/empty
            ->set('description', 'User typed important note')
            ->set('picCustomer', 'Budi Free Text')
            ->set('dieHighRaw', '325,5')
            ->call('save')
            ->assertHasErrors(['projectId', 'activityDate'])
            ->assertSeeHtml('id="error-summary"')
            ->assertSee('Pilih JO No.')
            ->assertSee('Isi tanggal aktivitas.')
            // Confirm user inputs are preserved after validation failure
            ->assertSet('description', 'User typed important note')
            ->assertSet('picCustomer', 'Budi Free Text')
            ->assertSet('dieHighRaw', '325,5');
    }

    public function test_legacy_incomplete_record_displays_notice_and_does_not_auto_fill_date(): void
    {
        // Insert legacy incomplete progress directly with null activity_date
        $legacy = Progress::forceCreate([
            'project_id' => $this->project->id,
            'activity_date' => null,
            'activity' => 'Trial',
            'description' => 'Migrated Access note',
            'legacy_incomplete' => true,
            'status' => 'Open',
            'revision' => 1,
        ]);

        Livewire::test(ProgressEditor::class, ['progressId' => $legacy->id])
            ->assertSet('activityDate', null)
            ->assertSee('Data Legacy Incomplete')
            // User tries to save without filling activityDate
            ->call('save')
            ->assertHasErrors(['activityDate'])
            ->assertSee('Isi tanggal aktivitas.');
    }

    public function test_saving_legacy_incomplete_with_minimum_fields_clears_incomplete_flag(): void
    {
        $legacy = Progress::forceCreate([
            'project_id' => $this->project->id,
            'activity_date' => null,
            'activity' => 'Trial',
            'description' => 'Migrated record to be completed',
            'legacy_incomplete' => true,
            'status' => 'Open',
            'revision' => 1,
        ]);

        $this->assertTrue($legacy->legacy_incomplete);

        Livewire::test(ProgressEditor::class, ['progressId' => $legacy->id])
            ->set('activityDate', '2026-10-19')
            ->call('save')
            ->assertHasNoErrors();

        $fresh = $legacy->fresh();
        $this->assertSame('2026-10-19', $fresh->activity_date->format('Y-m-d'));
        $this->assertFalse($fresh->legacy_incomplete);
    }

    public function test_optional_fields_have_no_hidden_required_rules(): void
    {
        // Verify that description, PIC, event note, remark, and trial details are strictly optional
        Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-19')
            ->set('activity', 'Sent Sample')
            ->set('description', '')
            ->set('picCustomer', '')
            ->set('processTrial', '')
            ->set('machine', '')
            ->set('dieHighRaw', '')
            ->set('materialActualTrial', '')
            ->set('qtyMaterialRaw', '')
            ->set('qtyProcessRaw', '')
            ->set('qtySampleRaw', '')
            ->set('eventNote', '')
            ->set('remark', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('progresses', [
            'project_id' => $this->project->id,
            'activity' => 'Sent Sample',
            'status' => 'Open',
        ]);
    }
}
