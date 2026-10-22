<?php

namespace Tests\Feature\Drafts;

use App\Exceptions\VersionConflict;
use App\Livewire\Progresses\ProgressEditor;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\Project;
use App\Models\User;
use App\Services\ProgressDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DraftConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Project $project;

    private ProgressDraftService $draftService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->create();
        $this->project = Project::factory()->create([
            'jo_no' => '0077/NM/2026',
            'part_no' => '0077-XYZ-01',
            'part_name' => 'Floor Cross Member',
        ]);
        $this->draftService = app(ProgressDraftService::class);
        $this->actingAs($this->admin);
    }

    /**
     * AT17: Out of order requests - Request lama yang tiba belakangan
     * tidak boleh menimpa data yang lebih baru (urutan sequence dilindungi).
     */
    public function test_at17_out_of_order_request_does_not_overwrite_newer_draft_payload(): void
    {
        // 1. Simpan draft awal dengan sequence 1
        $draft = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['schema_version' => 1, 'description' => 'Isian Awal (Seq 1)'],
            requestSequence: 1
        );
        $this->assertSame(1, $draft->request_sequence);
        $this->assertSame('Isian Awal (Seq 1)', $draft->payload['description']);

        // 2. Simpan request kedua dengan sequence 2 (lebih baru)
        $draftUpdated = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['schema_version' => 1, 'description' => 'Isian Baru (Seq 2)'],
            draftId: $draft->id,
            requestSequence: 2
        );
        $this->assertSame(2, $draftUpdated->request_sequence);
        $this->assertSame('Isian Baru (Seq 2)', $draftUpdated->payload['description']);

        // 3. Simulasikan request lama (sequence 1) yang terlambat sampai di jaringan
        $draftStale = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['schema_version' => 1, 'description' => 'Paket Terlambat (Seq 1)'],
            draftId: $draft->id,
            requestSequence: 1
        );

        // Server tetap mempertahankan Isian Baru (Seq 2)
        $this->assertSame(2, $draftStale->request_sequence);
        $this->assertSame('Isian Baru (Seq 2)', $draftStale->payload['description']);

        $draftFresh = ProgressDraft::find($draft->id);
        $this->assertSame('Isian Baru (Seq 2)', $draftFresh->payload['description']);
    }

    /**
     * AT18: Dua tab mengedit draft yang sama - jika revisi draft tidak cocok (optimistic CAS),
     * VersionConflict dilempar dan UI menampilkan status conflict tanpa silent overwrite.
     */
    public function test_at18_two_tabs_draft_revision_conflict_detected_and_handled(): void
    {
        // 1. Buat draft awal
        $draft = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['schema_version' => 1, 'description' => 'Original Draft Text'],
            requestSequence: 1
        );
        $this->assertSame(1, $draft->draft_revision);

        // 2. Buka Tab 1 dan Tab 2 dengan draft yang sama
        $tab1 = Livewire::test(ProgressEditor::class, ['draft' => $draft->id]);
        $tab2 = Livewire::test(ProgressEditor::class, ['draft' => $draft->id]);

        $this->assertSame(1, $tab1->get('draftRevision'));
        $this->assertSame(1, $tab2->get('draftRevision'));

        // 3. Tab 1 melakukan autosave (draft_revision naik menjadi 2)
        $tab1->set('description', 'Perubahan dari Tab 1');
        $this->assertSame('saved', $tab1->get('saveStatus'));
        $this->assertSame(2, $tab1->get('draftRevision'));

        // 4. Tab 2 mencoba melakukan perubahan dengan dasar revisi 1 (sudah usang)
        $tab2->set('description', 'Perubahan dari Tab 2 yang konflik');

        // Tab 2 harus mendeteksi konflik, bukan menimpa data Tab 1 secara diam-diam
        $this->assertSame('conflict', $tab2->get('saveStatus'));
        $this->assertTrue($tab2->get('hasConflict'));
        $tab2->assertHasErrors(['save']);
        $tab2->assertSee('Konflik Versi Terdeteksi');

        // Isian Tab 2 tetap terjaga di UI lokalnya (tidak dihapus)
        $this->assertSame('Perubahan dari Tab 2 yang konflik', $tab2->get('description'));

        // Di basis data, data dari Tab 1 tetap aman
        $freshDraft = ProgressDraft::find($draft->id);
        $this->assertSame('Perubahan dari Tab 1', $freshDraft->payload['description']);
        $this->assertSame(2, $freshDraft->draft_revision);
    }

    /**
     * AT18: Tab yang mengedit progress dengan base_revision yang lebih lama dari progress aktual
     * ditolak dengan VersionConflict saat progress telah diperbarui di sesi lain.
     */
    public function test_at18_stale_base_revision_rejected_when_progress_updated(): void
    {
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'revision' => 1,
            'status' => 'Open',
            'description' => 'Original Progress',
        ]);

        $draft = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['schema_version' => 1, 'description' => 'Draft edit'],
            progressId: $progress->id,
            baseRevision: 1
        );

        // Di sesi/tab lain, progress di-commit sehingga revisi progress menjadi 2
        $progress->update(['revision' => 2]);

        // Simpan autosave draft selanjutnya dengan base_revision lama (1) akan mendeteksi konflik
        $this->expectException(VersionConflict::class);
        $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['schema_version' => 1, 'description' => 'Draft edit further'],
            progressId: $progress->id,
            baseRevision: 1
        );
    }
}
