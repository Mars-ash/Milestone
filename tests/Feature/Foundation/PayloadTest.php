<?php

namespace Tests\Feature\Foundation;

use App\Data\ProgressPayload;
use App\Data\ProjectPayload;
use App\Models\Attachment;
use App\Models\ProgressDraft;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\Fixtures\FoundationGraph;
use Tests\TestCase;

class PayloadTest extends TestCase
{
    use RefreshDatabase;

    private function row(array $data = []): array
    {
        return ['client_id' => (string) Str::uuid(), ...$data];
    }

    private function invalid(callable $fn, string $field): void
    {
        try {
            $fn();
            $this->fail('Invalid payload accepted');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors());
        }
    }

    private function base(): array
    {
        return ['project_id' => Project::factory()->create()->id, 'activity_date' => '2026-10-15', 'activity' => 'Trial'];
    }

    public function test_partial_draft_and_only_three_final_required_fields(): void
    {
        $this->assertSame(['schema_version' => 1], ProgressPayload::fromArray([])->data);
        $this->invalid(fn () => ProgressPayload::fromArray([], true), 'project_id');
        $dto = ProgressPayload::fromArray($this->base(), true);
        $this->assertArrayNotHasKey('problems', $dto->data);
        $this->assertArrayNotHasKey('part_no', $dto->data);
    }

    public function test_nested_payload_preserves_raw_text_pic_only_rows_and_null_dates(): void
    {
        $input = [...$this->base(), 'die_high_raw' => '1,5', 'qty_material_raw' => '1.5',
            'problems' => [$this->row(['corrective_actions' => [$this->row(['pic' => 'PIC bebas', 'actual_date' => ''])]])],
            'next_actions' => [$this->row(['pic' => 'Nama bebas', 'actual_date' => null])],
            'feedbacks' => [$this->row(['content' => 'Satu', 'feedback_date' => '']), $this->row(['content' => 'Dua'])]];
        $data = ProgressPayload::fromArray($input, true)->data;
        $this->assertSame('1,5', $data['die_high_raw']);
        $this->assertSame('1.5', $data['qty_material_raw']);
        $this->assertNull($data['problems'][0]['corrective_actions'][0]['actual_date']);
        $this->assertSame('PIC bebas', $data['problems'][0]['corrective_actions'][0]['pic']);
        $this->assertCount(2, $data['feedbacks']);
    }

    public function test_invalid_activity_unknown_fields_and_invalid_date_are_rejected(): void
    {
        $base = $this->base();
        $this->invalid(fn () => ProgressPayload::fromArray([...$base, 'activity' => 'Other'], true), 'activity');
        $this->invalid(fn () => ProgressPayload::fromArray([...$base, 'activity_date' => '2026-02-30'], true), 'activity_date');
        foreach (['part_no', 'pic_progress', 'status', 'project_user'] as $field) {
            $this->invalid(fn () => ProgressPayload::fromArray([...$base, $field => 'injected']), 'payload');
        }
        $this->invalid(fn () => ProgressPayload::fromArray(['schema_version' => 2]), 'schema_version');
        $this->invalid(fn () => ProgressPayload::fromArray(['next_actions' => [$this->row(['target_date' => '2026-10-15'])]]), 'next_actions.0');
    }

    public function test_cross_parent_ids_changed_client_ids_and_duplicates_are_rejected(): void
    {
        $p = FoundationGraph::create();
        $other = FoundationGraph::create();
        $first = $p->problems[0];
        $second = $p->problems[1];
        $row = ['id' => $first->id, 'client_id' => $first->client_id];
        $this->invalid(fn () => ProgressPayload::fromArray(['problems' => [['id' => $other->problems[0]->id, 'client_id' => $other->problems[0]->client_id]]], false, $p), 'problems.0.id');
        $foreignAction = $second->correctiveActions[0];
        $this->invalid(fn () => ProgressPayload::fromArray(['problems' => [[...$row, 'corrective_actions' => [['id' => $foreignAction->id, 'client_id' => $foreignAction->client_id]]]]], false, $p), 'problems.0.corrective_actions.0.id');
        $this->invalid(fn () => ProgressPayload::fromArray(['problems' => [[...$row, 'client_id' => (string) Str::uuid()]]], false, $p), 'problems.0.id');
        $duplicate = $this->row();
        $this->invalid(fn () => ProgressPayload::fromArray(['next_actions' => [$duplicate, $duplicate]]), 'next_actions.1.client_id');
        $this->assertCount(2, $p->fresh()->problems);
    }

    public function test_attachment_references_cannot_cross_drafts_or_owners(): void
    {
        $admin = User::factory()->create();
        $draft = ProgressDraft::factory()->create(['admin_id' => $admin->id]);
        $other = ProgressDraft::factory()->create(['admin_id' => $admin->id]);
        $client = (string) Str::uuid();
        $attachment = Attachment::create(['uploaded_by' => $admin->id, 'draft_id' => $draft->id, 'owner_kind' => 'problem', 'owner_client_id' => $client, 'state' => 'staging', 'kind' => 'photo', 'storage_key' => 'attachments/'.Str::uuid(), 'original_name' => 'a.png', 'mime_type' => 'image/png', 'size_bytes' => 10, 'checksum' => str_repeat('b', 64)]);
        $input = ['problems' => [['client_id' => $client, 'photo_refs' => [$attachment->id]]]];
        $this->assertSame($attachment->id, ProgressPayload::fromArray($input, false, null, $draft, $admin)->data['problems'][0]['photo_refs'][0]);
        $this->invalid(fn () => ProgressPayload::fromArray($input, false, null, $other, $admin), 'problems.0.photo_refs.0');
        $this->invalid(fn () => ProgressPayload::fromArray(['attachment_refs' => [$attachment->id]], false, null, $draft, $admin), 'attachment_refs.0');
    }

    public function test_project_required_identity_trim_and_clear_dates_without_normalizing_raw(): void
    {
        $p = Project::factory()->create(['t0_target' => '2026-10-15']);
        $data = ProjectPayload::fromArray(['jo_no' => ' 0007 ', 'part_no' => '0008', 'part_name' => 'Part fiktif', 't0_target' => '', 'material_thickness_raw' => '1,5'], $p)->data;
        $this->assertSame('0007', $data['jo_no']);
        $this->assertSame('1,5', $data['material_thickness_raw']);
        $this->assertNull($data['t0_target']);
        $p->update($data);
        $this->assertNull($p->fresh()->t0_target);
        $this->invalid(fn () => ProjectPayload::fromArray(['jo_no' => '0007', 'part_no' => 'unique', 'part_name' => 'Part']), 'jo_no');
        $this->invalid(fn () => ProjectPayload::fromArray([]), 'part_name');
    }
}
