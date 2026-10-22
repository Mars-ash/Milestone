<?php

namespace Database\Seeders;

use App\Data\ProgressPayload;
use App\Data\ProjectPayload;
use App\Models\Progress;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $db = config('database.connections.pgsql.database');
        if (! app()->environment('local', 'testing') || ! in_array($db, ['progress_new_model_dev', 'progress_new_model_test'])) {
            throw new \RuntimeException('Fixture hanya untuk database development/test terisolasi.');
        }
        if (Project::exists() || Progress::exists()) {
            throw new \RuntimeException('Fixture memerlukan database kosong; tidak menimpa data.');
        }
        $source = json_decode(file_get_contents(resource_path('fixtures/prototype.json')), true, flags: JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($source) {
            $map = [];
            foreach ($source['projects'] as $row) {
                $data = array_intersect_key($row, array_flip(['jo_no', 'part_no', ...ProjectPayload::TEXT, ...ProjectPayload::DATES]));
                $map[$row['id']] = Project::create($data)->id;
            }
            foreach ($source['progresses'] as $row) {
                $data = array_intersect_key($row, array_flip(['activity_date', 'activity', 'status', ...ProgressPayload::TEXT_FIELDS]));
                $data['project_id'] = $map[$row['project_id']];
                $data['description'] = $row['notes'] ?? $row['description'] ?? null;
                $progress = Progress::create($data);
                foreach ($row['problems'] ?? [] as $i => $p) {
                    $problem = $progress->problems()->create(['client_id' => (string) Str::uuid(), 'description' => $p['description'] ?? null, 'position' => $i]);
                    foreach ($p['corrective_actions'] ?? [] as $j => $a) {
                        $problem->correctiveActions()->create([...array_intersect_key($a, array_flip(['description', 'pic', 'actual_date'])), 'client_id' => (string) Str::uuid(), 'position' => $j]);
                    }
                }
                foreach (['next_actions' => 'nextActions', 'feedbacks' => 'feedbacks'] as $key => $relation) {
                    foreach ($row[$key] ?? [] as $i => $child) {
                        $attrs = array_intersect_key($child, array_flip(['description', 'content', 'pic', 'actual_date']));
                        if ($key === 'feedbacks') {
                            $attrs['feedback_date'] = $child['date'] ?? null;
                        }
                        $progress->$relation()->create([...$attrs, 'client_id' => (string) Str::uuid(), 'position' => $i]);
                    }
                }
            }
            $legacy = Progress::factory()->create(['project_id' => reset($map), 'legacy_incomplete' => true, 'activity' => null, 'activity_date' => null]);
            $legacy->legacyDocuments()->create(['source_field' => 'Doc1', 'name' => 'arsip-fiktif.pdf', 'description' => 'Metadata saja; byte file tidak tersedia.']);
        });
    }
}
