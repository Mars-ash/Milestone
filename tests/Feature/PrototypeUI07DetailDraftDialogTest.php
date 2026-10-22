<?php

namespace Tests\Feature;

use App\Livewire\Prototype\DraftList;
use App\Livewire\Prototype\ProgressDetail;
use App\Livewire\Prototype\ProgressForm;
use App\Livewire\Prototype\ProjectDetail;
use App\Prototype\FakeDraftStore;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\PrototypeTestCase as TestCase;

class PrototypeUI07DetailDraftDialogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        FakeStore::reset();
    }

    protected function tearDown(): void
    {
        FakeStore::reset();
        parent::tearDown();
    }

    /**
     * Buka p1 (prog-001): problem, CA, Next Action, feedback, dan detail terbaca dengan benar.
     */
    public function test_detail_reads_complete_graph_and_identities(): void
    {
        $component = Livewire::test(ProgressDetail::class, ['progressId' => 'prog-001'])
            ->assertStatus(200)
            ->assertSee('Problem 1')
            ->assertSee('Next Action')
            ->assertSee('Feedback Customer')
            ->assertSee('Aktivitas')
            ->assertSee('PT ASTRA FIKTIF MOTOR')
            ->assertSee('K-FIKTIF-1');

        $progress = $component->get('progress');
        $this->assertNotEmpty($progress);
        $this->assertSame('prog-001', $progress['id']);
        $this->assertNotEmpty($progress['problems']);
    }

    /**
     * Buat draft perubahan; ringkasan tetap versi tersimpan dan menampilkan catatan draft.
     */
    public function test_detail_displays_saved_version_with_draft_notification(): void
    {
        // Prog-001 awal
        $initial = FakeStore::findProgress('prog-001');
        $savedNotes = $initial['notes'] ?? '';

        // Simpan draft perubahan untuk prog-001
        FakeDraftStore::save([
            'progress_id' => 'prog-001',
            'project_id' => 'proj-001',
            'activity' => 'Trial',
            'notes' => 'Catatan draft yang belum di-apply',
        ]);

        $component = Livewire::test(ProgressDetail::class, ['progressId' => 'prog-001'])
            ->assertSee($savedNotes)
            ->assertDontSee('Catatan draft yang belum di-apply')
            ->assertSee('Ada draft perubahan. Data di halaman ini adalah versi yang terakhir diterapkan.')
            ->assertSee('Lanjutkan draft');
    }

    /**
     * Close manual melalui konfirmasi, tanpa syarat actual date kosong.
     * Semua catatan, problem, dan tindakan tetap tersimpan.
     */
    public function test_close_progress_with_modal_flow(): void
    {
        $component = Livewire::test(ProgressDetail::class, ['progressId' => 'prog-001']);
        $this->assertFalse($component->get('confirmingClose'));

        // Buka dialog konfirmasi
        $component->call('openConfirmClose');
        $this->assertTrue($component->get('confirmingClose'));

        // Batal konfirmasi
        $component->call('cancelConfirmClose');
        $this->assertFalse($component->get('confirmingClose'));
        $this->assertSame('Open', FakeStore::findProgress('prog-001')['status']);

        // Buka lagi lalu konfirmasi
        $component->call('openConfirmClose');
        $component->call('confirmCloseProgress');

        $this->assertFalse($component->get('confirmingClose'));
        $closed = FakeStore::findProgress('prog-001');
        $this->assertSame('Closed', $closed['status']);
        $this->assertNotEmpty($closed['problems']);
        $this->assertNotEmpty($closed['next_actions']);

        // Komponen menampilkan tombol Reopen
        $component->assertSee('Reopen progress');
    }

    /**
     * Reopen progress mengaktifkan kembali mode edit tanpa kehilangan child.
     */
    public function test_reopen_progress_preserves_all_children(): void
    {
        FakeStore::closeProgress('prog-001');

        $component = Livewire::test(ProgressDetail::class, ['progressId' => 'prog-001']);
        $component->call('reopenProgress');

        $reopened = FakeStore::findProgress('prog-001');
        $this->assertSame('Open', $reopened['status']);
        $this->assertNotEmpty($reopened['problems']);
        $this->assertNotEmpty($reopened['next_actions']);
    }

    /**
     * Halaman Draft: buang draft via konfirmasi dialog menurunkan jumlah draft dan membiarkan progress utuh.
     */
    public function test_draft_list_discard_modal_flow(): void
    {
        $draft = FakeDraftStore::save([
            'progress_id' => 'prog-001',
            'project_id' => 'proj-001',
            'activity' => 'Trial',
            'notes' => 'Draft untuk dibuang',
        ]);

        $component = Livewire::test(DraftList::class);
        $this->assertNull($component->get('confirmingDiscardUuid'));

        // Minta buang draft
        $component->call('promptDiscard', $draft['uuid']);
        $this->assertSame($draft['uuid'], $component->get('confirmingDiscardUuid'));

        // Batal
        $component->call('cancelDiscard');
        $this->assertNull($component->get('confirmingDiscardUuid'));
        $this->assertNotNull(FakeDraftStore::find($draft['uuid']));

        // Minta lagi lalu konfirmasi buang
        $component->call('promptDiscard', $draft['uuid']);
        $component->call('confirmDiscard');

        $this->assertNull($component->get('confirmingDiscardUuid'));
        $this->assertNull(FakeDraftStore::find($draft['uuid']));

        // Progress asli tetap utuh
        $this->assertNotNull(FakeStore::findProgress('prog-001'));
    }

    /**
     * Draft pada progress Closed mengarah ke ringkasan (tidak melewati guard edit).
     */
    public function test_draft_on_closed_progress_routes_to_detail(): void
    {
        FakeStore::closeProgress('prog-001');

        $draft = FakeDraftStore::save([
            'progress_id' => 'prog-001',
            'project_id' => 'proj-001',
            'activity' => 'Trial',
            'notes' => 'Draft closed test',
        ]);

        $component = Livewire::test(DraftList::class)
            ->assertSee('Progress Closed')
            ->assertSee('Lihat progress');
    }

    /**
     * Detail Project: tambah progress membawa context JO dan autofill benar.
     */
    public function test_project_detail_add_progress_prefills_jo(): void
    {
        $projectComponent = Livewire::test(ProjectDetail::class, ['projectId' => 'proj-001'])
            ->assertStatus(200)
            ->assertSee('Jadwal')
            ->assertSee('Parameter Material')
            ->assertSee('Riwayat Kegiatan')
            ->assertSee('Catat Progress');

        // Navigasi ke Form dengan project_id
        $formComponent = Livewire::test(ProgressForm::class, ['projectId' => 'proj-001']);
        $this->assertSame('proj-001', $formComponent->get('projectId'));
        $this->assertNotNull($formComponent->get('selectedProject'));
        $this->assertSame('0012/NM/2026', $formComponent->get('selectedProject.jo_no'));
    }

    /**
     * Modal dialog foto problem: klik thumbnail membuka dialog foto dengan caption, dan dapat ditutup.
     */
    public function test_photo_preview_dialog_flow(): void
    {
        $component = Livewire::test(ProgressDetail::class, ['progressId' => 'prog-001']);
        $this->assertNull($component->get('activePhoto'));

        $component->call('openPhoto', '/images/burry_detail.jpg', 'burry_detail.jpg');
        $this->assertSame('/images/burry_detail.jpg', $component->get('activePhoto.src'));
        $this->assertSame('burry_detail.jpg', $component->get('activePhoto.name'));
        $component->assertSee('Foto problem');

        $component->call('closePhoto');
        $this->assertNull($component->get('activePhoto'));
    }
}
