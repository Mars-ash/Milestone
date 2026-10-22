<?php

namespace App\Prototype;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class FakeStore
{
    protected const SESSION_KEY = 'prototype_store';

    /**
     * Fallback in-memory state when session is not available.
     *
     * @var array<string, mixed>|null
     */
    protected static ?array $memoryStore = null;

    /**
     * Get the full state (from session or memory).
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        if (Session::isStarted() && Session::has(self::SESSION_KEY)) {
            return Session::get(self::SESSION_KEY);
        }

        if (static::$memoryStore !== null) {
            return static::$memoryStore;
        }

        return static::loadInitialFixtures();
    }

    /**
     * Load initial fixtures from JSON and cache in store.
     *
     * @return array<string, mixed>
     */
    protected static function loadInitialFixtures(): array
    {
        $path = resource_path('fixtures/prototype.json');

        if (! file_exists($path)) {
            throw new \RuntimeException("Prototype fixture file not found at: {$path}");
        }

        $data = json_decode(file_get_contents($path), true) ?: [];

        static::saveStore($data);

        return $data;
    }

    /**
     * Persist the store into session and static memory.
     *
     * @param array<string, mixed> $data
     */
    protected static function saveStore(array $data): void
    {
        static::$memoryStore = $data;

        if (Session::isStarted()) {
            Session::put(self::SESSION_KEY, $data);
        }
    }

    /**
     * Reset store back to the original fixture data.
     */
    public static function reset(): void
    {
        static::$memoryStore = null;

        if (Session::isStarted()) {
            Session::forget(self::SESSION_KEY);
        }

        static::loadInitialFixtures();
    }

    /*
    |--------------------------------------------------------------------------
    | Project Operations
    |--------------------------------------------------------------------------
    */

    /**
     * Get all projects.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getProjects(): array
    {
        return static::all()['projects'] ?? [];
    }

    /**
     * Find project by ID or JO No.
     *
     * @return array<string, mixed>|null
     */
    public static function findProject(string $idOrJo): ?array
    {
        foreach (static::getProjects() as $project) {
            if ($project['id'] === $idOrJo || $project['jo_no'] === $idOrJo) {
                return $project;
            }
        }

        return null;
    }

