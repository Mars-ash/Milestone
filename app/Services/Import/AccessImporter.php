<?php

namespace App\Services\Import;

use App\Models\CorrectiveAction;
use App\Models\Feedback;
use App\Models\ImportRecord;
use App\Models\LegacyDocumentMetadata;
use App\Models\NextAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\Project;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleXMLElement;
use ZipArchive;

class AccessImporter
{
    public const ALLOWED_ACTIVITIES = [
        'Trial',
        'Sent Sample',
        'Send Document',
        'Audit',
        'Others',
    ];

    /**
     * Convert Excel date serial or raw date string to Y-m-d.
     */
    public function excelSerialToDate(mixed $val): ?string
    {
        if ($val === null || $val === '') {
            return null;
        }

        $str = trim((string) $val);
        if ($str === '') {
            return null;
        }

        if (is_numeric($str)) {
            $serial = (float) $str;
            if ($serial < 1000) {
                return null;
            }
            $days = (int) floor($serial);
            $base = new DateTimeImmutable('1899-12-30');
            return $base->modify("+{$days} days")->format('Y-m-d');
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $str, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }

        try {
            $dt = new DateTimeImmutable($str);
            return $dt->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parse XLSX file into an array of associative row arrays using first row as header keys.
     */
    public function parseXlsx(string $filePath): array
    {
        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("Excel file not found: {$filePath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException("Could not open zip archive: {$filePath}");
        }

        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $xmlContent = $zip->getFromName('xl/sharedStrings.xml');
            $dom = new \DOMDocument();
            $dom->loadXML($xmlContent);
            $sis = $dom->getElementsByTagName('si');
            foreach ($sis as $si) {
                $ts = $si->getElementsByTagName('t');
                $text = '';
                foreach ($ts as $t) {
                    $text .= $t->nodeValue;
                }
                $sharedStrings[] = $text;
            }
        }

        $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! $sheetXmlContent) {
            throw new \RuntimeException("Could not read sheet1.xml from: {$filePath}");
        }

        $dom = new \DOMDocument();
        $dom->loadXML($sheetXmlContent);
        $rows = $dom->getElementsByTagName('row');

        $rawRows = [];
        foreach ($rows as $rowElem) {
            $rowData = [];
            $cells = $rowElem->getElementsByTagName('c');
            foreach ($cells as $cell) {
                $ref = $cell->getAttribute('r');
                $colLetter = preg_replace('/[0-9]/', '', $ref);
                $type = $cell->getAttribute('t');

                $vElem = $cell->getElementsByTagName('v')->item(0);
                $val = $vElem ? $vElem->nodeValue : null;

                if ($type === 's' && $val !== null) {
                    $idx = (int) $val;
                    $val = $sharedStrings[$idx] ?? $val;
                } elseif ($type === 'inlineStr') {
                    $tElem = $cell->getElementsByTagName('t')->item(0);
                    if ($tElem) {
                        $val = $tElem->nodeValue;
                    }
                }

                $rowData[$colLetter] = $val;
            }
            if (! empty($rowData)) {
                $rawRows[] = $rowData;
            }
        }

        if (empty($rawRows)) {
            return [];
        }

        $headerRow = $rawRows[0];
        $colMap = [];
        foreach ($headerRow as $col => $headerName) {
            if ($headerName !== null && trim($headerName) !== '') {
                $colMap[$col] = trim($headerName);
            }
        }

        $dataRows = [];
        for ($i = 1; $i < count($rawRows); $i++) {
            $mapped = [];
            foreach ($colMap as $col => $name) {
                $mapped[$name] = $rawRows[$i][$col] ?? null;
            }
            $dataRows[] = $mapped;
        }

        return $dataRows;
    }

