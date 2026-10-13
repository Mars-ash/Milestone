<?php

namespace Tests\Unit;

use App\Prototype\FakeStore;
use Tests\Fixtures\PrototypeDataset;
use Tests\TestCase;

class FakeStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        FakeStore::reset();
    }

    protected function tearDown(): void
    {
        FakeStore::reset();
        parent::tearDown();
    }

    public function test_projects_fixture_has_three_unique_projects_with_leading_zeros(): void
    {
        $projects = FakeStore::getProjects();

        $this->assertCount(3, $projects);

        $joList = array_column($projects, 'jo_no');
        $partList = array_column($projects, 'part_no');

        // D01: 1 JO = 1 Part No, unique
        $this->assertCount(3, array_unique($joList));
        $this->assertCount(3, array_unique($partList));

        // Leading zeros exist
        $hasLeadingZeroJo = false;
        $hasLeadingZeroPart = false;
        foreach ($projects as $project) {
            if (str_starts_with($project['jo_no'], '0')) {
                $hasLeadingZeroJo = true;
            }
            if (str_starts_with($project['part_no'], '0')) {
                $hasLeadingZeroPart = true;
            }
        }

        $this->assertTrue($hasLeadingZeroJo, 'At least one project JO must have leading zero');
        $this->assertTrue($hasLeadingZeroPart, 'At least one project Part No must have leading zero');

        // D15: Raw text for DH, size, qty
        $first = $projects[0];
        $this->assertStringContainsString(',', $first['dh']);
        $this->assertStringContainsString('.', $first['qty']);
    }

    public function test_progresses_fixture_covers_all_five_activities_and_both_statuses(): void
    {
        $progresses = FakeStore::getProgresses();

        $this->assertCount(8, $progresses);

        $activities = array_unique(array_column($progresses, 'activity'));
        $expectedActivities = ['Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'];

        foreach ($expectedActivities as $expected) {
            $this->assertContains($expected, $activities, "Activity {$expected} must be present in fixture");
        }

        $statuses = array_unique(array_column($progresses, 'status'));
        $this->assertContains('Open', $statuses);
        $this->assertContains('Closed', $statuses);

        // Check old historical date exists
        $hasOldDate = false;
        foreach ($progresses as $p) {
            if (str_starts_with($p['activity_date'], '2025-')) {
                $hasOldDate = true;
            }
        }
        $this->assertTrue($hasOldDate, 'At least one progress must have an old date (2025)');
    }

    public function test_complex_progress_nested_problems_and_corrective_actions_do_not_mix(): void
    {
        $complex = FakeStore::findProgress('prog-001');

        $this->assertNotNull($complex);
        $this->assertSame('Trial', $complex['activity']);
        $this->assertSame('Open', $complex['status']);

        // Must have 2 problems
        $problems = $complex['problems'];
        $this->assertCount(2, $problems);

        $prob1 = $problems[0];
        $prob2 = $problems[1];

        $this->assertNotEquals($prob1['id'], $prob2['id']);

        // Each problem must have 2 corrective actions
        $this->assertCount(2, $prob1['corrective_actions']);
        $this->assertCount(2, $prob2['corrective_actions']);

        $prob1ActionIds = array_column($prob1['corrective_actions'], 'id');
        $prob2ActionIds = array_column($prob2['corrective_actions'], 'id');

        // Verification requirement: actions of problem 1 are distinct from problem 2
        $intersection = array_intersect($prob1ActionIds, $prob2ActionIds);
        $this->assertEmpty($intersection, 'Corrective action IDs between problems must not mix or overlap');

        // Next Actions: exactly 3
        $this->assertCount(3, $complex['next_actions']);

        // Feedbacks: exactly 2
        $this->assertCount(2, $complex['feedbacks']);

        // Raw numbers 1,5 and 1.5 present in notes or descriptions
        $combinedText = $complex['notes'] . ' ' . $prob1['description'] . ' ' . $prob2['description'];
        $this->assertStringContainsString('1,5', $combinedText);
        $this->assertStringContainsString('1.5', $combinedText);

        // Dummy attachments clearly marked as simulation
        $this->assertNotEmpty($complex['attachments']);
        foreach ($complex['attachments'] as $att) {
            $this->assertTrue($att['is_dummy']);
            $this->assertStringContainsString('[SIMULASI]', $att['caption']);
        }
    }

    public function test_close_and_reopen_progress_preserves_content(): void
    {
        $original = FakeStore::findProgress('prog-001');
        $this->assertSame('Open', $original['status']);

        // Close manual
        $closed = FakeStore::closeProgress('prog-001');
        $this->assertSame('Closed', $closed['status']);

        // Reopen manual
        $reopened = FakeStore::reopenProgress('prog-001');
        $this->assertSame('Open', $reopened['status']);

        // Preserves all contents, problems, actions, notes
        $this->assertSame($original['notes'], $reopened['notes']);
        $this->assertSame($original['problems'], $reopened['problems']);
        $this->assertSame($original['next_actions'], $reopened['next_actions']);
        $this->assertSame($original['feedbacks'], $reopened['feedbacks']);
    }

    public function test_stats_counts_open_and_closed_excluding_drafts(): void
    {
        $stats = FakeStore::getStats();

        $this->assertSame(3, $stats['total_projects']);
        $this->assertSame(8, $stats['total_progresses']);
        $this->assertGreaterThan(0, $stats['open_count']);
        $this->assertGreaterThan(0, $stats['closed_count']);
        $this->assertSame(8, $stats['open_count'] + $stats['closed_count']);
        $this->assertGreaterThan(0, $stats['draft_count']);
    }

    public function test_draft_lifecycle_and_isolation(): void
    {
        $drafts = FakeStore::getDrafts();
        $this->assertCount(2, $drafts);

        // Save a new draft
        $newDraft = FakeStore::saveDraft([
            'type' => 'new',
            'project_id' => 'proj-001',
            'activity' => 'Trial',
            'notes' => 'Draft test notes',
        ]);

        $this->assertNotEmpty($newDraft['uuid']);
        $found = FakeStore::findDraft($newDraft['uuid']);
        $this->assertNotNull($found);
        $this->assertSame('Draft test notes', $found['notes']);

        // Delete draft
        $deleted = FakeStore::deleteDraft($newDraft['uuid']);
        $this->assertTrue($deleted);
        $this->assertNull(FakeStore::findDraft($newDraft['uuid']));
    }

    public function test_reset_store_restores_initial_fixture_dataset(): void
    {
        // Mutate store
        FakeStore::saveProgress([
            'project_id' => 'proj-001',
            'activity' => 'Trial',
            'notes' => 'Temporary mutated progress',
        ]);

        $this->assertCount(9, FakeStore::getProgresses());

        // Reset
        FakeStore::reset();

        // Must restore back to 8 progresses
        $this->assertCount(8, FakeStore::getProgresses());
        $this->assertCount(3, FakeStore::getProjects());
        $this->assertCount(2, FakeStore::getDrafts());
    }

    public function test_get_customers_returns_sorted_unique_customers(): void
    {
        $customers = FakeStore::getCustomers();

        $this->assertCount(3, $customers);
        $this->assertSame([
            'PT ASTRA FIKTIF MOTOR',
            'PT FIKTIF PRESISI INDONESIA',
            'PT GLOBAL AUTO FIKTIF',
        ], $customers);
    }

    public function test_get_global_stats_calculates_open_draft_and_month_activity_uniquely(): void
    {
        $statsOct = FakeStore::getGlobalStats('2026-10');

        $this->assertSame(5, $statsOct['open_count'], 'Open count should be exactly 5');
        $this->assertSame(2, $statsOct['draft_count'], 'Draft count should be exactly 2');
        $this->assertSame(1, $statsOct['current_month_count'], 'October 2026 activity should be 1 (prog-008)');
        $this->assertNotEmpty($statsOct['current_month_label']);

        // Check March 2026 (prog-001, prog-002, prog-004)
        $statsMar = FakeStore::getGlobalStats('2026-03');
        $this->assertSame(3, $statsMar['current_month_count'], 'March 2026 activities should be 3');
    }

    public function test_get_progresses_default_sorting_oldest_first(): void
    {
        $all = FakeStore::getProgresses(['status' => 'all']);

        $this->assertCount(8, $all);
        $this->assertSame('prog-006', $all[0]['id'], 'Oldest progress (2025-08-10) should be first');
        $this->assertSame('prog-008', $all[7]['id'], 'Newest progress (2026-10-10) should be last in oldest-first sort');

        // Test newest sort
        $newest = FakeStore::getProgresses(['status' => 'all', 'sort' => 'newest']);
        $this->assertSame('prog-008', $newest[0]['id'], 'Newest progress (2026-10-10) should be first in newest sort');
    }

    public function test_pic_multi_role_search_returns_unique_progress_records(): void
    {
        // 1. Search PIC in 'progress' role
        $resultsProg = FakeStore::getProgresses(['status' => 'all', 'pic' => 'Ahmad Fauzi', 'pic_role' => 'progress']);
        $this->assertCount(1, $resultsProg);
        $this->assertSame('prog-001', $resultsProg[0]['id']);

        // 2. Search PIC in 'corrective_action' role on complex progress with 2 problems x 2 actions
        $resultsAct = FakeStore::getProgresses(['status' => 'all', 'pic' => 'Budi Santoso', 'pic_role' => 'corrective_action']);
        $this->assertCount(1, $resultsAct, 'Corrective action search should not multiply progress rows');
        $this->assertSame('prog-001', $resultsAct[0]['id']);

        // 3. Search PIC in 'next_action' role
        $resultsNext = FakeStore::getProgresses(['status' => 'all', 'pic' => 'Dewi Lestari', 'pic_role' => 'next_action']);
        // Dewi Lestari is in next_action for prog-001, prog-002, and prog-008
        $this->assertCount(3, $resultsNext);
        $ids = array_column($resultsNext, 'id');
        $this->assertSame(count($ids), count(array_unique($ids)), 'Results must contain unique progress records');

        // 4. Search PIC with 'all' roles
        $resultsAll = FakeStore::getProgresses(['status' => 'all', 'pic' => 'Budi Santoso', 'pic_role' => 'all']);
        // Budi Santoso is in corrective_action in prog-001, pic_progress and next_action in prog-005
        $this->assertCount(2, $resultsAll);
        $allIds = array_column($resultsAll, 'id');
        $this->assertContains('prog-001', $allIds);
        $this->assertContains('prog-005', $allIds);
        $this->assertSame(2, count(array_unique($allIds)), 'Even with multiple roles in prog-005, prog-005 must appear exactly once');
    }
}
