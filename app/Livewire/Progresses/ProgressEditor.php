<?php

namespace App\Livewire\Progresses;

use App\Actions\ApplyProgressDraft;
use App\Actions\SaveProgress;
use App\Exceptions\VersionConflict;
use App\Livewire\Forms\ProgressFormData;
use App\Models\Attachment;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\User;
use App\Services\AttachmentService;
use App\Services\ProgressDraftService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProgressEditor extends Component
{
    use WithFileUploads;

    public ?int $progressId = null;

    public ?Progress $progress = null;

    public ?int $projectId = null;

    // Autosave Draft State (Fase 05)
    public ?string $draftId = null;

    public ?int $draftRevision = null;

    public int $requestSequence = 0;

    public string $saveStatus = 'unsaved'; // 'unsaved', 'saving', 'saved', 'error', 'conflict'

    public ?string $lastSavedAt = null;

    public bool $hasConflict = false;

    public ?string $conflictMessage = null;

    public bool $isDirty = false;

    public bool $simulateSaveError = false;

    // Aktivitas
    public ?string $activityDate = null;

    public string $activity = 'Trial';

    public ?string $picCustomer = '';

    public ?string $description = '';

    // Rincian Trial
    public ?string $processTrial = '';

    public ?string $machine = '';

    public ?string $tonageRaw = '';

    public ?string $dieHighRaw = '';

    public ?string $materialActualTrial = '';

    public ?string $qtyMaterialRaw = '';

    public ?string $qtyProcessRaw = '';

    public ?string $qtySampleRaw = '';

    public ?string $qtyOkRaw = '';

    public ?string $qtyNgRaw = '';

    public ?string $eventNote = '';

    // Catatan
    public ?string $remark = '';

    // Graph Anak Fase 04
    public array $problems = [];

    public array $nextActions = [];

    public array $feedbacks = [];

    public array $documents = [];

    public array $documentRefs = [];

    public array $legacyDocuments = [];

    public $pendingProgressDocuments = [];

    public array $pendingFeedbackDocuments = [];

    public array $pendingPhotos = [];

    public ?array $activePhoto = null;

    public ?array $pendingRemoval = null;

    public bool $isClosed = false;

    protected $listeners = [
        'project-selected' => 'onProjectSelected',
    ];

    public function mount(?int $projectId = null, ?int $progressId = null, ?string $draft = null): void
    {
        $this->projectId = $projectId;
        $this->progressId = $progressId;
        $draftId = $draft ?? request()->query('draft');

        $admin = auth()->user();
        $draftService = app(ProgressDraftService::class);

        // Jika ada parameter draft eksplisit, muat dari draft tersebut
        if ($draftId && $admin) {
            $draftModel = $draftService->getDraft($admin, $draftId);
            if ($draftModel) {
                $this->restoreFromDraft($draftModel);

                return;
            }
        }

        // Jika mengedit progress yang sudah ada, cek apakah ada draft edit aktif untuk admin ini
        if ($this->progressId && $admin) {
            $activeDraft = $draftService->getActiveEditDraft($admin, $this->progressId);
            if ($activeDraft) {
                $this->restoreFromDraft($activeDraft);

                return;
            }

            $this->loadFromProgress($this->progressId);

            return;
        }

        // Form baru kosong (tidak membuat row di progress_drafts pada mount)
        if (! $this->isClosed) {
            $this->ensureInitialRows();
        }
    }

    private function restoreFromDraft(ProgressDraft $draftModel): void
    {
        $this->draftId = $draftModel->id;
        $this->draftRevision = $draftModel->draft_revision;
        $this->requestSequence = $draftModel->request_sequence;
        $this->saveStatus = 'saved';
        $this->lastSavedAt = $draftModel->updated_at?->format('H:i');

        if ($draftModel->progress_id) {
            $this->progressId = $draftModel->progress_id;
            $this->progress = Progress::with([
                'project',
                'problems.correctiveActions',
                'problems.attachments',
                'nextActions',
                'feedbacks.attachments',
                'feedbacks.legacyDocuments',
                'attachments',
                'legacyDocuments',
            ])->find($this->progressId);

            $this->isClosed = ($this->progress?->status === 'Closed');
            $this->legacyDocuments = $this->progress?->legacyDocuments->values()->map(fn ($leg) => [
                'id' => $leg->id,
                'source_field' => $leg->source_field,
                'name' => (string) ($leg->name ?? ''),
                'description' => (string) ($leg->description ?? ''),
            ])->all() ?? [];
        }

        $payload = $draftModel->payload ?? [];
        $this->projectId = $payload['project_id'] ?? $this->projectId;
        $this->activityDate = $payload['activity_date'] ?? null;
        $this->activity = $payload['activity'] ?? 'Trial';
        $this->picCustomer = $payload['pic_customer'] ?? '';
        $this->description = $payload['description'] ?? '';
        $this->processTrial = $payload['process_trial'] ?? '';
        $this->machine = $payload['machine'] ?? '';
        $this->tonageRaw = $payload['tonage_raw'] ?? '';
        $this->dieHighRaw = $payload['die_high_raw'] ?? '';
        $this->materialActualTrial = $payload['material_actual_trial'] ?? '';
        $this->qtyMaterialRaw = $payload['qty_material_raw'] ?? '';
        $this->qtyProcessRaw = $payload['qty_process_raw'] ?? '';
        $this->qtySampleRaw = $payload['qty_sample_raw'] ?? '';
        $this->qtyOkRaw = $payload['qty_ok_raw'] ?? '';
        $this->qtyNgRaw = $payload['qty_ng_raw'] ?? '';
        $this->eventNote = $payload['event_note'] ?? '';
        $this->remark = $payload['remark'] ?? '';

        // Restore Problems
        $this->problems = [];
        foreach ($payload['problems'] ?? [] as $idx => $p) {
            $photoRefs = $p['photo_refs'] ?? [];
            $photos = [];
            if (! empty($photoRefs)) {
                $attachments = Attachment::whereIn('id', $photoRefs)->get();
                foreach ($attachments as $att) {
                    $photos[] = [
                        'id' => $att->id,
                        'original_name' => $att->original_name,
                        'caption' => (string) ($att->caption ?? ''),
                        'thumbnail_url' => route('attachments.thumbnail', $att),
                        'preview_url' => route('attachments.preview', $att),
                        'download_url' => route('attachments.download', $att),
                        'is_staging' => ($att->state === 'staging'),
                    ];
                }
            }

            $actions = [];
            foreach ($p['corrective_actions'] ?? [] as $aIdx => $a) {
                $actions[] = [
                    'id' => $a['id'] ?? null,
                    'client_id' => strtolower((string) ($a['client_id'] ?? Str::uuid())),
                    'description' => (string) ($a['description'] ?? ''),
                    'pic' => (string) ($a['pic'] ?? ''),
                    'actual_date' => $a['actual_date'] ?? null,
                    'position' => $aIdx,
                ];
            }

            $this->problems[] = [
                'id' => $p['id'] ?? null,
                'client_id' => strtolower((string) ($p['client_id'] ?? Str::uuid())),
                'description' => (string) ($p['description'] ?? ''),
                'position' => $idx,
                'photos' => $photos,
                'photo_refs' => $photoRefs,
                'corrective_actions' => $actions,
            ];
        }

        // Restore Next Actions
        $this->nextActions = [];
        foreach ($payload['next_actions'] ?? [] as $idx => $a) {
            $this->nextActions[] = [
                'id' => $a['id'] ?? null,
                'client_id' => strtolower((string) ($a['client_id'] ?? Str::uuid())),
                'description' => (string) ($a['description'] ?? ''),
                'pic' => (string) ($a['pic'] ?? ''),
                'actual_date' => $a['actual_date'] ?? null,
                'position' => $idx,
            ];
        }

        // Restore Feedbacks
        $this->feedbacks = [];
        foreach ($payload['feedbacks'] ?? [] as $idx => $fb) {
            $docRefs = $fb['document_refs'] ?? [];
            $docs = [];
            if (! empty($docRefs)) {
                $attachments = Attachment::whereIn('id', $docRefs)->get();
                foreach ($attachments as $att) {
                    $docs[] = [
                        'id' => $att->id,
                        'original_name' => $att->original_name,
                        'caption' => (string) ($att->caption ?? ''),
                        'size_bytes' => $att->size_bytes,
                        'preview_url' => route('attachments.preview', $att),
                        'download_url' => route('attachments.download', $att),
                        'is_staging' => ($att->state === 'staging'),
                    ];
                }
            }

            $this->feedbacks[] = [
                'id' => $fb['id'] ?? null,
                'client_id' => strtolower((string) ($fb['client_id'] ?? Str::uuid())),
                'content' => (string) ($fb['content'] ?? ''),
                'pic' => (string) ($fb['pic'] ?? ''),
                'feedback_date' => $fb['feedback_date'] ?? null,
                'position' => $idx,
                'documents' => $docs,
                'document_refs' => $docRefs,
                'legacy_documents' => [],
            ];
        }

        // Restore Progress Documents
        $this->documentRefs = $payload['attachment_refs'] ?? [];
        $this->documents = [];
        if (! empty($this->documentRefs)) {
            $attachments = Attachment::whereIn('id', $this->documentRefs)->get();
            foreach ($attachments as $att) {
                $this->documents[] = [
                    'id' => $att->id,
                    'original_name' => $att->original_name,
                    'caption' => (string) ($att->caption ?? ''),
                    'size_bytes' => $att->size_bytes,
                    'preview_url' => route('attachments.preview', $att),
                    'download_url' => route('attachments.download', $att),
                    'is_staging' => ($att->state === 'staging'),
                ];
            }
        }

        if (! $this->isClosed) {
            $this->ensureInitialRows();
        }
    }

    private function loadFromProgress(int $progressId): void
    {
        $this->progress = Progress::with([
            'project',
            'problems.correctiveActions',
            'problems.attachments',
            'nextActions',
            'feedbacks.attachments',
            'feedbacks.legacyDocuments',
            'attachments',
            'legacyDocuments',
        ])->find($progressId);

        if (! $this->progress) {
            return;
        }

        $this->projectId = $this->progress->project_id;
        $this->activityDate = $this->progress->activity_date?->format('Y-m-d');
        $this->activity = $this->progress->activity ?? 'Trial';
        $this->picCustomer = $this->progress->pic_customer ?? '';
        $this->description = $this->progress->description ?? '';
        $this->processTrial = $this->progress->process_trial ?? '';
        $this->machine = $this->progress->machine ?? '';
        $this->tonageRaw = $this->progress->tonage_raw ?? '';
        $this->dieHighRaw = $this->progress->die_high_raw ?? '';
        $this->materialActualTrial = $this->progress->material_actual_trial ?? '';
        $this->qtyMaterialRaw = $this->progress->qty_material_raw ?? '';
        $this->qtyProcessRaw = $this->progress->qty_process_raw ?? '';
        $this->qtySampleRaw = $this->progress->qty_sample_raw ?? '';
        $this->qtyOkRaw = $this->progress->qty_ok_raw ?? '';
        $this->qtyNgRaw = $this->progress->qty_ng_raw ?? '';
        $this->eventNote = $this->eventNote ?? $this->progress->event_note ?? '';
        $this->remark = $this->progress->remark ?? '';
        $this->isClosed = ($this->progress->status === 'Closed');

        $this->problems = $this->progress->problems->values()->map(fn ($prob, $idx) => [
            'id' => $prob->id,
            'client_id' => strtolower((string) $prob->client_id),
            'description' => (string) ($prob->description ?? ''),
            'position' => $idx,
            'photos' => $prob->attachments->where('kind', 'photo')->values()->map(fn ($att) => [
                'id' => $att->id,
                'original_name' => $att->original_name,
                'caption' => (string) ($att->caption ?? ''),
                'thumbnail_url' => route('attachments.thumbnail', $att),
                'preview_url' => route('attachments.preview', $att),
                'download_url' => route('attachments.download', $att),
                'is_staging' => $att->state === 'staging',
            ])->all(),
            'photo_refs' => $prob->attachments->where('kind', 'photo')->pluck('id')->all(),
            'corrective_actions' => $prob->correctiveActions->values()->map(fn ($act, $aIdx) => [
                'id' => $act->id,
                'client_id' => strtolower((string) $act->client_id),
                'description' => (string) ($act->description ?? ''),
                'pic' => (string) ($act->pic ?? ''),
                'actual_date' => $act->actual_date?->format('Y-m-d'),
                'position' => $aIdx,
            ])->all(),
        ])->all();

        $this->nextActions = $this->progress->nextActions->values()->map(fn ($act, $idx) => [
            'id' => $act->id,
            'client_id' => strtolower((string) $act->client_id),
            'description' => (string) ($act->description ?? ''),
            'pic' => (string) ($act->pic ?? ''),
            'actual_date' => $act->actual_date?->format('Y-m-d'),
            'position' => $idx,
        ])->all();

        $this->feedbacks = $this->progress->feedbacks->values()->map(fn ($fb, $idx) => [
            'id' => $fb->id,
            'client_id' => strtolower((string) $fb->client_id),
            'content' => (string) ($fb->content ?? ''),
            'pic' => (string) ($fb->pic ?? ''),
            'feedback_date' => $fb->feedback_date?->format('Y-m-d'),
            'position' => $idx,
            'documents' => $fb->attachments->where('kind', 'document')->values()->map(fn ($att) => [
                'id' => $att->id,
                'original_name' => $att->original_name,
                'caption' => (string) ($att->caption ?? ''),
                'size_bytes' => $att->size_bytes,
                'preview_url' => route('attachments.preview', $att),
                'download_url' => route('attachments.download', $att),
                'is_staging' => $att->state === 'staging',
            ])->all(),
            'document_refs' => $fb->attachments->where('kind', 'document')->pluck('id')->all(),
            'legacy_documents' => $fb->legacyDocuments->values()->map(fn ($leg) => [
                'id' => $leg->id,
                'source_field' => $leg->source_field,
                'name' => (string) ($leg->name ?? ''),
                'description' => (string) ($leg->description ?? ''),
            ])->all(),
        ])->all();

        $this->documents = $this->progress->attachments->where('kind', 'document')->values()->map(fn ($att) => [
            'id' => $att->id,
            'original_name' => $att->original_name,
            'caption' => (string) ($att->caption ?? ''),
            'size_bytes' => $att->size_bytes,
            'preview_url' => route('attachments.preview', $att),
            'download_url' => route('attachments.download', $att),
            'is_staging' => $att->state === 'staging',
        ])->all();
        $this->documentRefs = $this->progress->attachments->where('kind', 'document')->pluck('id')->all();
        $this->legacyDocuments = $this->progress->legacyDocuments->values()->map(fn ($leg) => [
            'id' => $leg->id,
            'source_field' => $leg->source_field,
            'name' => (string) ($leg->name ?? ''),
            'description' => (string) ($leg->description ?? ''),
        ])->all();

        if (! $this->isClosed) {
            $this->ensureInitialRows();
        }
    }

    public function ensureInitialRows(): void
    {
        if (empty($this->problems)) {
            $this->problems[] = $this->makeBlankProblem(0);
        } else {
            foreach ($this->problems as &$prob) {
                if (empty($prob['corrective_actions'])) {
                    $prob['corrective_actions'][] = $this->makeBlankCorrectiveAction(0);
                }
            }
            unset($prob);
        }

        if (empty($this->nextActions)) {
            $this->nextActions[] = $this->makeBlankNextAction(0);
        }

        if (empty($this->feedbacks)) {
            $this->feedbacks[] = $this->makeBlankFeedback(0);
        }
    }

    private function makeBlankProblem(int $position = 0): array
    {
        return [
            'id' => null,
            'client_id' => (string) Str::uuid(),
            'description' => '',
            'position' => $position,
            'photos' => [],
            'photo_refs' => [],
            'corrective_actions' => [
                $this->makeBlankCorrectiveAction(0),
            ],
        ];
    }

    private function makeBlankCorrectiveAction(int $position = 0): array
    {
        return [
            'id' => null,
            'client_id' => (string) Str::uuid(),
            'description' => '',
            'pic' => '',
            'actual_date' => null,
            'position' => $position,
        ];
    }

    private function makeBlankNextAction(int $position = 0): array
    {
        return [
            'id' => null,
            'client_id' => (string) Str::uuid(),
            'description' => '',
            'pic' => '',
            'actual_date' => null,
            'position' => $position,
        ];
    }

    private function makeBlankFeedback(int $position = 0): array
    {
        return [
            'id' => null,
            'client_id' => (string) Str::uuid(),
            'content' => '',
            'pic' => '',
            'feedback_date' => null,
            'position' => $position,
            'documents' => [],
            'document_refs' => [],
            'legacy_documents' => [],
        ];
    }

    public function onProjectSelected(?int $projectId): void
    {
        $this->projectId = $projectId;
        $this->performAutosave();
    }

    public function addProblem(): void
    {
        if ($this->isClosed) {
            return;
        }

        $nextIndex = count($this->problems);
        $this->problems[] = $this->makeBlankProblem($nextIndex);
        $this->dispatch('editor-focus', selector: '#problem-'.$nextIndex);
        $this->performAutosave();
    }

    public function addCorrectiveAction(string $problemClientId): void
    {
        if ($this->isClosed) {
            return;
        }

        foreach ($this->problems as $pIdx => &$prob) {
            if (($prob['client_id'] ?? '') === $problemClientId) {
                $prob['corrective_actions'] ??= [];
                $caIdx = count($prob['corrective_actions']);
                $prob['corrective_actions'][] = $this->makeBlankCorrectiveAction($caIdx);
                $this->dispatch('editor-focus', selector: "#f-ca-{$pIdx}-{$caIdx}-desc");
                break;
            }
        }
        unset($prob);
        $this->performAutosave();
    }

    public function addNextAction(): void
    {
        if ($this->isClosed) {
            return;
        }

        $nextIdx = count($this->nextActions);
        $this->nextActions[] = $this->makeBlankNextAction($nextIdx);
        $this->dispatch('editor-focus', selector: "#f-na-{$nextIdx}-desc");
        $this->performAutosave();
    }

    public function addFeedback(): void
    {
        if ($this->isClosed) {
            return;
        }

        $nextIdx = count($this->feedbacks);
        $this->feedbacks[] = $this->makeBlankFeedback($nextIdx);
        $this->dispatch('editor-focus', selector: "#f-fb-{$nextIdx}-content");
        $this->performAutosave();
    }

    public function canRemoveProblem(string $clientId): bool
    {
        foreach ($this->problems as $prob) {
            if (($prob['client_id'] ?? '') === $clientId) {
                return empty($prob['id']);
            }
        }

        return false;
    }

    public function canRemoveCorrectiveAction(string $problemClientId, string $actionClientId): bool
    {
        foreach ($this->problems as $prob) {
            if (($prob['client_id'] ?? '') === $problemClientId) {
                foreach ($prob['corrective_actions'] ?? [] as $act) {
                    if (($act['client_id'] ?? '') === $actionClientId) {
                        return empty($act['id']);
                    }
                }
            }
        }

        return false;
    }

    public function canRemoveNextAction(string $clientId): bool
    {
        foreach ($this->nextActions as $act) {
            if (($act['client_id'] ?? '') === $clientId) {
                return empty($act['id']);
            }
        }

        return false;
    }

    public function canRemoveFeedback(string $clientId): bool
    {
        foreach ($this->feedbacks as $fb) {
            if (($fb['client_id'] ?? '') === $clientId) {
                return empty($fb['id']);
            }
        }

        return false;
    }

    public function promptRemove(string $type, string $clientId, ?string $parentClientId = null): void
    {
        if ($this->isClosed) {
            return;
        }

        if ($type === 'problem') {
            if (! $this->canRemoveProblem($clientId)) {
                $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');

                return;
            }

            if (count($this->problems) <= 1) {
                return;
            }

            $this->pendingRemoval = [
                'type' => 'problem',
                'client_id' => $clientId,
                'parent_client_id' => $parentClientId,
            ];
        } elseif ($type === 'corrective_action') {
            if (! $parentClientId || ! $this->canRemoveCorrectiveAction($parentClientId, $clientId)) {
                $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');

                return;
            }

            foreach ($this->problems as $prob) {
                if (($prob['client_id'] ?? '') === $parentClientId && count($prob['corrective_actions'] ?? []) <= 1) {
                    return;
                }
            }

            $this->pendingRemoval = [
                'type' => 'corrective_action',
                'client_id' => $clientId,
                'parent_client_id' => $parentClientId,
            ];
        } elseif ($type === 'next_action') {
            if (! $this->canRemoveNextAction($clientId)) {
                $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');

                return;
            }

            if (count($this->nextActions) <= 1) {
                return;
            }

            $this->pendingRemoval = [
                'type' => 'next_action',
                'client_id' => $clientId,
                'parent_client_id' => null,
            ];
        } elseif ($type === 'feedback') {
            if (! $this->canRemoveFeedback($clientId)) {
                $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');

                return;
            }

            if (count($this->feedbacks) <= 1) {
                return;
            }

            $this->pendingRemoval = [
                'type' => 'feedback',
                'client_id' => $clientId,
                'parent_client_id' => null,
            ];
        }
    }

    public function cancelRemove(): void
    {
        $this->pendingRemoval = null;
    }

    public function confirmRemove(): void
    {
        if (! $this->pendingRemoval) {
            return;
        }

        $type = $this->pendingRemoval['type'] ?? '';
        $clientId = $this->pendingRemoval['client_id'] ?? '';
        $parentClientId = $this->pendingRemoval['parent_client_id'] ?? null;

        if ($type === 'problem') {
            $this->removeProblem($clientId);
        } elseif ($type === 'corrective_action' && $parentClientId) {
            $this->removeCorrectiveAction($parentClientId, $clientId);
        } elseif ($type === 'next_action') {
            $this->removeNextAction($clientId);
        } elseif ($type === 'feedback') {
            $this->removeFeedback($clientId);
        }

        $this->pendingRemoval = null;
        $this->performAutosave();
    }

    public function removeProblem(string $clientId): void
    {
        if ($this->isClosed) {
            return;
        }

        if (! $this->canRemoveProblem($clientId)) {
            $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');

            return;
        }

        $this->problems = array_values(array_filter(
            $this->problems,
            fn (array $prob) => ($prob['client_id'] ?? '') !== $clientId
        ));

        foreach ($this->problems as $idx => &$prob) {
            $prob['position'] = $idx;
        }
        unset($prob);

        $this->ensureInitialRows();
    }

    public function removeCorrectiveAction(string $problemClientId, string $actionClientId): void
    {
        if ($this->isClosed) {
            return;
        }

        if (! $this->canRemoveCorrectiveAction($problemClientId, $actionClientId)) {
            $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');

            return;
        }

        foreach ($this->problems as &$prob) {
            if (($prob['client_id'] ?? '') === $problemClientId) {
                $prob['corrective_actions'] = array_values(array_filter(
                    $prob['corrective_actions'] ?? [],
                    fn (array $act) => ($act['client_id'] ?? '') !== $actionClientId
                ));

                foreach ($prob['corrective_actions'] as $idx => &$act) {
                    $act['position'] = $idx;
                }
                unset($act);

                if (empty($prob['corrective_actions'])) {
                    $prob['corrective_actions'][] = $this->makeBlankCorrectiveAction(0);
                }
                break;
            }
        }
        unset($prob);
    }

    public function removeNextAction(string $clientId): void
    {
        if ($this->isClosed) {
            return;
        }

        if (! $this->canRemoveNextAction($clientId)) {
            $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');

            return;
        }

        $this->nextActions = array_values(array_filter(
            $this->nextActions,
            fn (array $act) => ($act['client_id'] ?? '') !== $clientId
        ));

        foreach ($this->nextActions as $idx => &$act) {
            $act['position'] = $idx;
        }
        unset($act);

        $this->ensureInitialRows();
    }

    public function removeFeedback(string $clientId): void
    {
        if ($this->isClosed) {
            return;
        }

        if (! $this->canRemoveFeedback($clientId)) {
            $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');

            return;
        }

        $this->feedbacks = array_values(array_filter(
            $this->feedbacks,
            fn (array $fb) => ($fb['client_id'] ?? '') !== $clientId
        ));

        foreach ($this->feedbacks as $idx => &$fb) {
            $fb['position'] = $idx;
        }
        unset($fb);

        $this->ensureInitialRows();
    }

    public function updated($propertyName): void
    {
        $ignored = [
            'pendingProgressDocuments',
            'pendingFeedbackDocuments',
            'pendingPhotos',
            'activePhoto',
            'pendingRemoval',
            'saveStatus',
            'hasConflict',
            'conflictMessage',
            'lastSavedAt',
            'simulateSaveError',
        ];

        foreach ($ignored as $ig) {
            if ($propertyName === $ig || str_starts_with((string) $propertyName, $ig.'.')) {
                return;
            }
        }

        $this->isDirty = true;
        $this->performAutosave();
    }

    public function saveDraft(): void
    {
        $this->performAutosave();
    }

    public function performAutosave(): void
    {
        if ($this->isClosed) {
            return;
        }

        $admin = auth()->user();
        if (! $admin) {
            return;
        }

        $this->requestSequence++;
        $currentSeq = $this->requestSequence;
        $this->saveStatus = 'saving';

        try {
            if ($this->simulateSaveError) {
                throw new \Exception('Simulasi kegagalan koneksi jaringan.');
            }

            $draftService = app(ProgressDraftService::class);
            $payload = $this->buildDraftPayload();

            $draft = $draftService->saveDraft(
                admin: $admin,
                payload: $payload,
                draftId: $this->draftId,
                progressId: $this->progressId,
                baseRevision: $this->progress?->revision,
                expectedDraftRevision: $this->draftRevision,
                requestSequence: $currentSeq,
            );

            if ($currentSeq >= $this->requestSequence) {
                $this->draftId = $draft->id;
                $this->draftRevision = $draft->draft_revision;
                $this->saveStatus = 'saved';
                $this->lastSavedAt = $draft->updated_at?->format('H:i');
                $this->isDirty = false;
                $this->hasConflict = false;
                $this->resetErrorBag('save');
            }
        } catch (VersionConflict $e) {
            $this->saveStatus = 'conflict';
            $this->hasConflict = true;
            $this->addError('save', 'Terjadi konflik versi: draft telah diubah di sesi atau tab lain.');
        } catch (\Throwable $e) {
            $this->saveStatus = 'error';
            $this->addError('save', 'Gagal menyimpan draft: '.$e->getMessage());
        }
    }

    public function discardDraft(): void
    {
        $admin = auth()->user();
        if ($this->draftId && $admin) {
            app(ProgressDraftService::class)->discardDraft($admin, $this->draftId);
        }

        $this->draftId = null;
        $this->draftRevision = null;
        $this->saveStatus = 'unsaved';
        $this->hasConflict = false;

        if ($this->progressId) {
            $this->loadFromProgress($this->progressId);
        } else {
            $this->activityDate = null;
            $this->activity = 'Trial';
            $this->picCustomer = '';
            $this->description = '';
            $this->processTrial = '';
            $this->machine = '';
            $this->tonageRaw = '';
            $this->dieHighRaw = '';
            $this->materialActualTrial = '';
            $this->qtyMaterialRaw = '';
            $this->qtyProcessRaw = '';
            $this->qtySampleRaw = '';
            $this->qtyOkRaw = '';
            $this->qtyNgRaw = '';
            $this->eventNote = '';
            $this->remark = '';
            $this->problems = [];
            $this->nextActions = [];
            $this->feedbacks = [];
            $this->documents = [];
            $this->documentRefs = [];
            $this->ensureInitialRows();
        }

        $this->dispatch('ui-toast', message: 'Draft berhasil dibuang.');
    }

    public function updatedPendingPhotos($value, $key = null): void
    {
        $admin = auth()->user();
        if (! $admin || $this->isClosed) {
            return;
        }

        $problemClientId = $key;
        if (! $problemClientId && is_array($value)) {
            foreach ($value as $k => $files) {
                if ($files) {
                    $this->stagePhotosForProblem($admin, (string) $k, is_array($files) ? $files : [$files]);
                }
            }
            $this->pendingPhotos = [];

            return;
        }

        if ($problemClientId && $value) {
            $this->stagePhotosForProblem($admin, (string) $problemClientId, is_array($value) ? $value : [$value]);
            unset($this->pendingPhotos[$problemClientId]);
        }
    }

    private function getOrCreateDraft(): ProgressDraft
    {
        $admin = auth()->user();
        $draftService = app(ProgressDraftService::class);

        if ($this->draftId) {
            $draft = $draftService->getDraft($admin, $this->draftId);
            if ($draft) {
                return $draft;
            }
        }

        if ($this->progressId) {
            $draft = $draftService->getActiveEditDraft($admin, $this->progressId);
            if ($draft) {
                $this->draftId = $draft->id;
                $this->draftRevision = $draft->draft_revision;

                return $draft;
            }
        }

        $this->requestSequence++;
        $draft = $draftService->saveDraft(
            admin: $admin,
            payload: $this->buildDraftPayload(),
            draftId: $this->draftId,
            progressId: $this->progressId,
            baseRevision: $this->progress?->revision,
            expectedDraftRevision: null,
            requestSequence: $this->requestSequence,
        );

        $this->draftId = $draft->id;
        $this->draftRevision = $draft->draft_revision;
        $this->saveStatus = 'saved';
        $this->lastSavedAt = $draft->updated_at?->format('H:i');

        return $draft;
    }

    private function stagePhotosForProblem($admin, string $problemClientId, array $files): void
    {
        $draft = $this->getOrCreateDraft();
        $service = app(AttachmentService::class);

        foreach ($files as $file) {
            try {
                $attachment = $service->stage(
                    admin: $admin,
                    draft: $draft,
                    file: $file,
                    kind: 'photo',
                    ownerKind: 'problem',
                    ownerClientId: $problemClientId,
                    caption: null,
                );

                foreach ($this->problems as &$prob) {
                    if (($prob['client_id'] ?? '') === $problemClientId) {
                        $prob['photos'] ??= [];
                        $prob['photo_refs'] ??= [];

                        $prob['photos'][] = [
                            'id' => $attachment->id,
                            'original_name' => $attachment->original_name,
                            'caption' => '',
                            'thumbnail_url' => route('attachments.thumbnail', $attachment),
                            'preview_url' => route('attachments.preview', $attachment),
                            'download_url' => route('attachments.download', $attachment),
                            'is_staging' => true,
                        ];
                        $prob['photo_refs'][] = $attachment->id;
                        break;
                    }
                }
                unset($prob);
            } catch (ValidationException $e) {
                $this->addError("photos.{$problemClientId}", $e->getMessage());
            } catch (\Throwable $e) {
                $this->addError("photos.{$problemClientId}", 'Gagal mengunggah foto: '.$e->getMessage());
            }
        }

        $this->performAutosave();
    }

    public function removePhoto(string $problemClientId, string $photoId): void
    {
        if ($this->isClosed) {
            return;
        }

        $admin = auth()->user();

        foreach ($this->problems as &$prob) {
            if (($prob['client_id'] ?? '') === $problemClientId) {
                foreach ($prob['photos'] ?? [] as $pIdx => $photo) {
                    if ((string) ($photo['id'] ?? '') === (string) $photoId) {
                        if (empty($photo['is_staging'])) {
                            $this->addError("photos.{$problemClientId}", 'Foto tersimpan tidak dapat dihapus.');

                            return;
                        }

                        $att = Attachment::whereKey($photoId)->first();
                        if ($att && $att->state === 'staging' && $admin) {
                            try {
                                app(AttachmentService::class)->deleteStaging($admin, $att);
                            } catch (\Throwable $e) {
                                // ignore
                            }
                        }

                        unset($prob['photos'][$pIdx]);
                        $prob['photos'] = array_values($prob['photos']);
                        $prob['photo_refs'] = array_values(array_filter(
                            $prob['photo_refs'] ?? [],
                            fn ($refId) => (string) $refId !== (string) $photoId
                        ));

                        $this->performAutosave();

                        return;
                    }
                }
            }
        }
        unset($prob);
    }

    public function openLightbox(string|int $photoId): void
    {
        $att = Attachment::find($photoId);
        if ($att) {
            $this->activePhoto = [
                'id' => $att->id,
                'caption' => (string) ($att->caption ?? ''),
                'original_name' => (string) $att->original_name,
                'preview_url' => route('attachments.preview', $att),
                'download_url' => route('attachments.download', $att),
            ];
        }
    }

    public function closeLightbox(): void
    {
        $this->activePhoto = null;
    }

    public function closePhoto(): void
    {
        $this->closeLightbox();
    }

    public function updatedPendingProgressDocuments($value): void
    {
        $admin = auth()->user();
        if (! $admin || $this->isClosed) {
            return;
        }

        $files = is_array($value) ? $value : [$value];
        $this->stageDocumentsForProgress($admin, $files);
        $this->pendingProgressDocuments = [];
    }

    private function stageDocumentsForProgress(User $admin, array $files): void
    {
        $draft = $this->getOrCreateDraft();
        $service = app(AttachmentService::class);

        foreach ($files as $file) {
            try {
                $attachment = $service->stage(
                    admin: $admin,
                    draft: $draft,
                    file: $file,
                    kind: 'document',
                    ownerKind: 'progress',
                    ownerClientId: null,
                    caption: null,
                );

                $this->documents[] = [
                    'id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'caption' => '',
                    'size_bytes' => $attachment->size_bytes,
                    'preview_url' => route('attachments.preview', $attachment),
                    'download_url' => route('attachments.download', $attachment),
                    'is_staging' => true,
                ];
                $this->documentRefs[] = $attachment->id;
            } catch (ValidationException $e) {
                $this->addError('documents.progress', $e->getMessage());
            } catch (\Throwable $e) {
                $this->addError('documents.progress', 'Gagal mengunggah dokumen: '.$e->getMessage());
            }
        }

        $this->performAutosave();
    }

    public function updatedPendingFeedbackDocuments($value, $key = null): void
    {
        $admin = auth()->user();
        if (! $admin || $this->isClosed) {
            return;
        }

        $feedbackClientId = $key;
        if (! $feedbackClientId && is_array($value)) {
            foreach ($value as $k => $files) {
                if ($files) {
                    $this->stageDocumentsForFeedback($admin, (string) $k, is_array($files) ? $files : [$files]);
                }
            }
            $this->pendingFeedbackDocuments = [];

            return;
        }

        if ($feedbackClientId && $value) {
            $this->stageDocumentsForFeedback($admin, (string) $feedbackClientId, is_array($value) ? $value : [$value]);
            unset($this->pendingFeedbackDocuments[$feedbackClientId]);
        }
    }

    private function stageDocumentsForFeedback(User $admin, string $feedbackClientId, array $files): void
    {
        $draft = $this->getOrCreateDraft();
        $service = app(AttachmentService::class);

        foreach ($files as $file) {
            try {
                $attachment = $service->stage(
                    admin: $admin,
                    draft: $draft,
                    file: $file,
                    kind: 'document',
                    ownerKind: 'feedback',
                    ownerClientId: $feedbackClientId,
                    caption: null,
                );

                foreach ($this->feedbacks as &$fb) {
                    if (($fb['client_id'] ?? '') === $feedbackClientId) {
                        $fb['documents'] ??= [];
                        $fb['document_refs'] ??= [];

                        $fb['documents'][] = [
                            'id' => $attachment->id,
                            'original_name' => $attachment->original_name,
                            'caption' => '',
                            'size_bytes' => $attachment->size_bytes,
                            'preview_url' => route('attachments.preview', $attachment),
                            'download_url' => route('attachments.download', $attachment),
                            'is_staging' => true,
                        ];
                        $fb['document_refs'][] = $attachment->id;
                        break;
                    }
                }
                unset($fb);
            } catch (ValidationException $e) {
                $this->addError("documents.{$feedbackClientId}", $e->getMessage());
            } catch (\Throwable $e) {
                $this->addError("documents.{$feedbackClientId}", 'Gagal mengunggah dokumen: '.$e->getMessage());
            }
        }

        $this->performAutosave();
    }

    public function removeProgressDocument(string $docId): void
    {
        if ($this->isClosed) {
            return;
        }

        $admin = auth()->user();
        foreach ($this->documents as $idx => $doc) {
            if ((string) ($doc['id'] ?? '') === (string) $docId) {
                if (empty($doc['is_staging'])) {
                    $this->addError('documents.progress', 'Dokumen tersimpan tidak dapat dihapus.');

                    return;
                }

                $att = Attachment::whereKey($docId)->first();
                if ($att && $att->state === 'staging' && $admin) {
                    try {
                        app(AttachmentService::class)->deleteStaging($admin, $att);
                    } catch (\Throwable $e) {
                    }
                }

                unset($this->documents[$idx]);
                $this->documents = array_values($this->documents);
                $this->documentRefs = array_values(array_filter(
                    $this->documentRefs,
                    fn ($refId) => (string) $refId !== (string) $docId
                ));

                $this->performAutosave();

                return;
            }
        }
    }

    public function removeFeedbackDocument(string $feedbackClientId, string $docId): void
    {
        if ($this->isClosed) {
            return;
        }

        $admin = auth()->user();
        foreach ($this->feedbacks as &$fb) {
            if (($fb['client_id'] ?? '') === $feedbackClientId) {
                foreach ($fb['documents'] ?? [] as $idx => $doc) {
                    if ((string) ($doc['id'] ?? '') === (string) $docId) {
                        if (empty($doc['is_staging'])) {
                            $this->addError("documents.{$feedbackClientId}", 'Dokumen tersimpan tidak dapat dihapus.');

                            return;
                        }

                        $att = Attachment::whereKey($docId)->first();
                        if ($att && $att->state === 'staging' && $admin) {
                            try {
                                app(AttachmentService::class)->deleteStaging($admin, $att);
                            } catch (\Throwable $e) {
                            }
                        }

                        unset($fb['documents'][$idx]);
                        $fb['documents'] = array_values($fb['documents']);
                        $fb['document_refs'] = array_values(array_filter(
                            $fb['document_refs'] ?? [],
                            fn ($refId) => (string) $refId !== (string) $docId
                        ));

                        $this->performAutosave();

                        return;
                    }
                }
            }
        }
        unset($fb);
    }

    /**
     * Format daftar problem untuk dikirim ke ProgressPayload::fromArray().
     */
    private function buildProblemsPayload(): array
    {
        $rows = [];
        foreach (array_values($this->problems) as $idx => $prob) {
            $actions = [];
            foreach (array_values($prob['corrective_actions'] ?? []) as $aIdx => $act) {
                $actions[] = [
                    'id' => ! empty($act['id']) ? (int) $act['id'] : null,
                    'client_id' => (string) ($act['client_id'] ?? Str::uuid()),
                    'position' => $aIdx,
                    'description' => $act['description'] ?? null,
                    'pic' => $act['pic'] ?? null,
                    'actual_date' => $act['actual_date'] ?? null,
                ];
            }

            $rows[] = [
                'id' => ! empty($prob['id']) ? (int) $prob['id'] : null,
                'client_id' => (string) ($prob['client_id'] ?? Str::uuid()),
                'position' => $idx,
                'description' => $prob['description'] ?? null,
                'photo_refs' => $prob['photo_refs'] ?? [],
                'corrective_actions' => $actions,
            ];
        }

        return $rows;
    }

    /**
     * Format daftar Next Action untuk dikirim ke ProgressPayload::fromArray().
     */
    private function buildNextActionsPayload(): array
    {
        $rows = [];
        foreach (array_values($this->nextActions) as $idx => $act) {
            $rows[] = [
                'id' => ! empty($act['id']) ? (int) $act['id'] : null,
                'client_id' => (string) ($act['client_id'] ?? Str::uuid()),
                'position' => $idx,
                'description' => $act['description'] ?? null,
                'pic' => $act['pic'] ?? null,
                'actual_date' => $act['actual_date'] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * Format daftar Feedback untuk dikirim ke ProgressPayload::fromArray().
     */
    private function buildFeedbacksPayload(): array
    {
        $rows = [];
        foreach (array_values($this->feedbacks) as $idx => $fb) {
            $rows[] = [
                'id' => ! empty($fb['id']) ? (int) $fb['id'] : null,
                'client_id' => (string) ($fb['client_id'] ?? Str::uuid()),
                'position' => $idx,
                'content' => $fb['content'] ?? null,
                'pic' => $fb['pic'] ?? null,
                'feedback_date' => $fb['feedback_date'] ?? null,
                'document_refs' => $fb['document_refs'] ?? [],
            ];
        }

        return $rows;
    }

    public function buildDraftPayload(): array
    {
        return [
            'schema_version' => 1,
            'project_id' => $this->projectId,
            'activity_date' => $this->activityDate,
            'activity' => $this->activity,
            'pic_customer' => $this->picCustomer,
            'description' => $this->description,
            'process_trial' => $this->processTrial,
            'machine' => $this->machine,
            'tonage_raw' => $this->tonageRaw,
            'die_high_raw' => $this->dieHighRaw,
            'material_actual_trial' => $this->materialActualTrial,
            'qty_material_raw' => $this->qtyMaterialRaw,
            'qty_process_raw' => $this->qtyProcessRaw,
            'qty_sample_raw' => $this->qtySampleRaw,
            'qty_ok_raw' => $this->qtyOkRaw,
            'qty_ng_raw' => $this->qtyNgRaw,
            'event_note' => $this->eventNote,
            'remark' => $this->remark,
            'problems' => $this->buildProblemsPayload(),
            'next_actions' => $this->buildNextActionsPayload(),
            'feedbacks' => $this->buildFeedbacksPayload(),
            'attachment_refs' => $this->documentRefs,
        ];
    }

    public function save(SaveProgress $action)
    {
        $this->resetErrorBag();

        if ($this->isClosed) {
            $this->addError('save', 'Progress berstatus Closed tidak dapat diedit langsung. Silakan Reopen terlebih dahulu.');

            return;
        }

        $admin = auth()->user();
        if (! $admin) {
            return;
        }

        // Persist captions for photos and documents
        foreach ($this->problems as $prob) {
            foreach ($prob['photos'] ?? [] as $photo) {
                if (! empty($photo['id']) && isset($photo['caption'])) {
                    Attachment::whereKey($photo['id'])->update(['caption' => $photo['caption']]);
                }
            }
        }
        foreach ($this->documents as $doc) {
            if (! empty($doc['id']) && isset($doc['caption'])) {
                Attachment::whereKey($doc['id'])->update(['caption' => $doc['caption']]);
            }
        }
        foreach ($this->feedbacks as $fb) {
            foreach ($fb['documents'] ?? [] as $doc) {
                if (! empty($doc['id']) && isset($doc['caption'])) {
                    Attachment::whereKey($doc['id'])->update(['caption' => $doc['caption']]);
                }
            }
        }

        try {
            // Pastikan draft tersimpan dengan isian terbaru sebelum di-apply
            $draftService = app(ProgressDraftService::class);
            $payload = $this->buildDraftPayload();

            $this->requestSequence++;
            $draft = $draftService->saveDraft(
                admin: $admin,
                payload: $payload,
                draftId: $this->draftId,
                progressId: $this->progressId,
                baseRevision: $this->progress?->revision,
                expectedDraftRevision: $this->draftRevision,
                requestSequence: $this->requestSequence,
            );

            $this->draftId = $draft->id;
            $this->draftRevision = $draft->draft_revision;

            // Apply draft secara transaksional
            $applyAction = app(ApplyProgressDraft::class);
            $result = $applyAction->handle($admin, $this->draftId, $this->draftRevision);
            $saved = $result->progress;

            $this->progress = $saved;
            $this->progressId = $saved->id;
            $this->draftId = null;
            $this->draftRevision = null;
            $this->saveStatus = 'saved';

            $this->loadFromProgress($saved->id);

            session()->flash('ui-toast', 'Progress berhasil disimpan.');

            return redirect()->to('?tab=progresses&detail='.$saved->id);
        } catch (ValidationException $e) {
            $mapped = ProgressFormData::mapValidationErrors($e->errors());
            foreach ($mapped as $field => $messages) {
                foreach ($messages as $msg) {
                    $this->addError($field, $msg);
                }
            }
        } catch (VersionConflict $e) {
            $this->saveStatus = 'conflict';
            $this->hasConflict = true;
            $this->addError('save', 'Terjadi konflik versi: draft atau progress telah diubah di sesi lain.');
        } catch (\Throwable $e) {
            $this->addError('save', 'Gagal menyimpan progress: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.progresses.progress-editor');
    }
}