    /**
     * Import Project records from an array of raw rows.
     */
    public function importProjectRows(array $rows, bool $dryRun = false): array
    {
        $imported = 0;
        $updated = 0;
        $exceptions = 0;
        $details = [];

        foreach ($rows as $index => $row) {
            $joNo = isset($row['JO No']) ? trim((string) $row['JO No']) : null;
            $partNo = isset($row['Part No']) ? trim((string) $row['Part No']) : null;
            $partName = isset($row['Part Name']) ? trim((string) $row['Part Name']) : null;

            if (empty($joNo) && empty($partNo)) {
                $exceptions++;
                $details[] = ['index' => $index, 'status' => 'exception', 'reason' => 'Missing JO No and Part No'];
                continue;
            }

            $legacyIncomplete = empty($joNo) || empty($partNo) || empty($partName);

            $attributes = [
                'jo_no' => $joNo ?: null,
                'part_no' => $partNo ?: null,
                'part_name' => $partName ?: null,
                'customer' => $row['Customer'] ?? null,
                'model' => $row['Model'] ?? null,
                'no_doc' => $row['No Doc'] ?? null,
                'material' => $row['Material Spec'] ?? null,
                'spec' => $row['Spec'] ?? null,
                'material_thickness_raw' => isset($row['Material Thickness']) && $row['Material Thickness'] !== null ? (string) $row['Material Thickness'] : null,
                'material_width_raw' => isset($row['Material Width']) && $row['Material Width'] !== null ? (string) $row['Material Width'] : null,
                'process' => $row['Process'] ?? null,
                'remark' => $row['Remark Project list'] ?? $row['Remark'] ?? null,
                'date_receive' => $this->excelSerialToDate($row['Date Receive'] ?? null),
                't0_target' => $this->excelSerialToDate($row['T0 Target'] ?? null),
                't0_actual' => $this->excelSerialToDate($row['T0 Actual'] ?? null),
                'plan_start' => $this->excelSerialToDate($row['Plan Start'] ?? null),
                'plan_finish' => $this->excelSerialToDate($row['Plan Finish'] ?? null),
                'actual_start' => $this->excelSerialToDate($row['Actual Start'] ?? null),
                'actual_finish' => $this->excelSerialToDate($row['Actual Finish'] ?? null),
                'event_1' => $this->excelSerialToDate($row['PP1'] ?? null),
                'event_2' => $this->excelSerialToDate($row['PP2'] ?? null),
                'event_3' => $this->excelSerialToDate($row['MP'] ?? null),
                'legacy_incomplete' => $legacyIncomplete,
            ];

            if ($dryRun) {
                $imported++;
                continue;
            }

            DB::transaction(function () use ($attributes, $joNo, &$imported, &$updated) {
                $project = Project::where('jo_no', $joNo)->first();
                if ($project) {
                    $project->update($attributes);
                    $updated++;
                } else {
                    $project = Project::create($attributes);
                    $imported++;
                }

                ImportRecord::updateOrCreate(
                    [
                        'source_table' => 'project_list_master',
                        'source_id' => $joNo,
                    ],
                    [
                        'target_table' => 'projects',
                        'target_id' => $project->id,
                        'status' => 'imported',
                        'notes' => "Imported project {$joNo}",
                    ]
                );
            });
        }

        return [
            'total' => count($rows),
            'imported' => $imported,
            'updated' => $updated,
            'exceptions' => $exceptions,
            'details' => $details,
        ];
    }

