<?php

namespace Tests\Feature;

use App\Livewire\Prototype\Shell;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\PrototypeTestCase as TestCase;

class PrototypeShellTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        FakeStore::reset();
    }

    public function test_shell_renders_successfully_with_initial_stats(): void
    {
        Livewire::test(Shell::class)
            ->assertStatus(200)
            ->assertSee('Ringkasan Keseluruhan')
            ->assertSee('Progress Open')
            ->assertSee('Draft Tersimpan')
            ->assertSee('Aktivitas Bulan Berjalan');
    }

    public function test_shell_switches_tabs(): void
    {
        // Projects Tab
        Livewire::test(Shell::class, ['tab' => 'projects'])
            ->assertSee('Project Master')
            ->assertSee('0012/NM/2026')
            ->assertSee('0091-XYZ-01');

        // Progresses Tab
        Livewire::test(Shell::class, ['tab' => 'progresses'])
            ->assertSee('Trial')
            ->assertSee('Sent Sample');

        // Drafts Tab
        Livewire::test(Shell::class, ['tab' => 'drafts'])
            ->assertSee('Draft')
            ->assertSee('Lanjutkan');

        // Components Tab
        Livewire::test(Shell::class, ['tab' => 'components'])
            ->assertSee('Pustaka Komponen UI Terstandardisasi')
            ->assertSee('Simpan (Primary)')
            ->assertSee('Burry 1,5 mm');
    }

    public function test_shell_reset_action_restores_fixtures(): void
    {
        // Mutate store
        FakeStore::saveProgress([
            'project_id' => 'proj-001',
            'activity' => 'Trial',
            'notes' => 'Mutated test notes',
        ]);

        $this->assertCount(9, FakeStore::getProgresses());

        // Call resetData on component
        Livewire::test(Shell::class)
            ->call('resetData')
            ->assertDispatched('fixture-reset')
            ->assertSee('Data simulasi prototype berhasil direset');

        // Verify FakeStore is back to 8
        $this->assertCount(8, FakeStore::getProgresses());
    }
}
