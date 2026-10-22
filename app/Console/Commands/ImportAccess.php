<?php

namespace App\Console\Commands;

use App\Services\Import\AccessImporter;
use Illuminate\Console\Command;

class ImportAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:access
                            {--dry-run : Simulate the import without persisting changes}
                            {--project-file= : Path to Table - Project List Master.xlsx}
                            {--progress-file= : Path to Table - Progress New model Master.xlsx}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import legacy Access/Excel project and progress data into PostgreSQL';

    /**
     * Execute the console command.
     */
    public function handle(AccessImporter $importer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $projectPath = $this->option('project-file') ?: base_path('import/Table - Project List Master.xlsx');
        $progressPath = $this->option('progress-file') ?: base_path('import/Table - Progress New model Master.xlsx');

        $this->info('Starting Access data migration...');
        if ($dryRun) {
            $this->warn('*** DRY RUN MODE ENABLED - No changes will be written to database ***');
        }

        $this->line("Project file: {$projectPath}");
        $this->line("Progress file: {$progressPath}");

        try {
            $report = $importer->run($projectPath, $progressPath, $dryRun);
        } catch (\Throwable $e) {
            $this->error("Import failed with exception: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('=== IMPORT SUMMARY ===');

        $this->table(
            ['Entity', 'Total Rows', 'Imported (New)', 'Updated (Existing)', 'Exceptions'],
            [
                [
                    'Projects',
                    $report['projects']['total'],
                    $report['projects']['imported'],
                    $report['projects']['updated'],
                    $report['projects']['exceptions'],
                ],
                [
                    'Progresses',
                    $report['progresses']['total'],
                    $report['progresses']['imported'],
                    $report['progresses']['updated'],
                    $report['progresses']['exceptions'],
                ],
            ]
        );

        if (! empty($report['projects']['details'])) {
            $this->warn('Project exceptions/notes:');
            foreach ($report['projects']['details'] as $detail) {
                $this->line("- " . json_encode($detail));
            }
        }

        if (! empty($report['progresses']['details'])) {
            $this->warn('Progress exceptions/notes:');
            foreach ($report['progresses']['details'] as $detail) {
                $this->line("- " . json_encode($detail));
            }
        }

        if ($dryRun) {
            $this->info('DRY RUN simulation finished successfully. All rows validated.');
        } else {
            $this->info('Import completed successfully!');
        }

        return Command::SUCCESS;
    }
}
