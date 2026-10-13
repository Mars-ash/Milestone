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
}
