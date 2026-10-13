<?php

namespace Tests\Feature;

use App\Livewire\Prototype\DraftList;
use App\Livewire\Prototype\ProgressForm;
use App\Prototype\FakeDraftStore;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\TestCase;

class PrototypeAutosaveDraftTest extends TestCase
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
     * FR12, AT12, AT13: Perubahan data pada form memicu simpan draft secara reaktif
     * tanpa merubah data progress resmi tersimpan di dashboard (D11).
     */
    public function test_at12_at13_autosave_persists_to_draft_without_modifying_saved_progress(): void
    {
        $originalProgress = FakeStore::findProgress('prog-001');
        $this->assertNotNull($originalProgress);
        $originalNotes = $originalProgress['notes'];
        $initialStats = FakeStore::getStats();

        // Buka form edit untuk prog-001
        $component = Livewire::test(ProgressForm::class, ['progressId' => 'prog-001'])
            ->set('notes', 'CATATAN BARU: Masih dianalisis oleh tim internal (Draft Only)');

        // Verifikasi draft tersimpan di FakeDraftStore
        $draftUuid = $component->get('draftUuid');
        $this->assertNotNull($draftUuid, 'Autosave harus menghasilkan UUID draft');
        $this->assertSame('saved', $component->get('autosaveStatus'));

        $savedDraft = FakeDraftStore::find($draftUuid);
        $this->assertNotNull($savedDraft);
        $this->assertSame('CATATAN BARU: Masih dianalisis oleh tim internal (Draft Only)', $savedDraft['notes']);
        $this->assertSame('prog-001', $savedDraft['progress_id']);
        $this->assertSame('edit', $savedDraft['type']);

        // VERIFIKASI KRITIS: Data progress resmi tersimpan TIDAK BERUBAH (tetap catatan lama)
        $currentProgress = FakeStore::findProgress('prog-001');
        $this->assertSame($originalNotes, $currentProgress['notes'], 'Data tersimpan tidak boleh berubah sebelum Simpan final');

        // VERIFIKASI KRITIS: Statistik Open/Closed tidak berubah (PRD D11)
        $currentStats = FakeStore::getStats();
        $this->assertSame($initialStats['open_count'], $currentStats['open_count']);
        $this->assertSame($initialStats['closed_count'], $currentStats['closed_count']);
    }

    /**
     * AT14: Membuka form baru tanpa mengisi apapun tidak boleh membuat record draft kosong.
     */
    public function test_at14_opening_empty_form_does_not_create_empty_draft(): void
    {
        $initialDraftCount = FakeDraftStore::count();

        // Buka form baru kosong tanpa modifikasi apapun
        $component = Livewire::test(ProgressForm::class);

        $this->assertNull($component->get('draftUuid'));
        $this->assertSame($initialDraftCount, FakeDraftStore::count(), 'Form baru kosong tidak boleh menambah record draft');
    }

    /**
     * AT15, AT16: Melanjutkan draft dari URL/UUID memulihkan seluruh data yang disimpan.
     */
    public function test_at15_at16_resuming_draft_restores_all_state(): void
    {
        // 1. Buat draft dengan data spesifik
        $payload = [
            'type' => 'new',
            'project_id' => 'proj-001',
            'activity' => 'Sent Sample',
            'activity_date' => '2026-10-28',
            'notes' => 'Draft pengiriman sampel ke customer',
            'pic_progress' => 'Rahmat Specialist',
            'pic_customer' => 'Mr. Tanaka',
            'machine' => 'KOMATSU 200T',
            'die_high_raw' => '135,5',
        ];
        $draft = FakeDraftStore::save($payload);
        $draftUuid = $draft['uuid'];

        // 2. Buka form dengan parameter query draft
        $component = Livewire::withQueryParams(['draft' => $draftUuid])
            ->test(ProgressForm::class);

        $this->assertSame($draftUuid, $component->get('draftUuid'));
        $this->assertSame('proj-001', $component->get('projectId'));
        $this->assertSame('Sent Sample', $component->get('activity'));
        $this->assertSame('2026-10-28', $component->get('activityDate'));
        $this->assertSame('Draft pengiriman sampel ke customer', $component->get('notes'));
        $this->assertSame('Rahmat Specialist', $component->get('picProgress'));
        $this->assertSame('Mr. Tanaka', $component->get('picCustomer'));
        $this->assertSame('KOMATSU 200T', $component->get('machine'));
        $this->assertSame('135,5', $component->get('dieHighRaw'));
    }

    /**
     * FR13, AT17, AT18: Simpan final menerapkan perubahan ke data tersimpan,
     * dan otomatis menghapus draft terkait.
     */
    public function test_at17_at18_final_save_applies_changes_and_deletes_draft(): void
    {
        // Buat draft perubahan untuk prog-001
        $component = Livewire::test(ProgressForm::class, ['progressId' => 'prog-001'])
            ->set('notes', 'PERUBAHAN FINAL: Masalah flange selesai diperbaiki')
            ->set('picProgress', 'Budi Senior QA');

        $draftUuid = $component->get('draftUuid');
        $this->assertNotNull(FakeDraftStore::find($draftUuid));

        // Submit form (Simpan final)
        $component->call('save')->assertHasNoErrors();

        // 1. Progress resmi sekarang telah ter-update dengan catatan baru
        $updatedProgress = FakeStore::findProgress('prog-001');
        $this->assertSame('PERUBAHAN FINAL: Masalah flange selesai diperbaiki', $updatedProgress['notes']);
        $this->assertSame('Budi Senior QA', $updatedProgress['pic_progress']);

        // 2. Draft terkait harus sudah terhapus otomatis dari FakeDraftStore
        $this->assertNull(FakeDraftStore::find($draftUuid), 'Draft harus otomatis terhapus saat progress final tersimpan');
    }

    /**
     * FR14, AT19: Buang draft melalui DraftList menghapus draft dan menjaga data resmi tetap utuh.
     */
    public function test_at19_discard_draft_removes_draft_and_leaves_saved_data_untouched(): void
    {
        // Buat draft perubahan
        $draft = FakeDraftStore::save([
            'type' => 'edit',
            'progress_id' => 'prog-001',
            'project_id' => 'proj-001',
            'notes' => 'Draft yang akan dibuang',
        ]);
        $draftUuid = $draft['uuid'];

        $originalProgress = FakeStore::findProgress('prog-001');

        // Buang draft via DraftList
        Livewire::test(DraftList::class)
            ->call('confirmDiscard', $draftUuid)
            ->call('discardDraft', $draftUuid)
            ->assertSee('Draft berhasil dibuang');

        // Draft terhapus dari store
        $this->assertNull(FakeDraftStore::find($draftUuid));

        // Progress resmi tetap utuh
        $currentProgress = FakeStore::findProgress('prog-001');
        $this->assertSame($originalProgress['notes'], $currentProgress['notes']);
    }

    /**
     * UI07, AT37: Indikator status autosave dan simulasi kegagalan simpan (Retry).
     */
    public function test_at37_save_error_simulation_and_retry(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('notes', 'Uji coba retry autosave');

        // Normal state: saved
        $this->assertSame('saved', $component->get('autosaveStatus'));

        // Toggle simulasi error
        $component->set('simulateSaveError', true);
        $component->call('saveDraft');

        $this->assertSame('error', $component->get('autosaveStatus'));
        $component->assertHasErrors(['save']);

        // Kembalikan ke normal lalu retry
        $component->set('simulateSaveError', false);
        $component->call('saveDraft');

        $this->assertSame('saved', $component->get('autosaveStatus'));
    }
}