    /**
     * Save (create or update) a project record.
     * Enforces D01, FR01, AT01, AT02:
     * - JO No must be unique (trimmed, case-insensitive)
     * - Part No must be unique (trimmed, case-insensitive)
     * - Part Name is required for new projects
     * - Leading zeros preserved as string
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function saveProject(array $data): array
    {
        $joNo = trim($data['jo_no'] ?? '');
        $partNo = trim($data['part_no'] ?? '');
        $partName = trim($data['part_name'] ?? '');

        $errors = [];

        if ($joNo === '') {
            $errors['jo_no'] = ['JO No wajib diisi.'];
        }
        if ($partNo === '') {
            $errors['part_no'] = ['Part No wajib diisi.'];
        }
        if ($partName === '') {
            $errors['part_name'] = ['Part Name wajib diisi.'];
        }

        $isNew = empty($data['id']);
        $id = $isNew ? 'proj-' . Str::lower(Str::random(6)) : $data['id'];

        $store = static::all();
        $projects = $store['projects'] ?? [];

        // Check uniqueness of JO No (AT01) and Part No (AT02)
        foreach ($projects as $p) {
            if ($p['id'] === $id) {
                continue;
            }

            if ($joNo !== '' && strcasecmp(trim($p['jo_no']), $joNo) === 0) {
                $errors['jo_no'] = ["JO No '{$joNo}' sudah digunakan pada project lain."];
            }

            if ($partNo !== '' && strcasecmp(trim($p['part_no']), $partNo) === 0) {
                $errors['part_no'] = ["Part No '{$partNo}' sudah digunakan pada project lain."];
            }
        }

        if (! empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        $existingIndex = null;
        if (! $isNew) {
            foreach ($projects as $idx => $p) {
                if ($p['id'] === $id) {
                    $existingIndex = $idx;
                    break;
                }
            }
        }

        $existing = $existingIndex !== null ? $projects[$existingIndex] : [];

        $dateValue = static function (string $key, ?string $legacyKey = null) use ($data, $existing): ?string {
            if (array_key_exists($key, $data)) {
                return $data[$key] === '' ? null : $data[$key];
            }

            return $existing[$key]
                ?? ($legacyKey !== null ? ($existing[$legacyKey] ?? null) : null);
        };

        // Build normalized record according to Section 15 of PRD (23 active fields)
        $record = [
            'id' => $id,
            'jo_no' => (string) $joNo,
            'part_no' => (string) $partNo,
            'part_name' => (string) $partName,
            'customer' => $data['customer'] ?? $existing['customer'] ?? null,
            'model' => $data['model'] ?? $existing['model'] ?? null,
            'no_doc' => $data['no_doc'] ?? $existing['no_doc'] ?? null,
            'date_receive' => $dateValue('date_receive'),
            'material' => $data['material'] ?? $existing['material'] ?? null,
            'spec' => $data['spec'] ?? $existing['spec'] ?? null,
            'material_thickness_raw' => $data['material_thickness_raw'] ?? $existing['material_thickness_raw'] ?? null,
            'material_width_raw' => $data['material_width_raw'] ?? $existing['material_width_raw'] ?? null,
            'process' => $data['process'] ?? $existing['process'] ?? null,
            't0_target' => $dateValue('t0_target', 't0_target_date'),
            't0_actual' => $dateValue('t0_actual', 't0_actual_date'),
            't0_target_date' => $dateValue('t0_target', 't0_target_date'),
            't0_actual_date' => $dateValue('t0_actual', 't0_actual_date'),
            'plan_start' => $dateValue('plan_start'),
            'plan_finish' => $dateValue('plan_finish'),
            'actual_start' => $dateValue('actual_start'),
            'actual_finish' => $dateValue('actual_finish'),
            'event_1' => $dateValue('event_1', 'event_1_date'),
            'event_2' => $dateValue('event_2', 'event_2_date'),
            'event_3' => $dateValue('event_3', 'event_3_date'),
            'event_1_date' => $dateValue('event_1', 'event_1_date'),
            'event_2_date' => $dateValue('event_2', 'event_2_date'),
            'event_3_date' => $dateValue('event_3', 'event_3_date'),
            'dh' => $data['dh'] ?? $existing['dh'] ?? null,
            'size' => $data['size'] ?? $existing['size'] ?? null,
            'qty' => $data['qty'] ?? $existing['qty'] ?? null,
            'remark' => $data['remark'] ?? $existing['remark'] ?? null,
        ];

        if ($existingIndex !== null) {
            $projects[$existingIndex] = $record;
        } else {
            $projects[] = $record;
        }

        $store['projects'] = $projects;
        static::saveStore($store);

        return $record;
    }

    /*
    |--------------------------------------------------------------------------
    | Progress Operations
    |--------------------------------------------------------------------------
    */

    /**
     * Get unique customer names sorted alphabetically.
     *
     * @return array<int, string>
     */
    public static function getCustomers(): array
    {
        $customers = [];
        foreach (static::getProjects() as $p) {
            if (! empty($p['customer']) && ! in_array($p['customer'], $customers, true)) {
                $customers[] = $p['customer'];
            }
        }
        sort($customers);

        return $customers;
    }