    /**
     * Import Progress records and child entities from an array of raw rows.
     */
    public function importProgressRows(array $rows, bool $dryRun = false): array
    {
        $imported = 0;
        $updated = 0;
        $exceptions = 0;
        $details = [];

        foreach ($rows as $index => $row) {
            $sourceId = isset($row['ID Progress']) ? (string) $row['ID Progress'] : (isset($row['ID']) ? (string) $row['ID'] : (string) ($index + 1));
            $joNo = isset($row['JO No']) ? trim((string) $row['JO No']) : null;

            if (empty($joNo)) {
                $exceptions++;
                $details[] = ['source_id' => $sourceId, 'status' => 'exception', 'reason' => 'Missing JO No'];
                continue;
            }

            $projectId = Project::where('jo_no', $joNo)->value('id');
            if (! $projectId && ! $dryRun) {
                $exceptions++;
                $details[] = ['source_id' => $sourceId, 'status' => 'exception', 'reason' => "Project not found for JO: {$joNo}"];
                continue;
            }

            $activityDate = $this->excelSerialToDate($row['Date'] ?? null);
            $rawActivity = isset($row['Activity']) ? trim((string) $row['Activity']) : null;
            $activity = in_array($rawActivity, self::ALLOWED_ACTIVITIES, true) ? $rawActivity : null;

            $legacyIncomplete = empty($activityDate) || empty($activity);
            $isClose = isset($row['Progress Close']) && (string) $row['Progress Close'] === '1';
            $status = $isClose ? 'Closed' : 'Open';

            $attributes = [
                'project_id' => $projectId,
                'activity_date' => $activityDate,
                'activity' => $activity,
                'description' => $row['Activity Description'] ?? null,
                'pic_customer' => $row['PIC Customer'] ?? null,
                'process_trial' => $row['Process Trial'] ?? null,
                'machine' => $row['Mesin'] ?? null,
                'die_high_raw' => isset($row['DH']) && $row['DH'] !== null ? (string) $row['DH'] : null,
                'material_actual_trial' => $row['Material Actual trial'] ?? null,
                'qty_material_raw' => isset($row['Qty Material']) && $row['Qty Material'] !== null ? (string) $row['Qty Material'] : null,
                'qty_process_raw' => isset($row['Qty Process']) && $row['Qty Process'] !== null ? (string) $row['Qty Process'] : null,
                'qty_sample_raw' => isset($row['Qty Sample']) && $row['Qty Sample'] !== null ? (string) $row['Qty Sample'] : null,
                'event_note' => $row['Ket Event'] ?? null,
                'remark' => $row['Remark Progress'] ?? null,
                'status' => $status,
                'revision' => 1,
                'legacy_incomplete' => $legacyIncomplete,
            ];

            if ($dryRun) {
                $imported++;
                continue;
            }

            DB::transaction(function () use ($attributes, $row, $sourceId, &$imported, &$updated) {
                $record = ImportRecord::where('source_table', 'progress_new_model_master')
                    ->where('source_id', $sourceId)
                    ->first();

                if ($record) {
                    $progress = Progress::find($record->target_id);
                    if ($progress) {
                        $progress->update($attributes);
                        $updated++;
                    } else {
                        $progress = Progress::create($attributes);
                        $record->update(['target_id' => $progress->id]);
                        $imported++;
                    }
                } else {
                    $progress = Progress::create($attributes);
                    ImportRecord::create([
                        'source_table' => 'progress_new_model_master',
                        'source_id' => $sourceId,
                        'target_table' => 'progresses',
                        'target_id' => $progress->id,
                        'status' => 'imported',
                        'notes' => "Imported progress #{$sourceId}",
                    ]);
                    $imported++;
                }

                $this->syncChildren($progress, $row);
            });
        }

        return [
            'total' => count($rows),
            'imported' => $imported,
            'updated' => $updated,
            'exceptions' => $exceptions,
            'details' => $details,
        ];
    }

