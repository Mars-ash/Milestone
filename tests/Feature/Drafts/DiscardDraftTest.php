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
use Tests\TestCase;

class DiscardDraftTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Project $project;

    private ProgressDraftService $draftService;

    private AttachmentService $attachmentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->project = Project::factory()->create();
        $this->draftService = app(ProgressDraftService::class);
        $this->attachmentService = app(AttachmentService::class);
        $this->actingAs($this->admin);
        Storage::fake('attachments');
    }

    /**
     * FR14, AT19: Buang Draft menghapus record draft dan membersihkan berkas staging terkait di disk privat.
     */
    public function test_at19_discard_draft_deletes_draft_and_staging_files_from_disk(): void
    {
        $draft = $this->draftService->saveDraft($this->admin, [
            'schema_version' => 1,
            'project_id' => $this->project->id,
            'description' => 'Draft to discard',
        ]);

        $file = UploadedFile::fake()->image('test-photo.jpg', 200, 200);
        $attachment = $this->attachmentService->stage(
            admin: $this->admin,
            draft: $draft,
            file: $file,
            kind: 'photo',
            ownerKind: 'problem',
            ownerClientId: (string) \Illuminate\Support\Str::uuid(),
        );

        $disk = $this->attachmentService->getDisk();
        $this->assertTrue($disk->exists($attachment->storage_key), 'File staging harus ada di storage privat sebelum dibuang');
        $this->assertDatabaseHas('attachments', ['id' => $attachment->id, 'state' => 'staging']);

        // Buang draft
        $discarded = $this->draftService->discardDraft($this->admin, $draft->id);
        $this->assertTrue($discarded);

        // Record draft terhapus
        $this->assertDatabaseMissing('progress_drafts', ['id' => $draft->id]);

        // File staging di storage dan DB terhapus
        $this->assertFalse($disk->exists($attachment->storage_key), 'File staging harus terhapus dari storage saat draft dibuang');
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }

    /**
     * FR14, AT19: Membuang draft edit menjaga progress resmi tetap utuh.
     */
    public function test_discard_edit_draft_leaves_saved_progress_untouched(): void
    {
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-15',
            'activity' => 'Trial',
            'description' => 'Official Progress Description',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $draft = $this->draftService->saveDraft(
            admin: $this->admin,
            payload: [
                'schema_version' => 1,
                'project_id' => $this->project->id,
                'description' => 'Unwanted draft edit',
            ],
            progressId: $progress->id,
            baseRevision: 1
        );

        $this->draftService->discardDraft($this->admin, $draft->id);

        $this->assertDatabaseMissing('progress_drafts', ['id' => $draft->id]);

        $progress->refresh();
        $this->assertSame('Official Progress Description', $progress->description);
        $this->assertSame(1, $progress->revision);
    }

    /**
     * Menolak buang draft jika draft sudah berstatus applied atau milik admin lain.
     */
    public function test_cannot_discard_applied_draft_or_other_admin_draft(): void
    {
        $draft = $this->draftService->saveDraft($this->admin, [
            'schema_version' => 1,
            'project_id' => $this->project->id,
        ]);

        $progress = Progress::factory()->create(['project_id' => $this->project->id]);
        $draft->update([
            'state' => 'applied',
            'applied_progress_id' => $progress->id,
        ]);

        $result = $this->draftService->discardDraft($this->admin, $draft->id);
        $this->assertFalse($result);

        // User dengan ID berbeda tidak berhak membuang draft ini
        $unauthorizedAdmin = new User();
        $unauthorizedAdmin->id = 99999;

        $result2 = $this->draftService->discardDraft($unauthorizedAdmin, $draft->id);
        $this->assertFalse($result2);
        $this->assertDatabaseHas('progress_drafts', ['id' => $draft->id]);
    }

    /**
     * Command cleanup-staging dengan --dry-run dan live execution.
     */
    public function test_cleanup_staging_command_dry_run_and_execution(): void
    {
        $draft = $this->draftService->saveDraft($this->admin, [
            'schema_version' => 1,
            'project_id' => $this->project->id,
        ]);

        $file = UploadedFile::fake()->create('expired.pdf', 50, 'application/pdf');
        $attachment = $this->attachmentService->stage(
            admin: $this->admin,
            draft: $draft,
            file: $file,
            kind: 'document',
            ownerKind: 'progress',
        );

        // Set created_at ke 48 jam yang lalu
        Attachment::where('id', $attachment->id)->update(['created_at' => now()->subHours(48)]);

        $disk = $this->attachmentService->getDisk();
        $this->assertTrue($disk->exists($attachment->storage_key));

        // 1. Jalankan dry run
        $this->artisan('uploads:cleanup-staging', ['--dry-run' => true, '--hours' => 24])
            ->expectsOutputToContain('[DRY-RUN]')
            ->assertSuccessful();

        $this->assertTrue($disk->exists($attachment->storage_key));
        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);

        // 2. Jalankan pembersihan aktual
        $this->artisan('uploads:cleanup-staging', ['--hours' => 24])
            ->expectsOutputToContain('[Selesai]')
            ->assertSuccessful();

        $this->assertFalse($disk->exists($attachment->storage_key));
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }
}