    /**
     * Get all progresses with optional filters and sorting.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public static function getProgresses(array $filters = []): array
    {
        $progresses = static::all()['progresses'] ?? [];

        // Filter by project_id
        if (! empty($filters['project_id'])) {
            $progresses = array_filter($progresses, fn ($p) => $p['project_id'] === $filters['project_id']);
        }

        // Filter by customer
        if (! empty($filters['customer'])) {
            $targetCustomer = strtolower(trim($filters['customer']));
            $progresses = array_filter($progresses, function ($p) use ($targetCustomer) {
                $project = static::findProject($p['project_id']);
                return strtolower(trim($project['customer'] ?? '')) === $targetCustomer;
            });
        }

        // Filter by activity (Trial, Sent Sample, Send Document, Audit, Others)
        if (! empty($filters['activity']) && ! in_array(strtolower($filters['activity']), ['all', 'semua'], true)) {
            $progresses = array_filter($progresses, fn ($p) => strtolower($p['activity']) === strtolower($filters['activity']));
        }

        // Filter by status (Open / Closed). If empty or 'all'/'semua', do not filter.
        if (! empty($filters['status']) && ! in_array(strtolower($filters['status']), ['all', 'semua'], true)) {
            $progresses = array_filter($progresses, fn ($p) => strtolower($p['status']) === strtolower($filters['status']));
        }

        // Filter by search keyword (JO, Part No, Part Name, Customer, Notes, Problems)
        if (! empty($filters['search'])) {
            $keyword = strtolower(trim($filters['search']));
            $progresses = array_filter($progresses, function ($p) use ($keyword, $filters) {
                $project = static::findProject($p['project_id']);
                $joNo = strtolower($project['jo_no'] ?? '');
                $partNo = strtolower($project['part_no'] ?? '');
                $partName = strtolower($project['part_name'] ?? '');
                if (! empty($filters['identity_only'])) {
                    return str_contains(implode(' ', [$joNo, $partNo, $partName]), $keyword);
                }
                $customer = strtolower($project['customer'] ?? '');
                $notes = strtolower($p['notes'] ?? '');

                if (str_contains($joNo, $keyword) ||
                    str_contains($partNo, $keyword) ||
                    str_contains($partName, $keyword) ||
                    str_contains($customer, $keyword) ||
                    str_contains($notes, $keyword)) {
                    return true;
                }

                // Check problem descriptions
                foreach ($p['problems'] ?? [] as $prob) {
                    if (str_contains(strtolower($prob['description'] ?? ''), $keyword)) {
                        return true;
                    }
                }

                return false;
            });
        }

        // Filter by from date (YYYY-MM-DD)
        if (! empty($filters['from'])) {
            $progresses = array_filter($progresses, function ($p) use ($filters) {
                return ! empty($p['activity_date']) && $p['activity_date'] >= $filters['from'];
            });
        }

        // Filter by to date (YYYY-MM-DD)
        if (! empty($filters['to'])) {
            $progresses = array_filter($progresses, function ($p) use ($filters) {
                return ! empty($p['activity_date']) && $p['activity_date'] <= $filters['to'];
            });
        }

        // Filter by PIC and PIC Role (FR18, AT24) - Guarantees unique progress records
        if (! empty($filters['pic'])) {
            $picKw = strtolower(trim($filters['pic']));
            $role = strtolower(trim($filters['pic_role'] ?? 'all'));

            $progresses = array_filter($progresses, function ($p) use ($picKw, $role) {
                $matchProgress = str_contains(strtolower($p['pic_progress'] ?? ''), $picKw);
                $matchCustomer = str_contains(strtolower($p['pic_customer'] ?? ''), $picKw);

                $matchCorrective = false;
                foreach ($p['problems'] ?? [] as $prob) {
                    foreach ($prob['corrective_actions'] ?? [] as $act) {
                        if (str_contains(strtolower($act['pic'] ?? ''), $picKw)) {
                            $matchCorrective = true;
                            break 2;
                        }
                    }
                }

                $matchNextAction = false;
                foreach ($p['next_actions'] ?? [] as $na) {
                    if (str_contains(strtolower($na['pic'] ?? ''), $picKw)) {
                        $matchNextAction = true;
                        break;
                    }
                }

                $matchFeedback = false;
                foreach ($p['feedbacks'] ?? [] as $fb) {
                    if (str_contains(strtolower($fb['pic'] ?? ''), $picKw)) {
                        $matchFeedback = true;
                        break;
                    }
                }

                return match ($role) {
                    'progress' => $matchProgress,
                    'customer' => $matchCustomer,
                    'corrective', 'corrective_action' => $matchCorrective,
                    'next', 'next_action' => $matchNextAction,
                    'feedback' => $matchFeedback,
                    'all' => $matchProgress || $matchCustomer || $matchCorrective || $matchNextAction || $matchFeedback,
                    default => $matchProgress || $matchCustomer,
                };
            });
        }

        // Convert to indexed array
        $result = array_values($progresses);

        // Sort: default 'oldest' (FR18). Oldest activity_date first, null dates at the end, secondary order by ID
        $sort = strtolower(trim($filters['sort'] ?? 'oldest'));
        usort($result, function ($a, $b) use ($sort) {
            $dateA = $a['activity_date'] ?? null;
            $dateB = $b['activity_date'] ?? null;

            if (empty($dateA) && ! empty($dateB)) {
                return 1;
            }
            if (! empty($dateA) && empty($dateB)) {
                return -1;
            }
            if (empty($dateA) && empty($dateB)) {
                return strcmp($a['id'] ?? '', $b['id'] ?? '');
            }

            if ($sort === 'newest') {
                $cmp = strcmp($dateB, $dateA);
            } else {
                $cmp = strcmp($dateA, $dateB);
            }

            if ($cmp !== 0) {
                return $cmp;
            }

            return strcmp($a['id'] ?? '', $b['id'] ?? '');
        });

        // Enrich each progress with related project and safe fallbacks for convenient display
        foreach ($result as &$item) {
            $item['pic_progress'] = $item['pic_progress'] ?? '';
            $item['pic_customer'] = $item['pic_customer'] ?? null;
            $item['project'] = static::findProject($item['project_id']);
        }
        unset($item);

        return $result;
    }

    /**
     * Find a single progress by ID.
     *
     * @return array<string, mixed>|null
     */
    public static function findProgress(string $id): ?array
    {
        foreach (static::all()['progresses'] ?? [] as $progress) {
            if ($progress['id'] === $id) {
                // Enrich with project details and safe fallbacks
                $progress['pic_progress'] = $progress['pic_progress'] ?? '';
                $progress['pic_customer'] = $progress['pic_customer'] ?? null;
                $progress['project'] = static::findProject($progress['project_id']);
                return $progress;
            }
        }

        return null;
    }

