<?php

namespace Tests\Feature\Progress;

use App\Actions\SaveProgress;
use App\Models\Attachment;
use App\Models\CorrectiveAction;
use App\Models\Feedback;
use App\Models\NextAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\Project;
use App\Models\User;
use App\Services\AttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SaveGraphTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Project $project;
    private SaveProgress $action;
    private AttachmentService $attachmentService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('uploads.disk', 'private'));

        $this->admin = User::first() ?? User::factory()->create([
            'email' => 'admin@example.com',
            'admin_slot' => true,
        ]);

        $this->project = Project::factory()->create([
            'jo_no' => 'JO-GRAPH-01',
            'part_no' => 'PN-GRAPH-01',
            'part_name' => 'Part Graph Test',
        ]);

        $this->action = app(SaveProgress::class);
        $this->attachmentService = app(AttachmentService::class);
    }

    /**
     * Menyimpan graf lengkap (problem, corrective actions, next action, feedback, lampiran)
     * dalam satu transaksi tunggal yang sukses.
     */
    public function test_atomic_save_persists_complete_nested_graph(): void
    {
        $draft = ProgressDraft::create([
            'admin_id' => $this->admin->id,
            'state' => 'active',
            'payload' => ['schema_version' => 1],
        ]);

        $prob1ClientId = (string) Str::uuid();
        $prob2ClientId = (string) Str::uuid();
        $fbClientId = (string) Str::uuid();

        // Staging photo for prob1
        $img = UploadedFile::fake()->image('prob1.jpg', 640, 480);
        $attPhoto = $this->attachmentService->stage(
            admin: $this->admin,
            draft: $draft,
            file: $img,
            kind: 'photo',
            ownerKind: 'problem',
            ownerClientId: $prob1ClientId,
            caption: 'Foto temuan 1'
        );

        // Staging document for progress
        $doc = UploadedFile::fake()->create('report.pdf', 300, 'application/pdf');
        $attDoc = $this->attachmentService->stage(
            admin: $this->admin,
            draft: $draft,
            file: $doc,
            kind: 'document',
            ownerKind: 'progress',
            caption: 'Dokumen laporan uji'
        );

        $payload = [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-25',
            'activity' => 'Trial',
            'pic_customer' => 'Customer PIC',
            'description' => 'Graf lengkap transaksi',
            'process_trial' => 'Forming',
            'machine' => 'Press 500T',
            'die_high_raw' => '450,5',
            'material_actual_trial' => 'SPCC 1.2',
            'qty_material_raw' => '100',
            'qty_process_raw' => '98',
            'qty_sample_raw' => '5',
            'remark' => 'Semua anak terhubung',
            'problems' => [
                [
                    'client_id' => $prob1ClientId,
                    'description' => 'Problem 1: Burr',
                    'photo_refs' => [$attPhoto->id],
                    'corrective_actions' => [
                        [
                            'client_id' => (string) Str::uuid(),
                            'description' => 'Asah pisau potong',
                            'pic' => 'Teknisi A',
                            'actual_date' => '2026-10-26',
                        ],
                    ],
                ],
                [
                    'client_id' => $prob2ClientId,
                    'description' => 'Problem 2: Scratch',
                    'photo_refs' => [],
                    'corrective_actions' => [
                        [
                            'client_id' => (string) Str::uuid(),
                            'description' => 'Poles die cavity',
                            'pic' => 'Teknisi B',
                            'actual_date' => null,
                        ],
                    ],
                ],
            ],
            'next_actions' => [
                [
                    'client_id' => (string) Str::uuid(),
                    'description' => 'Trial ulang batch 2',
                    'pic' => 'PIC Next',
                    'actual_date' => null,
                ],
            ],
            'feedbacks' => [
                [
                    'client_id' => $fbClientId,
                    'content' => 'Feedback dari customer OK',
                    'pic' => 'PIC Feedback',
                    'feedback_date' => '2026-10-27',
                    'document_refs' => [],
                ],
            ],
            'attachment_refs' => [$attDoc->id],
        ];

        $saved = $this->action->saveBasic($this->admin, $payload, draft: $draft);

        $this->assertNotNull($saved->id);
        $this->assertSame('Open', $saved->status);
        $this->assertCount(2, $saved->problems);
        $this->assertCount(1, $saved->nextActions);
        $this->assertCount(1, $saved->feedbacks);
        $this->assertCount(1, $saved->attachments);

        // Verifikasi kepemilikan dan state lampiran
        $reloadedPhoto = $attPhoto->fresh();
        $this->assertSame('attached', $reloadedPhoto->state);
        $this->assertSame($saved->problems[0]->id, $reloadedPhoto->problem_id);
        $this->assertNull($reloadedPhoto->draft_id);

        $reloadedDoc = $attDoc->fresh();
        $this->assertSame('attached', $reloadedDoc->state);
        $this->assertSame($saved->id, $reloadedDoc->progress_id);
        $this->assertNull($reloadedDoc->draft_id);
    }

    /**
     * AT11: Rollback seluruh mutasi saat terjadi kegagalan validasi anak (atomisitas),
     * tidak ada baris setengah tersimpan, dan berkas staging tetap utuh untuk retry.
     */
    public function test_at11_rollback_on_child_validation_failure_preserves_staging(): void
    {
        $draft = ProgressDraft::create([
            'admin_id' => $this->admin->id,
            'state' => 'active',
            'payload' => ['schema_version' => 1],
        ]);

        $probClientId = (string) Str::uuid();

        // Staging photo
        $img = UploadedFile::fake()->image('rollback_photo.jpg', 640, 480);
        $attPhoto = $this->attachmentService->stage(
            admin: $this->admin,
            draft: $draft,
            file: $img,
            kind: 'photo',
            ownerKind: 'problem',
            ownerClientId: $probClientId,
            caption: 'Foto staging akan rollback'
        );

        // Foreign progress dan problem milik progress lain
        $foreignProgress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-20',
            'activity' => 'Trial',
            'status' => 'Open',
        ]);
        $foreignProblem = Problem::create([
            'progress_id' => $foreignProgress->id,
            'client_id' => (string) Str::uuid(),
            'description' => 'Problem Progress Lain',
            'position' => 0,
        ]);

        // Kirim payload dengan problem ID milik progress lain (cross-progress injection)
        $payload = [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-25',
            'activity' => 'Trial',
            'problems' => [
                [
                    'id' => $foreignProblem->id, // INJEKSI ID ASING
                    'client_id' => $probClientId,
                    'description' => 'Mencoba injeksi ID problem asing',
                    'photo_refs' => [$attPhoto->id],
                    'corrective_actions' => [],
                ],
            ],
            'next_actions' => [],
            'feedbacks' => [],
        ];

        $initialProgressCount = Progress::count();
        $initialProblemCount = Problem::count();

        try {
            $this->action->saveBasic($this->admin, $payload, draft: $draft);
            $this->fail('Harusnya gagal dengan ValidationException');
        } catch (ValidationException $e) {
            $this->assertTrue(isset($e->errors()['problems.0.id']));
        }

        // Pastikan tidak ada record baru yang tersimpan (rollback penuh)
        $this->assertSame($initialProgressCount, Progress::count());
        $this->assertSame($initialProblemCount, Problem::count());

        // Pastikan berkas staging tidak terhapus dan tetap berstatus staging untuk retry
        $reloadedPhoto = $attPhoto->fresh();
        $this->assertNotNull($reloadedPhoto);
        $this->assertSame('staging', $reloadedPhoto->state);
        $this->assertSame($draft->id, $reloadedPhoto->draft_id);
    }

    /**
     * AT16: Menolak corrective action ID milik problem/progress lain.
     */
    public function test_at16_rejects_cross_progress_corrective_action_id(): void
    {
        $prog1 = Progress::factory()->create(['project_id' => $this->project->id]);
        $prob1 = Problem::create([
            'progress_id' => $prog1->id,
            'client_id' => (string) Str::uuid(),
            'description' => 'Problem 1',
            'position' => 0,
        ]);
        $act1 = CorrectiveAction::create([
            'problem_id' => $prob1->id,
            'client_id' => (string) Str::uuid(),
            'description' => 'Action Asli 1',
            'position' => 0,
        ]);

        $prog2 = Progress::factory()->create(['project_id' => $this->project->id]);
        $prob2 = Problem::create([
            'progress_id' => $prog2->id,
            'client_id' => (string) Str::uuid(),
            'description' => 'Problem 2',
            'position' => 0,
        ]);

        // Coba masukkan ID action milik prob1 ke dalam payload prob2
        $payload = [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-25',
            'activity' => 'Trial',
            'problems' => [
                [
                    'id' => $prob2->id,
                    'client_id' => $prob2->client_id,
                    'description' => 'Problem 2 diubah',
                    'corrective_actions' => [
                        [
                            'id' => $act1->id, // INJEKSI ID DARI PROB1
                            'client_id' => (string) Str::uuid(),
                            'description' => 'Action curian',
                        ],
                    ],
                ],
            ],
            'next_actions' => [],
            'feedbacks' => [],
        ];

        try {
            $this->action->saveBasic($this->admin, $payload, $prog2);
            $this->fail('Harusnya melempar ValidationException');
        } catch (ValidationException $e) {
            $this->assertTrue(isset($e->errors()['problems.0.corrective_actions.0.id']));
        }

        // Pastikan action asli pada prob1 tidak berubah
        $this->assertSame('Action Asli 1', $act1->fresh()->description);
        $this->assertSame($prob1->id, $act1->fresh()->problem_id);
    }

    /**
     * AT16: Menolak Next Action ID atau Feedback ID milik progress lain.
     */
    public function test_at16_rejects_cross_progress_next_action_and_feedback(): void
    {
        $prog1 = Progress::factory()->create(['project_id' => $this->project->id]);
        $na1 = NextAction::create([
            'progress_id' => $prog1->id,
            'client_id' => (string) Str::uuid(),
            'description' => 'Next Action Prog 1',
            'position' => 0,
        ]);
        $fb1 = Feedback::create([
            'progress_id' => $prog1->id,
            'client_id' => (string) Str::uuid(),
            'content' => 'Feedback Prog 1',
            'position' => 0,
        ]);

        $prog2 = Progress::factory()->create(['project_id' => $this->project->id]);

        // Coba suntikkan na1 ke prog2
        $payloadNA = [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-25',
            'activity' => 'Trial',
            'problems' => [],
            'next_actions' => [
                [
                    'id' => $na1->id,
                    'client_id' => (string) Str::uuid(),
                    'description' => 'Injeksi NA',
                ],
            ],
            'feedbacks' => [],
        ];

        try {
            $this->action->saveBasic($this->admin, $payloadNA, $prog2);
            $this->fail('Harusnya melempar ValidationException untuk next_actions');
        } catch (ValidationException $e) {
            $this->assertTrue(isset($e->errors()['next_actions.0.id']));
        }

        // Coba suntikkan fb1 ke prog2
        $payloadFB = [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-25',
            'activity' => 'Trial',
            'problems' => [],
            'next_actions' => [],
            'feedbacks' => [
                [
                    'id' => $fb1->id,
                    'client_id' => (string) Str::uuid(),
                    'content' => 'Injeksi FB',
                ],
            ],
        ];

        try {
            $this->action->saveBasic($this->admin, $payloadFB, $prog2);
            $this->fail('Harusnya melempar ValidationException untuk feedbacks');
        } catch (ValidationException $e) {
            $this->assertTrue(isset($e->errors()['feedbacks.0.id']));
        }

        // Verifikasi entitas asal tidak terpengaruh
        $this->assertSame($prog1->id, $na1->fresh()->progress_id);
        $this->assertSame($prog1->id, $fb1->fresh()->progress_id);
    }
}