    /**
     * Map and synchronize child entities (Problem, CA, NA, Feedback, Legacy Document Metadata).
     */
    protected function syncChildren(Progress $progress, array $row): void
    {
        // 1. Problem & Corrective Action
        $problemText = isset($row['Problem']) && trim((string) $row['Problem']) !== '' ? trim((string) $row['Problem']) : null;
        $caText = isset($row['Corrective Action']) && trim((string) $row['Corrective Action']) !== '' ? trim((string) $row['Corrective Action']) : null;
        $caPic = isset($row['PIC Corrective Action']) && trim((string) $row['PIC Corrective Action']) !== '' ? trim((string) $row['PIC Corrective Action']) : null;
        $caDate = $this->excelSerialToDate($row['Date Corrective Action'] ?? null);

        if ($problemText !== null || $caText !== null || $caPic !== null || $caDate !== null) {
            $problem = $progress->problems()->first();
            if (! $problem) {
                $problem = Problem::create([
                    'progress_id' => $progress->id,
                    'client_id' => Str::uuid()->toString(),
                    'position' => 0,
                    'description' => $problemText,
                ]);
            }

            if ($caText !== null || $caPic !== null || $caDate !== null) {
                $ca = $problem->correctiveActions()->first();
                if (! $ca) {
                    CorrectiveAction::create([
                        'problem_id' => $problem->id,
                        'client_id' => Str::uuid()->toString(),
                        'position' => 0,
                        'description' => $caText,
                        'pic' => $caPic,
                        'actual_date' => $caDate,
                    ]);
                }
            }
        }

        // 2. Next Action
        $naText = isset($row['Next Action']) && trim((string) $row['Next Action']) !== '' ? trim((string) $row['Next Action']) : null;
        $naPic = isset($row['PIC Next Action']) && trim((string) $row['PIC Next Action']) !== '' ? trim((string) $row['PIC Next Action']) : null;
        $naDate = $this->excelSerialToDate($row['Date Next Action'] ?? null);

        if ($naText !== null || $naPic !== null || $naDate !== null) {
            $na = $progress->nextActions()->first();
            if (! $na) {
                NextAction::create([
                    'progress_id' => $progress->id,
                    'client_id' => Str::uuid()->toString(),
                    'position' => 0,
                    'description' => $naText,
                    'pic' => $naPic,
                    'actual_date' => $naDate,
                ]);
            }
        }

        // 3. Feedback Customer
        $fbText = isset($row['Feedback Customer']) && trim((string) $row['Feedback Customer']) !== '' ? trim((string) $row['Feedback Customer']) : null;
        $fbPic = isset($row['PIC Feedback Customer']) && trim((string) $row['PIC Feedback Customer']) !== '' ? trim((string) $row['PIC Feedback Customer']) : null;
        $fbDate = $this->excelSerialToDate($row['Date Feedback Customer'] ?? null);

        if ($fbText !== null || $fbPic !== null || $fbDate !== null) {
            $fb = $progress->feedbacks()->first();
            if (! $fb) {
                $fb = Feedback::create([
                    'progress_id' => $progress->id,
                    'client_id' => Str::uuid()->toString(),
                    'position' => 0,
                    'content' => $fbText,
                    'pic' => $fbPic,
                    'feedback_date' => $fbDate,
                ]);
            }
        }

        // 4. Legacy Document Metadata (Doc1 to Doc5)
        for ($i = 1; $i <= 5; $i++) {
            $docKey = "Doc{$i}";
            $ketKey = "Ket Doc{$i}";
            $docName = isset($row[$docKey]) && trim((string) $row[$docKey]) !== '' ? trim((string) $row[$docKey]) : null;
            $docDesc = isset($row[$ketKey]) && trim((string) $row[$ketKey]) !== '' ? trim((string) $row[$ketKey]) : null;

            if ($docName !== null || $docDesc !== null) {
                $existingDoc = $progress->legacyDocuments()->where('source_field', $docKey)->first();
                if (! $existingDoc) {
                    LegacyDocumentMetadata::create([
                        'progress_id' => $progress->id,
                        'source_field' => $docKey,
                        'name' => $docName,
                        'description' => $docDesc,
                    ]);
                }
            }
        }
    }

    /**
     * Run full import of projects and progresses from given or default XLSX files.
     */
    public function run(?string $projectPath = null, ?string $progressPath = null, bool $dryRun = false): array
    {
        $projectPath ??= base_path('import/Table - Project List Master.xlsx');
        $progressPath ??= base_path('import/Table - Progress New model Master.xlsx');

        $projectRows = $this->parseXlsx($projectPath);
        $projectSummary = $this->importProjectRows($projectRows, $dryRun);

        $progressRows = $this->parseXlsx($progressPath);
        $progressSummary = $this->importProgressRows($progressRows, $dryRun);

        return [
            'projects' => $projectSummary,
            'progresses' => $progressSummary,
        ];
    }
}
