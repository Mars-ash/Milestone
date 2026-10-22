<?php

namespace Tests\Feature\Progress;

use App\Livewire\Progresses\ProgressEditor;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProgressEditorTest extends TestCase
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
            'jo_no' => '0030/NM/2026',
            'part_no' => '0030-XYZ-01',
            'part_name' => 'Support Bracket',
        ]);
        $this->actingAs($this->admin);
    }

    public function test_progress_editor_mounts_with_defaults(): void
    {
        Livewire::test(ProgressEditor::class)
            ->assertSet('activity', 'Trial')
            ->assertSet('isClosed', false)
            ->assertSee('Aktivitas')
            ->assertSee('Rincian trial');
    }

    /**
     * AT05: Kelima Activity memiliki field dan bagian sama; perubahan Activity tidak menghilangkan isian.
     */
    public function test_at05_all_five_activities_preserve_trial_fields_without_conditional_removal(): void
    {
        $component = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-19')
            ->set('processTrial', 'Forming Process')
            ->set('machine', 'AIDA 200T')
            ->set('dieHighRaw', '250,5')
            ->set('qtyMaterialRaw', '50.0');

        foreach (['Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'] as $act) {
            $component->set('activity', $act)
                ->assertSet('processTrial', 'Forming Process')
                ->assertSet('machine', 'AIDA 200T')
                ->assertSet('dieHighRaw', '250,5')
                ->assertSet('qtyMaterialRaw', '50.0')
                ->assertSee('Process Trial')
                ->assertSee('Mesin')
                ->assertSee('DH · Die High')
                ->assertSee('Qty Material')
                ->assertDontSee('Qty OK')
                ->assertDontSee('Qty NG');
        }
    }

    /**
     * AT06: PIC nama bebas tersimpan tanpa master; 1,5 dan 1.5 kembali persis seperti input.
     */
    public function test_at06_pic_customer_and_raw_numeric_saved_safely(): void
    {
        Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-19')
            ->set('activity', 'Trial')
            ->set('picCustomer', 'Budi Santoso Bebas')
            ->set('dieHighRaw', '1,5')
            ->set('qtyMaterialRaw', '1.5')
            ->set('description', 'Testing raw numeric and free PIC')
            ->call('save')
            ->assertHasNoErrors();

        $saved = Progress::where('project_id', $this->project->id)->firstOrFail();
        $this->assertSame('Budi Santoso Bebas', $saved->pic_customer);
        $this->assertSame('1,5', $saved->die_high_raw);
        $this->assertSame('1.5', $saved->qty_material_raw);
        $this->assertSame('Open', $saved->status);
    }

    public function test_edit_existing_progress_loads_data_and_updates(): void
    {
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-15',
            'activity' => 'Trial',
            'status' => 'Open',
            'description' => 'Original description',
        ]);

        Livewire::test(ProgressEditor::class, ['progressId' => $progress->id])
            ->assertSet('projectId', $this->project->id)
            ->assertSet('activityDate', '2026-10-15')
            ->assertSet('description', 'Original description')
            ->set('description', 'Updated description after edit')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Updated description after edit', $progress->fresh()->description);
    }

    public function test_closed_progress_cannot_be_saved(): void
    {
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'Closed',
        ]);

        Livewire::test(ProgressEditor::class, ['progressId' => $progress->id])
            ->assertSet('isClosed', true)
            ->call('save')
            ->assertHasErrors(['save']);
    }
}
