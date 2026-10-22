<?php

namespace Tests\Feature\Progress;

use App\Actions\SaveProgress;
use App\Livewire\Progresses\ProgressEditor;
use App\Models\CorrectiveAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CorrectiveActionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->admin = User::factory()->create(['admin_slot' => true]);
        $this->project = Project::create([
            'jo_no' => '0090/NM/2026',
            'part_no' => '0090-ABC-01',
            'part_name' => 'BRACKET ASSEMBLY CA',
        ]);
    }

    /**
     * AT07: Dua problem masing-masing memiliki minimal dua corrective action;
     * setiap tindakan mempertahankan PIC dan actual date tersendiri tanpa target date.
     */
    public function test_at07_two_problems_with_two_corrective_actions_each_saved_under_correct_parent(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(ProgressEditor::class, ['projectId' => $this->project->id])
            ->set('activityDate', '2026-10-19')
            ->set('activity', 'Trial')
            ->set('dieHighRaw', '320,5')
            // Problem 1
            ->set('problems.0.description', 'Problem 1: Burr tebal di tepi potong')
            ->set('problems.0.corrective_actions.0.description', 'Tindakan 1.1: Poles punch cutting')
            ->set('problems.0.corrective_actions.0.pic', 'Budi Santoso')
            ->set('problems.0.corrective_actions.0.actual_date', '2026-10-20');

        // Add 2nd corrective action to Problem 1
        $prob1ClientId = $component->get('problems.0.client_id');
        $component->call('addCorrectiveAction', $prob1ClientId)
            ->set('problems.0.corrective_actions.1.description', 'Tindakan 1.2: Cek clearance pisau')
            ->set('problems.0.corrective_actions.1.pic', 'Joko Tooling')
            ->set('problems.0.corrective_actions.1.actual_date', '2026-10-21');

        // Add Problem 2
        $component->call('addProblem')
            ->set('problems.1.description', 'Problem 2: Spring back berlebih')
            ->set('problems.1.corrective_actions.0.description', 'Tindakan 2.1: Shim die bender')
            ->set('problems.1.corrective_actions.0.pic', 'Agus Maintenance')
            ->set('problems.1.corrective_actions.0.actual_date', '2026-10-22');

        // Add 2nd corrective action to Problem 2
        $prob2ClientId = $component->get('problems.1.client_id');
        $component->call('addCorrectiveAction', $prob2ClientId)
            ->set('problems.1.corrective_actions.1.description', 'Tindakan 2.2: Naikkan tekanan bantalan')
            ->set('problems.1.corrective_actions.1.pic', 'Siti Press')
            ->set('problems.1.corrective_actions.1.actual_date', '2026-10-23');

        $component->call('save')
            ->assertHasNoErrors();

        $progress = Progress::firstOrFail();
        $this->assertSame('Open', $progress->status);
        $this->assertSame('320,5', $progress->die_high_raw);

        $problems = $progress->problems()->with('correctiveActions')->get();
        $this->assertCount(2, $problems);

        // Problem 1 assertions
        $p1 = $problems[0];
        $this->assertSame('Problem 1: Burr tebal di tepi potong', $p1->description);
        $this->assertCount(2, $p1->correctiveActions);
        $this->assertSame('Tindakan 1.1: Poles punch cutting', $p1->correctiveActions[0]->description);
        $this->assertSame('Budi Santoso', $p1->correctiveActions[0]->pic);
        $this->assertSame('2026-10-20', $p1->correctiveActions[0]->actual_date->format('Y-m-d'));
        $this->assertSame('Tindakan 1.2: Cek clearance pisau', $p1->correctiveActions[1]->description);
        $this->assertSame('Joko Tooling', $p1->correctiveActions[1]->pic);
        $this->assertSame('2026-10-21', $p1->correctiveActions[1]->actual_date->format('Y-m-d'));

        // Problem 2 assertions
        $p2 = $problems[1];
        $this->assertSame('Problem 2: Spring back berlebih', $p2->description);
        $this->assertCount(2, $p2->correctiveActions);
        $this->assertSame('Tindakan 2.1: Shim die bender', $p2->correctiveActions[0]->description);
        $this->assertSame('Agus Maintenance', $p2->correctiveActions[0]->pic);
        $this->assertSame('2026-10-22', $p2->correctiveActions[0]->actual_date->format('Y-m-d'));
        $this->assertSame('Tindakan 2.2: Naikkan tekanan bantalan', $p2->correctiveActions[1]->description);
        $this->assertSame('Siti Press', $p2->correctiveActions[1]->pic);
        $this->assertSame('2026-10-23', $p2->correctiveActions[1]->actual_date->format('Y-m-d'));
    }

    /**
     * AT10: Actual date boleh kosong tanpa auto-close atau auto-date;
     * PIC/tanggal tanpa uraian tetap dianggap data valid.
     */
    public function test_at10_actual_date_nullable_and_action_with_only_pic_or_date_persisted(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(ProgressEditor::class, ['projectId' => $this->project->id])
            ->set('activityDate', '2026-10-19')
            ->set('activity', 'Trial')
            ->set('problems.0.description', 'Problem uji tanggal kosong')
            // Action 1: description & PIC only, actual_date null
            ->set('problems.0.corrective_actions.0.description', 'Rencana modifikasi mold')
            ->set('problems.0.corrective_actions.0.pic', 'PIC Tanpa Tanggal')
            ->set('problems.0.corrective_actions.0.actual_date', null);

        // Action 2: PIC & actual_date only, description null/empty
        $probClientId = $component->get('problems.0.client_id');
        $component->call('addCorrectiveAction', $probClientId)
            ->set('problems.0.corrective_actions.1.description', '')
            ->set('problems.0.corrective_actions.1.pic', 'PIC Dengan Tanggal Saja')
            ->set('problems.0.corrective_actions.1.actual_date', '2026-10-25');

        $component->call('save')
            ->assertHasNoErrors();

        $progress = Progress::firstOrFail();
        $this->assertSame('Open', $progress->status); // Still Open, no auto-close

        $problem = $progress->problems()->firstOrFail();
        $actions = $problem->correctiveActions;
        $this->assertCount(2, $actions);

        $this->assertSame('Rencana modifikasi mold', $actions[0]->description);
        $this->assertSame('PIC Tanpa Tanggal', $actions[0]->pic);
        $this->assertNull($actions[0]->actual_date);

        $this->assertEmpty($actions[1]->description);
        $this->assertSame('PIC Dengan Tanggal Saja', $actions[1]->pic);
        $this->assertSame('2026-10-25', $actions[1]->actual_date->format('Y-m-d'));
    }

    /**
     * AT11: Manipulasi ID tindakan silang antarproblem ditolak validasi DTO tanpa merusak data sumber.
     */
    public function test_at11_cross_problem_action_id_manipulation_rejected_without_corrupting_source(): void
    {
        $progress = Progress::create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $probA = Problem::create([
            'progress_id' => $progress->id,
            'client_id' => (string) Str::uuid(),
            'position' => 0,
            'description' => 'Problem A Asli',
        ]);

        $actionA = CorrectiveAction::create([
            'problem_id' => $probA->id,
            'client_id' => (string) Str::uuid(),
            'position' => 0,
            'description' => 'Tindakan Milik Problem A',
            'pic' => 'PIC A',
        ]);

        $probB = Problem::create([
            'progress_id' => $progress->id,
            'client_id' => (string) Str::uuid(),
            'position' => 1,
            'description' => 'Problem B Asli',
        ]);

        // Attempt to submit actionA id inside probB
        $maliciousPayload = [
            'schema_version' => 1,
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'problems' => [
                [
                    'id' => $probA->id,
                    'client_id' => $probA->client_id,
                    'position' => 0,
                    'description' => 'Problem A Asli',
                    'corrective_actions' => [],
                ],
                [
                    'id' => $probB->id,
                    'client_id' => $probB->client_id,
                    'position' => 1,
                    'description' => 'Problem B Asli',
                    'corrective_actions' => [
                        [
                            'id' => $actionA->id, // Cross-parent injection!
                            'client_id' => $actionA->client_id,
                            'position' => 0,
                            'description' => 'Hacked action moving to B',
                            'pic' => 'Hacker',
                        ],
                    ],
                ],
            ],
        ];

        $saveAction = app(SaveProgress::class);

        $this->expectException(ValidationException::class);

        try {
            $saveAction->saveBasic($this->admin, $maliciousPayload, $progress);
        } finally {
            // Source records MUST remain completely unchanged
            $actionAFresh = CorrectiveAction::findOrFail($actionA->id);
            $this->assertSame($probA->id, $actionAFresh->problem_id);
            $this->assertSame('Tindakan Milik Problem A', $actionAFresh->description);
            $this->assertSame('PIC A', $actionAFresh->pic);

            $this->assertSame(0, CorrectiveAction::where('problem_id', $probB->id)->count());
        }
    }

    public function test_corrective_action_remove_controls_and_saved_protection(): void
    {
        $this->actingAs($this->admin);

        $progress = Progress::create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $prob = Problem::create([
            'progress_id' => $progress->id,
            'client_id' => (string) Str::uuid(),
            'position' => 0,
            'description' => 'Problem Terdaftar',
        ]);

        $storedAction = CorrectiveAction::create([
            'problem_id' => $prob->id,
            'client_id' => (string) Str::uuid(),
            'position' => 0,
            'description' => 'Tindakan Tersimpan di DB',
            'pic' => 'PIC Tetap',
        ]);

        $component = Livewire::test(ProgressEditor::class, ['progressId' => $progress->id]);

        $probClientId = strtolower((string) $prob->client_id);
        $storedActionClientId = strtolower((string) $storedAction->client_id);

        // Attempting to remove stored action is rejected
        $component->call('promptRemove', 'corrective_action', $storedActionClientId, $probClientId)
            ->assertHasErrors(['remove']);

        // Add unsaved action
        $component->call('addCorrectiveAction', $probClientId);
        $actions = $component->get('problems.0.corrective_actions');
        $this->assertCount(2, $actions);
        $newActionClientId = $actions[1]['client_id'];

        // Prompt and confirm remove unsaved action succeeds
        $component->call('promptRemove', 'corrective_action', $newActionClientId, $probClientId)
            ->assertSet('pendingRemoval.type', 'corrective_action')
            ->call('confirmRemove');

        $this->assertCount(1, $component->get('problems.0.corrective_actions'));
    }
}
