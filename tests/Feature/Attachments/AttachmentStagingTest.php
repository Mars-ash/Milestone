<?php

namespace Tests\Feature\Attachments;

use App\Models\Attachment;
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

class AttachmentStagingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ProgressDraft $draft;

    private AttachmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        Storage::fake('private');
        config(['uploads.disk' => 'private']);

        $this->admin = User::factory()->create(['admin_slot' => true]);
        $this->draft = ProgressDraft::factory()->create([
            'admin_id' => $this->admin->id,
            'state' => 'active',
        ]);
        $this->service = app(AttachmentService::class);
    }

    /**
     * Upload foto valid (PNG) berhasil masuk ke staging disk privat
     * dengan storage key acak berformat ^attachments/[UUID]$ dan checksum sha256 valid.
     */
    public function test_valid_photo_is_staged_on_private_disk_with_proper_metadata(): void
    {
        $problemClientId = (string) Str::uuid();
        $file = UploadedFile::fake()->image('burr_sample.png', 400, 300)->size(500); // 500 KB

        $attachment = $this->service->stage(
            admin: $this->admin,
            draft: $this->draft,
            file: $file,
            kind: 'photo',
            ownerKind: 'problem',
            ownerClientId: $problemClientId,
            caption: 'Foto burr setelah perbaikan'
        );

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertSame('staging', $attachment->state);
        $this->assertSame('photo', $attachment->kind);
        $this->assertSame('problem', $attachment->owner_kind);
        $this->assertSame(strtolower($problemClientId), $attachment->owner_client_id);
        $this->assertSame($this->draft->id, $attachment->draft_id);
        $this->assertSame($this->admin->id, $attachment->uploaded_by);
        $this->assertSame('Foto burr setelah perbaikan', $attachment->caption);

        // Validasi format storage_key
        $this->assertMatchesRegularExpression('/^attachments\/[0-9a-f-]{36}$/', $attachment->storage_key);

        // Validasi checksum sha256 64 chars
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $attachment->checksum);

        // Verifikasi file benar-benar ada di storage privat
        Storage::disk('private')->assertExists($attachment->storage_key);
    }

    /**
     * File dengan ekstensi gambar (.jpg) namun isinya teks (bukan gambar valid) harus ditolak.
     */
    public function test_fake_image_file_is_rejected(): void
    {
        $problemClientId = (string) Str::uuid();
        $fakeImage = UploadedFile::fake()->create('fake_picture.jpg', 10, 'image/jpeg');
        // Isi berkas dengan teks biasa, bukan byte gambar
        file_put_contents($fakeImage->getRealPath(), 'This is plain text pretending to be a JPG');

        $this->expectException(ValidationException::class);
        $this->service->stage(
            admin: $this->admin,
            draft: $this->draft,
            file: $fakeImage,
            kind: 'photo',
            ownerKind: 'problem',
            ownerClientId: $problemClientId
        );
    }

    /**
     * File foto yang melebihi batas konfigurasi (default 5 MB) harus ditolak.
     */
    public function test_photo_exceeding_max_size_is_rejected(): void
    {
        $problemClientId = (string) Str::uuid();
        // File 6000 KB (> 5120 KB)
        $oversized = UploadedFile::fake()->image('huge_photo.jpg', 2000, 2000)->size(6000);

        $this->expectException(ValidationException::class);
        $this->service->stage(
            admin: $this->admin,
            draft: $this->draft,
            file: $oversized,
            kind: 'photo',
            ownerKind: 'problem',
            ownerClientId: $problemClientId
        );
    }

    /**
     * Staging pada draft milik admin lain atau draft tidak aktif harus ditolak.
     */
    public function test_staging_on_another_admins_draft_or_inactive_draft_is_rejected(): void
    {
        $otherDraft = clone $this->draft;
        $otherDraft->admin_id = $this->admin->id + 999;

        $file = UploadedFile::fake()->image('test.png', 100, 100)->size(200);

        try {
            $this->service->stage(
                admin: $this->admin,
                draft: $otherDraft,
                file: $file,
                kind: 'photo',
                ownerKind: 'problem',
                ownerClientId: (string) Str::uuid()
            );
            $this->fail('Expected ValidationException was not thrown for foreign draft.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('draft_id', $e->errors());
        }

        // Uji draft yang tidak berstatus active (misal: applied)
        $inactiveDraft = clone $this->draft;
        $inactiveDraft->state = 'applied';

        try {
            $this->service->stage(
                admin: $this->admin,
                draft: $inactiveDraft,
                file: $file,
                kind: 'photo',
                ownerKind: 'problem',
                ownerClientId: (string) Str::uuid()
            );
            $this->fail('Expected ValidationException was not thrown for inactive draft.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('draft_id', $e->errors());
        }
    }

    /**
     * Upload dokumen valid (PDF, XLSX) berhasil masuk ke staging disk privat.
     */
    public function test_valid_document_is_staged(): void
    {
        $feedbackClientId = (string) Str::uuid();
        $doc = UploadedFile::fake()->create('laporan_hasil_uji.pdf', 1024, 'application/pdf');

        $attachment = $this->service->stage(
            admin: $this->admin,
            draft: $this->draft,
            file: $doc,
            kind: 'document',
            ownerKind: 'feedback',
            ownerClientId: $feedbackClientId,
            caption: 'Hasil uji lab customer'
        );

        $this->assertSame('staging', $attachment->state);
        $this->assertSame('document', $attachment->kind);
        $this->assertSame('feedback', $attachment->owner_kind);
        $this->assertSame(strtolower($feedbackClientId), $attachment->owner_client_id);
        Storage::disk('private')->assertExists($attachment->storage_key);
    }

    /**
     * deleteStaging menghapus berkas dari disk privat dan record database.
     */
    public function test_delete_staging_removes_file_and_record(): void
    {
        $file = UploadedFile::fake()->image('sample.png', 100, 100)->size(200);
        $attachment = $this->service->stage(
            admin: $this->admin,
            draft: $this->draft,
            file: $file,
            kind: 'photo',
            ownerKind: 'problem',
            ownerClientId: (string) Str::uuid()
        );

        Storage::disk('private')->assertExists($attachment->storage_key);

        $this->service->deleteStaging($this->admin, $attachment);

        Storage::disk('private')->assertMissing($attachment->storage_key);
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }

    /**
     * finalizeAttachment mengubah state menjadi attached dan mengisi relasi entitas tujuan.
     */
    public function test_finalize_attachment_transitions_to_attached(): void
    {
        $project = Project::create([
            'jo_no' => '0093/ATT/2026',
            'part_no' => '0093-ATT-01',
            'part_name' => 'BRACKET ATTACHMENT',
        ]);

        $progress = Progress::create([
            'project_id' => $project->id,
            'activity_date' => '2026-10-19',
            'activity' => 'Trial',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $problem = Problem::create([
            'progress_id' => $progress->id,
            'client_id' => (string) Str::uuid(),
            'description' => 'Problem pengujian',
            'position' => 0,
        ]);

        $file = UploadedFile::fake()->image('problem_photo.jpg', 300, 300)->size(300);
        $attachment = $this->service->stage(
            admin: $this->admin,
            draft: $this->draft,
            file: $file,
            kind: 'photo',
            ownerKind: 'problem',
            ownerClientId: $problem->client_id
        );

        $this->service->finalizeAttachment($attachment, $progress, problemId: $problem->id);

        $attachment->refresh();
        $this->assertSame('attached', $attachment->state);
        $this->assertNull($attachment->draft_id);
        $this->assertNull($attachment->owner_client_id);
        $this->assertSame($problem->id, $attachment->problem_id);
    }
}
