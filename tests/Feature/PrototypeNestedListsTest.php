<?php

namespace Tests\Feature;

use App\Livewire\Prototype\ProgressForm;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\PrototypeTestCase as TestCase;

class PrototypeNestedListsTest extends TestCase
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
     * FR07 & AT07: Struktur dua tingkat Problem (Induk) dan Corrective Action (Anak).
     * Verifikasi: Isi PIC berbeda pada 4 tindakan, tambah problem ketiga, kembali ke problem pertama;
     * tidak ada nilai atau data tindakan yang tertukar.
     */
    public function test_at07_nested_problem_and_corrective_actions_stability(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Trial');

        // 1. Problem 1 sudah tersedia di awal form
        $problems = $component->get('problems');
        $this->assertCount(1, $problems);
        $prob1Id = $problems[0]['id'];

        $component->set('problems.0.description', 'Problem 1: Burry pada lubang pierching diameter 8 mm');

        // Tambah 2 Tindakan pada Problem 1 dengan PIC berbeda
        $component->call('addCorrectiveAction', $prob1Id);
        $component->call('addCorrectiveAction', $prob1Id);

        $component->set('problems.0.corrective_actions.0.description', 'Tindakan 1.1: Asah punch die');
        $component->set('problems.0.corrective_actions.0.pic', 'Budi Toolmaker');
        $component->set('problems.0.corrective_actions.0.actual_date', '2026-10-26');

        $component->set('problems.0.corrective_actions.1.description', 'Tindakan 1.2: Check clearance stripper');
        $component->set('problems.0.corrective_actions.1.pic', 'Andi Stamping');
        $component->set('problems.0.corrective_actions.1.actual_date', '2026-10-27');

        // 2. Tambah Problem 2
        $component->call('addProblem');
        $problems = $component->get('problems');
        $this->assertCount(2, $problems);
        $prob2Id = $problems[1]['id'];

        $component->set('problems.1.description', 'Problem 2: Springback sudut bending flange melebihi 2 derajat');

        // Tambah 2 Tindakan pada Problem 2 dengan PIC berbeda
        $component->call('addCorrectiveAction', $prob2Id);
        $component->call('addCorrectiveAction', $prob2Id);

        $component->set('problems.1.corrective_actions.0.description', 'Tindakan 2.1: Shim die bending');
        $component->set('problems.1.corrective_actions.0.pic', 'Joko Maintenance');

        $component->set('problems.1.corrective_actions.1.description', 'Tindakan 2.2: Trial ulang 10 pcs');
        $component->set('problems.1.corrective_actions.1.pic', 'Siti QA');

        // 3. Tambah Problem 3
        $component->call('addProblem');
        $problems = $component->get('problems');
        $this->assertCount(3, $problems);
        $prob3Id = $problems[2]['id'];
        $component->set('problems.2.description', 'Problem 3: Scratch pada permukaan luar part');

        // 4. Verifikasi Nilai Problem 1 dan Problem 2 tetap utuh dan TIDAK tertukar (AT07)
        $currentProblems = $component->get('problems');
        $this->assertSame('Problem 1: Burry pada lubang pierching diameter 8 mm', $currentProblems[0]['description']);
        $this->assertSame('Budi Toolmaker', $currentProblems[0]['corrective_actions'][0]['pic']);
        $this->assertSame('Andi Stamping', $currentProblems[0]['corrective_actions'][1]['pic']);
        $this->assertSame('2026-10-26', $currentProblems[0]['corrective_actions'][0]['actual_date']);

        $this->assertSame('Problem 2: Springback sudut bending flange melebihi 2 derajat', $currentProblems[1]['description']);
        $this->assertSame('Joko Maintenance', $currentProblems[1]['corrective_actions'][0]['pic']);
        $this->assertSame('Siti QA', $currentProblems[1]['corrective_actions'][1]['pic']);

        $this->assertSame('Problem 3: Scratch pada permukaan luar part', $currentProblems[2]['description']);

        // 5. Hapus Problem 2 -> Problem 1 dan Problem 3 tetap ada dengan data lengkap
        $component->call('removeProblem', $prob2Id);
        $afterDelete = $component->get('problems');
        $this->assertCount(2, $afterDelete);
        $this->assertSame($prob1Id, $afterDelete[0]['id']);
        $this->assertSame('Budi Toolmaker', $afterDelete[0]['corrective_actions'][0]['pic']);
        $this->assertSame($prob3Id, $afterDelete[1]['id']);
        $this->assertSame('Problem 3: Scratch pada permukaan luar part', $afterDelete[1]['description']);
    }

    /**
     * FR08 & AT08: Next Action berdiri sendiri sebagai saudara problem, bukan anak dari problem (D07).
     */
    public function test_at08_next_actions_stand_independently(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Trial');

        // Tambah Next Action ke-2 (Next Action ke-1 sudah tersedia di awal)
        $component->call('addNextAction');

        $this->assertCount(2, $component->get('nextActions'));

        $component->set('nextActions.0.description', 'Jadwalkan Trial 2 bersama pihak Customer');
        $component->set('nextActions.0.pic', 'Doni Project Coordinator');
        $component->set('nextActions.0.actual_date', '2026-10-28');

        $component->set('nextActions.1.description', 'Kirim sample trial pertama ke lab CMM');
        $component->set('nextActions.1.pic', 'Eko Lab CMM');

        // Pastikan problems tetap kosong (tidak tertukar)
        $this->assertCount(1, $component->get('problems'));

        // Hapus salah satu next action
        $na1Id = $component->get('nextActions')[0]['id'];
        $component->call('removeNextAction', $na1Id);

        $this->assertCount(1, $component->get('nextActions'));
        $this->assertSame('Kirim sample trial pertama ke lab CMM', $component->get('nextActions')[0]['description']);
    }

    /**
     * FR09 & AT09: Feedback Customer sebagai riwayat berulang dengan tanggal, isi, dan PIC mandiri (D08).
     */
    public function test_at09_feedback_entries_are_repeatable_and_independent(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Sent Sample');

        // Tambah Feedback ke-2 (Feedback ke-1 sudah tersedia di awal)
        $component->call('addFeedback');

        $this->assertCount(2, $component->get('feedbacks'));

        $component->set('feedbacks.0.date', '2026-10-26');
        $component->set('feedbacks.0.pic', 'Mr. Tanaka (QA Honda)');
        $component->set('feedbacks.0.content', 'Dimensi flange disetujui, radius R2,5 masih ada burry halus.');

        $component->set('feedbacks.1.date', '2026-10-27');
        $component->set('feedbacks.1.pic', 'Pak Hendra (Purchasing)');
        $component->set('feedbacks.1.content', 'Mohon dikirimkan ulang 5 pcs sampel setelah perbaikan.');

        $feedbacks = $component->get('feedbacks');
        $this->assertSame('Mr. Tanaka (QA Honda)', $feedbacks[0]['pic']);
        $this->assertSame('Pak Hendra (Purchasing)', $feedbacks[1]['pic']);
    }

    /**
     * FR10, FR11, FR19 & AT10, AT11, AT26: Galeri foto per problem dengan caption dan badge [SIMULASI].
     */
    public function test_at10_gallery_photos_per_problem_with_simulation_badge(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-25')
            ->set('activity', 'Trial');

        $component->call('addProblem');
        $probId = $component->get('problems')[0]['id'];

        // Tambah 2 foto simulasi pada problem
        $component->call('addSimulationPhoto', $probId);
        $component->call('addSimulationPhoto', $probId);

        $problems = $component->get('problems');
        $this->assertCount(2, $problems[0]['photos']);

        $component->set('problems.0.photos.0.caption', 'Foto burry pada lubang pierching');
        $component->set('problems.0.photos.1.caption', 'Grafik hasil pengukuran CMM');

        $component->assertSee('[SIMULASI]')
            ->assertSee('Foto burry pada lubang pierching');

        // Hapus foto pertama
        $ph1Id = $problems[0]['photos'][0]['id'];
        $component->call('removeSimulationPhoto', $probId, $ph1Id);

        $updatedProblems = $component->get('problems');
        $this->assertCount(1, $updatedProblems[0]['photos']);
        $this->assertSame('Grafik hasil pengukuran CMM', $updatedProblems[0]['photos'][0]['caption']);
    }

    /**
     * Menyimpan form final ke FakeStore menyimpan seluruh relasi graf bertingkat secara utuh.
     */
    public function test_save_persists_complete_nested_graph_to_fake_store(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('projectId', 'proj-001')
            ->set('activityDate', '2026-10-29')
            ->set('activity', 'Trial')
            ->set('notes', 'Full test of nested relations');

        // 1 problem with 1 action & 1 photo
        $component->call('addProblem');
        $probId = $component->get('problems')[0]['id'];
        $component->set('problems.0.description', 'Problem Utama');
        $component->call('addCorrectiveAction', $probId);
        $component->set('problems.0.corrective_actions.0.description', 'Tindakan Korektif');
        $component->set('problems.0.corrective_actions.0.pic', 'PIC Khusus');
        $component->call('addSimulationPhoto', $probId);
        $component->set('problems.0.photos.0.caption', 'Foto Bukti');

        // 1 next action
        $component->call('addNextAction');
        $component->set('nextActions.0.description', 'Langkah Lanjutan Mandiri');
        $component->set('nextActions.0.pic', 'PIC Next');

        // 1 feedback
        $component->call('addFeedback');
        $component->set('feedbacks.0.content', 'Feedback Customer Terkait');
        $component->set('feedbacks.0.pic', 'PIC Customer');

        // Simpan final
        $component->call('save')->assertHasNoErrors();

        // Verifikasi FakeStore
        $progresses = FakeStore::getProgresses(['project_id' => 'proj-001', 'activity' => 'Trial']);
        $saved = end($progresses);

        $this->assertNotEmpty($saved['problems']);
        $this->assertSame('Problem Utama', $saved['problems'][0]['description']);
        $this->assertSame('Tindakan Korektif', $saved['problems'][0]['corrective_actions'][0]['description']);
        $this->assertSame('PIC Khusus', $saved['problems'][0]['corrective_actions'][0]['pic']);
        $this->assertSame('Foto Bukti', $saved['problems'][0]['photos'][0]['caption']);

        $this->assertNotEmpty($saved['next_actions']);
        $this->assertSame('Langkah Lanjutan Mandiri', $saved['next_actions'][0]['description']);
        $this->assertSame('PIC Next', $saved['next_actions'][0]['pic']);

        $this->assertNotEmpty($saved['feedbacks']);
        $this->assertSame('Feedback Customer Terkait', $saved['feedbacks'][0]['content']);
        $this->assertSame('PIC Customer', $saved['feedbacks'][0]['pic']);
    }
}
