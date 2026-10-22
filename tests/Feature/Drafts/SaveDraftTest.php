<?php

namespace Tests\Feature\Drafts;

use App\Models\Attachment;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\Project;
use App\Models\User;
use App\Services\AttachmentService;
use App\Services\ProgressDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SaveDraftTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Project $project;
    private ProgressDraftService $draftService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('uploads.disk', 'private'));

        $this->admin = User::first() ?? User::factory()->create([
            'email' => 'admin@example.com',
            'admin_slot' => true,
        ]);

        $this->project = Project::factory()->create([
            'jo_no' => 'JO-DRAFT-01',
            'part_no' => 'PN-DRAFT-01',
            'part_name' => 'Part Draft Test',
        ]);

        $this->draftService = app(ProgressDraftService::class);
    }

    /**
     * AT12: Draft baru tanpa JO, Activity, atau tanggal berhasil diautosave secara parsial,
     * dan tidak menambah record pada tabel progresses.
     */
    public function test_at12_partial_new_draft_autosave_succeeds_without_jo_or_activity_or_date(): void
    {
        $initialProgressCount = Progress::count();

        $payload = [
            'project_id' => null,
            'activity_date' => null,
            'activity' => null,
            'description' => 'Draft ide awal kegiatan tanpa JO',
            'problems' => [],
            'next_actions' => [],
            'feedbacks' => [],
        ];

        $draft = $this->draftService->saveDraft($this->admin, $payload);

        $this->assertNotNull($draft->id);
        $this->assertTrue($draft->isActive());
        $this->assertTrue($draft->isNewDraft());
        $this->assertNull($draft->progress_id);
        $this->assertNull($draft->base_revision);
        $this->assertSame(1, $draft->draft_revision);
        $this->assertSame('Draft ide awal kegiatan tanpa JO', $draft->payload['description']);

        // Pastikan tabel progresses sama sekali tidak bertambah
        $this->assertSame($initialProgressCount, Progress::count());
    }

    /**
     * AT12 & AT14: Autosave pada edit progress yang sudah ada tidak memutasi record progress resmi
     * sebelum Simpan final dijalankan.
     */
    public function test_at12_edit_draft_autosave_preserves_original_progress_without_mutating_it(): void
    {
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-20',
            'activity' => 'Trial',
            'description' => 'Deskripsi Asli Tersimpan',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $payload = [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-20',
            'activity' => 'Trial',
            'description' => 'Deskripsi yang Baru Diedit di Draft',
            'problems' => [],
            'next_actions' => [],
            'feedbacks' => [],
        ];

        $draft = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: $payload,
            progressId: $progress->id,
            baseRevision: $progress->revision
        );

        $this->assertTrue($draft->isActive());
        $this->assertTrue($draft->isEditDraft());
        $this->assertSame($progress->id, $draft->progress_id);
        $this->assertSame(1, $draft->base_revision);
        $this->assertSame(1, $draft->draft_revision);

        // Record progress di database TETAP UTUH dengan deskripsi asli
        $freshProgress = $progress->fresh();
        $this->assertSame('Deskripsi Asli Tersimpan', $freshProgress->description);
        $this->assertSame(1, $freshProgress->revision);
    }

    /**
     * AT13: Draft menyimpan relasi anak lengkap (problem, actions, feedbacks, staging refs)
     * dan dapat dimuat kembali dengan state utuh.
     */
    public function test_at13_draft_preserves_nested_child_entries_and_staging_refs(): void
    {
        $draftId = (string) Str::uuid();
        $probClientId = (string) Str::uuid();
        $actionClientId = (string) Str::uuid();
        $naClientId = (string) Str::uuid();
        $fbClientId = (string) Str::uuid();

        // Buat staging file
        $attachmentService = app(AttachmentService::class);
        $tempDraft = ProgressDraft::create([
            'id' => $draftId,
            'admin_id' => $this->admin->id,
            'state' => 'active',
            'payload' => ['schema_version' => 1],
        ]);

        $file = UploadedFile::fake()->image('staging.jpg', 640, 480);
        $stagingAtt = $attachmentService->stage(
            admin: $this->admin,
            draft: $tempDraft,
            file: $file,
            kind: 'photo',
            ownerKind: 'problem',
            ownerClientId: $probClientId
        );

        $payload = [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-21',
            'activity' => 'Trial',
            'problems' => [
                [
                    'client_id' => $probClientId,
                    'description' => 'Problem Draft A',
                    'photo_refs' => [$stagingAtt->id],
                    'corrective_actions' => [
                        [
                            'client_id' => $actionClientId,
                            'description' => 'Tindakan Draft A',
                            'pic' => 'PIC A',
                            'actual_date' => '2026-10-22',
                        ],
                    ],
                ],
            ],
            'next_actions' => [
                [
                    'client_id' => $naClientId,
                    'description' => 'Next Action Draft',
                    'pic' => 'PIC NA',
                    'actual_date' => null,
                ],
            ],
            'feedbacks' => [
                [
                    'client_id' => $fbClientId,
                    'content' => 'Feedback Customer Draft',
                    'pic' => 'Customer',
                    'feedback_date' => '2026-10-23',
                    'document_refs' => [],
                ],
            ],
        ];

        $savedDraft = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: $payload,
            draftId: $draftId
        );

        $this->assertSame($draftId, $savedDraft->id);
        $this->assertCount(1, $savedDraft->payload['problems']);
        $this->assertSame($probClientId, $savedDraft->payload['problems'][0]['client_id']);
        $this->assertSame([$stagingAtt->id], $savedDraft->payload['problems'][0]['photo_refs']);
        $this->assertSame('Tindakan Draft A', $savedDraft->payload['problems'][0]['corrective_actions'][0]['description']);
        $this->assertSame('Next Action Draft', $savedDraft->payload['next_actions'][0]['description']);
        $this->assertSame('Feedback Customer Draft', $savedDraft->payload['feedbacks'][0]['content']);
    }

    /**
     * Menegakkan tepat satu draft edit aktif per admin per progress.
     * Pembaruan draft edit meningkatkan draft_revision tanpa menduplikasi baris di progress_drafts.
     */
    public function test_only_one_active_edit_draft_per_admin_per_progress(): void
    {
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'revision' => 1,
            'status' => 'Open',
        ]);

        // Simpan autosave 1
        $draft1 = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['description' => 'Versi 1'],
            progressId: $progress->id
        );
        $this->assertSame(1, $draft1->draft_revision);

        // Simpan autosave 2 untuk progress yang sama
        $draft2 = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['description' => 'Versi 2'],
            progressId: $progress->id
        );

        // ID harus sama (update inplace), revision naik jadi 2
        $this->assertSame($draft1->id, $draft2->id);
        $this->assertSame(2, $draft2->draft_revision);
        $this->assertSame('Versi 2', $draft2->payload['description']);

        // Verifikasi hanya ada 1 draft di database untuk progress ini
        $count = ProgressDraft::where('admin_id', $this->admin->id)
            ->where('progress_id', $progress->id)
            ->where('state', 'active')
            ->count();
        $this->assertSame(1, $count);
    }

    /**
     * Draft baru (progress_id = null) boleh ada beberapa dengan UUID berbeda.
     */
    public function test_multiple_new_drafts_allowed_with_different_uuids(): void
    {
        $draftA = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['description' => 'Draft Baru 1']
        );

        $draftB = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['description' => 'Draft Baru 2']
        );

        $this->assertNotSame($draftA->id, $draftB->id);
        $this->assertNull($draftA->progress_id);
        $this->assertNull($draftB->progress_id);

        $newDraftsCount = ProgressDraft::where('admin_id', $this->admin->id)
            ->whereNull('progress_id')
            ->where('state', 'active')
            ->count();
        $this->assertGreaterThanOrEqual(2, $newDraftsCount);
    }

    /**
     * Menolak autosave draft pada progress yang berstatus Closed.
     */
    public function test_cannot_save_draft_for_closed_progress(): void
    {
        $closedProgress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'Closed',
            'revision' => 2,
        ]);

        $this->expectException(ValidationException::class);

        $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['description' => 'Coba edit progress closed'],
            progressId: $closedProgress->id
        );
    }
}
