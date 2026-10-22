<?php

namespace Tests\Feature\Foundation;

use App\Actions\CloseProgress;
use App\Actions\ReopenProgress;
use App\Actions\SaveProgress;
use App\Exceptions\FeatureNotReady;
use App\Exceptions\VersionConflict;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\User;
use App\Services\ProgressDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Fixtures\FoundationGraph;
use Tests\TestCase;

class ServiceContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_close_reopen_are_manual_revision_checked_and_preserve_graph_and_draft(): void
    {
        $admin = User::factory()->create();
        $p = FoundationGraph::create();
        $before = $p->problems->toArray();
        $draft = ProgressDraft::factory()->create(['admin_id' => $admin->id, 'progress_id' => $p->id, 'base_revision' => 1]);
        $other = Progress::factory()->create();
        $closed = app(CloseProgress::class)->handle($admin, $p->id, 1)->progress;
        $this->assertSame('Closed', $closed->status);
        $this->assertSame(2, $closed->revision);
        $this->assertSame('Open', $other->fresh()->status);
        $this->assertSame('active', $draft->fresh()->state);
        try {
            app(ReopenProgress::class)->handle($admin, $p->id, 1);
            $this->fail('stale revision accepted');
        } catch (VersionConflict $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $opened = app(ReopenProgress::class)->handle($admin, $p->id, 2)->progress;
        $this->assertSame('Open', $opened->status);
        $this->assertSame(3, $opened->revision);
        $this->assertSame($before, $opened->problems()->with('correctiveActions')->get()->toArray());
    }

    public function test_save_progress_applies_draft_and_updates_database(): void
    {
        $admin = User::factory()->create();
        $p = Progress::factory()->create();
        $draft = ProgressDraft::factory()->create([
            'admin_id' => $admin->id,
            'payload' => [
                'project_id' => $p->project_id,
                'activity' => 'Trial',
                'activity_date' => '2026-10-15',
            ],
            'draft_revision' => 1,
        ]);

        $result = app(SaveProgress::class)->handle($admin, $draft->id, 1);
        $this->assertNotNull($result->progress);
        $this->assertSame(2, Progress::count());
        $this->assertSame('applied', $draft->fresh()->state);
        $this->assertSame($result->progress->id, $draft->fresh()->applied_progress_id);
    }

    public function test_applied_draft_retries_return_same_record_and_never_duplicate(): void
    {
        $admin = User::factory()->create();
        $p = Progress::factory()->create();
        $draft = ProgressDraft::factory()->create(['admin_id' => $admin->id, 'state' => 'applied', 'applied_progress_id' => $p->id]);
        foreach ([1, 2] as $_) {
            $result = app(SaveProgress::class)->handle($admin, $draft->id, 1);
            $this->assertSame($p->id, $result->progress->id);
            $this->assertSame('already_applied', $result->outcome);
        }
        $this->assertSame(1, Progress::count());
    }

    public function test_stale_edit_base_conflicts_without_discarding_draft(): void
    {
        $admin = User::factory()->create();
        $p = Progress::factory()->create();
        $draft = ProgressDraft::factory()->create(['admin_id' => $admin->id, 'progress_id' => $p->id, 'base_revision' => 1]);
        app(CloseProgress::class)->handle($admin, $p->id, 1);
        $this->expectException(VersionConflict::class);
        app(SaveProgress::class)->handle($admin, $draft->id, 1);
    }

    public function test_draft_service_saves_active_draft_successfully(): void
    {
        $admin = User::factory()->create();
        $draft = app(ProgressDraftService::class)->saveDraft(
            admin: $admin,
            payload: ['activity' => 'Trial'],
            draftId: (string) Str::uuid(),
        );

        $this->assertSame('active', $draft->state);
        $this->assertSame(1, ProgressDraft::count());
        $this->assertSame('active', ProgressDraft::first()->state);
    }
}
