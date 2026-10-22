<?php

namespace Tests\Feature\Import;

use App\Models\ImportRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportRecordMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_records_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('import_records'));
        $this->assertTrue(Schema::hasColumns('import_records', [
            'id',
            'source_table',
            'source_id',
            'target_table',
            'target_id',
            'checksum',
            'status',
            'notes',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_import_record_model_can_be_created_and_queried(): void
    {
        $record = ImportRecord::create([
            'source_table' => 'progress_master',
            'source_id' => '101',
            'target_table' => 'progresses',
            'target_id' => 1,
            'status' => 'imported',
            'notes' => 'Legacy trial',
        ]);

        $this->assertNotNull($record->id);
        $this->assertSame('progress_master', $record->source_table);
        $this->assertSame('101', $record->source_id);
        $this->assertSame(1, $record->target_id);
    }
}
