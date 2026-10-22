<?php

namespace Tests\Feature\Import;

use App\Models\ImportRecord;
use App\Models\LegacyDocumentMetadata;
use App\Models\Progress;
use App\Models\Project;
use App\Services\Import\AccessImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_serial_to_date_converts_correctly(): void
    {
        $importer = new AccessImporter();

        // 45678 serial test
        $this->assertNull($importer->excelSerialToDate(null));
        $this->assertNull($importer->excelSerialToDate(''));
        
        // Serial 45292 is 2024-01-01
        $this->assertSame('2024-01-01', $importer->excelSerialToDate('45292'));
        
        // Standard ISO string passthrough
        $this->assertSame('2024-05-15', $importer->excelSerialToDate('2024-05-15'));
    }

    public function test_import_projects_maps_records_and_creates_import_records(): void
    {
        $importer = new AccessImporter();

        $rows = [
            [
                'JO No' => 'JO-9901',
                'Part No' => 'P-9901',
                'Part Name' => 'Bracket Front',
                'Customer' => 'Honda',
                'Model' => '2TG',
                'Material Thickness' => '1,6',
                'Material Width' => '120.5',
                'Date Receive' => '45292',
                'T0 Target' => '45300',
            ],
        ];

        $summary = $importer->importProjectRows($rows);

        $this->assertSame(1, $summary['imported']);
        $this->assertDatabaseHas('projects', [
            'jo_no' => 'JO-9901',
            'part_no' => 'P-9901',
            'part_name' => 'Bracket Front',
            'customer' => 'Honda',
            'material_thickness_raw' => '1,6',
            'material_width_raw' => '120.5',
            'date_receive' => '2024-01-01',
        ]);

        $this->assertDatabaseHas('import_records', [
            'source_table' => 'project_list_master',
            'source_id' => 'JO-9901',
            'target_table' => 'projects',
            'status' => 'imported',
        ]);
    }

    public function test_import_progresses_maps_all_fields_and_children(): void
    {
        $importer = new AccessImporter();

        // Create project first
        $project = Project::create([
            'jo_no' => 'JO-8801',
            'part_no' => 'P-8801',
            'part_name' => 'Door Panel',
        ]);

        $rows = [
            [
                'ID Progress' => '501',
                'JO No' => 'JO-8801',
                'Date' => '45292', // 2024-01-01
                'Activity' => 'Trial',
                'Activity Description' => 'Trial sample T0',
                'Mesin' => 'AIDA 200T',
                'DH' => '450',
                'Progress Close' => '0',
                'Problem' => 'Burr pada sudut R',
                'Corrective Action' => 'Grinding punch die',
                'PIC Corrective Action' => 'Budi Tooling',
                'Date Corrective Action' => '45293',
                'Next Action' => 'Trial ulang T1',
                'PIC Next Action' => 'Siti QC',
                'Date Next Action' => '45295',
                'Feedback Customer' => 'Sampel diterima',
                'PIC Feedback Customer' => 'Tanaka',
                'Date Feedback Customer' => '45294',
                'Doc1' => 'report_trial.pdf',
                'Ket Doc1' => 'Hasil trial T0',
            ],
        ];

        $summary = $importer->importProgressRows($rows);

        $this->assertSame(1, $summary['imported']);
        
        $progress = Progress::where('project_id', $project->id)->first();
        $this->assertNotNull($progress);
        $this->assertSame('Trial', $progress->activity);
        $this->assertSame('2024-01-01', $progress->activity_date->format('Y-m-d'));
        $this->assertSame('Open', $progress->status);
        $this->assertSame('AIDA 200T', $progress->machine);
        $this->assertSame('450', $progress->die_high_raw);
        $this->assertFalse($progress->legacy_incomplete);

        // Check Problem and Corrective Action
        $this->assertCount(1, $progress->problems);
        $problem = $progress->problems->first();
        $this->assertSame('Burr pada sudut R', $problem->description);
        $this->assertCount(1, $problem->correctiveActions);
        $ca = $problem->correctiveActions->first();
        $this->assertSame('Grinding punch die', $ca->description);
        $this->assertSame('Budi Tooling', $ca->pic);
        $this->assertSame('2024-01-02', $ca->actual_date->format('Y-m-d'));

        // Check Next Action
        $this->assertCount(1, $progress->nextActions);
        $na = $progress->nextActions->first();
        $this->assertSame('Trial ulang T1', $na->description);
        $this->assertSame('Siti QC', $na->pic);

        // Check Feedback
        $this->assertCount(1, $progress->feedbacks);
        $fb = $progress->feedbacks->first();
        $this->assertSame('Sampel diterima', $fb->content);
        $this->assertSame('Tanaka', $fb->pic);

        // Check Legacy Doc Metadata
        $this->assertCount(1, $progress->legacyDocuments);
        $doc = $progress->legacyDocuments->first();
        $this->assertSame('Doc1', $doc->source_field);
        $this->assertSame('report_trial.pdf', $doc->name);
        $this->assertSame('Hasil trial T0', $doc->description);
    }

    public function test_import_handles_null_date_and_activity_with_legacy_incomplete(): void
    {
        $importer = new AccessImporter();

        $project = Project::create([
            'jo_no' => 'JO-7701',
            'part_no' => 'P-7701',
            'part_name' => 'Bracket Incomplete',
        ]);

        $rows = [
            [
                'ID Progress' => '601',
                'JO No' => 'JO-7701',
                'Date' => null,
                'Activity' => null,
                'Activity Description' => 'Legacy without date and activity',
                'Progress Close' => '1',
            ],
        ];

        $summary = $importer->importProgressRows($rows);
        $this->assertSame(1, $summary['imported']);

        $progress = Progress::where('project_id', $project->id)->first();
        $this->assertNotNull($progress);
        $this->assertTrue($progress->legacy_incomplete);
        $this->assertNull($progress->activity_date);
        $this->assertNull($progress->activity);
        $this->assertSame('Closed', $progress->status);
    }

    public function test_corrective_action_without_problem_creates_null_problem_container(): void
    {
        $importer = new AccessImporter();

        $project = Project::create([
            'jo_no' => 'JO-6601',
            'part_no' => 'P-6601',
            'part_name' => 'Reinforce Bracket',
        ]);

        $rows = [
            [
                'ID Progress' => '701',
                'JO No' => 'JO-6601',
                'Date' => '45292',
                'Activity' => 'Others',
                'Problem' => null, // empty problem
                'Corrective Action' => 'Tindakan tanpa uraian problem',
                'PIC Corrective Action' => 'Agus',
            ],
        ];

        $summary = $importer->importProgressRows($rows);
        $this->assertSame(1, $summary['imported']);

        $progress = Progress::where('project_id', $project->id)->first();
        $this->assertCount(1, $progress->problems);
        $problem = $progress->problems->first();
        $this->assertNull($problem->description); // legacy container
        $this->assertCount(1, $problem->correctiveActions);
        $ca = $problem->correctiveActions->first();
        $this->assertSame('Tindakan tanpa uraian problem', $ca->description);
        $this->assertSame('Agus', $ca->pic);
    }

    public function test_parse_xlsx_reads_actual_import_files(): void
    {
        $importer = new AccessImporter();

        $projectRows = $importer->parseXlsx(base_path('import/Table - Project List Master.xlsx'));
        $this->assertCount(51, $projectRows);
        $this->assertArrayHasKey('JO No', $projectRows[0]);
        $this->assertArrayHasKey('Part No', $projectRows[0]);
        $this->assertArrayHasKey('Part Name', $projectRows[0]);

        $progressRows = $importer->parseXlsx(base_path('import/Table - Progress New model Master.xlsx'));
        $this->assertCount(266, $progressRows);
        $this->assertArrayHasKey('JO No', $progressRows[0]);
        $this->assertArrayHasKey('Activity', $progressRows[0]);
    }

    public function test_importer_run_imports_all_legacy_records_from_actual_files(): void
    {
        $importer = new AccessImporter();
        $report = $importer->run();

        $this->assertSame(51, $report['projects']['total']);
        $this->assertSame(51, $report['projects']['imported']);
        $this->assertSame(0, $report['projects']['exceptions']);

        $this->assertSame(266, $report['progresses']['total']);
        $this->assertSame(266, $report['progresses']['imported']);
        $this->assertSame(0, $report['progresses']['exceptions']);

        $this->assertSame(51, Project::count());
        $this->assertSame(266, Progress::count());

        $this->assertSame(255, Progress::where('status', 'Open')->count());
        $this->assertSame(11, Progress::where('status', 'Closed')->count());
    }
}