    /**
     * Save (insert or update) a progress record.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function saveProgress(array $data): array
    {
        $store = static::all();
        $progresses = $store['progresses'] ?? [];

        $isNew = empty($data['id']);
        $id = $isNew ? 'prog-' . Str::lower(Str::random(6)) : $data['id'];

        if ($isNew) {
            $errors = [];
            if (empty(trim($data['project_id'] ?? ''))) {
                $errors['project_id'] = ['JO No wajib dipilih.'];
            }
            if (isset($data['activity_date']) && $data['activity_date'] === '') {
                $errors['activity_date'] = ['Tanggal Aktivitas wajib diisi.'];
            }
            $allowedActivities = ['Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'];
            if (! empty($data['activity']) && ! in_array($data['activity'], $allowedActivities, true)) {
                $errors['activity'] = ['Activity wajib dipilih dari salah satu: Trial, Sent Sample, Send Document, Audit, Others.'];
            }
            if (! empty($errors)) {
                throw \Illuminate\Validation\ValidationException::withMessages($errors);
            }
        }

        $existingIndex = null;
        if (! $isNew) {
            foreach ($progresses as $idx => $p) {
                if ($p['id'] === $id) {
                    $existingIndex = $idx;
                    break;
                }
            }
        }

        $existing = $existingIndex !== null ? $progresses[$existingIndex] : [];

        // Build normalized progress record according to Section 16 of PRD
        $notesContent = $data['notes'] ?? $data['description'] ?? $existing['notes'] ?? '';
        $record = [
            'id' => $id,
            'project_id' => $data['project_id'] ?? $existing['project_id'] ?? '',
            'activity' => $data['activity'] ?? $existing['activity'] ?? 'Trial',
            'activity_date' => $data['activity_date'] ?? $existing['activity_date'] ?? date('Y-m-d'),
            'pic_progress' => $data['pic_progress'] ?? $existing['pic_progress'] ?? '',
            'pic_customer' => $data['pic_customer'] ?? $existing['pic_customer'] ?? null,
            'status' => $existing['status'] ?? 'Open',
            'notes' => $notesContent,
            'description' => $notesContent,
            'process_trial' => $data['process_trial'] ?? $existing['process_trial'] ?? null,
            'machine' => $data['machine'] ?? $existing['machine'] ?? null,
            'die_high_raw' => $data['die_high_raw'] ?? $data['dh'] ?? $existing['die_high_raw'] ?? $existing['dh'] ?? null,
            'material_actual_trial' => $data['material_actual_trial'] ?? $existing['material_actual_trial'] ?? null,
            'qty_material_raw' => $data['qty_material_raw'] ?? $existing['qty_material_raw'] ?? null,
            'qty_process_raw' => $data['qty_process_raw'] ?? $existing['qty_process_raw'] ?? null,
            'qty_sample_raw' => $data['qty_sample_raw'] ?? $existing['qty_sample_raw'] ?? null,
            'event_note' => $data['event_note'] ?? $existing['event_note'] ?? null,
            'remark' => $data['remark'] ?? $existing['remark'] ?? null,
            'revision' => ($existing['revision'] ?? 0) + 1,
            'problems' => static::normalizeProblems($data['problems'] ?? $existing['problems'] ?? []),
            'next_actions' => static::normalizeNextActions($data['next_actions'] ?? $existing['next_actions'] ?? []),
            'feedbacks' => static::normalizeFeedbacks($data['feedbacks'] ?? $existing['feedbacks'] ?? []),
            'attachments' => $data['attachments'] ?? $existing['attachments'] ?? [],
        ];

        if ($existingIndex !== null) {
            $progresses[$existingIndex] = $record;
        } else {
            $progresses[] = $record;
        }

        $store['progresses'] = $progresses;
        static::saveStore($store);

        return $record;
    }

    /**
     * Close a progress manually.
     * Per D10: Close is an explicit admin action.
     *
     * @return array<string, mixed>|null
     */
    public static function closeProgress(string $id): ?array
    {
        $store = static::all();
        foreach ($store['progresses'] as $idx => $p) {
            if ($p['id'] === $id) {
                $store['progresses'][$idx]['status'] = 'Closed';
                $store['progresses'][$idx]['revision'] = ($p['revision'] ?? 1) + 1;
                static::saveStore($store);
                return $store['progresses'][$idx];
            }
        }

        return null;
    }

