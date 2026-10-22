<?php

namespace App\Data;

use App\Models\Attachment;
use App\Models\CorrectiveAction;
use App\Models\Feedback;
use App\Models\NextAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class ProgressPayload
{
    public const VERSION = 1;

    public const ACTIVITIES = ['Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'];

    public const TEXT_FIELDS = ['description', 'pic_customer', 'process_trial', 'machine', 'tonage_raw', 'die_high_raw', 'material_actual_trial', 'qty_material_raw', 'qty_process_raw', 'qty_sample_raw', 'qty_ok_raw', 'qty_ng_raw', 'event_note', 'remark'];

    private function __construct(public array $data) {}

    public static function fromArray(array $input, bool $final = false, ?Progress $progress = null, ?ProgressDraft $draft = null, ?User $admin = null): self
    {
        $input = self::emptyDatesToNull($input);
        if ($final) {
            if (isset($input['problems']) && is_array($input['problems'])) {
                $input['problems'] = self::cleanProblems($input['problems']);
            }
            if (isset($input['next_actions']) && is_array($input['next_actions'])) {
                $input['next_actions'] = self::cleanNextActions($input['next_actions']);
            }
            if (isset($input['feedbacks']) && is_array($input['feedbacks'])) {
                $input['feedbacks'] = self::cleanFeedbacks($input['feedbacks']);
            }
        }
        $input['schema_version'] ??= self::VERSION;
        self::knownKeys($input, ['schema_version', 'project_id', 'activity_date', 'activity', ...self::TEXT_FIELDS, 'problems', 'next_actions', 'feedbacks', 'attachment_refs'], 'payload');
        $rules = [
            'schema_version' => ['required', 'integer', Rule::in([self::VERSION])],
            'project_id' => [$final ? 'required' : 'nullable', 'integer', 'exists:projects,id'],
            'activity_date' => [$final ? 'required' : 'nullable', 'date_format:Y-m-d'],
            'activity' => [$final ? 'required' : 'nullable', Rule::in(self::ACTIVITIES)],
        ];
        foreach (self::TEXT_FIELDS as $field) {
            $rules[$field] = ['nullable', 'string'];
        }
        foreach (['problems', 'next_actions', 'feedbacks', 'attachment_refs'] as $list) {
            $rules[$list] = ['sometimes', 'array', 'list'];
        }
        $rules['attachment_refs.*'] = ['required', 'uuid', 'distinct'];
        foreach (['problems', 'problems.*.corrective_actions', 'next_actions', 'feedbacks'] as $path) {
            $rules[$path] = ['sometimes', 'array', 'list'];
            $row = $path.'.*';
            $rules[$row] = ['required', 'array'];
            $rules[$row.'.id'] = ['nullable', 'integer', 'min:1'];
            $rules[$row.'.client_id'] = ['required', 'uuid'];
            $rules[$row.'.position'] = ['sometimes', 'integer', 'min:0'];
            $isFeedback = $path === 'feedbacks';
            $rules[$row.'.'.($isFeedback ? 'content' : 'description')] = ['nullable', 'string'];
            if ($path !== 'problems') {
                $rules[$row.'.pic'] = ['nullable', 'string'];
                $rules[$row.'.'.($isFeedback ? 'feedback_date' : 'actual_date')] = ['nullable', 'date_format:Y-m-d'];
            }
            if (in_array($path, ['problems', 'feedbacks'])) {
                $refs = $row.'.'.($isFeedback ? 'document_refs' : 'photo_refs');
                $rules[$refs] = ['sometimes', 'array', 'list'];
                $rules[$refs.'.*'] = ['required', 'uuid', 'distinct'];
            }
        }
        Validator::make($input, $rules, [
            'project_id.required' => 'Pilih JO No.', 'activity_date.required' => 'Isi tanggal aktivitas.',
            'activity.required' => 'Pilih Activity.', 'activity.in' => 'Activity tidak tersedia.',
        ])->validate();

        $seen = [];
        $seenRefs = [];
        $validateRow = function (array $row, string $path, string $model, ?int $parent, string $parentKey, array $keys) use (&$seen): void {
            self::knownKeys($row, ['id', 'client_id', 'position', ...$keys], $path);
            $client = strtolower($row['client_id']);
            if (isset($seen[$client])) {
                self::invalid($path.'.client_id', 'ID baris harus unik dan stabil.');
            }
            $seen[$client] = true;
            if (isset($row['id'])) {
                $stored = $parent === null ? null : $model::whereKey($row['id'])->where($parentKey, $parent)->first();
                if (! $stored || strtolower($stored->client_id) !== $client) {
                    self::invalid($path.'.id', 'Baris bukan milik induk ini atau client_id berubah.');
                }
            } elseif ($parent !== null && $model::where($parentKey, $parent)->where('client_id', $client)->exists()) {
                self::invalid($path.'.id', 'Baris tersimpan harus menyertakan ID database.');
            }
        };
        foreach ($input['problems'] ?? [] as $i => $problem) {
            $path = "problems.$i";
            $validateRow($problem, $path, Problem::class, $progress?->id, 'progress_id', ['description', 'corrective_actions', 'photo_refs']);
            self::validateRefs($problem['photo_refs'] ?? [], $path.'.photo_refs', 'problem', $problem['id'] ?? null, $problem['client_id'], 'photo', $draft, $admin, $seenRefs);
            foreach ($problem['corrective_actions'] ?? [] as $j => $action) {
                $validateRow($action, "$path.corrective_actions.$j", CorrectiveAction::class, $problem['id'] ?? null, 'problem_id', ['description', 'pic', 'actual_date']);
            }
        }
        foreach (['next_actions' => NextAction::class, 'feedbacks' => Feedback::class] as $key => $class) {
            foreach ($input[$key] ?? [] as $i => $row) {
                $fields = $key === 'feedbacks' ? ['content', 'pic', 'feedback_date', 'document_refs'] : ['description', 'pic', 'actual_date'];
                $validateRow($row, "$key.$i", $class, $progress?->id, 'progress_id', $fields);
                if ($key === 'feedbacks') {
                    self::validateRefs($row['document_refs'] ?? [], "$key.$i.document_refs", 'feedback', $row['id'] ?? null, $row['client_id'], 'document', $draft, $admin, $seenRefs);
                }
            }
        }
        self::validateRefs($input['attachment_refs'] ?? [], 'attachment_refs', 'progress', $progress?->id, null, null, $draft, $admin, $seenRefs);

        return new self($input);
    }

    /**
     * Abaikan baris problem baru yang seluruhnya kosong.
     * Pertahankan item tersimpan (id != null) atau item yang memiliki foto/tindakan walaupun uraian kosong.
     */
    public static function cleanProblems(array $problems): array
    {
        $hasText = static fn ($val): bool => is_string($val) && trim($val) !== '';
        $allowedProblemKeys = ['id', 'client_id', 'position', 'description', 'corrective_actions', 'photo_refs'];
        $allowedActionKeys = ['id', 'client_id', 'position', 'description', 'pic', 'actual_date'];
        $cleaned = [];

        foreach ($problems as $problem) {
            if (! is_array($problem)) {
                continue;
            }

            $isStoredProblem = ! empty($problem['id']);

            if (isset($problem['corrective_actions']) && is_array($problem['corrective_actions'])) {
                $cleanedActions = [];
                foreach ($problem['corrective_actions'] as $action) {
                    if (! is_array($action)) {
                        continue;
                    }
                    $isStoredAction = ! empty($action['id']);
                    $actionFilled = $hasText($action['description'] ?? null)
                        || $hasText($action['pic'] ?? null)
                        || ! empty($action['actual_date']);
                    $hasUnknownActionKeys = count(array_diff(array_keys($action), $allowedActionKeys)) > 0;

                    if ($isStoredAction || $actionFilled || $hasUnknownActionKeys) {
                        $action['position'] = count($cleanedActions);
                        $cleanedActions[] = $action;
                    }
                }
                $problem['corrective_actions'] = $cleanedActions;
            }

            $problemFilled = $hasText($problem['description'] ?? null)
                || ! empty($problem['photo_refs'])
                || ! empty($problem['corrective_actions']);
            $hasUnknownProblemKeys = count(array_diff(array_keys($problem), $allowedProblemKeys)) > 0;

            if ($isStoredProblem || $problemFilled || $hasUnknownProblemKeys) {
                $problem['position'] = count($cleaned);
                $cleaned[] = $problem;
            }
        }

        return $cleaned;
    }

    /**
     * Bersihkan baris Next Action yang seluruhnya kosong dari payload final.
     */
    public static function cleanNextActions(array $actions): array
    {
        $hasText = static fn ($val): bool => is_string($val) && trim($val) !== '';
        $allowedKeys = ['id', 'client_id', 'position', 'description', 'pic', 'actual_date'];
        $cleaned = [];

        foreach ($actions as $action) {
            if (! is_array($action)) {
                continue;
            }

            $isStored = ! empty($action['id']);
            $isFilled = $hasText($action['description'] ?? null)
                || $hasText($action['pic'] ?? null)
                || ! empty($action['actual_date']);
            $hasUnknownKeys = count(array_diff(array_keys($action), $allowedKeys)) > 0;

            if ($isStored || $isFilled || $hasUnknownKeys) {
                $action['position'] = count($cleaned);
                $cleaned[] = $action;
            }
        }

        return $cleaned;
    }

    /**
     * Bersihkan baris Feedback yang seluruhnya kosong dari payload final.
     */
    public static function cleanFeedbacks(array $feedbacks): array
    {
        $hasText = static fn ($val): bool => is_string($val) && trim($val) !== '';
        $allowedKeys = ['id', 'client_id', 'position', 'content', 'pic', 'feedback_date', 'document_refs'];
        $cleaned = [];

        foreach ($feedbacks as $fb) {
            if (! is_array($fb)) {
                continue;
            }

            $isStored = ! empty($fb['id']);
            $isFilled = $hasText($fb['content'] ?? null)
                || $hasText($fb['pic'] ?? null)
                || ! empty($fb['feedback_date'])
                || ! empty($fb['document_refs']);
            $hasUnknownKeys = count(array_diff(array_keys($fb), $allowedKeys)) > 0;

            if ($isStored || $isFilled || $hasUnknownKeys) {
                $fb['position'] = count($cleaned);
                $cleaned[] = $fb;
            }
        }

        return $cleaned;
    }

    private static function validateRefs(array $refs, string $path, string $owner, ?int $storedId, ?string $clientId, ?string $kind, ?ProgressDraft $draft, ?User $admin, array &$seen): void
    {
        foreach ($refs as $i => $id) {
            $id = strtolower($id);
            $file = Attachment::find($id);
            $valid = ! isset($seen[$id]) && $admin?->admin_slot === true && $file && (int) $file->uploaded_by === (int) $admin->id;
            if ($valid) {
                $valid = $file->owner_kind === $owner && ($kind === null || $file->kind === $kind);
                if ($file->state === 'staging') {
                    $valid = $valid && $draft && $draft->state === 'active' && (int) $draft->admin_id === (int) $admin->id
                        && $file->draft_id === $draft->id && $file->owner_client_id === $clientId;
                } else {
                    $valid = $valid && $storedId !== null && (int) $file->{$owner.'_id'} === $storedId;
                }
            }
            if (! $valid) {
                self::invalid("$path.$i", 'Lampiran bukan milik baris atau draft ini.');
            }
            $seen[$id] = true;
        }
    }

    private static function knownKeys(array $input, array $keys, string $path): void
    {
        if ($unknown = array_diff(array_keys($input), $keys)) {
            self::invalid($path, 'Field tidak dikenal: '.implode(', ', $unknown));
        }
    }

    private static function emptyDatesToNull(array $input): array
    {
        foreach ($input as $key => &$value) {
            if (is_array($value)) {
                $value = self::emptyDatesToNull($value);
            } elseif (in_array($key, ['activity_date', 'actual_date', 'feedback_date']) && $value === '') {
                $value = null;
            }
        }

        return $input;
    }

    private static function invalid(string $path, string $message): never
    {
        throw ValidationException::withMessages([$path => $message]);
    }
}