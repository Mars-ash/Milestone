<?php

namespace Tests\Feature\Progress;

use App\Actions\SaveProgress;
use App\Livewire\Progresses\ProgressEditor;
use App\Models\NextAction;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class NextActionTest extends TestCase
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
            'jo_no' => '0091/NA/2026',
            'part_no' => '0091-XYZ-01',
            'part_name' => 'BRACKET NEXT ACTION',
        ]);
    }

    /**
     * AT08: Tiga Next Action mandiri tersimpan pada progress tanpa problem;
     * Setiap baris memiliki ID stabil, PIC teks bebas, dan actual date nullable.
     * Mengedit PIC pada satu baris mempertahankan baris lainnya secara utuh.
     */
    public function test_at08_three_standalone_next_actions_saved_without_problem_and_edit_preserves_others(): void
    {
        $this->actingAs($this->admin);

        // Input progress tanpa problem (problem dibiarkan kosong sehingga cleanProblems mengabaikannya)
        $component = Livewire::test(ProgressEditor::class, ['projectId' => $this->project->id])
            ->set('activityDate', '2026-10-19')
            ->set('activity', 'Sent Sample')
            ->set('problems.0.description', '') // Empty problem row should be ignored
            // Next Action 1
            ->set('nextActions.0.description', 'Kirim sampel 5 pcs ke customer')
            ->set('nextActions.0.pic', 'Ahmad Logistik')
            ->set('nextActions.0.actual_date', '2026-10-21');

        // Tambah Next Action 2
        $component->call('addNextAction')
            ->set('nextActions.1.description', 'Konfirmasi penerimaan via telepon')
            ->set('nextActions.1.pic', 'Dewi Marketing')
            ->set('nextActions.1.actual_date', '2026-10-22');

        // Tambah Next Action 3
        $component->call('addNextAction')
            ->set('nextActions.2.description', 'Persiapkan tooling backup')
            ->set('nextActions.2.pic', 'Bambang Workshop')
            ->set('nextActions.2.actual_date', null);

        $component->call('save')
            ->assertHasNoErrors();

        $progress = Progress::firstOrFail();
        $this->assertSame('Open', $progress->status);
        $this->assertCount(0, $progress->problems);

        $nextActions = $progress->nextActions()->orderBy('position')->get();
        $this->assertCount(3, $nextActions);

        $this->assertSame('Kirim sampel 5 pcs ke customer', $nextActions[0]->description);
        $this->assertSame('Ahmad Logistik', $nextActions[0]->pic);
        $this->assertSame('2026-10-21', $nextActions[0]->actual_date?->format('Y-m-d'));
        $this->assertSame(0, $nextActions[0]->position);

        $this->assertSame('Konfirmasi penerimaan via telepon', $nextActions[1]->description);
        $this->assertSame('Dewi Marketing', $nextActions[1]->pic);
        $this->assertSame('2026-10-22', $nextActions[1]->actual_date?->format('Y-m-d'));
        $this->assertSame(1, $nextActions[1]->position);

        $this->assertSame('Persiapkan tooling backup', $nextActions[2]->description);
        $this->assertSame('Bambang Workshop', $nextActions[2]->pic);
        $this->assertNull($nextActions[2]->actual_date);
        $this->assertSame(2, $nextActions[2]->position);

        $act1Id = $nextActions[0]->id;
        $act2Id = $nextActions[1]->id;
        $act3Id = $nextActions[2]->id;

        // Edit salah satu PIC (misal Next Action 2 diubah PIC-nya ke 'Rina Sales')
        $editComponent = Livewire::test(ProgressEditor::class, [
            'projectId' => $this->project->id,
            'progressId' => $progress->id,
        ])
            ->set('nextActions.1.pic', 'Rina Sales');

        $editComponent->call('save')
            ->assertHasNoErrors();

        $reloaded = $progress->fresh(['nextActions']);
        $reloadedActions = $reloaded->nextActions()->orderBy('position')->get();
        $this->assertCount(3, $reloadedActions);

        // ID tetap sama
        $this->assertSame($act1Id, $reloadedActions[0]->id);
        $this->assertSame('Ahmad Logistik', $reloadedActions[0]->pic);

        $this->assertSame($act2Id, $reloadedActions[1]->id);
        $this->assertSame('Rina Sales', $reloadedActions[1]->pic);
        $this->assertSame('Konfirmasi penerimaan via telepon', $reloadedActions[1]->description);

        $this->assertSame($act3Id, $reloadedActions[2]->id);
        $this->assertSame('Bambang Workshop', $reloadedActions[2]->pic);
    }

    /**
     * AT10: Actual date pada Next Action nullable dan tidak menentukan status Open/Closed.
     * Baris dengan PIC atau actual_date saja (tanpa deskripsi) tetap disimpan.
     * Baris baru yang seluruhnya kosong diabaikan.
     */
    public function test_at10_nullable_actual_date_and_partial_rows_preserved(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(ProgressEditor::class, ['projectId' => $this->project->id])
            ->set('activityDate', '2026-10-19')
            ->set('activity', 'Trial')
            // Row 0: hanya isi PIC
            ->set('nextActions.0.description', '')
            ->set('nextActions.0.pic', 'PIC Khusus')
            ->set('nextActions.0.actual_date', null);

        // Row 1: hanya isi actual date
        $component->call('addNextAction')
            ->set('nextActions.1.description', '')
            ->set('nextActions.1.pic', '')
            ->set('nextActions.1.actual_date', '2026-10-25');

        // Row 2: seluruhnya kosong (akan dibersihkan oleh cleanNextActions)
        $component->call('addNextAction')
            ->set('nextActions.2.description', '')
            ->set('nextActions.2.pic', '')
            ->set('nextActions.2.actual_date', null);

        $component->call('save')
            ->assertHasNoErrors();

        $progress = Progress::firstOrFail();
        $this->assertSame('Open', $progress->status);

        $actions = $progress->nextActions()->orderBy('position')->get();
        $this->assertCount(2, $actions);

        $this->assertSame('PIC Khusus', $actions[0]->pic);
        $this->assertSame('', (string) $actions[0]->description);
        $this->assertNull($actions[0]->actual_date);

        $this->assertSame('2026-10-25', $actions[1]->actual_date?->format('Y-m-d'));
        $this->assertSame('', (string) $actions[1]->pic);
    }

    /**
     * Kontrol hapus Next Action:
     * - Baris baru yang belum tersimpan dapat dihapus jika total baris > 1.
     * - Baris tersimpan tidak dapat dihapus melalui tombol hapus (harus melalui edit).
     */
    public function test_removal_controls_for_new_and_stored_next_actions(): void
    {
        $this->actingAs($this->admin);

        $progress = Progress::create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $storedAction = NextAction::create([
            'progress_id' => $progress->id,
            'client_id' => (string) Str::uuid(),
            'description' => 'Aksi tersimpan',
            'position' => 0,
        ]);

        $component = Livewire::test(ProgressEditor::class, [
            'projectId' => $this->project->id,
            'progressId' => $progress->id,
        ]);

        // Coba hapus aksi tersimpan langsung -> ditolak
        $component->call('promptRemove', 'next_action', $storedAction->client_id)
            ->assertHasErrors(['remove' => 'Catatan tersimpan dapat dikoreksi melalui edit.']);

        // Tambah baris baru
        $component->call('addNextAction');
        $newClientId = $component->get('nextActions.1.client_id');
        $this->assertNotNull($newClientId);

        // Hapus baris baru -> berhasil via dialog
        $component->call('promptRemove', 'next_action', $newClientId)
            ->assertSet('pendingRemoval.type', 'next_action')
            ->assertSet('pendingRemoval.client_id', $newClientId);

        $component->call('confirmRemove');
        $this->assertCount(1, $component->get('nextActions'));
        $this->assertSame($storedAction->id, $component->get('nextActions.0.id'));
    }

    /**
     * Cross-progress injection rejection:
     * Menyuntikkan ID Next Action milik progress lain harus ditolak oleh SaveProgress.
     */
    public function test_cross_progress_next_action_id_injection_rejected(): void
    {
        $this->actingAs($this->admin);

        $otherProgress = Progress::create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-18',
            'activity' => 'Audit',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $victimAction = NextAction::create([
            'progress_id' => $otherProgress->id,
            'client_id' => (string) Str::uuid(),
            'description' => 'Next Action milik progress lain',
            'position' => 0,
        ]);

        $saveProgress = app(SaveProgress::class);

        $payload = [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'next_actions' => [
                [
                    'id' => $victimAction->id, // Mencoba menyuntikkan ID dari otherProgress
                    'client_id' => $victimAction->client_id,
                    'position' => 0,
                    'description' => 'Bajak action!',
                ],
            ],
        ];

        $this->expectException(ValidationException::class);
        $saveProgress->saveBasic($this->admin, $payload);
    }
}
