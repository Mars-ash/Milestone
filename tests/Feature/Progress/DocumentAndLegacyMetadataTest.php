<?php

namespace Tests\Feature\Progress;

use App\Livewire\Progresses\ProgressEditor;
use App\Models\Attachment;
use App\Models\Feedback;
use App\Models\LegacyDocumentMetadata;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentAndLegacyMetadataTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('uploads.disk', 'private'));

        $this->admin = User::first() ?? User::factory()->create([
            'email' => 'admin@example.com',
            'admin_slot' => true,
        ]);

        $this->project = Project::factory()->create([
            'jo_no' => 'JO-DOC-01',
            'part_no' => 'PN-DOC-01',
            'part_name' => 'Part Document Test',
        ]);
    }

    /**
     * AT28: Enam dokumen baru dapat menjadi enam entri (melebihi batas 5 slot lama Doc1-Doc5)
     * dan tersimpan sebagai lampiran operasional aktif.
     */
    public function test_at28_six_new_documents_can_be_uploaded_and_persisted(): void
    {
        $this->actingAs($this->admin);

        // Buat 6 file dokumen uji
        $files = [
            UploadedFile::fake()->create('spec_drawing.pdf', 500, 'application/pdf'),
            UploadedFile::fake()->create('inspection_sheet.xlsx', 300, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            UploadedFile::fake()->create('test_report.docx', 400, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            UploadedFile::fake()->create('raw_material_cert.pdf', 250, 'application/pdf'),
            UploadedFile::fake()->create('customer_agreement.doc', 350, 'application/msword'),
            UploadedFile::fake()->create('presentation_review.pptx', 600, 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
        ];

        $test = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Trial')
            ->set('remark', 'Uji 6 dokumen operasional');

        // Upload ke dokumen progress
        $test->set('pendingProgressDocuments', $files);

        $documents = $test->get('documents');
        $this->assertCount(6, $documents);

        // Beri caption pada dokumen ke-1 dan ke-6
        $test->set('documents.0.caption', 'Drawing revisi 3')
            ->set('documents.5.caption', 'Slide review trial');

        // Simpan progress
        $test->call('save')
            ->assertHasNoErrors();

        $progress = Progress::where('project_id', $this->project->id)->first();
        $this->assertNotNull($progress);

        $attachedDocs = Attachment::where('progress_id', $progress->id)
            ->where('kind', 'document')
            ->get();

        $this->assertCount(6, $attachedDocs);
        $this->assertTrue($attachedDocs->every(fn ($d) => $d->state === 'attached'));

        // Cek caption
        $doc1 = $attachedDocs->firstWhere('original_name', 'spec_drawing.pdf');
        $this->assertSame('Drawing revisi 3', $doc1->caption);

        $doc6 = $attachedDocs->firstWhere('original_name', 'presentation_review.pptx');
        $this->assertSame('Slide review trial', $doc6->caption);

        // Masing-masing dokumen memiliki download aktif
        $this->get(route('attachments.download', $doc1))->assertOk();
        $this->get(route('attachments.download', $doc6))->assertOk();
    }

    /**
     * AT28: Metadata dokumen lama (arsip Access) ditampilkan sebagai metadata-only
     * tanpa tombol unduh atau link unduh aktif, dan tanpa membuat file fisik palsu.
     */
    public function test_at28_legacy_metadata_only_entries_have_no_active_download(): void
    {
        $this->actingAs($this->admin);

        // Buat progress yang memiliki 2 entri metadata legacy
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-26',
            'activity' => 'Trial',
            'status' => 'Open',
            'revision' => 1,
        ]);

        LegacyDocumentMetadata::create([
            'progress_id' => $progress->id,
            'source_field' => 'doc_1',
            'name' => 'scan_lembar_ukur_lama.pdf',
            'description' => 'Arsip lembar ukur Access 2018',
        ]);

        LegacyDocumentMetadata::create([
            'progress_id' => $progress->id,
            'source_field' => 'doc_2',
            'name' => 'laporan_uji_tarik_legacy.xlsx',
            'description' => 'Data tarik laboratorium eksternal',
        ]);

        $test = Livewire::test(ProgressEditor::class, [
            'projectId' => $this->project->id,
            'progressId' => $progress->id,
        ]);

        // Verifikasi komponen merender teks metadata
        $test->assertSee('scan_lembar_ukur_lama.pdf')
            ->assertSee('Arsip lembar ukur Access 2018')
            ->assertSee('laporan_uji_tarik_legacy.xlsx')
            ->assertSee('Data tarik laboratorium eksternal')
            ->assertSee('File lama tidak dimigrasikan');

        // Pastikan TIDAK ADA link unduh untuk metadata lama
        $test->assertDontSeeHtml('href="/attachments/legacy')
            ->assertDontSeeHtml('Unduh Dokumen Lama');

        // Pastikan tidak ada file fisik palsu di storage
        $this->assertCount(0, Attachment::where('progress_id', $progress->id)->get());
    }

    /**
     * Dokumen pada feedback tersimpan dan terhubung ke feedback_id yang tepat.
     */
    public function test_feedback_documents_linked_to_correct_feedback(): void
    {
        $this->actingAs($this->admin);

        $fbDoc = UploadedFile::fake()->create('feedback_minutes.pdf', 300, 'application/pdf');

        $test = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-27')
            ->set('activity', 'Trial')
            ->set('feedbacks.0.content', 'Catatan feedback customer')
            ->set('feedbacks.0.pic', 'Bpk. Hendra');

        $fbClientId = $test->get('feedbacks.0.client_id');
        $this->assertNotNull($fbClientId);

        // Upload dokumen ke feedback
        $test->set("pendingFeedbackDocuments.{$fbClientId}", [$fbDoc]);

        $feedbacks = $test->get('feedbacks');
        $this->assertCount(1, $feedbacks[0]['documents']);
        $this->assertSame('feedback_minutes.pdf', $feedbacks[0]['documents'][0]['original_name']);

        // Simpan
        $test->call('save')
            ->assertHasNoErrors();

        $progress = Progress::where('project_id', $this->project->id)->first();
        $this->assertNotNull($progress);

        $savedFb = Feedback::where('progress_id', $progress->id)->first();
        $this->assertNotNull($savedFb);

        $fbAttachment = Attachment::where('feedback_id', $savedFb->id)->first();
        $this->assertNotNull($fbAttachment);
        $this->assertSame('feedback_minutes.pdf', $fbAttachment->original_name);
        $this->assertSame('attached', $fbAttachment->state);
        $this->assertSame('feedback', $fbAttachment->owner_kind);
    }

    /**
     * Menolak tipe file yang tidak diizinkan untuk dokumen.
     */
    public function test_rejects_unsupported_document_types(): void
    {
        $this->actingAs($this->admin);

        $unsupported = UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload');

        $test = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-28')
            ->set('activity', 'Trial');

        $test->set('pendingProgressDocuments', [$unsupported]);

        $test->assertHasErrors(['documents.progress']);
        $this->assertCount(0, $test->get('documents'));
    }

    /**
     * Menghapus dokumen staging sebelum simpan menghapus file dari staging disk.
     */
    public function test_staging_document_can_be_removed_before_save(): void
    {
        $this->actingAs($this->admin);

        $doc = UploadedFile::fake()->create('draft_doc.pdf', 200, 'application/pdf');

        $test = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-28')
            ->set('activity', 'Trial');

        $test->set('pendingProgressDocuments', [$doc]);

        $docs = $test->get('documents');
        $this->assertCount(1, $docs);
        $docId = $docs[0]['id'];

        $test->call('removeProgressDocument', $docId);

        $this->assertCount(0, $test->get('documents'));
        $this->assertNull(Attachment::find($docId));
    }
}
