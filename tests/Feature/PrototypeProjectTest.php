<?php

namespace Tests\Feature;

use App\Livewire\Prototype\ProjectDetail;
use App\Livewire\Prototype\ProjectForm;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\TestCase;

class PrototypeProjectTest extends TestCase
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
     * AT01: JO yang sudah ada ditolak pada project baru; tidak muncul project kedua.
     */
    public function test_at01_duplicate_jo_no_is_rejected_on_new_project(): void
    {
        Livewire::test(ProjectForm::class)
            ->set('jo_no', '0012/NM/2026') // Existing JO in proj-001
            ->set('part_no', '9988-UNIQUE-PART')
            ->set('part_name', 'TEST DUPLICATE JO')
            ->call('save')
            ->assertHasErrors(['jo_no'])
            ->assertSee("JO No &#039;0012/NM/2026&#039; sudah digunakan pada project lain.", false);

        // Ensure total projects remains 3
        $this->assertCount(3, FakeStore::getProjects());
    }

    /**
     * AT02: Part No yang sudah dipakai JO lain ditolak; nol di depan tetap utuh.
     */
    public function test_at02_duplicate_part_no_is_rejected_and_leading_zeros_preserved(): void
    {
        Livewire::test(ProjectForm::class)
            ->set('jo_no', '8888/NM/2026')
            ->set('part_no', '0091-XYZ-01') // Existing Part No in proj-001 with leading zero
            ->set('part_name', 'TEST DUPLICATE PART')
            ->call('save')
            ->assertHasErrors(['part_no'])
            ->assertSee("Part No &#039;0091-XYZ-01&#039; sudah digunakan pada project lain.", false);

        // Ensure total projects remains 3
        $this->assertCount(3, FakeStore::getProjects());

        // Test creating a new valid project with leading zeros
        Livewire::test(ProjectForm::class)
            ->set('jo_no', '0007/NM/2026')
            ->set('part_no', '00045-K0001')
            ->set('part_name', 'COVER FRONT LH TEST')
            ->call('save')
            ->assertHasNoErrors();

        $saved = FakeStore::findProject('0007/NM/2026');
        $this->assertNotNull($saved);
        $this->assertSame('0007/NM/2026', $saved['jo_no']);
        $this->assertSame('00045-K0001', $saved['part_no']);
        $this->assertCount(4, FakeStore::getProjects());
    }

    /**
     * AT03: Koreksi Part Name / Customer master terlihat pada progress lama tanpa mengubah uraian kegiatan.
     */
    public function test_at03_updating_master_project_reflects_in_old_progress(): void
    {
        // prog-001 belongs to proj-001
        $progBefore = FakeStore::findProgress('prog-001');
        $this->assertSame('BRACKET FRONT LH (FIKTIF)', $progBefore['project']['part_name']);
        $originalNotes = $progBefore['notes'];

        // Edit master proj-001 through ProjectForm
        Livewire::test(ProjectForm::class, ['projectId' => 'proj-001'])
            ->assertSet('jo_no', '0012/NM/2026')
            ->assertSet('part_no', '0091-XYZ-01')
            ->set('part_name', 'BRACKET FRONT LH (REVISI DESIGN MASTER)')
            ->set('customer', 'PT ASTRA HONDA FIKTIF')
            ->call('save')
            ->assertHasNoErrors();

        // Re-read progress: must reflect dynamic new master identity
        $progAfter = FakeStore::findProgress('prog-001');
        $this->assertSame('BRACKET FRONT LH (REVISI DESIGN MASTER)', $progAfter['project']['part_name']);
        $this->assertSame('PT ASTRA HONDA FIKTIF', $progAfter['project']['customer']);
        $this->assertSame($originalNotes, $progAfter['notes'], 'Uraian kegiatan progress tidak boleh berubah');
    }

    /**
     * AT04: T0 Target/Actual, jadwal, dan Event 1 sampai 3 tersimpan manual.
     */
    public function test_at04_master_schedule_and_events_saved_manually(): void
    {
        Livewire::test(ProjectForm::class)
            ->set('jo_no', '0999/NM/2026')
            ->set('part_no', '08999-XYZ-99')
            ->set('part_name', 'STAMPING TEST SCHEDULE')
            ->set('t0_target', '2026-05-10')
            ->set('t0_actual', '2026-05-15')
            ->set('event_1', '2026-06-01')
            ->set('event_2', '2026-07-01')
            ->set('event_3', '2026-08-01')
            ->call('save')
            ->assertHasNoErrors();

        $saved = FakeStore::findProject('0999/NM/2026');
        $this->assertSame('2026-05-10', $saved['t0_target']);
        $this->assertSame('2026-05-15', $saved['t0_actual']);
        $this->assertSame('2026-06-01', $saved['event_1']);
        $this->assertSame('2026-07-01', $saved['event_2']);
        $this->assertSame('2026-08-01', $saved['event_3']);
    }

    /**
     * D02 & FR01: Project User dan Status Project tidak mempunyai field di tampilan dan form.
     */
    public function test_d02_project_user_and_status_project_removed_completely(): void
    {
        Livewire::test(ProjectForm::class)
            ->assertDontSee('Project User')
            ->assertDontSee('Status Project');

        Livewire::test(ProjectDetail::class, ['projectId' => 'proj-001'])
            ->assertDontSee('Project User')
            ->assertDontSee('Status Project')
            ->assertSee('BRACKET FRONT LH (FIKTIF)')
            ->assertSee('0012/NM/2026');
    }
}
