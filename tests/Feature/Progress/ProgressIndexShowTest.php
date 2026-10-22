<?php

namespace Tests\Feature\Progress;

use App\Livewire\Progresses\ProgressIndex;
use App\Livewire\Progresses\ProgressShow;
use App\Models\NextAction;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProgressIndexShowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_progress_index_renders_and_displays_latest_master_identity(): void
    {
        $project = Project::factory()->create([
            'jo_no' => '0010/NM/2026',
            'part_no' => 'PART-A01',
            'part_name' => 'Original Part Name',
            'customer' => 'Customer Alpha',
            'model' => 'Model Alpha',
        ]);

        $prog1 = Progress::factory()->create([
            'project_id' => $project->id,
            'activity_date' => '2026-10-15',
            'activity' => 'Trial',
            'pic_customer' => 'PIC Budi',
            'status' => 'Open',
        ]);

        $prog2 = Progress::factory()->create([
            'project_id' => $project->id,
            'activity_date' => '2026-10-16',
            'activity' => 'Sent Sample',
            'pic_customer' => 'PIC Siti',
            'status' => 'Open',
        ]);

        $prog3 = Progress::factory()->create([
            'project_id' => $project->id,
            'activity_date' => '2026-10-17',
            'activity' => 'Audit',
            'pic_customer' => 'PIC Joko',
            'status' => 'Closed',
        ]);

        // Initially lists original master part name
        Livewire::test(ProgressIndex::class)
            ->assertSee('Original Part Name')
            ->assertSee('0010/NM/2026')
            ->assertSee('PART-A01')
            ->assertSee('Trial')
            ->assertSee('Sent Sample')
            ->assertSee('Audit')
            ->assertSee('PIC Budi')
            ->assertSee('PIC Siti')
            ->assertSee('PIC Joko');

        // Now update master project part_name
        $project->update(['part_name' => 'Updated Super Part']);

        // Assert all progress listings immediately reflect the updated master identity
        Livewire::test(ProgressIndex::class)
            ->assertSee('Updated Super Part')
            ->assertDontSee('Original Part Name');
    }

    public function test_progress_index_search_by_jo_part_and_description(): void
    {
        $proj1 = Project::factory()->create([
            'jo_no' => '0005/NM/2026',
            'part_no' => 'PN-ALPHA',
            'part_name' => 'Bracket Alpha',
        ]);

        $proj2 = Project::factory()->create([
            'jo_no' => '1005/NM/2026',
            'part_no' => 'PN-BETA',
            'part_name' => 'Plate Beta',
        ]);

        Progress::factory()->create([
            'project_id' => $proj1->id,
            'activity_date' => '2026-10-10',
            'activity' => 'Trial',
            'description' => 'Special stamping description',
        ]);

        Progress::factory()->create([
            'project_id' => $proj2->id,
            'activity_date' => '2026-10-11',
            'activity' => 'Audit',
            'description' => 'Regular inspection',
        ]);

        Livewire::test(ProgressIndex::class)
            ->set('search', '0005')
            ->assertSee('0005/NM/2026')
            ->assertDontSee('1005/NM/2026')
            ->set('search', 'stamping')
            ->assertSee('Bracket Alpha')
            ->assertDontSee('Plate Beta');
    }

    public function test_progress_index_filter_by_status(): void
    {
        $projOpen = Project::factory()->create(['part_name' => 'Open Part Target']);
        $projClosed = Project::factory()->create(['part_name' => 'Closed Part Target']);

        Progress::factory()->create([
            'project_id' => $projOpen->id,
            'activity' => 'Trial',
            'status' => 'Open',
        ]);

        Progress::factory()->create([
            'project_id' => $projClosed->id,
            'activity' => 'Audit',
            'status' => 'Closed',
        ]);

        Livewire::test(ProgressIndex::class)
            ->set('status', 'Open')
            ->assertSee('Open Part Target')
            ->assertDontSee('Closed Part Target')
            ->set('status', 'Closed')
            ->assertSee('Closed Part Target')
            ->assertDontSee('Open Part Target')
            ->set('status', 'all')
            ->assertSee('Open Part Target')
            ->assertSee('Closed Part Target');
    }

    public function test_progress_index_filter_by_activity_and_customer(): void
    {
        $projToyota = Project::factory()->create([
            'customer' => 'Toyota Motor',
            'part_name' => 'Toyota Part',
        ]);

        $projHonda = Project::factory()->create([
            'customer' => 'Honda Motor',
            'part_name' => 'Honda Part',
        ]);

        Progress::factory()->create([
            'project_id' => $projToyota->id,
            'activity' => 'Trial',
        ]);

        Progress::factory()->create([
            'project_id' => $projHonda->id,
            'activity' => 'Sent Sample',
        ]);

        Livewire::test(ProgressIndex::class)
            ->set('customer', 'Toyota Motor')
            ->assertSee('Toyota Part')
            ->assertDontSee('Honda Part')
            ->set('customer', '')
            ->set('activity', 'Sent Sample')
            ->assertSee('Honda Part')
            ->assertDontSee('Toyota Part');
    }

    public function test_progress_index_empty_state_distinguishes_no_records_from_no_results(): void
    {
        // 1. Database completely empty
        Livewire::test(ProgressIndex::class)
            ->assertSee('Belum ada progress kegiatan')
            ->assertDontSee('Tidak ada progress yang cocok');

        // 2. Records exist in DB, but search yields 0 matches
        $proj = Project::factory()->create();
        Progress::factory()->create([
            'project_id' => $proj->id,
            'activity' => 'Trial',
        ]);

        Livewire::test(ProgressIndex::class)
            ->set('search', 'NONEXISTENT_KEYWORD_XYZ')
            ->assertSee('Tidak ada progress yang cocok')
            ->assertDontSee('Belum ada progress kegiatan');
    }

    public function test_progress_show_displays_identity_and_sections_and_latest_master_data(): void
    {
        $project = Project::factory()->create([
            'jo_no' => '0099/NM/2026',
            'part_no' => 'PN-99-REV',
            'part_name' => 'Door Panel Assembly',
            'customer' => 'Daihatsu',
            'model' => 'D26A',
            'no_doc' => 'DOC-99-D26',
        ]);

        $progress = Progress::factory()->create([
            'project_id' => $project->id,
            'activity_date' => '2026-10-18',
            'activity' => 'Trial',
            'status' => 'Open',
            'description' => 'Initial stamping run trial notes',
            'pic_customer' => 'Mr. Tanaka',
            'process_trial' => 'Forming T1',
            'machine' => 'Press 500T',
            'die_high_raw' => '450,5',
            'material_actual_trial' => 'SPFH590',
            'qty_ok_raw' => '100.0',
            'qty_ng_raw' => '2',
            'qty_process_raw' => '98',
            'qty_sample_raw' => '5',
            'event_note' => 'Event 1 PP1 Trial',
            'remark' => 'Clean finish',
        ]);

        NextAction::create([
            'progress_id' => $progress->id,
            'client_id' => '11111111-1111-1111-1111-111111111111',
            'position' => 0,
            'description' => 'Prepare inspection gauge',
            'pic' => 'QC Team',
            'actual_date' => '2026-10-20',
        ]);

        $test = Livewire::test(ProgressShow::class, ['progressId' => $progress->id])
            ->assertSee('0099/NM/2026')
            ->assertSee('PN-99-REV')
            ->assertSee('Door Panel Assembly')
            ->assertSee('Daihatsu')
            ->assertSee('D26A')
            ->assertSee('DOC-99-D26')
            ->assertSee('Mr. Tanaka')
            ->assertSee('Initial stamping run trial notes')
            ->assertSee('Forming T1')
            ->assertSee('Press 500T')
            ->assertSee('450,5')
            ->assertSee('SPFH590')
            ->assertSee('100.0')
            ->assertSee('98')
            ->assertSee('5')
            ->assertSee('Event 1 PP1 Trial')
            ->assertSee('Clean finish')
            ->assertSee('Prepare inspection gauge')
            ->assertSee('QC Team')
            // Empty sections show appropriate clean messages
            ->assertSee('Belum ada problem.')
            ->assertSee('Belum ada feedback.')
            ->assertSee('Belum ada dokumen.');

        // Update master part_name and verify show view reflects latest master data
        $project->update(['part_name' => 'Updated Master Door Panel']);

        Livewire::test(ProgressShow::class, ['progressId' => $progress->id])
            ->assertSee('Updated Master Door Panel')
            ->assertDontSee('Door Panel Assembly');
    }

    public function test_progress_show_open_displays_edit_button_and_closed_displays_reopen_notice(): void
    {
        $project = Project::factory()->create();

        $openProgress = Progress::factory()->create([
            'project_id' => $project->id,
            'status' => 'Open',
        ]);

        Livewire::test(ProgressShow::class, ['progressId' => $openProgress->id])
            ->assertSee('Edit progress')
            ->assertSee('Close progress')
            ->assertDontSee('Reopen progress');

        $closedProgress = Progress::factory()->create([
            'project_id' => $project->id,
            'status' => 'Closed',
        ]);

        Livewire::test(ProgressShow::class, ['progressId' => $closedProgress->id])
            ->assertDontSee('Edit progress')
            ->assertSee('Reopen progress')
            ->assertSee('Closed');
    }

    public function test_progress_show_not_found_renders_empty_state(): void
    {
        Livewire::test(ProgressShow::class, ['progressId' => 999999])
            ->assertSee('Progress tidak ditemukan');
    }

    public function test_trial_progress_displays_print_report_button_and_opens_dialog(): void
    {
        $project = Project::factory()->create();

        $trialProgress = Progress::factory()->create([
            'project_id' => $project->id,
            'activity' => 'Trial',
            'tonage_raw' => '250T',
            'qty_ok_raw' => '90 pcs',
            'qty_ng_raw' => '10 pcs',
        ]);

        $nonTrialProgress = Progress::factory()->create([
            'project_id' => $project->id,
            'activity' => 'Sent Sample',
        ]);

        // Non-trial should not have print button
        Livewire::test(ProgressShow::class, ['progressId' => $nonTrialProgress->id])
            ->assertDontSee('Cetak Trial Report');

        // Trial should have print button, opens dialog and confirms
        $component = Livewire::test(ProgressShow::class, ['progressId' => $trialProgress->id])
            ->assertSee('Cetak Trial Report')
            ->assertSet('showPrintDialog', false)
            ->call('openPrintDialog')
            ->assertSet('showPrintDialog', true)
            ->assertSet('printTonage', '250T')
            ->assertSet('printQtyOk', '90 pcs')
            ->assertSet('printQtyNg', '10 pcs')
            ->assertSee('Cetak Trial Report')
            ->assertSeeHtml('id="print-trial-dialog"')
            ->set('printTonage', '300T')
            ->set('printQtyOk', '100 pcs')
            ->set('printQtyNg', '2 pcs')
            ->call('confirmPrint')
            ->assertSet('showPrintDialog', false)
            ->assertDispatched('open-trial-report');

        $trialProgress->refresh();
        $this->assertEquals('300T', $trialProgress->tonage_raw);
        $this->assertEquals('100 pcs', $trialProgress->qty_ok_raw);
        $this->assertEquals('2 pcs', $trialProgress->qty_ng_raw);
    }
}
