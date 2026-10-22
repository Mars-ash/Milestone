<?php

namespace Tests\Feature;

use App\Livewire\Prototype\Dashboard;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\PrototypeTestCase as TestCase;

class PrototypeDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        FakeStore::reset();
        session()->forget('prototype_ui_filters');
    }

    protected function tearDown(): void
    {
        FakeStore::reset();
        parent::tearDown();
    }

    /**
     * AT23: Ringkasan Open/Draft/bulan menghitung record unik; jumlah anak tidak menggandakan angka.
     */
    public function test_at23_global_stats_counts_unique_records_and_is_immune_to_table_filters(): void
    {
        $component = Livewire::test(Dashboard::class, ['yearMonth' => '2026-10'])
            ->assertStatus(200)
            ->assertSee('Ringkasan Keseluruhan')
            // Open count = 5
            ->assertSeeHtml('data-testid="stat-open"')
            ->assertSee('5')
            // Draft count = 2
            ->assertSeeHtml('data-testid="stat-draft"')
            ->assertSee('2')
            // Current month activity count = 1 (prog-008)
            ->assertSeeHtml('data-testid="stat-month"')
            ->assertSee('1');

        // Apply a strict filter that returns 0 rows in worklist
        $component->set('search', 'NONEXISTENT_KEYWORD_XYZ')
            ->assertSee('Tidak ada hasil yang cocok dengan filter')
            // FR17: Filter on table does NOT mutate global summary numbers
            ->assertSeeHtml('data-testid="stat-open"')
            ->assertSee('5')
            ->assertSeeHtml('data-testid="stat-draft"')
            ->assertSee('2');
    }

    /**
     * AT24: Default dashboard menampilkan progress Open terlama lebih dahulu (FR18).
     */
    public function test_at24_default_sort_displays_open_progresses_oldest_first(): void
    {
        // prog-003 is 2026-02-28 (oldest Open)
        // prog-001 is 2026-03-10
        // prog-005 is 2026-04-02
        // prog-007 is 2026-04-12
        // prog-008 is 2026-10-10 (newest Open)
        Livewire::test(Dashboard::class)
            ->assertStatus(200)
            ->assertSet('status', 'Open')
            ->assertSet('sort', 'oldest')
            ->assertSeeInOrder(['prog-003', 'prog-001', 'prog-005', 'prog-007', 'prog-008'])
            // Closed records must not appear in default Open view
            ->assertDontSee('prog-002')
            ->assertDontSee('prog-004')
            ->assertDontSee('prog-006');
    }

    /**
     * AT24: Urutan terbaru menampilkan progress terbaru lebih dahulu.
     */
    public function test_at24_newest_sort_displays_newest_first(): void
    {
        Livewire::test(Dashboard::class)
            ->set('sort', 'newest')
            ->assertSeeInOrder(['prog-008', 'prog-007', 'prog-005', 'prog-001', 'prog-003']);
    }

    /**
     * AT24 & AT23: Pencarian PIC berdasarkan peran mengembalikan progress unik (tidak berlipat karena problem/action bertingkat).
     */
    public function test_at24_pic_role_filter_guarantees_unique_progress_rows(): void
    {
        // 1. prog-001 has 2 problems and 4 corrective actions.
        // Budi Santoso is in corrective action 1 of problem 1.
        Livewire::test(Dashboard::class)
            ->set('status', 'all')
            ->set('pic', 'Budi Santoso')
            ->set('picRole', 'corrective_action')
            // prog-001 must appear exactly once
            ->assertSee('prog-001')
            ->assertDontSee('prog-005')
            ->assertViewHas('progresses', function ($progresses) {
                return count($progresses) === 1 && $progresses[0]['id'] === 'prog-001';
            });

        // 2. prog-005 has Budi Santoso as PIC Progress.
        Livewire::test(Dashboard::class)
            ->set('status', 'all')
            ->set('pic', 'Budi Santoso')
            ->set('picRole', 'progress')
            ->assertSee('prog-005')
            ->assertDontSee('prog-001')
            ->assertViewHas('progresses', function ($progresses) {
                return count($progresses) === 1 && $progresses[0]['id'] === 'prog-005';
            });

        // 3. Search 'Budi Santoso' with 'all' roles: appears in prog-001 (action) and prog-005 (pic_progress + next_action).
        // Must yield exactly 2 unique rows.
        Livewire::test(Dashboard::class)
            ->set('status', 'all')
            ->set('pic', 'Budi Santoso')
            ->set('picRole', 'all')
            ->assertViewHas('progresses', function ($progresses) {
                $ids = array_column($progresses, 'id');
                return count($progresses) === 2
                    && in_array('prog-001', $ids)
                    && in_array('prog-005', $ids)
                    && count($ids) === count(array_unique($ids));
            });
    }

    /**
     * AT24: Filter Customer, Activity, dan Status bekerja dengan benar.
     */
    public function test_at24_filters_by_customer_activity_and_status(): void
    {
        // Customer filter: PT ASTRA FIKTIF MOTOR
        Livewire::test(Dashboard::class)
            ->set('customer', 'PT ASTRA FIKTIF MOTOR')
            ->assertViewHas('progresses', function ($progresses) {
                foreach ($progresses as $p) {
                    if (($p['project']['customer'] ?? '') !== 'PT ASTRA FIKTIF MOTOR') {
                        return false;
                    }
                }
                return count($progresses) > 0;
            });

        // Activity filter: Send Document
        Livewire::test(Dashboard::class)
            ->set('activity', 'Send Document')
            ->assertViewHas('progresses', function ($progresses) {
                return count($progresses) === 1 && $progresses[0]['activity'] === 'Send Document';
            });

        // Status filter: Closed
        Livewire::test(Dashboard::class)
            ->set('status', 'Closed')
            ->assertViewHas('progresses', function ($progresses) {
                return count($progresses) === 3;
            });
    }

    /**
     * AT24: Tombol Bersihkan Filter (clearFilters) mengembalikan ke kondisi awal.
     */
    public function test_at24_clear_filter_resets_all_fields_to_default(): void
    {
        Livewire::test(Dashboard::class)
            ->set('search', 'Trial')
            ->set('status', 'Closed')
            ->set('activity', 'Trial')
            ->set('customer', 'PT ASTRA FIKTIF MOTOR')
            ->set('pic', 'Budi')
            ->set('picRole', 'corrective_action')
            ->set('sort', 'newest')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('status', 'Open')
            ->assertSet('activity', '')
            ->assertSet('customer', '')
            ->assertSet('pic', '')
            ->assertSet('picRole', 'progress')
            ->assertSet('sort', 'oldest');
    }

    /**
     * AT25: Diferensiasi pesan empty state antara hasil filter kosong vs default Open kosong.
     */
    public function test_at25_empty_state_differentiation(): void
    {
        // Case A: Filtered empty state
        Livewire::test(Dashboard::class)
            ->set('search', 'KATA_KUNCI_TIDAK_DITEMUKAN')
            ->assertSee('Tidak ada hasil yang cocok dengan filter')
            ->assertSee('Bersihkan Filter')
            ->assertDontSee('Tidak ada progress Open');

        // Case B: Default Open empty state
        session()->forget('prototype_ui_filters');
        // Close all open progresses first
        foreach (FakeStore::getProgresses(['status' => 'Open']) as $p) {
            FakeStore::closeProgress($p['id']);
        }

        Livewire::test(Dashboard::class)
            // On default filter (status='Open', search='')
            ->assertSee('Tidak ada progress Open')
            ->assertSee('Tambah Progress Baru')
            ->assertDontSee('Tidak ada hasil yang cocok dengan filter');
    }
}