    /**
     * Reopen a closed progress.
     * Per D10: Reopen preserves all contents, actual dates, problems, actions, and photos.
     *
     * @return array<string, mixed>|null
     */
    public static function reopenProgress(string $id): ?array
    {
        $store = static::all();
        foreach ($store['progresses'] as $idx => $p) {
            if ($p['id'] === $id) {
                $store['progresses'][$idx]['status'] = 'Open';
                $store['progresses'][$idx]['revision'] = ($p['revision'] ?? 1) + 1;
                static::saveStore($store);
                return $store['progresses'][$idx];
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Draft Operations
    |--------------------------------------------------------------------------
    */

    /**
     * Get all drafts.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getDrafts(): array
    {
        return static::all()['drafts'] ?? [];
    }

    /**
     * Find draft by UUID.
     *
     * @return array<string, mixed>|null
     */
    public static function findDraft(string $uuid): ?array
    {
        foreach (static::getDrafts() as $draft) {
            if ($draft['uuid'] === $uuid) {
                return $draft;
            }
        }

        return null;
    }

    /**
     * Save draft payload into store.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public static function saveDraft(array $payload): array
    {
        $store = static::all();
        $drafts = $store['drafts'] ?? [];

        $uuid = $payload['uuid'] ?? (string) Str::uuid();
        $payload['uuid'] = $uuid;
        $payload['updated_at'] = now()->toIso8601String();

        $existingIdx = null;
        foreach ($drafts as $idx => $d) {
            if ($d['uuid'] === $uuid) {
                $existingIdx = $idx;
                break;
            }
        }

        if ($existingIdx !== null) {
            $drafts[$existingIdx] = array_merge($drafts[$existingIdx], $payload);
        } else {
            $drafts[] = $payload;
        }

        $store['drafts'] = $drafts;
        static::saveStore($store);

        return $payload;
    }

    /**
     * Delete draft by UUID.
     */
    public static function deleteDraft(string $uuid): bool
    {
        $store = static::all();
        $initialCount = count($store['drafts'] ?? []);

        $store['drafts'] = array_values(array_filter(
            $store['drafts'] ?? [],
            fn ($d) => $d['uuid'] !== $uuid
        ));

        if (count($store['drafts']) < $initialCount) {
            static::saveStore($store);
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    /**
     * Get counts summary.
     * Per D11: Draft does not count towards Open or Closed.
     *
     * @return array<string, int>
     */
    public static function getStats(): array
    {
        $progresses = static::all()['progresses'] ?? [];

        $openCount = 0;
        $closedCount = 0;

        foreach ($progresses as $p) {
            if (strtolower($p['status'] ?? '') === 'open') {
                $openCount++;
            } elseif (strtolower($p['status'] ?? '') === 'closed') {
                $closedCount++;
            }
        }

        return [
            'total_projects' => count(static::getProjects()),
            'total_progresses' => count($progresses),
            'open_count' => $openCount,
            'closed_count' => $closedCount,
            'draft_count' => count(static::getDrafts()),
        ];
    }

    /**
     * Get global dashboard summary stats according to FR17 & AT23.
     * Returns:
     * - open_count: Progress Open tersimpan (excluding drafts)
     * - draft_count: Draft aktif (new and edit)
     * - current_month_count: Aktivitas tersimpan dalam bulan kalender Asia/Jakarta (Open + Closed, excluding drafts, excluding legacy null dates)
     * - current_month_label: Label periode kalender (e.g. "Oktober 2026")
     * - year_month: Format 'Y-m' yang dievaluasi
     *
     * @param string|null $yearMonth Format 'Y-m' (defaults to Asia/Jakarta current month)
     * @return array<string, mixed>
     */
    public static function getGlobalStats(?string $yearMonth = null): array
    {
        $progresses = static::all()['progresses'] ?? [];
        $drafts = static::getDrafts();

        $openCount = 0;
        foreach ($progresses as $p) {
            if (strtolower($p['status'] ?? '') === 'open') {
                $openCount++;
            }
        }

        $draftCount = count($drafts);

        $now = \Illuminate\Support\Carbon::now('Asia/Jakarta')->locale('id');
        $targetYm = $yearMonth ?: $now->format('Y-m');

        if ($yearMonth) {
            $parsed = \Illuminate\Support\Carbon::createFromFormat('Y-m', $yearMonth, 'Asia/Jakarta');
            $monthLabel = $parsed ? $parsed->locale('id')->translatedFormat('F Y') : $yearMonth;
        } else {
            $monthLabel = $now->translatedFormat('F Y');
        }

        $currentMonthCount = 0;
        foreach ($progresses as $p) {
            $date = $p['activity_date'] ?? null;
            if (! empty($date) && str_starts_with($date, $targetYm)) {
                $currentMonthCount++;
            }
        }

        return [
            'open_count' => $openCount,
            'draft_count' => $draftCount,
            'current_month_count' => $currentMonthCount,
            'current_month_label' => $monthLabel,
            'year_month' => $targetYm,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Normalization Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Normalize problems array and their nested corrective actions.
     *
     * @param array<int, mixed> $problems
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeProblems(array $problems): array
    {
        $normalized = [];

        foreach ($problems as $i => $prob) {
            $probId = ! empty($prob['id']) ? $prob['id'] : 'prob-' . Str::lower(Str::random(5));
            $actions = [];

            foreach ($prob['corrective_actions'] ?? [] as $j => $act) {
                $actions[] = [
                    'id' => ! empty($act['id']) ? $act['id'] : 'act-' . Str::lower(Str::random(5)),
                    'description' => $act['description'] ?? '',
                    'pic' => $act['pic'] ?? '',
                    'actual_date' => $act['actual_date'] ?? null,
                    'order' => $act['order'] ?? ($j + 1),
                ];
            }

            $normalized[] = [
                'id' => $probId,
                'description' => $prob['description'] ?? '',
                'order' => $prob['order'] ?? ($i + 1),
                'photos' => $prob['photos'] ?? [],
                'corrective_actions' => $actions,
            ];
        }

        return $normalized;
    }

    /**
     * Normalize next actions array.
     *
     * @param array<int, mixed> $nextActions
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeNextActions(array $nextActions): array
    {
        $normalized = [];

        foreach ($nextActions as $i => $na) {
            $normalized[] = [
                'id' => ! empty($na['id']) ? $na['id'] : 'next-' . Str::lower(Str::random(5)),
                'description' => $na['description'] ?? '',
                'pic' => $na['pic'] ?? '',
                'actual_date' => $na['actual_date'] ?? null,
                'order' => $na['order'] ?? ($i + 1),
            ];
        }

        return $normalized;
    }

    /**
     * Normalize feedbacks array.
     *
     * @param array<int, mixed> $feedbacks
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeFeedbacks(array $feedbacks): array
    {
        $normalized = [];

        foreach ($feedbacks as $i => $fb) {
            $normalized[] = [
                'id' => ! empty($fb['id']) ? $fb['id'] : 'fb-' . Str::lower(Str::random(5)),
                'date' => $fb['date'] ?? null,
                'content' => $fb['content'] ?? '',
                'pic' => $fb['pic'] ?? '',
                'order' => $fb['order'] ?? ($i + 1),
                'files' => $fb['files'] ?? [],
            ];
        }

        return $normalized;
    }
}
