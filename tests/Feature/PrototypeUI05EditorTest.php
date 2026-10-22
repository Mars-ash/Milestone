<?php

namespace Tests\Feature;

use App\Livewire\Prototype\ProgressForm;
use App\Prototype\FakeStore;
use Livewire\Livewire;
use Tests\PrototypeTestCase as TestCase;

class PrototypeUI05EditorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        FakeStore::reset();
    }

    protected function tearDown(): void
    {
        FakeStore::reset();
        parent::tearDown();
    }

    /**
     * UI-05: 24 initial bindings are available on fresh form without clicking Tambah.
     */
    public function test_ui05_all_24_initial_bindings_available_on_fresh_form(): void
    {
        $component = Livewire::withQueryParams(['tab' => 'form'])
            ->test(ProgressForm::class);

        // 1. JO No (projectId)
        $component->assertSeeHtml('id="jo-selector"');

        // 2. Tanggal aktivitas
        $component->assertSeeHtml('id="f-date"');

        // 3. Activity
        $component->assertSeeHtml('id="f-activity"');

        // 4. PIC Customer
        $component->assertSeeHtml('id="f-customerPic"');

        // 5. Uraian aktivitas
        $component->assertSeeHtml('id="f-description"');

        // 6-13. Subkelompok trial (8 fields)
        $component->assertSeeHtml('id="f-processTrial"');
        $component->assertSeeHtml('id="f-machine"');
        $component->assertSeeHtml('id="f-dh"');
        $component->assertSeeHtml('id="f-material"');
        $component->assertSeeHtml('id="f-qtyMaterial"');
        $component->assertSeeHtml('id="f-qtyProcess"');
        $component->assertSeeHtml('id="f-qtySample"');
        $component->assertSeeHtml('id="f-eventNote"');

        // 14-17. First Problem + CA
        $component->assertSeeHtml('id="problem-0"');
        $component->assertSeeHtml('id="f-ca-0-0-desc"');
        $component->assertSeeHtml('id="f-ca-0-0-pic"');
        $component->assertSeeHtml('id="f-ca-0-0-date"');

        // 18-20. First Next Action
        $component->assertSeeHtml('id="f-na-0-desc"');
        $component->assertSeeHtml('id="f-na-0-pic"');
        $component->assertSeeHtml('id="f-na-0-date"');

        // 21-23. First Feedback
        $component->assertSeeHtml('id="f-fb-0-content"');
        $component->assertSeeHtml('id="f-fb-0-pic"');
        $component->assertSeeHtml('id="f-fb-0-date"');

        // 24. Remark Progress
        $component->assertSeeHtml('id="f-remark"');

        // All 5 section headings present
        $component->assertSeeHtml('id="entry-activity"')
            ->assertSeeHtml('id="entry-problems"')
            ->assertSeeHtml('id="entry-nextActions"')
            ->assertSeeHtml('id="entry-feedback"')
            ->assertSeeHtml('id="entry-files"');

        // Savebar present with Ke feedback and Simpan progress
        $component->assertSee('Ke feedback ↓')
            ->assertSee('Simpan progress');
    }

    /**
     * UI-05: JO selection autofills Part, Customer, Model, No Doc.
     */
    public function test_ui05_jo_selection_autofills_identities(): void
    {
        $component = Livewire::withQueryParams(['tab' => 'form'])
            ->test(ProgressForm::class)
            ->set('projectId', 'proj-001');

        $component->assertSee('BRACKET FRONT LH (FIKTIF)')
            ->assertSee('PT ASTRA FIKTIF MOTOR')
            ->assertSee('K-FIKTIF-1')
            ->assertSee('DOC-001/2026');
    }

    /**
     * UI-05: Error summary and field errors appear when saving without mandatory fields.
     */
    public function test_ui05_error_summary_and_field_errors_appear_on_missing_fields(): void
    {
        Livewire::withQueryParams(['tab' => 'form'])
            ->test(ProgressForm::class)
            ->set('projectId', '')
            ->set('activityDate', '')
            ->call('save')
            ->assertSee('Isian masih ada di form. Penyimpanan draft belum terkonfirmasi.')
            ->assertSee('JO No wajib dipilih.')
            ->assertSee('Tanggal Aktivitas wajib diisi.');
    }

    /**
     * UI-05: Form mounts cleanly when loaded from Shell without project_id query param (null assignment safety).
     */
    public function test_ui05_mount_form_with_null_project_id(): void
    {
        Livewire::withQueryParams(['tab' => 'form'])
            ->test(\App\Livewire\Prototype\Shell::class)
            ->assertStatus(200)
            ->assertSee('Tambah progress');

        Livewire::test(ProgressForm::class, ['projectId' => null])
            ->assertStatus(200)
            ->assertSet('projectId', '')
            ->assertSee('Pilih JO No');
    }
}

