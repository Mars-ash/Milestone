<?php

namespace Tests\Feature\Foundation;

use App\Models\Attachment;
use App\Models\LegacyDocumentMetadata;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DevelopmentSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Fixtures\FoundationGraph;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    private function rejected(callable $write, string $state): void
    {
        try {
            DB::transaction($write);
            $this->fail('Database accepted invalid record');
        } catch (QueryException $e) {
            $this->assertSame($state, $e->getCode());
        }
    }

    public function test_unique_identity_raw_text_dates_and_removed_fields(): void
    {
        $p = Project::factory()->create(['jo_no' => '0007', 'part_no' => '0009', 'no_doc' => '00001']);
        $this->rejected(fn () => Project::factory()->create(['jo_no' => '0007']), '23505');
        $this->rejected(fn () => Project::factory()->create(['part_no' => '0009']), '23505');
        $this->rejected(fn () => Project::factory()->create(['jo_no' => ' 0007 ']), '23514');
        $this->assertSame('0007', $p->fresh()->jo_no);
        $this->assertSame('1,5', $p->fresh()->material_thickness_raw);
        $this->assertSame('1.5', $p->fresh()->material_width_raw);
        $this->assertNull($p->date_receive);
        foreach (['project_user', 'status_project'] as $field) {
            $this->assertFalse(Schema::hasColumn('projects', $field));
        }
        $this->assertFalse(Schema::hasColumn('progresses', 'pic_progress'));
        foreach (['corrective_actions', 'next_actions'] as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'target_date'));
        }
    }

    public function test_graph_relations_master_identity_and_foreign_key_integrity(): void
    {
        $p = FoundationGraph::create();
        $this->assertCount(2, $p->problems);
        $this->assertCount(3, $p->nextActions);
        $this->assertCount(2, $p->feedbacks);
        foreach ($p->problems as $i => $problem) {
            $this->assertCount(2, $problem->correctiveActions);
            foreach ($problem->correctiveActions as $j => $a) {
                $this->assertSame("PIC $i-$j Fiktif", $a->pic);
                $this->assertSame($problem->id, $a->problem->id);
            }
        }
        $p->project->update(['model' => 'RS8', 'part_name' => 'Master terkini']);
        $this->assertSame('RS8', $p->fresh()->project->model);
        $this->rejected(fn () => Problem::factory()->create(['progress_id' => 999999]), '23503');
        $this->rejected(fn () => $p->project->delete(), '23001');
    }

    public function test_legacy_nulls_and_database_activity_status_checks(): void
    {
        $p = Progress::factory()->create(['legacy_incomplete' => true, 'activity_date' => null, 'activity' => null]);
        $this->assertNull($p->fresh()->activity_date);
        $this->rejected(fn () => Progress::factory()->create(['activity_date' => null]), '23514');
        $this->rejected(fn () => Progress::factory()->create(['activity' => 'Other']), '23514');
        $this->rejected(fn () => Progress::factory()->create(['status' => 'Done']), '23514');
        $this->rejected(fn () => Progress::factory()->create(['revision' => 0]), '23514');
    }

    public function test_single_admin_and_one_active_edit_draft_constraint(): void
    {
        $admin = User::factory()->create();
        $progress = Progress::factory()->create();
        ProgressDraft::factory()->create(['admin_id' => $admin->id, 'progress_id' => $progress->id, 'base_revision' => 1]);
        $this->rejected(fn () => User::factory()->create(), '23505');
        $this->rejected(fn () => ProgressDraft::factory()->create(['admin_id' => $admin->id, 'progress_id' => $progress->id, 'base_revision' => 1]), '23505');
        ProgressDraft::factory()->count(2)->create(['admin_id' => $admin->id]);
        $this->assertSame(3, ProgressDraft::count());
        $this->rejected(fn () => ProgressDraft::factory()->create(['admin_id' => $admin->id, 'state' => 'applied']), '23514');
    }

    public function test_attachment_has_one_owner_and_legacy_metadata_has_no_download_key(): void
    {
        $admin = User::factory()->create();
        $progress = Progress::factory()->create();
        $draft = ProgressDraft::factory()->create(['admin_id' => $admin->id]);
        $attrs = ['uploaded_by' => $admin->id, 'draft_id' => $draft->id, 'owner_kind' => 'progress', 'state' => 'staging', 'kind' => 'document', 'storage_key' => 'attachments/'.Str::uuid(), 'original_name' => 'test.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 4, 'checksum' => str_repeat('a', 64)];
        Attachment::create($attrs);
        $this->rejected(fn () => Attachment::create([...$attrs, 'storage_key' => 'attachments/'.Str::uuid(), 'progress_id' => $progress->id]), '23514');
        $meta = $progress->legacyDocuments()->create(['source_field' => 'Doc1', 'name' => null, 'description' => 'Keterangan saja']);
        $this->assertNull($meta->name);
        $this->assertFalse(Schema::hasColumn('legacy_document_metadata', 'storage_key'));
        $this->rejected(fn () => LegacyDocumentMetadata::create(['source_field' => 'Doc2']), '23514');
    }

    public function test_nested_database_failure_rolls_back_graph_and_preserves_draft(): void
    {
        $admin = User::factory()->create();
        $draft = ProgressDraft::factory()->create(['admin_id' => $admin->id]);
        $count = Progress::count();
        $this->rejected(function () {
            $p = Progress::factory()->create();
            Problem::factory()->create(['progress_id' => $p->id]);
            Problem::factory()->create(['progress_id' => 999999]);
        }, '23503');
        $this->assertSame($count, Progress::count());
        $this->assertSame(0, Problem::count());
        $this->assertSame('active', $draft->fresh()->state);
    }

    public function test_development_seeder_is_fictitious_and_refuses_overwrite(): void
    {
        $this->seed(DevelopmentSeeder::class);
        $this->assertSame(9, Progress::count());
        $this->assertSame(3, Project::count());
        $this->assertSame(0, User::count());
        $this->expectException(\RuntimeException::class);
        $this->seed(DevelopmentSeeder::class);
    }
}
