<?php

namespace Tests\Feature\Progress;

use App\Livewire\Progresses\ProgressIndex;
use App\Models\CorrectiveAction;
use App\Models\Feedback;
use App\Models\NextAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use App\Queries\ProgressFilterQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProgressFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->create();
        $this->project = Project::factory()->create([
            'jo_no' => '0044/NM/2026',
            'part_no' => '0044-XYZ-01',
            'part_name' => 'Cross Member Inner',
            'customer' => 'PT Astra Daihatsu',
        ]);
        $this->actingAs($this->admin);
    }

    /**
     * FR18, AT24: Filter PIC teks bebas lintas role:
     * - all role menemukan PIC di corrective action, next action, feedback, dan customer
     * - per role membatasi pencarian hanya pada role yang dipilih.
     */
    public function test_at24_pic_filter_across_all_roles_and_per_specific_role(): void
    {
        // Progress 1: PIC Bambang di corrective action
        $prog1 = Progress::factory()->create([
            'project_id' => $this->project->id,
            'description' => 'Progress One with CA',
            'status' => 'Open',
        ]);
        $prob1 = Problem::factory()->create(['progress_id' => $prog1->id]);
        CorrectiveAction::factory()->create([
            'problem_id' => $prob1->id,
            'pic' => 'Bambang Toolmaker',
        ]);

        // Progress 2: PIC Bambang di next action
        $prog2 = Progress::factory()->create([
            'project_id' => $this->project->id,
            'description' => 'Progress Two with NA',
            'status' => 'Open',
        ]);
        NextAction::factory()->create([
            'progress_id' => $prog2->id,
            'pic' => 'Bambang Next Specialist',
        ]);

        // Progress 3: PIC Bambang di feedback
        $prog3 = Progress::factory()->create([
            'project_id' => $this->project->id,
            'description' => 'Progress Three with FB',
            'status' => 'Open',
        ]);
        Feedback::factory()->create([
            'progress_id' => $prog3->id,
            'pic' => 'Bambang Customer Rep',
        ]);

        // Progress 4: PIC Bambang di pic_customer
        $prog4 = Progress::factory()->create([
            'project_id' => $this->project->id,
            'description' => 'Progress Four with Customer PIC',
            'pic_customer' => 'Bambang Tanaka',
            'status' => 'Open',
        ]);

        // Progress 5: Tidak ada Bambang sama sekali
        $prog5 = Progress::factory()->create([
            'project_id' => $this->project->id,
            'description' => 'Progress Five without Bambang',
            'pic_customer' => 'Slamet',
            'status' => 'Open',
        ]);

        $filterQuery = app(ProgressFilterQuery::class);

        // 1. Filter PIC 'bambang' all role: menemukan prog1, prog2, prog3, prog4 (4 record)
        $allRoleResults = $filterQuery->build(['pic' => 'Bambang', 'pic_role' => 'all'])->pluck('id')->all();
        $this->assertCount(4, $allRoleResults);
        $this->assertContains($prog1->id, $allRoleResults);
        $this->assertContains($prog2->id, $allRoleResults);
        $this->assertContains($prog3->id, $allRoleResults);
        $this->assertContains($prog4->id, $allRoleResults);
        $this->assertNotContains($prog5->id, $allRoleResults);

        // 2. Filter PIC 'bambang' role 'corrective_action': HANYA prog1
        $caResults = $filterQuery->build(['pic' => 'Bambang', 'pic_role' => 'corrective_action'])->pluck('id')->all();
        $this->assertSame([$prog1->id], $caResults);

        // 3. Filter PIC 'bambang' role 'next_action': HANYA prog2
        $naResults = $filterQuery->build(['pic' => 'Bambang', 'pic_role' => 'next_action'])->pluck('id')->all();
        $this->assertSame([$prog2->id], $naResults);

        // 4. Filter PIC 'bambang' role 'feedback': HANYA prog3
        $fbResults = $filterQuery->build(['pic' => 'Bambang', 'pic_role' => 'feedback'])->pluck('id')->all();
        $this->assertSame([$prog3->id], $fbResults);

        // 5. Filter PIC 'bambang' role 'customer': HANYA prog4
        $custResults = $filterQuery->build(['pic' => 'Bambang', 'pic_role' => 'customer'])->pluck('id')->all();
        $this->assertSame([$prog4->id], $custResults);
    }

    /**
     * FR18, AT25: Pagination dan sorting (newest vs oldest) membagi data dengan benar
     * dan mereset halaman saat filter berubah.
     */
    public function test_at25_pagination_and_sorting_with_page_reset(): void
    {
        // Buat 20 progress dengan project dan tanggal berurutan
        for ($i = 1; $i <= 20; $i++) {
            $p = Project::factory()->create([
                'jo_no' => sprintf('%04d/NM/2026', $i),
                'part_name' => sprintf('Bracket Day %02d', $i),
            ]);

            Progress::factory()->create([
                'project_id' => $p->id,
                'activity_date' => sprintf('2026-10-%02d', $i),
                'status' => 'Open',
            ]);
        }

        // Default pagination: perPage = 15, newest first (Day 20 down to Day 06)
        $component = Livewire::test(ProgressIndex::class);
        $component->assertSee('Bracket Day 20')
            ->assertDontSee('Bracket Day 05');

        // Navigasi ke halaman 2 via gotoPage
        $component->call('gotoPage', 2)
            ->assertSee('Bracket Day 05')
            ->assertDontSee('Bracket Day 20');

        // Ubah filter search -> halaman direset dan hasil pencarian tampil
        $component->set('search', 'Day 01')
            ->assertSee('Bracket Day 01')
            ->assertDontSee('Bracket Day 05');

        // Toggle sort ke oldest -> Day 01 tampil pertama
        $component->set('search', '')
            ->call('toggleSort')
            ->assertSet('sort', 'oldest')
            ->assertSee('Bracket Day 01')
            ->assertDontSee('Bracket Day 20');
    }

    /**
     * Filter kombinasi status, activity, customer, dan tanggal.
     */
    public function test_combined_filters_status_activity_date_range(): void
    {
        $target = Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity' => 'Sent Sample',
            'status' => 'Open',
            'activity_date' => '2026-10-18',
            'description' => 'Target Sent Sample',
        ]);

        // Non-target: beda activity
        Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity' => 'Trial',
            'status' => 'Open',
            'activity_date' => '2026-10-18',
        ]);

        // Non-target: beda status
        Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity' => 'Sent Sample',
            'status' => 'Closed',
            'activity_date' => '2026-10-18',
        ]);

        // Non-target: beda tanggal
        Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity' => 'Sent Sample',
            'status' => 'Open',
            'activity_date' => '2026-10-01',
        ]);

        Livewire::test(ProgressIndex::class)
            ->set('status', 'Open')
            ->set('activity', 'Sent Sample')
            ->set('from', '2026-10-15')
            ->set('to', '2026-10-20')
            ->assertSee('Cross Member Inner')
            ->assertSee('Sent Sample')
            ->assertSee('1 progress · Open');
    }
}
