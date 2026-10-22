<?php

namespace App\Queries;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ProjectLookup
{
    public function search(string $term, int $limit = 20): Collection
    {
        if (blank($term)) {
            return new Collection;
        }

        $pattern = '%'.strtolower(trim($term)).'%';

        return Project::query()
            ->where(function ($q) use ($pattern) {
                $q->whereRaw('LOWER(jo_no) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(part_no) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(part_name) LIKE ?', [$pattern]);
            })
            ->orderBy('jo_no')
            ->limit($limit)
            ->get();
    }

    public function find(?int $id): ?Project
    {
        if (! $id) {
            return null;
        }

        return Project::find($id);
    }

    public function allForPicker(): Collection
    {
        return Project::query()
            ->select(['id', 'jo_no', 'part_no', 'part_name', 'customer', 'model', 'no_doc'])
            ->whereNotNull('jo_no')
            ->orderBy('jo_no')
            ->get();
    }
}
