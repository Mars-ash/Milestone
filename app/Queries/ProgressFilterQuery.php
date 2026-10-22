<?php

namespace App\Queries;

use App\Models\Progress;
use Illuminate\Database\Eloquent\Builder;

final class ProgressFilterQuery
{
    /**
     * Membangun query builder untuk daftar progress dengan filter lengkap dan eager loading optimal.
     *
     * @param array{
     *     search?: ?string,
     *     status?: ?string,
     *     activity?: ?string,
     *     customer?: ?string,
     *     pic?: ?string,
     *     pic_role?: ?string,
     *     from?: ?string,
     *     to?: ?string,
     *     sort?: ?string
     * } $filters
     */
    public function build(array $filters = []): Builder
    {
        $query = Progress::query()->with([
            'project',
            'nextActions' => fn ($q) => $q->orderBy('position')->orderBy('id'),
            'problems.correctiveActions',
            'feedbacks',
        ]);

        $status = $filters['status'] ?? 'all';
        if ($status === 'Open') {
            $query->where('status', 'Open');
        } elseif ($status === 'Closed') {
            $query->where('status', 'Closed');
        }

        if (! empty($filters['search'])) {
            $term = '%'.strtolower(trim($filters['search'])).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->whereHas('project', function (Builder $pq) use ($term) {
                    $pq->whereRaw('LOWER(jo_no) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(part_no) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(part_name) LIKE ?', [$term]);
                })
                ->orWhereRaw('LOWER(description) LIKE ?', [$term])
                ->orWhereHas('problems', fn (Builder $probQ) => $probQ->whereRaw('LOWER(description) LIKE ?', [$term]));
            });
        }

        if (! empty($filters['activity'])) {
            $query->where('activity', $filters['activity']);
        }

        if (! empty($filters['customer'])) {
            $query->whereHas('project', fn (Builder $q) => $q->where('customer', $filters['customer']));
        }

        if (! empty($filters['pic'])) {
            $picTerm = '%'.strtolower(trim($filters['pic'])).'%';
            $role = $filters['pic_role'] ?? 'all';

            $query->where(function (Builder $q) use ($picTerm, $role) {
                if ($role === 'all') {
                    $q->whereRaw('LOWER(pic_customer) LIKE ?', [$picTerm])
                        ->orWhereHas('nextActions', fn (Builder $nq) => $nq->whereRaw('LOWER(pic) LIKE ?', [$picTerm]))
                        ->orWhereHas('feedbacks', fn (Builder $fq) => $fq->whereRaw('LOWER(pic) LIKE ?', [$picTerm]))
                        ->orWhereHas('problems.correctiveActions', fn (Builder $caq) => $caq->whereRaw('LOWER(pic) LIKE ?', [$picTerm]));
                } elseif ($role === 'customer') {
                    $q->whereRaw('LOWER(pic_customer) LIKE ?', [$picTerm]);
                } elseif ($role === 'next_action') {
                    $q->whereHas('nextActions', fn (Builder $nq) => $nq->whereRaw('LOWER(pic) LIKE ?', [$picTerm]));
                } elseif ($role === 'feedback') {
                    $q->whereHas('feedbacks', fn (Builder $fq) => $fq->whereRaw('LOWER(pic) LIKE ?', [$picTerm]));
                } elseif ($role === 'corrective_action') {
                    $q->whereHas('problems.correctiveActions', fn (Builder $caq) => $caq->whereRaw('LOWER(pic) LIKE ?', [$picTerm]));
                }
            });
        }

        if (! empty($filters['from'])) {
            $query->whereDate('activity_date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('activity_date', '<=', $filters['to']);
        }

        $sort = $filters['sort'] ?? 'newest';
        if ($sort === 'oldest') {
            $query->orderBy('activity_date', 'asc')->orderBy('id', 'asc');
        } else {
            $query->orderBy('activity_date', 'desc')->orderBy('id', 'desc');
        }

        return $query;
    }
}
