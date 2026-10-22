<?php

namespace Tests\Feature\Progress;

use App\Livewire\Progresses\ProgressEditor;
use App\Models\Attachment;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProblemPhotoGalleryTest extends TestCase
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
            'jo_no' => 'JO-PHOTO-01',
            'part_no' => 'PN-PHOTO-01',
            'part_name' => 'Part Photo Test',
        ]);
    }

    /**
     * AT26: Dua problem masing-masing memiliki foto dengan caption berbeda,
     * foto tetap berada pada problem yang benar setelah disimpan.
     */
    public function test_at26_photos_and_captions_isolated_per_problem(): void
    {
        $this->actingAs($this->admin);

        $img1 = UploadedFile::fake()->image('prob1_photo.jpg', 640, 480);
        $img2 = UploadedFile::fake()->image('prob2_photo.png', 800, 600);

        $test = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-20')
            ->set('activity', 'Trial')
            ->set('problems.0.description', 'Problem Pertama: Retak pada flange')
            ->call('addProblem')
            ->set('problems.1.description', 'Problem Kedua: Burry lubang');

        $problems = $test->get('problems');
        $this->assertCount(2, $problems);
        $prob1ClientId = $problems[0]['client_id'];
        $prob2ClientId = $problems[1]['client_id'];

        // Upload photo 1 ke Problem 1
        $test->set("pendingPhotos.{$prob1ClientId}", [$img1]);

        // Upload photo 2 ke Problem 2
        $test->set("pendingPhotos.{$prob2ClientId}", [$img2]);

        $updatedProblems = $test->get('problems');
        $this->assertCount(1, $updatedProblems[0]['photos']);
        $this->assertCount(1, $updatedProblems[1]['photos']);

        // Set caption masing-masing
        $test->set('problems.0.photos.0.caption', 'Foto retak detail 1')
            ->set('problems.1.photos.0.caption', 'Foto burry detail 2');

        // Simpan progress
        $test->call('save')
            ->assertHasNoErrors();

        // Verifikasi di database
        $progress = Progress::where('project_id', $this->project->id)->first();
        $this->assertNotNull($progress);

        $savedProblems = Problem::where('progress_id', $progress->id)->orderBy('position')->get();
        $this->assertCount(2, $savedProblems);

        $p1Photos = Attachment::where('problem_id', $savedProblems[0]->id)->get();
        $this->assertCount(1, $p1Photos);
        $this->assertSame('Foto retak detail 1', $p1Photos[0]->caption);
        $this->assertSame('prob1_photo.jpg', $p1Photos[0]->original_name);
        $this->assertSame('attached', $p1Photos[0]->state);

        $p2Photos = Attachment::where('problem_id', $savedProblems[1]->id)->get();
        $this->assertCount(1, $p2Photos);
        $this->assertSame('Foto burry detail 2', $p2Photos[0]->caption);
        $this->assertSame('prob2_photo.png', $p2Photos[0]->original_name);
        $this->assertSame('attached', $p2Photos[0]->state);
    }

    /**
     * AT27: Layanan unduh dan preview terkunci auth, menolak akses tanpa sesi,
     * serta menolak file non-gambar.
     */
    public function test_at27_unauthenticated_download_and_preview_are_blocked(): void
    {
        $this->actingAs($this->admin);

        $img = UploadedFile::fake()->image('audit_finding.jpg', 640, 480);

        $test = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-21')
            ->set('activity', 'Trial')
            ->set('problems.0.description', 'Problem Audit');

        $probClientId = $test->get('problems.0.client_id');
        $test->set("pendingPhotos.{$probClientId}", [$img])
            ->set('problems.0.photos.0.caption', 'Temuan audit')
            ->call('save')
            ->assertHasNoErrors();

        $attachment = Attachment::where('caption', 'Temuan audit')->first();
        $this->assertNotNull($attachment);

        // Akses dengan admin berhasil
        $this->get(route('attachments.download', $attachment))
            ->assertOk();
        $this->get(route('attachments.preview', $attachment))
            ->assertOk();
        $this->get(route('attachments.thumbnail', $attachment))
            ->assertOk();

        // Logout dan akses unauthenticated
        auth()->logout();

        $this->get(route('attachments.download', $attachment))
            ->assertRedirect(route('login'));
        $this->get(route('attachments.preview', $attachment))
            ->assertRedirect(route('login'));
        $this->get(route('attachments.thumbnail', $attachment))
            ->assertRedirect(route('login'));
    }

    /**
     * Menolak upload berkas fake image (bukan gambar asli).
     */
    public function test_rejects_fake_image_uploads(): void
    {
        $this->actingAs($this->admin);

        // File ekstensi .jpg tapi isinya teks executable/palsu
        $fakeImg = UploadedFile::fake()->create('malicious.jpg', 100, 'text/plain');

        $test = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-22')
            ->set('activity', 'Trial');

        $probClientId = $test->get('problems.0.client_id');
        $test->set("pendingPhotos.{$probClientId}", [$fakeImg]);

        $test->assertHasErrors(["photos.{$probClientId}"]);
    }

    /**
     * Mengoreksi caption foto tersimpan mempertahankan berkas sumber tanpa mengubah byte atau ID.
     */
    public function test_caption_update_preserves_file_bytes_and_identity(): void
    {
        $this->actingAs($this->admin);

        $img = UploadedFile::fake()->image('original_sample.jpg', 640, 480);

        $test = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-22')
            ->set('activity', 'Trial')
            ->set('problems.0.description', 'Problem Uji Caption');

        $probClientId = $test->get('problems.0.client_id');
        $test->set("pendingPhotos.{$probClientId}", [$img])
            ->set('problems.0.photos.0.caption', 'Caption Awal')
            ->call('save')
            ->assertHasNoErrors();

        $attachment = Attachment::where('caption', 'Caption Awal')->first();
        $this->assertNotNull($attachment);
        $originalChecksum = $attachment->checksum;
        $originalStorageKey = $attachment->storage_key;
        $originalId = $attachment->id;

        // Buka editor untuk progress ini dan koreksi caption
        $progress = Progress::where('project_id', $this->project->id)->first();
        $editTest = Livewire::test(ProgressEditor::class, [
            'projectId' => $this->project->id,
            'progressId' => $progress->id,
        ]);

        $editTest->set('problems.0.photos.0.caption', 'Caption Diperbarui')
            ->call('save')
            ->assertHasNoErrors();

        $reloaded = $attachment->fresh();
        $this->assertSame($originalId, $reloaded->id);
        $this->assertSame('Caption Diperbarui', $reloaded->caption);
        $this->assertSame($originalChecksum, $reloaded->checksum);
        $this->assertSame($originalStorageKey, $reloaded->storage_key);
    }

    /**
     * Staging photo dapat dihapus dari draft sebelum disimpan.
     */
    public function test_staging_photo_can_be_removed_before_save(): void
    {
        $this->actingAs($this->admin);

        $img = UploadedFile::fake()->image('temp_photo.jpg', 640, 480);

        $test = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-23')
            ->set('activity', 'Trial')
            ->set('problems.0.description', 'Problem Staging Remove');

        $probClientId = $test->get('problems.0.client_id');
        $test->set("pendingPhotos.{$probClientId}", [$img]);

        $photos = $test->get('problems.0.photos');
        $this->assertCount(1, $photos);
        $photoId = $photos[0]['id'];

        // Hapus foto staging
        $test->call('removePhoto', $probClientId, $photoId);

        $updatedPhotos = $test->get('problems.0.photos');
        $this->assertCount(0, $updatedPhotos);

        // Verifikasi staging record dihapus dari database
        $this->assertNull(Attachment::find($photoId));
    }

    /**
     * Lightbox preview modal dapat dibuka dan ditutup.
     */
    public function test_lightbox_modal_open_and_close(): void
    {
        $this->actingAs($this->admin);

        $img = UploadedFile::fake()->image('sample_lightbox.jpg', 640, 480);

        $test = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-24')
            ->set('activity', 'Trial');

        $probClientId = $test->get('problems.0.client_id');
        $test->set("pendingPhotos.{$probClientId}", [$img]);

        $photoId = $test->get('problems.0.photos.0.id');
        $this->assertNotNull($photoId);

        // Buka lightbox
        $test->call('openLightbox', $photoId);
        $activePhoto = $test->get('activePhoto');
        $this->assertNotNull($activePhoto);
        $this->assertSame($photoId, $activePhoto['id']);
        $test->assertSeeHtml('id="editor-photo-dialog"');

        // Tutup lightbox
        $test->call('closeLightbox');
        $this->assertNull($test->get('activePhoto'));
    }
}
