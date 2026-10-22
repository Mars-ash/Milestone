<?php

namespace Tests\Feature;

use App\Livewire\Prototype\ProgressForm;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\PrototypeTestCase as TestCase;

class PrototypeProgressFormTest extends TestCase
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
     * FR04 & AT05: 1 Form Identik untuk Seluruh 5 Activity.
     * Mengubah Activity tidak mereset atau menghilangkan isian rincian trial.
     */
    public function test_at05_form_structure_is_identical_across_all_five_activities(): void
    {
        $component = Livewire::test(ProgressForm::class)
            // Pastikan 5 pilihan activity tersedia
            ->assertSee('Trial')
            ->assertSee('Sent Sample')
            ->assertSee('Send Document')
            ->assertSee('Audit')
            ->assertSee('Others')
            // Isi data rincian parameter trial
            ->set('projectId', 'proj-001')
            ->set('machine', 'KOMATSU 300T PRESS')
            ->set('dieHighRaw', '120,5')
            ->set('processTrial', 'OP10 DRAWING')
            ->set('materialActualTrial', 'SPCC 1.2 x 350')
            ->set('qtyMaterialRaw', '15,5')
            ->set('qtyProcessRaw', '10')
            ->set('qtySampleRaw', '2,5')
            ->set('eventNote', 'Uji coba tooling batch pertama')
            ->set('remark', 'Perlu penyesuaian radius R3');

        // Verifikasi pada activity awal (Trial)
        $this->assertSame('', $component->get('activity'));
        $this->assertSame('KOMATSU 300T PRESS', $component->get('machine'));
        $this->assertSame('120,5', $component->get('dieHighRaw'));

        // Ubah Activity ke 'Sent Sample' (FR04 / AT05)
        $component->call('setActivity', 'Sent Sample');
        $this->assertSame('Sent Sample', $component->get('activity'));
        $this->assertSame('KOMATSU 300T PRESS', $component->get('machine'), 'Nilai mesin tidak boleh hilang saat pindah ke Sent Sample');
        $this->assertSame('120,5', $component->get('dieHighRaw'), 'Nilai DH tidak boleh hilang saat pindah ke Sent Sample');
        $this->assertSame('OP10 DRAWING', $component->get('processTrial'));

        // Ubah Activity ke 'Others'
        $component->call('setActivity', 'Others');
        $this->assertSame('Others', $component->get('activity'));
        $this->assertSame('KOMATSU 300T PRESS', $component->get('machine'), 'Nilai mesin tidak boleh hilang saat pindah ke Others');
        $this->assertSame('120,5', $component->get('dieHighRaw'), 'Nilai DH tidak boleh hilang saat pindah ke Others');

        // Ubah Activity ke 'Send Document'
        $component->call('setActivity', 'Send Document');
        $this->assertSame('Send Document', $component->get('activity'));
        $this->assertSame('KOMATSU 300T PRESS', $component->get('machine'));

        // Ubah Activity ke 'Audit'
        $component->call('setActivity', 'Audit');
        $this->assertSame('Audit', $component->get('activity'));
        $this->assertSame('KOMATSU 300T PRESS', $component->get('machine'));
    }

    /**
     * FR05 & AT06: PIC Progress dan PIC Customer teks bebas tanpa ketergantungan akun/master PIC.
     * Raw text mempertahankan koma dan titik (misal '1,5' dan '1.5') tanpa konversi otomatis.
     */
    public function test_at06_pic_and_raw_text_formatting_preserved(): void
    {
        Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-15')
            ->set('activity', 'Trial')
            ->set('picCustomer', 'Mr. Tanaka / QA Inspector Honda')
            ->set('notes', 'Trial mold die casting')
            ->set('dieHighRaw', '145,75')
            ->set('qtyMaterialRaw', '1,5')
            ->set('qtyProcessRaw', '3.5')
            ->set('qtySampleRaw', '0,75')
            ->call('save')
            ->assertHasNoErrors();

        // Ambil record yang baru disimpan di FakeStore
        $progresses = FakeStore::getProgresses(['project_id' => 'proj-001']);
        $newProgress = end($progresses);

        $this->assertNotEmpty($newProgress);
        $this->assertSame('Mr. Tanaka / QA Inspector Honda', $newProgress['pic_customer']);
        $this->assertSame('145,75', $newProgress['die_high_raw'], 'Format koma pada Die High harus dipertahankan');
        $this->assertSame('1,5', $newProgress['qty_material_raw'], 'Format koma pada Qty Material harus dipertahankan');
        $this->assertSame('3.5', $newProgress['qty_process_raw'], 'Format titik pada Qty Process harus dipertahankan');
        $this->assertSame('0,75', $newProgress['qty_sample_raw'], 'Format koma pada Qty Sample harus dipertahankan');
    }

    /**
     * FR02 & AT03: Memilih JO otomatis memicu autofill 5 field identitas hanya-baca dari Master Project terkini.
     */
    public function test_at03_autofill_five_identity_fields_from_current_project_master(): void
    {
        $component = Livewire::test(ProgressForm::class);

        // Awalnya belum memilih JO -> data identitas kosong
        $this->assertNull($component->get('selectedProject'));

        // Pilih proj-001 (JO: 0012/NM/2026)
        $component->set('projectId', 'proj-001');
        $selected = $component->get('selectedProject');

        $this->assertNotNull($selected);
        $this->assertSame('0012/NM/2026', $selected['jo_no']);
        $this->assertSame('0091-XYZ-01', $selected['part_no']);
        $this->assertSame('BRACKET FRONT LH (FIKTIF)', $selected['part_name']);
        $this->assertSame('PT ASTRA FIKTIF MOTOR', $selected['customer']);
        $this->assertSame('K-FIKTIF-1', $selected['model']);
        $this->assertSame('DOC-001/2026', $selected['no_doc']);

        // Komponen merender identitas di view
        $component->assertSee('BRACKET FRONT LH (FIKTIF)')
            ->assertSee('0091-XYZ-01')
            ->assertSee('PT ASTRA FIKTIF MOTOR')
            ->assertSee('K-FIKTIF-1')
            ->assertSee('DOC-001/2026');

        // Ganti ke proj-002 (JO: 0450/NM/2026)
        $component->set('projectId', 'proj-002');
        $selected2 = $component->get('selectedProject');

        $this->assertSame('0450/NM/2026', $selected2['jo_no']);
        $this->assertSame('08234-K0100', $selected2['part_no']);
        $this->assertSame('COVER SIDE LOWER (FIKTIF)', $selected2['part_name']);
        $component->assertSee('COVER SIDE LOWER (FIKTIF)');
    }

    /**
     * Validasi Simpan Baru: Hanya 3 field wajib (project_id, activity_date, activity).
     * Problem/action/foto/rincian trial adalah opsional untuk simpan awal.
     */
    public function test_validation_new_progress_only_requires_three_mandatory_fields(): void
    {
        // 1. Submit tanpa field wajib -> error pada 3 field wajib
        Livewire::test(ProgressForm::class)
            ->set('projectId', '')
            ->set('activityDate', '')
            ->set('activity', '')
            ->call('save')
            ->assertHasErrors(['projectId', 'activityDate', 'activity']);

        // 2. Isi HANYA 3 field wajib -> berhasil disimpan tanpa error
        Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-20')
            ->set('activity', 'Audit')
            ->set('notes', '') // Catatan kosong diperbolehkan untuk simpan cepat awal
            ->call('save')
            ->assertHasNoErrors();

        // Pastikan progress tercatat dengan status default Open
        $progresses = FakeStore::getProgresses(['project_id' => 'proj-001', 'activity' => 'Audit']);
        $this->assertNotEmpty($progresses);
        $this->assertSame('Open', end($progresses)['status']);
    }

    /**
     * PRD D11: Simpan draft tidak mempengaruhi hitungan statistik Open / Closed.
     */
    public function test_d11_save_draft_does_not_affect_open_closed_statistics(): void
    {
        $statsInitial = FakeStore::getStats();
        $initialDraftCount = count(FakeStore::getDrafts());

        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activity', 'Trial')
            ->set('activityDate', '2026-10-21')
            ->set('notes', 'DRAFT: Hasil trial sementara masih dianalisis')
            ->call('saveDraft');

        $draftUuid = $component->get('draftUuid');
        $this->assertNotNull($draftUuid);
        $this->assertSame('saved', $component->get('autosaveStatus'));

        // Cek bahwa jumlah draft bertambah
        $draftsAfter = FakeStore::getDrafts();
        $this->assertCount($initialDraftCount + 1, $draftsAfter);

        // Cek bahwa statistik Open dan Closed TIDAK berubah (PRD D11)
        $statsAfter = FakeStore::getStats();
        $this->assertSame($statsInitial['open_count'], $statsAfter['open_count'], 'Draft tidak boleh menambah open count');
        $this->assertSame($statsInitial['closed_count'], $statsAfter['closed_count'], 'Draft tidak boleh menambah closed count');

        // Pastikan draft dapat dimuat kembali
        $loadedDraft = FakeStore::findDraft($draftUuid);
        $this->assertNotNull($loadedDraft);
        $this->assertSame('DRAFT: Hasil trial sementara masih dianalisis', $loadedDraft['notes']);
    }

    /**
     * Menyimpan progress final menghapus draft terkait bila sebelumnya disimpan sebagai draft.
     */
    public function test_saving_final_progress_cleans_up_associated_draft(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activity', 'Sent Sample')
            ->set('activityDate', '2026-10-22')
            ->set('notes', 'Sampel dikirim ke customer')
            ->call('saveDraft');

        $draftUuid = $component->get('draftUuid');
        $this->assertNotNull(FakeStore::findDraft($draftUuid));

        // Submit form final
        $component->call('save')->assertHasNoErrors();

        // Draft harus sudah terhapus
        $this->assertNull(FakeStore::findDraft($draftUuid), 'Draft harus otomatis terhapus saat progress final tersimpan');
    }
}
