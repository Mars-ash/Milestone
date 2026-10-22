<?php

namespace Tests\Feature;

use App\Livewire\Prototype\ProgressForm;
use App\Prototype\FakeDraftStore;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\PrototypeTestCase as TestCase;

class PrototypeUiParityRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        session()->forget('prototype_ui_filters');
        FakeStore::reset();
    }

    public function test_new_editor_is_blank_but_first_rows_are_ready_without_creating_draft(): void
    {
        $before = count(FakeDraftStore::all());
        $component = Livewire::test(ProgressForm::class)
            ->assertSet('activity', '')->assertSet('activityDate', '');
        $this->assertCount(1, $component->get('problems'));
        $this->assertCount(1, $component->get('problems')[0]['corrective_actions']);
        $this->assertCount(1, $component->get('nextActions'));
        $this->assertCount(1, $component->get('feedbacks'));
        $this->assertSame($before, count(FakeDraftStore::all()));
    }

    public function test_opening_saved_progress_resumes_draft_and_keeps_applied_record_separate(): void
    {
        $before = FakeStore::findProgress('prog-001');
        $draft = FakeDraftStore::save([
            'uuid' => 'audit-resume', 'progress_id' => 'prog-001',
            'project_id' => 'proj-001', 'activity' => 'Trial',
            'notes' => 'Perubahan yang belum diterapkan',
        ]);
        Livewire::test(ProgressForm::class, ['progressId' => 'prog-001'])
            ->assertSet('draftUuid', $draft['uuid'])
            ->assertSet('notes', 'Perubahan yang belum diterapkan');
        $this->assertSame($before, FakeStore::findProgress('prog-001'));
    }

    public function test_closed_url_uses_read_only_detail(): void
    {
        FakeStore::closeProgress('prog-001');
        Livewire::test(ProgressForm::class, ['progressId' => 'prog-001'])
            ->assertRedirect(route('prototype.index', ['tab' => 'progresses', 'detail' => 'prog-001']));
    }

    public function test_new_feedback_has_no_automatic_date(): void
    {
        $component = Livewire::test(ProgressForm::class)->call('addFeedback');
        $this->assertCount(2, $component->get('feedbacks'));
        $this->assertSame('', $component->get('feedbacks')[1]['date']);
    }

    public function test_selected_photo_and_documents_are_kept_in_draft_and_photo_open_does_not_delete(): void
    {
        $component = Livewire::test(ProgressForm::class);
        $problemId = $component->get('problems')[0]['id'];
        $feedbackId = $component->get('feedbacks')[0]['id'];
        $image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aE1sAAAAASUVORK5CYII=';
        $component->call('attachProblemPhoto', $problemId, ['name' => 'uji.png', 'src' => $image]);
        $photos = $component->get('problems')[0]['photos'];
        $this->assertCount(1, $photos);
        $component->call('openProblemPhoto', $problemId, $photos[0]['id'])
            ->assertSet('activePhoto.name', 'uji.png');
        $this->assertSame($photos, $component->get('problems')[0]['photos']);
        $component->call('attachFiles', 'progress', [['name' => 'hasil.pdf', 'size' => 1200]])
            ->call('attachFiles', $feedbackId, [['name' => 'feedback.pdf', 'size' => 2300]]);
        $draft = FakeDraftStore::find($component->get('draftUuid'));
        $this->assertSame('hasil.pdf', $draft['attachments'][0]['name']);
        $this->assertSame('feedback.pdf', $draft['feedbacks'][0]['files'][0]['name']);
        $this->assertSame($image, $draft['problems'][0]['photos'][0]['path']);
    }

    public function test_removed_pic_progress_is_not_matched_as_pic_customer(): void
    {
        $progress = FakeStore::findProgress('prog-001');
        FakeStore::saveProgress(array_replace($progress, ['pic_progress' => 'PIC_LEGACY_UNIQUE']));
        $this->assertSame([], FakeStore::getProgresses([
            'pic' => 'PIC_LEGACY_UNIQUE', 'pic_role' => 'customer', 'status' => 'all',
        ]));
    }
}
