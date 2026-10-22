<?php

namespace Tests\Feature\Progress;

use App\Actions\ApplyProgressDraft;
use App\Actions\CloseProgress;
use App\Actions\ReopenProgress;
use App\Livewire\Drafts\DraftIndex;
use App\Livewire\Progresses\ProgressEditor;
use App\Livewire\Progresses\ProgressIndex;
use App\Livewire\Progresses\ProgressShow;
use App\Models\Attachment;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\Project;
use App\Models\User;
use App\Services\ProgressDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ProgressWorkflowTest extends TestCase
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
            'jo_no' => '0089/NM/2026',
            'part_no' => '0089-XYZ-01',
            'part_name' => 'Roof Header Panel',
            'customer' => 'PT Toyota Motor',
        ]);
        $this->actingAs($this->admin);
        Storage::fake('attachments');
    }

    /**
     * Skenario Lengkap P05-T09:
     * 1. Pengisian form baru dengan entitas anak (Problem, CA, NA, FB).
     * 2. Autosave menghasilkan draft aktif tanpa menyentuh tabel progresses.
     * 3. Pemulihan lintas sesi (reload) mengembalikan data secara utuh.
     * 4. Simpan final menerapkan graph ke progresses dan entitas anak serta menandai draft applied.
     * 5. Siklus Close -> Reopen manual menjaga seluruh isian data utuh dan menaikkan revision.
     * 6. Filter daftar kerja menemukan progress berdasarkan PIC dan status.
     */
    public function test_complete_progress_operational_workflow(): void
    {
        // 1 & 2. Buka editor dan isi form
        $editor = Livewire::test(ProgressEditor::class)
            ->set('projectId', $this->project->id)
            ->set('activityDate', '2026-10-19')
            ->set('activity', 'Trial')
            ->set('picCustomer', 'Mr. Sato')
            ->set('description', 'Comprehensive operational workflow test')
            ->set('processTrial', 'OP10 Stamping')
            ->set('machine', 'KOMATSU 500T')
            ->set('dieHighRaw', '320,5')
            ->set('qtyMaterialRaw', '100')
            ->set('qtyProcessRaw', '98')
            ->set('qtySampleRaw', '5');

        // Pastikan draft tersimpan
        $draftId = $editor->get('draftId');
        $this->assertNotNull($draftId);
        $this->assertSame('saved', $editor->get('saveStatus'));
        $this->assertSame(0, Progress::count(), 'Belum ada progress sebelum Simpan final');

        // 3. Simulasi reload / buka kembali via draftId
        $reloadedEditor = Livewire::test(ProgressEditor::class, ['draft' => $draftId])
            ->assertSet('projectId', $this->project->id)
            ->assertSet('activityDate', '2026-10-19')
            ->assertSet('description', 'Comprehensive operational workflow test')
            ->assertSet('machine', 'KOMATSU 500T')
            ->assertSet('dieHighRaw', '320,5')
            ->assertSet('saveStatus', 'saved');

        // Tambah Problem, Corrective Action, Next Action, Feedback
        $reloadedEditor->call('addProblem');
        $problems = $reloadedEditor->get('problems');
        $problemClient = $problems[0]['client_id'];
        $problems[0]['description'] = 'Scratches on surface';
        $problems[0]['corrective_actions'][0]['description'] = 'Polish die cavity';
        $problems[0]['corrective_actions'][0]['pic'] = 'Hendra';
        $problems[0]['corrective_actions'][0]['actual_date'] = '2026-10-19';
        $reloadedEditor->set('problems', $problems);

        $nextActions = $reloadedEditor->get('nextActions');
        $nextActions[0]['description'] = 'Check after 500 strokes';
        $nextActions[0]['pic'] = 'Rudi';
        $reloadedEditor->set('nextActions', $nextActions);

        $feedbacks = $reloadedEditor->get('feedbacks');
        $feedbacks[0]['content'] = 'Customer accepted initial trial';
        $feedbacks[0]['pic'] = 'Tanaka-san';
        $feedbacks[0]['feedback_date'] = '2026-10-19';
        $reloadedEditor->set('feedbacks', $feedbacks);

        // 4. Simpan Final
        $reloadedEditor->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertSame(1, Progress::count(), 'Progress resmi harus terbentuk');
        $saved = Progress::first();
        $this->assertSame('Open', $saved->status);
        $this->assertSame(1, $saved->revision);
        $this->assertSame('Comprehensive operational workflow test', $saved->description);
        $this->assertCount(1, $saved->problems);
        $this->assertSame('Scratches on surface', $saved->problems->first()->description);
        $this->assertCount(1, $saved->nextActions);
        $this->assertCount(1, $saved->feedbacks);

        // Verifikasi draft berstatus applied
        $draft = ProgressDraft::find($draftId);
        $this->assertSame('applied', $draft->state);
        $this->assertSame($saved->id, $draft->applied_progress_id);

        // 5. Buka Halaman Show, jalankan Close dan Reopen
        $show = Livewire::test(ProgressShow::class, ['progressId' => $saved->id])
            ->assertSee('Comprehensive operational workflow test')
            ->assertSee('Close progress')
            ->call('closeProgress')
            ->assertDispatched('ui-toast');

        $saved->refresh();
        $this->assertSame('Closed', $saved->status);
        $this->assertSame(2, $saved->revision);

        // Reopen progress
        $show->call('reopenProgress')
            ->assertDispatched('ui-toast');

        $saved->refresh();
        $this->assertSame('Open', $saved->status);
        $this->assertSame(3, $saved->revision);
        $this->assertSame('Comprehensive operational workflow test', $saved->description);
        $this->assertCount(1, $saved->problems);
        $this->assertCount(1, $saved->nextActions);

        // 6. Verifikasi pada ProgressIndex
        Livewire::test(ProgressIndex::class)
            ->set('status', 'Open')
            ->set('pic', 'Hendra')
            ->assertSee('Roof Header Panel')
            ->assertSee('1 progress · Open');
    }
}
