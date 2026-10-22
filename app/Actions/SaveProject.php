<?php

namespace App\Actions;

use App\Data\ProjectPayload;
use App\Models\Project;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SaveProject
{
    public function handle(array $input, ?Project $project = null): Project
    {
        return DB::transaction(function () use ($input, $project) {
            $payload = ProjectPayload::fromArray($input, $project);

            try {
                if ($project) {
                    $project->update($payload->data);

                    return $project->fresh();
                }

                return Project::create($payload->data);
            } catch (QueryException $e) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'projects_jo_no_unique')) {
                    throw ValidationException::withMessages([
                        'jo_no' => "JO No '{$payload->data['jo_no']}' sudah digunakan pada project lain.",
                    ]);
                }
                if (str_contains($msg, 'projects_part_no_unique')) {
                    throw ValidationException::withMessages([
                        'part_no' => "Part No '{$payload->data['part_no']}' sudah digunakan pada project lain.",
                    ]);
                }

                throw $e;
            }
        });
    }
}
