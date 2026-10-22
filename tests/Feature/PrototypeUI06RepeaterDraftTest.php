<?php

namespace Tests\Feature;

use App\Livewire\Prototype\ProgressForm;
use App\Prototype\FakeDraftStore;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\PrototypeTestCase as TestCase;

class PrototypeUI06RepeaterDraftTest extends TestCase
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
     * Skenario 1: Simpan hanya JO/tanggal/Activity -> Satu Open; tidak ada child kosong.
     */
    public function test_scenario_01_save_minimal_removes_empty_skeleton_children(): void
    {
        $component = Livewire::withQueryParams(['tab' => 'form'])
            ->test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Trial');

        // Form awal memiliki kerangka kosong untuk problem, next action, feedback
        $this->assertNotEmpty($component->get('problems'));
        $this->assertNotEmpty($component->get('nextActions'));
        $this->assertNotEmpty($component->get('feedbacks'));

        // Simpan
        $component->call('save')->assertHasNoErrors();

        // Verifikasi pada FakeStore
        $progresses = FakeStore::getProgresses(['project_id' => 'proj-001']);
        $saved = end($progresses);

        $this->assertSame('Open', $saved['status']);
        $this->assertEmpty($saved['problems'], 'Problem kosong tidak boleh disimpan ke DB');
        $this->assertEmpty($saved['next_actions'], 'Next action kosong tidak boleh disimpan ke DB');
        $this->assertEmpty($saved['feedbacks'], 'Feedback kosong tidak boleh disimpan ke DB');
    }

    /**
     * Skenario 2: Dua problem × dua CA, PIC/tanggal berbeda -> Empat CA tetap di parent dan nilai yang benar.
     */
    public function test_scenario_02_two_problems_two_ca_distinct_pics_and_dates(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Trial');

        // Problem 1
        $component->call('addProblem');
        $prob1Id = $component->get('problems')[0]['id'];
        $component->set('problems.0.description', 'Problem 1');
        $component->set('problems.0.corrective_actions.0.description', 'CA 1.1');
        $component->set('problems.0.corrective_actions.0.pic', 'Budi 1');
        $component->set('problems.0.corrective_actions.0.actual_date', '2026-10-26');

        $component->call('addCorrectiveAction', $prob1Id);
        $component->set('problems.0.corrective_actions.1.description', 'CA 1.2');
        $component->set('problems.0.corrective_actions.1.pic', 'Andi 1');
        $component->set('problems.0.corrective_actions.1.actual_date', '2026-10-27');

        // Problem 2
        $component->call('addProblem');
        $prob2Id = $component->get('problems')[1]['id'];
        $component->set('problems.1.description', 'Problem 2');
        $component->set('problems.1.corrective_actions.0.description', 'CA 2.1');
        $component->set('problems.1.corrective_actions.0.pic', 'Joko 2');
        $component->set('problems.1.corrective_actions.0.actual_date', '2026-10-28');

        $component->call('addCorrectiveAction', $prob2Id);
        $component->set('problems.1.corrective_actions.1.description', 'CA 2.2');
        $component->set('problems.1.corrective_actions.1.pic', 'Siti 2');
        $component->set('problems.1.corrective_actions.1.actual_date', '2026-10-29');

        $component->call('save')->assertHasNoErrors();

        $progresses = FakeStore::getProgresses(['project_id' => 'proj-001']);
        $saved = end($progresses);

        $this->assertCount(2, $saved['problems']);
        $this->assertCount(2, $saved['problems'][0]['corrective_actions']);
        $this->assertCount(2, $saved['problems'][1]['corrective_actions']);

        $this->assertSame('Budi 1', $saved['problems'][0]['corrective_actions'][0]['pic']);
        $this->assertSame('Andi 1', $saved['problems'][0]['corrective_actions'][1]['pic']);
        $this->assertSame('Joko 2', $saved['problems'][1]['corrective_actions'][0]['pic']);
        $this->assertSame('Siti 2', $saved['problems'][1]['corrective_actions'][1]['pic']);
    }

    /**
     * Skenario 3: Satu CA hanya berisi PIC -> Tetap ada setelah draft/apply.
     */
    public function test_scenario_03_ca_pic_only_is_retained(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Trial');

        $component->call('addProblem');
        $component->set('problems.0.corrective_actions.0.pic', 'Mandor Pabrik');

        $component->call('save')->assertHasNoErrors();

        $progresses = FakeStore::getProgresses(['project_id' => 'proj-001']);
        $saved = end($progresses);

        $this->assertNotEmpty($saved['problems']);
        $this->assertNotEmpty($saved['problems'][0]['corrective_actions']);
        $this->assertSame('Mandor Pabrik', $saved['problems'][0]['corrective_actions'][0]['pic']);
    }

    /**
     * Skenario 4: Next Action hanya berisi tanggal -> Tetap ada, tidak menjadi CA.
     */
    public function test_scenario_04_next_action_date_only_is_retained(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Trial');

        $component->call('addNextAction');
        $component->set('nextActions.0.actual_date', '2026-11-05');

        $component->call('save')->assertHasNoErrors();

        $progresses = FakeStore::getProgresses(['project_id' => 'proj-001']);
        $saved = end($progresses);

        $this->assertEmpty($saved['problems']);
        $this->assertNotEmpty($saved['next_actions']);
        $this->assertSame('2026-11-05', $saved['next_actions'][0]['actual_date']);
    }

    /**
     * Skenario 5: Problem hanya berisi foto -> Parent tidak dibuang.
     */
    public function test_scenario_05_problem_photo_only_is_retained(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Trial');

        $component->call('addProblem');
        $probId = $component->get('problems')[0]['id'];
        $component->call('addSimulationPhoto', $probId);

        $component->call('save')->assertHasNoErrors();

        $progresses = FakeStore::getProgresses(['project_id' => 'proj-001']);
        $saved = end($progresses);

        $this->assertNotEmpty($saved['problems']);
        $this->assertNotEmpty($saved['problems'][0]['photos']);
    }

    /**
     * Skenario 6: Feedback hanya berisi file -> Feedback tidak dibuang.
     */
    public function test_scenario_06_feedback_file_only_is_retained(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Sent Sample');

        $component->call('addFeedback');
        $fbId = $component->get('feedbacks')[0]['id'];
        $component->call('addSimulationFeedbackFile', $fbId, 'laporan_sample.pdf');

        $component->call('save')->assertHasNoErrors();

        $progresses = FakeStore::getProgresses(['project_id' => 'proj-001']);
        $saved = end($progresses);

        $this->assertNotEmpty($saved['feedbacks']);
        $this->assertSame('laporan_sample.pdf', $saved['feedbacks'][0]['files'][0]['name']);
    }

    /**
     * Skenario 7: Keluar sebelum Apply, buka draft lagi -> Data pulih; progress tersimpan belum berubah.
     */
    public function test_scenario_07_draft_separation_and_restoration(): void
    {
        // Ambil progress awal
        $initial = FakeStore::findProgress('prog-001');
        $origNotes = $initial['notes'] ?? '';

        // Buka edit dan ubah notes memicu autosave
        $component = Livewire::test(ProgressForm::class, ['progressId' => 'prog-001'])
            ->set('notes', 'Catatan baru di draft yang belum di-apply');

        $draftUuid = $component->get('draftUuid');
        $this->assertNotNull($draftUuid);

        // Verifikasi FakeStore asli belum berubah
        $stillInitial = FakeStore::findProgress('prog-001');
        $this->assertSame($origNotes, $stillInitial['notes'] ?? '');

        // Buka kembali form dengan draft
        $restored = Livewire::withQueryParams(['draft' => $draftUuid])
            ->test(ProgressForm::class);

        $this->assertSame('Catatan baru di draft yang belum di-apply', $restored->get('notes'));
        $this->assertSame('prog-001', $restored->get('progressId'));
    }

    /**
     * Skenario 8: Simpan dua kali / retry gagal -> Tidak ada progress/child ganda.
     */
    public function test_scenario_08_save_double_click_no_duplicate(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Trial')
            ->set('notes', 'Single progress execution');

        $countBefore = count(FakeStore::getProgresses(['project_id' => 'proj-001']));

        $component->call('save');
        $component->call('save');

        $countAfter = count(FakeStore::getProgresses(['project_id' => 'proj-001']));
        $this->assertSame($countBefore + 1, $countAfter, 'Hanya boleh menambah tepat 1 progress');
    }

    /**
     * Skenario 9: Kegagalan autosave/apply -> Tidak muncul sukses palsu; isian tetap dapat dipulihkan.
     */
    public function test_scenario_09_save_failure_simulation(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('simulateSaveError', true)
            ->set('projectId', 'proj-001')
            ->set('notes', 'Isian tidak boleh hilang saat gagal');

        // Autosave status error
        $this->assertSame('error', $component->get('autosaveStatus'));
        $this->assertSame('Isian tidak boleh hilang saat gagal', $component->get('notes'));

        // Coba lagi tanpa error
        $component->set('simulateSaveError', false);
        $component->set('notes', 'Isian berhasil disimpan saat retry');

        $this->assertSame('saved', $component->get('autosaveStatus'));
    }

    /**
     * Skenario 10: Closed dengan draft aktif -> Wajib Reopen sebelum melanjutkan edit.
     */
    public function test_scenario_10_closed_guard_requires_reopen(): void
    {
        // Close progress
        FakeStore::closeProgress('prog-001');

        // Simpan draft untuk progress Closed tersebut
        $draft = FakeDraftStore::save([
            'progress_id' => 'prog-001',
            'project_id' => 'proj-001',
            'activity' => 'Trial',
            'notes' => 'Mencoba edit progress closed',
        ]);

        $component = Livewire::withQueryParams(['draft' => $draft['uuid']])
            ->test(ProgressForm::class);

        $component->assertRedirect(route('prototype.index', ['tab' => 'progresses', 'detail' => 'prog-001']));

        // Reopen progress lalu form dapat dimuat dan disimpan
        FakeStore::reopenProgress('prog-001');

        $component = Livewire::withQueryParams(['draft' => $draft['uuid']])
            ->test(ProgressForm::class);

        $this->assertFalse($component->get('isClosed'));
        $component->call('save')->assertHasNoErrors();
    }

    public function test_can_remove_and_confirmation_modal_flow(): void
    {
        $component = Livewire::test(ProgressForm::class, ['progressId' => 'prog-001']);
        $initialProblems = $component->get('problems');
        $savedProblemId = $initialProblems[0]['id'];

        // Record tersimpan TIDAK boleh dihapus
        $this->assertFalse($component->instance()->canRemove('problem', $savedProblemId));

        $component->call('promptRemove', 'problem', $savedProblemId);
        $this->assertNull($component->get('pendingRemoval'));
        $component->assertHasErrors(['remove']);

        // Tambah baris baru
        $component->call('addProblem');
        $newProblems = $component->get('problems');
        $newProblemId = end($newProblems)['id'];

        $this->assertTrue($component->instance()->canRemove('problem', $newProblemId));

        // Minta hapus baris baru -> Munculkan dialog konfirmasi
        $component->call('promptRemove', 'problem', $newProblemId);
        $this->assertSame('problem', $component->get('pendingRemoval.type'));
        $this->assertSame($newProblemId, $component->get('pendingRemoval.id'));

        // Batal
        $component->call('cancelRemove');
        $this->assertNull($component->get('pendingRemoval'));
        $this->assertCount(count($newProblems), $component->get('problems'));

        // Minta lagi lalu konfirmasi hapus
        $component->call('promptRemove', 'problem', $newProblemId);
        $component->call('confirmRemove');

        $this->assertNull($component->get('pendingRemoval'));
        $this->assertCount(count($initialProblems), $component->get('problems'));
    }
}
