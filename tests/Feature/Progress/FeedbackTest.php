<?php

namespace Tests\Feature\Progress;

use App\Actions\SaveProgress;
use App\Livewire\Progresses\ProgressEditor;
use App\Models\Feedback;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class FeedbackTest extends TestCase
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
            'jo_no' => '0092/FB/2026',
            'part_no' => '0092-FBK-01',
            'part_name' => 'BRACKET CUSTOMER FEEDBACK',
        ]);
    }

    /**
     * AT09: Dua feedback dengan PIC, tanggal, dan isi berbeda tetap merupakan dua entri mandiri.
     * Tanggal feedback berbeda dari tanggal aktivitas progress.
     * PIC Feedback tidak mengambil alih PIC Customer pada progress.
     * Mengoreksi entri kedua mempertahankan entri pertama secara utuh dengan ID stabil.
     */
    public function test_at09_two_feedbacks_with_different_pic_and_dates_are_saved_and_do_not_override_progress_pic_customer(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(ProgressEditor::class, ['projectId' => $this->project->id])
            ->set('activityDate', '2026-10-19')
            ->set('activity', 'Sent Sample')
            ->set('picCustomer', 'Mr. Honda PIC Master')
            // Feedback 1
            ->set('feedbacks.0.content', 'Dimensi flange disetujui, radius R2,5 masih ada burry halus.')
            ->set('feedbacks.0.pic', 'Mr. Tanaka (QA Honda)')
            ->set('feedbacks.0.feedback_date', '2026-10-21');

        // Tambah Feedback 2
        $component->call('addFeedback')
            ->set('feedbacks.1.content', 'Mohon dikirimkan ulang 5 pcs sampel setelah perbaikan.')
            ->set('feedbacks.1.pic', 'Pak Hendra (Purchasing)')
            ->set('feedbacks.1.feedback_date', '2026-10-23');

        $component->call('save')
            ->assertHasNoErrors();

        $progress = Progress::firstOrFail();
        $this->assertSame('Open', $progress->status);
        $this->assertSame('Mr. Honda PIC Master', $progress->pic_customer); // PIC progress tidak tertimpa

        $feedbacks = $progress->feedbacks()->orderBy('position')->get();
        $this->assertCount(2, $feedbacks);

        // Feedback 1 assertions
        $this->assertSame('Dimensi flange disetujui, radius R2,5 masih ada burry halus.', $feedbacks[0]->content);
        $this->assertSame('Mr. Tanaka (QA Honda)', $feedbacks[0]->pic);
        $this->assertSame('2026-10-21', $feedbacks[0]->feedback_date?->format('Y-m-d'));
        $this->assertSame(0, $feedbacks[0]->position);

        // Feedback 2 assertions
        $this->assertSame('Mohon dikirimkan ulang 5 pcs sampel setelah perbaikan.', $feedbacks[1]->content);
        $this->assertSame('Pak Hendra (Purchasing)', $feedbacks[1]->pic);
        $this->assertSame('2026-10-23', $feedbacks[1]->feedback_date?->format('Y-m-d'));
        $this->assertSame(1, $feedbacks[1]->position);

        $fb1Id = $feedbacks[0]->id;
        $fb2Id = $feedbacks[1]->id;

        // Edit Feedback 2 (koreksi PIC & tanggal)
        $editComponent = Livewire::test(ProgressEditor::class, [
            'projectId' => $this->project->id,
            'progressId' => $progress->id,
        ])
            ->set('feedbacks.1.pic', 'Pak Hendra (Procurement Division)')
            ->set('feedbacks.1.feedback_date', '2026-10-24');

        $editComponent->call('save')
            ->assertHasNoErrors();

        $reloadedFeedbacks = $progress->fresh(['feedbacks'])->feedbacks()->orderBy('position')->get();
        $this->assertCount(2, $reloadedFeedbacks);

        // Feedback 1 tetap utuh dengan ID sama
        $this->assertSame($fb1Id, $reloadedFeedbacks[0]->id);
        $this->assertSame('Mr. Tanaka (QA Honda)', $reloadedFeedbacks[0]->pic);
        $this->assertSame('2026-10-21', $reloadedFeedbacks[0]->feedback_date?->format('Y-m-d'));

        // Feedback 2 terupdate dengan ID sama
        $this->assertSame($fb2Id, $reloadedFeedbacks[1]->id);
        $this->assertSame('Pak Hendra (Procurement Division)', $reloadedFeedbacks[1]->pic);
        $this->assertSame('2026-10-24', $reloadedFeedbacks[1]->feedback_date?->format('Y-m-d'));
    }

    /**
     * Pergantian Activity tidak mereset daftar feedback yang sedang diisi.
     */
    public function test_activity_change_preserves_feedback_state(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(ProgressEditor::class, ['projectId' => $this->project->id])
            ->set('activity', 'Sent Sample')
            ->set('feedbacks.0.content', 'Catatan sampel awal')
            ->set('feedbacks.0.pic', 'QA Staff')
            ->set('activity', 'Audit');

        $this->assertSame('Catatan sampel awal', $component->get('feedbacks.0.content'));
        $this->assertSame('QA Staff', $component->get('feedbacks.0.pic'));
    }

    /**
     * Kontrol hapus feedback:
     * - Feedback baru yang belum disimpan dapat dihapus jika baris > 1.
     * - Feedback tersimpan tidak dapat dihapus melalui tombol hapus (harus melalui edit).
     */
    public function test_removal_controls_for_new_and_stored_feedbacks(): void
    {
        $this->actingAs($this->admin);

        $progress = Progress::create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $storedFb = Feedback::create([
            'progress_id' => $progress->id,
            'client_id' => (string) Str::uuid(),
            'content' => 'Feedback tersimpan',
            'position' => 0,
        ]);

        $component = Livewire::test(ProgressEditor::class, [
            'projectId' => $this->project->id,
            'progressId' => $progress->id,
        ]);

        // Coba hapus feedback tersimpan -> ditolak
        $component->call('promptRemove', 'feedback', $storedFb->client_id)
            ->assertHasErrors(['remove' => 'Catatan tersimpan dapat dikoreksi melalui edit.']);

        // Tambah baris baru
        $component->call('addFeedback');
        $newClientId = $component->get('feedbacks.1.client_id');
        $this->assertNotNull($newClientId);

        // Hapus baris baru -> berhasil via dialog konfirmasi
        $component->call('promptRemove', 'feedback', $newClientId)
            ->assertSet('pendingRemoval.type', 'feedback')
            ->assertSet('pendingRemoval.client_id', $newClientId);

        $component->call('confirmRemove');
        $this->assertCount(1, $component->get('feedbacks'));
        $this->assertSame($storedFb->id, $component->get('feedbacks.0.id'));
    }

    /**
     * Cross-progress feedback ID injection ditolak secara transaksional.
     */
    public function test_cross_progress_feedback_id_injection_rejected(): void
    {
        $this->actingAs($this->admin);

        $otherProgress = Progress::create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-18',
            'activity' => 'Trial',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $victimFb = Feedback::create([
            'progress_id' => $otherProgress->id,
            'client_id' => (string) Str::uuid(),
            'content' => 'Feedback milik progress lain',
            'position' => 0,
        ]);

        $saveProgress = app(SaveProgress::class);

        $payload = [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Sent Sample',
            'feedbacks' => [
                [
                    'id' => $victimFb->id,
                    'client_id' => $victimFb->client_id,
                    'position' => 0,
                    'content' => 'Bajak feedback!',
                ],
            ],
        ];

        $this->expectException(ValidationException::class);
        $saveProgress->saveBasic($this->admin, $payload);
    }
}
