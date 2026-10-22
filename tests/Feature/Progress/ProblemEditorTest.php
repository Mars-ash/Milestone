<?php

namespace Tests\Feature\Progress;

use App\Livewire\Progresses\ProgressEditor;
use App\Models\CorrectiveAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ProblemEditorTest extends TestCase
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
            'jo_no' => 'JO-P04-001',
            'part_no' => 'PN-P04-001',
            'part_name' => 'BRACKET REINFORCEMENT P04',
        ]);
    }

    public function test_p04_t01_add_three_problems_fill_second_add_new_and_save_preserves_state_and_ignores_blank_new_rows(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(ProgressEditor::class, ['projectId' => $this->project->id])
            ->set('activityDate', '2026-10-19')
            ->set('activity', 'Trial');

        $component->call('addProblem')
            ->assertDispatched('editor-focus', selector: '#problem-1')
            ->call('addProblem')
            ->assertDispatched('editor-focus', selector: '#problem-2');

        $problems = $component->get('problems');
        $this->assertCount(3, $problems);
        $secondClientId = $problems[1]['client_id'];

        $component->set('problems.1.description', 'Dimensi lubang bergeser 0,4 mm pada proses piercing')
            ->call('addProblem')
            ->assertDispatched('editor-focus', selector: '#problem-3');

        $afterAdd = $component->get('problems');
        $this->assertCount(4, $afterAdd);
        $this->assertSame($secondClientId, $afterAdd[1]['client_id']);
        $this->assertSame('Dimensi lubang bergeser 0,4 mm pada proses piercing', $afterAdd[1]['description']);

        $component->call('save')
            ->assertHasNoErrors();

        $progress = Progress::firstOrFail();
        $savedProblems = $progress->problems()->get();

        $this->assertCount(1, $savedProblems);
        $this->assertSame($secondClientId, strtolower($savedProblems->first()->client_id));
        $this->assertSame('Dimensi lubang bergeser 0,4 mm pada proses piercing', $savedProblems->first()->description);
        $this->assertSame(0, $savedProblems->first()->position);
    }

    public function test_p04_t01_editing_stored_problem_preserves_database_id_and_client_id_across_all_activities(): void
    {
        $this->actingAs($this->admin);

        $progress = Progress::create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Sent Sample',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $storedProblem = Problem::create([
            'progress_id' => $progress->id,
            'client_id' => (string) Str::uuid(),
            'position' => 0,
            'description' => 'Burr pada tepi potong sample awal',
        ]);

        $legacyProblem = Problem::create([
            'progress_id' => $progress->id,
            'client_id' => (string) Str::uuid(),
            'position' => 1,
            'description' => null,
        ]);
        CorrectiveAction::create([
            'problem_id' => $legacyProblem->id,
            'client_id' => (string) Str::uuid(),
            'position' => 0,
            'description' => 'Poles ulang die edge',
            'pic' => 'Budi',
        ]);

        $component = Livewire::test(ProgressEditor::class, ['progressId' => $progress->id]);

        foreach (['Send Document', 'Audit', 'Others', 'Trial', 'Sent Sample'] as $act) {
            $component->set('activity', $act);
            $this->assertCount(2, $component->get('problems'));
        }

        $component->call('removeProblem', strtolower($storedProblem->client_id))
            ->assertHasErrors(['remove']);

        $component->set('problems.0.description', 'Burr pada tepi potong telah dikoreksi')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, Problem::where('progress_id', $progress->id)->count());

        $freshStored = Problem::findOrFail($storedProblem->id);
        $this->assertSame(strtolower($storedProblem->client_id), strtolower($freshStored->client_id));
        $this->assertSame('Burr pada tepi potong telah dikoreksi', $freshStored->description);

        $freshLegacy = Problem::findOrFail($legacyProblem->id);
        $this->assertSame(strtolower($legacyProblem->client_id), strtolower($freshLegacy->client_id));
        $this->assertTrue(empty($freshLegacy->description));
    }
}