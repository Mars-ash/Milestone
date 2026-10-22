<?php

namespace Tests\Feature\Drafts;

use App\Actions\ApplyProgressDraft;
use App\Exceptions\VersionConflict;
use App\Models\Attachment;
use App\Models\CorrectiveAction;
use App\Models\Feedback;
use App\Models\NextAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\Project;
use App\Models\User;
use App\Services\ProgressDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApplyDraftTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Project $project;

    private ProgressDraftService $draftService;

    private ApplyProgressDraft $applyAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->project = Project::factory()->create([
            'jo_no' => '0088/NM/2026',
            'part_no' => '0088-XYZ-01',
            'part_name' => 'Reinforce Bracket',
        ]);
        $this->draftService = app(ProgressDraftService::class);
        $this->applyAction = app(ApplyProgressDraft::class);
        $this->actingAs($this->admin);
        Storage::fake('attachments');
    }

    /**
     * AT14, AT15: Terapkan draft baru menghasilkan progress operasional, anak lengkap, dan draft berstatus applied.
     */
    public function test_apply_new_draft_creates_progress_and_marks_draft_applied(): void
    {
        $problemClient = (string) Str::uuid();
        $actionClient = (string) Str::uuid();
        $nextClient = (string) Str::uuid();
        $feedbackClient = (string) Str::uuid();

        $payload = [
            'schema_version' => 1,
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'pic_customer' => 'Mr. Tanaka',
            'description' => 'First Trial Run',
            'problems' => [
                [
                    'client_id' => $problemClient,
                    'description' => 'Burr on left edge',
                    'corrective_actions' => [
                        [
                            'client_id' => $actionClient,
                            'description' => 'Adjust punch clearance',
                            'pic' => 'Eko',
                            'actual_date' => '2026-10-19',
                        ],
                    ],
                ],
            ],
            'next_actions' => [
                [
                    'client_id' => $nextClient,
                    'description' => 'Inspect tooling after 100 strokes',
                    'pic' => 'Budi',
                    'actual_date' => null,
                ],
            ],
            'feedbacks' => [
                [
                    'client_id' => $feedbackClient,
                    'content' => 'Customer reviewed initial stamp',
                    'pic' => 'Tanaka-san',
                    'feedback_date' => '2026-10-19',
                ],
            ],
        ];

        $draft = $this->draftService->saveDraft($this->admin, $payload);
        $this->assertSame('active', $draft->state);

        $result = $this->applyAction->handle($this->admin, $draft->id, $draft->draft_revision);

        $this->assertSame('saved', $result->outcome);
        $this->assertNotNull($result->progress);
        $this->assertSame('Open', $result->progress->status);
        $this->assertSame(1, $result->progress->revision);
        $this->assertSame('First Trial Run', $result->progress->description);

        // Verifikasi entitas anak tersimpan
        $this->assertCount(1, $result->progress->problems);
        $this->assertSame('Burr on left edge', $result->progress->problems->first()->description);
        $this->assertCount(1, $result->progress->problems->first()->correctiveActions);
        $this->assertSame('Adjust punch clearance', $result->progress->problems->first()->correctiveActions->first()->description);
        $this->assertCount(1, $result->progress->nextActions);
        $this->assertCount(1, $result->progress->feedbacks);

        // Verifikasi status draft diperbarui
        $draft->refresh();
        $this->assertSame('applied', $draft->state);
        $this->assertSame($result->progress->id, $draft->applied_progress_id);
    }

    /**
     * AT14, AT15: Edit draft pada progress yang sudah ada memperbarui data dan meningkatkan revision.
     */
    public function test_apply_edit_draft_updates_progress_increments_revision_and_marks_applied(): void
    {
        $existing = Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-15',
            'activity' => 'Trial',
            'revision' => 1,
            'description' => 'Old description',
            'status' => 'Open',
        ]);

        $payload = [
            'schema_version' => 1,
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-15',
            'activity' => 'Trial',
            'description' => 'Updated description from draft',
            'problems' => [],
            'next_actions' => [],
            'feedbacks' => [],
        ];

        $draft = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: $payload,
            progressId: $existing->id,
            baseRevision: 1
        );

        $result = $this->applyAction->handle($this->admin, $draft->id, $draft->draft_revision);

        $this->assertSame('saved', $result->outcome);
        $existing->refresh();
        $this->assertSame('Updated description from draft', $existing->description);
        $this->assertSame(2, $existing->revision);

        $draft->refresh();
        $this->assertSame('applied', $draft->state);
        $this->assertSame($existing->id, $draft->applied_progress_id);
    }

    /**
     * AT16: Replay idempotency: Memanggil simpan final ulang untuk draft yang sudah applied
     * mengembalikan progress yang sama tanpa menduplikasi anak.
     */
    public function test_replay_idempotency_returns_same_progress_without_duplicate_children(): void
    {
        $payload = [
            'schema_version' => 1,
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'description' => 'Idempotent test',
            'problems' => [
                [
                    'client_id' => (string) Str::uuid(),
                    'description' => 'Single Problem',
                    'corrective_actions' => [
                        [
                            'client_id' => (string) Str::uuid(),
                            'description' => 'Single Action',
                            'pic' => 'Dani',
                        ],
                    ],
                ],
            ],
            'next_actions' => [
                [
                    'client_id' => (string) Str::uuid(),
                    'description' => 'Single Next Action',
                ],
            ],
            'feedbacks' => [
                [
                    'client_id' => (string) Str::uuid(),
                    'content' => 'Single Feedback',
                ],
            ],
        ];

        $draft = $this->draftService->saveDraft($this->admin, $payload);
        $firstResult = $this->applyAction->handle($this->admin, $draft->id, $draft->draft_revision);
        $this->assertSame('saved', $firstResult->outcome);
        $progressId = $firstResult->progress->id;

        $probCount1 = Problem::where('progress_id', $progressId)->count();
        $actCount1 = CorrectiveAction::count();
        $naCount1 = NextAction::where('progress_id', $progressId)->count();
        $fbCount1 = Feedback::where('progress_id', $progressId)->count();

        $this->assertSame(1, $probCount1);
        $this->assertSame(1, $actCount1);
        $this->assertSame(1, $naCount1);
        $this->assertSame(1, $fbCount1);

        // Replay pemanggilan yang sama
        $secondResult = $this->applyAction->handle($this->admin, $draft->id);
        $this->assertSame('already_applied', $secondResult->outcome);
        $this->assertSame($progressId, $secondResult->progress->id);

        // Hitungan tidak berubah sama sekali
        $this->assertSame($probCount1, Problem::where('progress_id', $progressId)->count());
        $this->assertSame($actCount1, CorrectiveAction::count());
        $this->assertSame($naCount1, NextAction::where('progress_id', $progressId)->count());
        $this->assertSame($fbCount1, Feedback::where('progress_id', $progressId)->count());
    }

    /**
     * AT18: Apply draft menolak versi yang mengalami konflik draft_revision.
     */
    public function test_apply_draft_rejects_version_conflict_on_draft_revision(): void
    {
        $payload = [
            'schema_version' => 1,
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
        ];

        $draft = $this->draftService->saveDraft($this->admin, $payload);

        $this->expectException(VersionConflict::class);
        // Mengirim expected revision yang salah
        $this->applyAction->handle($this->admin, $draft->id, 999);
    }

    /**
     * AT18: Apply draft menolak jika progress aktual sudah berubah revision (conflict).
     */
    public function test_apply_draft_rejects_version_conflict_if_progress_revision_changed(): void
    {
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'revision' => 1,
            'status' => 'Open',
        ]);

        $draft = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: ['project_id' => $this->project->id, 'activity_date' => '2026-10-19', 'activity' => 'Trial'],
            progressId: $progress->id,
            baseRevision: 1
        );

        // Di sesi lain progress di-update sehingga revision menjadi 2
        $progress->update(['revision' => 2]);

        $this->expectException(VersionConflict::class);
        $this->applyAction->handle($this->admin, $draft->id, $draft->draft_revision);
    }

    /**
     * Staged attachments dipromosikan menjadi permanent dan ditautkan ke progress saat draft diterapkan.
     */
    public function test_apply_draft_promotes_staged_attachments_to_permanent(): void
    {
        $draft = $this->draftService->saveDraft($this->admin, [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'description' => 'With attachment',
        ]);

        $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');
        $attService = app(\App\Services\AttachmentService::class);
        $stagedAtt = $attService->stage(
            admin: $this->admin,
            draft: $draft,
            file: $file,
            kind: 'document',
            ownerKind: 'progress',
        );

        $this->assertSame('staging', $stagedAtt->state);

        // Update draft payload with attachment ref
        $this->draftService->saveDraft($this->admin, [
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'description' => 'With attachment',
            'attachment_refs' => [$stagedAtt->id],
        ], draftId: $draft->id);

        $draft->refresh();
        $result = $this->applyAction->handle($this->admin, $draft->id, $draft->draft_revision);

        $this->assertSame('saved', $result->outcome);
        $stagedAtt->refresh();
        $this->assertSame('attached', $stagedAtt->state);
        $this->assertSame($result->progress->id, $stagedAtt->progress_id);
    }
}
