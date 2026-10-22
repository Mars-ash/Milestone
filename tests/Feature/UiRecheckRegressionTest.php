<?php

namespace Tests\Feature;

use App\Livewire\Prototype\ProgressForm;
use App\Prototype\FakeDraftStore;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\PrototypeTestCase as TestCase;

class UiRecheckRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        FakeStore::reset();
        foreach (FakeDraftStore::all() as $draft) {
            FakeDraftStore::delete($draft['uuid']);
        }
    }

    public function test_leaving_empty_editor_does_not_create_draft(): void
    {
        Livewire::test(ProgressForm::class)
            ->call('cancel')
            ->assertRedirect(route('prototype.index', ['tab' => 'dashboard']));

        $this->assertCount(0, FakeDraftStore::all());
    }

    public function test_leaving_unchanged_saved_progress_does_not_create_draft(): void
    {
        $before = FakeStore::findProgress('prog-001');

        Livewire::test(ProgressForm::class, ['progressId' => 'prog-001'])
            ->call('cancel')
            ->assertRedirect(route('prototype.index', ['tab' => 'dashboard']));

        $this->assertCount(0, FakeDraftStore::all());
        $this->assertSame($before, FakeStore::findProgress('prog-001'));
    }

    public function test_actual_change_survives_failed_autosave_and_retry(): void
    {
        $component = Livewire::test(ProgressForm::class)
            ->set('simulateSaveError', true)
            ->set('notes', 'Catatan yang belum berhasil disimpan')
            ->assertSet('autosaveStatus', 'error');

        $this->assertCount(0, FakeDraftStore::all());

        $component->set('simulateSaveError', false)
            ->call('saveDraft')
            ->assertSet('autosaveStatus', 'saved');

        $draft = FakeDraftStore::find($component->get('draftUuid'));
        $this->assertSame('Catatan yang belum berhasil disimpan', $draft['notes']);
        $this->assertCount(1, FakeDraftStore::all());
    }

    public function test_project_dates_can_be_explicitly_cleared_including_aliases(): void
    {
        $keys = [
            'date_receive', 't0_target', 't0_actual',
            'plan_start', 'plan_finish', 'actual_start', 'actual_finish',
            'event_1', 'event_2', 'event_3',
        ];
        $project = FakeStore::findProject('proj-001');
        $dated = FakeStore::saveProject(array_replace(
            $project, array_fill_keys($keys, '2026-10-15')
        ));
        $cleared = FakeStore::saveProject(array_replace(
            $dated, array_fill_keys($keys, null)
        ));

        foreach ($keys as $key) {
            $this->assertNull($cleared[$key], $key);
        }
        foreach (['t0_target_date', 't0_actual_date', 'event_1_date', 'event_2_date', 'event_3_date'] as $key) {
            $this->assertNull($cleared[$key], $key);
        }
    }
}
