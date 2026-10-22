<?php

namespace Tests\Feature\Drafts;

use App\Livewire\Drafts\DraftIndex;
use App\Livewire\Progresses\ProgressEditor;
use App\Models\Attachment;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\Project;
use App\Models\User;
use App\Services\ProgressDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DraftRecoveryTest extends TestCase
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
            'jo_no' => '0099/NM/2026',
            'part_no' => '0099-XYZ-01',
            'part_name' => 'Side Member Bracket',
        ]);
        $this->actingAs($this->admin);
        Storage::fake('attachments');
    }

    /**
     * AT14: Membuka form baru tanpa mengisi apapun tidak boleh membuat record draft kosong.
     */
    public function test_at14_opening_empty_form_does_not_create_draft(): void
    {
        $initialCount = ProgressDraft::count();

        $component = Livewire::test(ProgressEditor::class);

        $this->assertNull($component->get('draftId'));
        $this->assertSame('unsaved', $component->get('saveStatus'));
        $this->assertSame($initialCount, ProgressDraft::count());
    }

    /**
     * AT12, AT13: Perubahan data pada form memicu simpan draft secara reaktif
     * tanpa merubah data progress resmi tersimpan.
     */
    public function test_at12_at13_autosave_persists_to_draft_without_modifying_saved_progress(): void
    {
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-15',
            'activity' => 'Trial',
            'description' => 'Original description',
            'status' => 'Open',
            'revision' => 1,
        ]);

        $component = Livewire::test(ProgressEditor::class, ['progressId' => $progress->id])
            ->set('description', 'CATATAN BARU: Masih dianalisis oleh tim internal (Draft Only)');

        $draftId = $component->get('draftId');
        $this->assertNotNull($draftId, 'Autosave harus menghasilkan UUID draft');
        $this->assertSame('saved', $component->get('saveStatus'));
        $this->assertNotNull($component->get('lastSavedAt'));

        $savedDraft = ProgressDraft::find($draftId);
        $this->assertNotNull($savedDraft);
        $this->assertSame('CATATAN BARU: Masih dianalisis oleh tim internal (Draft Only)', $savedDraft->payload['description']);
        $this->assertSame($progress->id, $savedDraft->progress_id);

        // Progress operasional di tabel progresses TIDAK BERUBAH
        $progress->refresh();
        $this->assertSame('Original description', $progress->description);
        $this->assertSame(1, $progress->revision);
    }

    /**
     * AT15, AT16: Melanjutkan draft dari parameter draft memulihkan seluruh data yang disimpan.
     */
    public function test_at15_at16_resuming_draft_restores_all_state(): void
    {
        $payload = [
            'schema_version' => 1,
            'project_id' => $this->project->id,
            'activity' => 'Sent Sample',
            'activity_date' => '2026-10-28',
            'description' => 'Draft pengiriman sampel ke customer',
            'pic_customer' => 'Mr. Tanaka',
            'machine' => 'KOMATSU 200T',
            'die_high_raw' => '135,5',
            'problems' => [
                [
                    'client_id' => '11111111-1111-1111-1111-111111111111',
                    'description' => 'Flange deformation',
                    'corrective_actions' => [
                        [
                            'client_id' => '22222222-2222-2222-2222-222222222222',
                            'description' => 'Adjust clamp pressure',
                            'pic' => 'Ahmad',
                            'actual_date' => '2026-10-28',
                        ],
                    ],
                ],
            ],
            'next_actions' => [
                [
                    'client_id' => '33333333-3333-3333-3333-333333333333',
                    'description' => 'Customer review next week',
                    'pic' => 'Budi',
                    'actual_date' => null,
                ],
            ],
            'feedbacks' => [],
        ];

        $draft = app(ProgressDraftService::class)->saveDraft($this->admin, $payload);

        $component = Livewire::test(ProgressEditor::class, ['draft' => $draft->id]);

        $this->assertSame($draft->id, $component->get('draftId'));
        $this->assertSame($this->project->id, $component->get('projectId'));
        $this->assertSame('Sent Sample', $component->get('activity'));
        $this->assertSame('2026-10-28', $component->get('activityDate'));
        $this->assertSame('Draft pengiriman sampel ke customer', $component->get('description'));
        $this->assertSame('Mr. Tanaka', $component->get('picCustomer'));
        $this->assertSame('KOMATSU 200T', $component->get('machine'));
        $this->assertSame('135,5', $component->get('dieHighRaw'));

        $problems = $component->get('problems');
        $this->assertCount(1, $problems);
        $this->assertSame('11111111-1111-1111-1111-111111111111', $problems[0]['client_id']);
        $this->assertSame('Flange deformation', $problems[0]['description']);
        $this->assertCount(1, $problems[0]['corrective_actions']);
        $this->assertSame('Adjust clamp pressure', $problems[0]['corrective_actions'][0]['description']);

        $nextActions = $component->get('nextActions');
        $this->assertCount(1, $nextActions);
        $this->assertSame('33333333-3333-3333-3333-333333333333', $nextActions[0]['client_id']);
    }

    /**
     * Membuka form edit progress yang memiliki draft aktif langsung memulihkan draft tersebut.
     */
    public function test_opening_edit_progress_automatically_restores_active_draft(): void
    {
        $progress = Progress::factory()->create([
            'project_id' => $this->project->id,
            'activity_date' => '2026-10-15',
            'activity' => 'Trial',
            'description' => 'Original',
            'status' => 'Open',
        ]);

        $draft = app(ProgressDraftService::class)->saveDraft(
            admin: $this->admin,
            payload: [
                'schema_version' => 1,
                'project_id' => $this->project->id,
                'activity_date' => '2026-10-15',
                'activity' => 'Trial',
                'description' => 'Draft description restored automatically',
            ],
            progressId: $progress->id,
            baseRevision: 1
        );

        $component = Livewire::test(ProgressEditor::class, ['progressId' => $progress->id]);

        $this->assertSame($draft->id, $component->get('draftId'));
        $this->assertSame('Draft description restored automatically', $component->get('description'));
        $this->assertSame('saved', $component->get('saveStatus'));
    }

    /**
     * Membuang draft membersihkan draft dari database dan mengembalikan form ke data tersimpan atau kosong.
     */
    public function test_discard_draft_in_editor(): void
    {
        $component = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('description', 'Draft to be discarded');

        $draftId = $component->get('draftId');
        $this->assertNotNull(ProgressDraft::find($draftId));

        $component->call('discardDraft');

        $this->assertNull($component->get('draftId'));
        $this->assertNull(ProgressDraft::find($draftId));
        $this->assertSame('', $component->get('description'));
        $this->assertSame('unsaved', $component->get('saveStatus'));
    }

    /**
     * AT37: Status error autosave dan pemulihan retry.
     */
    public function test_at37_save_error_simulation_and_retry(): void
    {
        $component = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('description', 'Uji coba retry autosave');

        $this->assertSame('saved', $component->get('saveStatus'));

        // Aktifkan simulasi error dan ubah data
        $component->set('simulateSaveError', true)
            ->set('description', 'Uji coba retry autosave - gagal')
            ->assertSet('saveStatus', 'error')
            ->assertHasErrors(['save']);

        // Kembalikan ke normal lalu retry
        $component->set('simulateSaveError', false)
            ->call('saveDraft')
            ->assertSet('saveStatus', 'saved')
            ->assertHasNoErrors();

        $draftId = $component->get('draftId');
        $draft = ProgressDraft::find($draftId);
        $this->assertSame('Uji coba retry autosave - gagal', $draft->payload['description']);
    }

    /**
     * DraftIndex menampilkan daftar draft aktif dan mendukung buang draft.
     */
    public function test_draft_index_lists_drafts_and_supports_discard(): void
    {
        $draft = app(ProgressDraftService::class)->saveDraft($this->admin, [
            'schema_version' => 1,
            'project_id' => $this->project->id,
            'activity' => 'Trial',
            'description' => 'Draft in list',
        ]);

        $component = Livewire::test(DraftIndex::class)
            ->assertSee('Side Member Bracket')
            ->assertSee('Draft baru')
            ->call('promptDiscard', $draft->id)
            ->assertSet('confirmingDiscardUuid', $draft->id)
            ->call('confirmDiscard');

        $this->assertNull(ProgressDraft::find($draft->id));
        $this->assertNull($component->get('confirmingDiscardUuid'));
    }
}
