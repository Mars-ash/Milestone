<?php

namespace App\Livewire\Prototype;

use App\Prototype\FakeDraftStore;
use App\Prototype\FakeStore;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Locked;
use App\Support\UiFormat;

class ProgressForm extends PreviewComponent
{
    public ?string $progressId = null;

    // Field Wajib Progress (FR04, AT05)
    public ?string $projectId = '';
    public string $activityDate = '';
    public string $activity = '';

    // Field Utama & PIC Teks Bebas (FR05, AT06)
    public string $notes = '';

    public ?string $picCustomer = '';

    // Field Rincian Parameter Trial (D15 Raw Text - Tersedia untuk semua Activity)
    public ?string $processTrial = '';
    public ?string $machine = '';
    public ?string $dieHighRaw = '';
    public ?string $materialActualTrial = '';
    public ?string $qtyMaterialRaw = '';
    public ?string $qtyOkRaw = '';
    public ?string $qtyNgRaw = '';
    public ?string $qtyProcessRaw = '';
    public ?string $qtySampleRaw = '';
    public ?string $eventNote = '';
    public ?string $remark = '';

    // Bagian Form Aktif (UI05, UI06)
    public string $activeSection = 'activity';

    // Data Identitas Terkini (FR02, AT03)
    public ?array $selectedProject = null;

    // Nested items collections (FR04, AT05)
    public array $problems = [];
    public array $nextActions = [];
    public array $feedbacks = [];
    public array $attachments = [];

    // Draft & Autosave State (PRD D11, UI07)
    public ?string $draftUuid = null;
    public string $autosaveStatus = 'unsaved'; // unsaved, saving, saved, error
    public ?string $lastSavedTime = null;
    public bool $simulateSaveError = false; // Mode simulasi kegagalan simpan untuk pengujian UI07 / AT37
    public bool $isClosed = false; // Closed memblokir edit langsung (D10, AT21)

    // Konfirmasi Penghapusan Baris Baru (UI-06)
    public ?array $pendingRemoval = null;

    public ?string $successMessage = null;
    public ?string $pendingUrl = null;
    public ?array $activePhoto = null;

    #[Locked]
    public string $draftFingerprint = '';

    public function mount(?string $progressId = null, ?string $projectId = null): void
    {
        $this->progressId = $progressId ?? request()->query('id');
        $draftId = request()->query('draft');
        $draft = $draftId ? FakeStore::findDraft($draftId) : null;
        abort_if($draftId && ! $draft, 404);

        if ($draft) {
            $this->progressId = $draft['progress_id'] ?? null;
        }
        $progress = $this->progressId ? FakeStore::findProgress($this->progressId) : null;
        abort_if($this->progressId && ! $progress, 404);

        if (strtolower($progress['status'] ?? '') === 'closed') {
            $this->isClosed = true;
            $this->redirectRoute('prototype.index', ['tab' => 'progresses', 'detail' => $this->progressId]);
            return;
        }

        // Opening an existing progress must resume its latest draft, if present.
        if (! $draft && $this->progressId) {
            $drafts = array_values(array_filter(FakeDraftStore::all(),
                fn ($d) => ($d['progress_id'] ?? null) === $this->progressId));
            usort($drafts, fn ($a, $b) => strtotime($b['updated_at'] ?? '') <=> strtotime($a['updated_at'] ?? ''));
            $draft = $drafts[0] ?? null;
        }
        $row = array_replace($progress ?? [], $draft ?? []);
        $this->projectId = $row['project_id'] ?? $projectId ?? request()->query('project_id') ?? '';
        $this->activity = (string) ($row['activity'] ?? '');
        $this->activityDate = (string) ($row['activity_date'] ?? '');
        $this->notes = (string) ($row['notes'] ?? $row['description'] ?? '');
        foreach ([
            'picCustomer' => 'pic_customer', 'processTrial' => 'process_trial',
            'machine' => 'machine', 'dieHighRaw' => 'die_high_raw',
            'materialActualTrial' => 'material_actual_trial', 'qtyMaterialRaw' => 'qty_material_raw',
            'qtyOkRaw' => 'qty_ok_raw', 'qtyNgRaw' => 'qty_ng_raw',
            'qtyProcessRaw' => 'qty_process_raw', 'qtySampleRaw' => 'qty_sample_raw',
            'eventNote' => 'event_note', 'remark' => 'remark',
        ] as $property => $key) {
            $this->{$property} = (string) ($row[$key] ?? ($key === 'die_high_raw' ? ($row['dh'] ?? '') : ''));
        }
        $this->problems = $row['problems'] ?? [];
        $this->nextActions = $row['next_actions'] ?? [];
        $this->feedbacks = $row['feedbacks'] ?? [];
        $this->attachments = $row['attachments'] ?? [];
        if ($draft) {
            $this->draftUuid = $draft['uuid'];
            $this->autosaveStatus = 'saved';
            $this->lastSavedTime = UiFormat::time($draft['updated_at'] ?? null);
        }
        $this->selectedProject = filled($this->projectId) ? FakeStore::findProject($this->projectId) : null;
        $this->ensureInitialRows();
        $this->draftFingerprint = $this->payloadFingerprint();
    }

    /**
     * Sediakan kerangka awal 1 problem + 1 CA, 1 next action, 1 feedback (UI-05, FR04).
     */
    public function ensureInitialRows(): void
    {
        if (empty($this->problems)) {
            $newId = 'prob-' . Str::lower(Str::random(5));
            $this->problems[] = [
                'id' => $newId,
                'description' => '',
                'order' => 1,
                'photos' => [],
                'corrective_actions' => [
                    [
                        'id' => 'act-' . Str::lower(Str::random(5)),
                        'description' => '',
                        'pic' => '',
                        'actual_date' => null,
                        'order' => 1,
                    ],
                ],
            ];
        } else {
            foreach ($this->problems as &$prob) {
                if (empty($prob['corrective_actions'])) {
                    $prob['corrective_actions'][] = [
                        'id' => 'act-' . Str::lower(Str::random(5)),
                        'description' => '',
                        'pic' => '',
                        'actual_date' => null,
                        'order' => 1,
                    ];
                }
            }
            unset($prob);
        }

        if (empty($this->nextActions)) {
            $this->nextActions[] = [
                'id' => 'next-' . Str::lower(Str::random(5)),
                'description' => '',
                'pic' => '',
                'actual_date' => null,
                'order' => 1,
            ];
        }

        if (empty($this->feedbacks)) {
            $this->feedbacks[] = [
                'id' => 'fb-' . Str::lower(Str::random(5)),
                'date' => '',
                'pic' => '',
                'content' => '',
                'order' => 1,
                'files' => [],
            ];
        }
    }

    public function updatedProjectId(?string $value): void
    {
        $this->projectId = $value ?? '';
        $this->selectedProject = ! empty($value) ? FakeStore::findProject($value) : null;
    }

    /**
     * Ganti Activity (FR04, AT05):
     * Nilai form dan rincian parameter trial tidak boleh hilang atau dihapus saat berganti Activity.
     */
    public function setActivity(string $activity): void
    {
        $allowed = ['Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'];
        if (in_array($activity, $allowed, true)) {
            $this->activity = $activity;
        }
    }

    public function setSection(string $section): void
    {
        $this->activeSection = $section;
    }

    public function save()
    {
        $this->resetErrorBag();

        // Validasi final Simpan Baru: Hanya 3 field wajib
        $errors = [];
        if (empty(trim((string) $this->projectId))) {
            $errors['projectId'] = 'JO No wajib dipilih.';
        }
        if (empty(trim((string) $this->activityDate))) {
            $errors['activityDate'] = 'Tanggal Aktivitas wajib diisi.';
        }
        $allowed = ['Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'];
        if (! in_array($this->activity, $allowed, true)) {
            $errors['activity'] = 'Pilihan Activity tidak valid.';
        }

        if ($this->progressId) {
            $latest = FakeStore::findProgress($this->progressId);
            if ($latest && strtolower($latest['status'] ?? 'open') === 'closed') {
                $this->isClosed = true;
            }
        }

        if ($this->isClosed) {
            $this->addError('save', 'Progress berstatus Closed tidak dapat diedit secara langsung. Klik tombol Reopen terlebih dahulu (D10, AT21).');
            return;
        }

        if (! empty($errors)) {
            $this->persistDraft();
            foreach ($errors as $field => $msg) {
                $this->addError($field, $msg);
            }
            $this->dispatch('focus-first-error');
            return;
        }

        $payload = [
            'id' => $this->progressId,
            'project_id' => $this->projectId,
            'activity' => $this->activity,
            'activity_date' => $this->activityDate,
            'notes' => $this->notes,
            'description' => $this->notes,
            'pic_customer' => $this->picCustomer,
            'process_trial' => $this->processTrial,
            'machine' => $this->machine,
            'die_high_raw' => $this->dieHighRaw,
            'material_actual_trial' => $this->materialActualTrial,
            'qty_material_raw' => $this->qtyMaterialRaw,
            'qty_ok_raw' => $this->qtyOkRaw,
            'qty_ng_raw' => $this->qtyNgRaw,
            'qty_process_raw' => $this->qtyProcessRaw,
            'qty_sample_raw' => $this->qtySampleRaw,
            'event_note' => $this->eventNote,
            'remark' => $this->remark,
            'problems' => $this->problems,
            'next_actions' => $this->nextActions,
            'feedbacks' => $this->feedbacks,
            'attachments' => $this->attachments,
        ];

        $payload = $this->cleanProgressPayload($payload);

        try {
            $saved = FakeStore::saveProgress($payload);
            if ($this->draftUuid) {
                FakeStore::deleteDraft($this->draftUuid);
            }
            $this->progressId = $saved['id'];
            $this->successMessage = 'Progress kegiatan berhasil disimpan.';
            $this->dispatch('progress-saved', progressId: $saved['id']);
            return redirect()->to(route('prototype.index', ['tab' => 'progresses', 'detail' => $saved['id']]))->with('ui-toast', 'Progress tersimpan.');
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $this->addError($field, $msg);
                }
            }
            $this->dispatch('focus-first-error');
        }
    }

    /**
     * Normalisasi baris kosong sesuai aturan handoff (UI-06):
     * - Action dengan deskripsi/pic/tanggal tetap disimpan.
     * - Problem dengan deskripsi/foto/action tetap disimpan.
     * - Next Action dengan deskripsi/pic/tanggal tetap disimpan.
     * - Feedback dengan teks/pic/tanggal/file tetap disimpan.
     * - Record tersimpan dari DB tidak pernah terhapus otomatis saat dikosongkan.
     */
    public function cleanProgressPayload(array $payload): array
    {
        $current = $this->progressId ? FakeStore::findProgress($this->progressId) : null;
        $retainedProblemIds = array_column($current['problems'] ?? [], 'id');
        $retainedActionIds = [];
        foreach ($current['problems'] ?? [] as $cp) {
            foreach ($cp['corrective_actions'] ?? [] as $ca) {
                if (! empty($ca['id'])) {
                    $retainedActionIds[] = $ca['id'];
                }
            }
        }
        $retainedNextActionIds = array_column($current['next_actions'] ?? [], 'id');
        $retainedFeedbackIds = array_column($current['feedbacks'] ?? [], 'id');

        $hasText = fn ($val) => is_string($val) && trim($val) !== '';

        if (! empty($payload['problems'])) {
            $cleanedProblems = [];
            foreach ($payload['problems'] as $p) {
                $probId = $p['id'] ?? '';
                $isRetainedProblem = in_array($probId, $retainedProblemIds, true);

                $cleanedActions = [];
                foreach ($p['corrective_actions'] ?? [] as $a) {
                    $actId = $a['id'] ?? '';
                    $isRetainedAction = in_array($actId, $retainedActionIds, true);

                    $actionFilled = $hasText($a['description'] ?? '')
                        || $hasText($a['pic'] ?? '')
                        || ! empty($a['actual_date']);

                    if ($actionFilled || $isRetainedAction) {
                        $cleanedActions[] = $a;
                    }
                }
                $p['corrective_actions'] = $cleanedActions;

                $problemFilled = $hasText($p['description'] ?? '')
                    || ! empty($p['photos'])
                    || ! empty($p['corrective_actions']);

                if ($problemFilled || $isRetainedProblem) {
                    $cleanedProblems[] = $p;
                }
            }
            $payload['problems'] = $cleanedProblems;
        }

        if (! empty($payload['next_actions'])) {
            $cleanedNext = [];
            foreach ($payload['next_actions'] as $na) {
                $naId = $na['id'] ?? '';
                $isRetained = in_array($naId, $retainedNextActionIds, true);

                $naFilled = $hasText($na['description'] ?? '')
                    || $hasText($na['pic'] ?? '')
                    || ! empty($na['actual_date']);

                if ($naFilled || $isRetained) {
                    $cleanedNext[] = $na;
                }
            }
            $payload['next_actions'] = $cleanedNext;
        }

        if (! empty($payload['feedbacks'])) {
            $cleanedFeedbacks = [];
            foreach ($payload['feedbacks'] as $fb) {
                $fbId = $fb['id'] ?? '';
                $isRetained = in_array($fbId, $retainedFeedbackIds, true);

                $fbFilled = $hasText($fb['content'] ?? '')
                    || $hasText($fb['pic'] ?? '')
                    || ! empty($fb['date'])
                    || ! empty($fb['files']);

                if ($fbFilled || $isRetained) {
                    $cleanedFeedbacks[] = $fb;
                }
            }
            $payload['feedbacks'] = $cleanedFeedbacks;
        }

        return $payload;
    }

    /**
     * Dapatkan payload data form untuk penyimpanan draft.
     */
    public function getDraftPayload(): array
    {
        return [
            'uuid' => $this->draftUuid,
            'type' => $this->progressId ? 'edit' : 'new',
            'progress_id' => $this->progressId,
            'project_id' => $this->projectId,
            'activity' => $this->activity,
            'activity_date' => $this->activityDate,
            'notes' => $this->notes,
            'pic_customer' => $this->picCustomer,
            'process_trial' => $this->processTrial,
            'machine' => $this->machine,
            'die_high_raw' => $this->dieHighRaw,
            'material_actual_trial' => $this->materialActualTrial,
            'qty_material_raw' => $this->qtyMaterialRaw,
            'qty_ok_raw' => $this->qtyOkRaw,
            'qty_ng_raw' => $this->qtyNgRaw,
            'qty_process_raw' => $this->qtyProcessRaw,
            'qty_sample_raw' => $this->qtySampleRaw,
            'event_note' => $this->eventNote,
            'remark' => $this->remark,
            'problems' => $this->problems,
            'next_actions' => $this->nextActions,
            'feedbacks' => $this->feedbacks,
            'attachments' => $this->attachments,
        ];
    }

    /**
     * Autosave reaktif saat ada perubahan properti (FR12, AT12, AT14).
     * Tidak menyimpan bila form masih kosong sama sekali (dirty check AT14).
     */
    public function updated($property): void
    {
        $root = explode('.', $property)[0];
        if (! in_array($root, [
            'projectId', 'activityDate', 'activity', 'notes', 'picCustomer',
            'processTrial', 'machine', 'dieHighRaw', 'materialActualTrial',
            'qtyMaterialRaw', 'qtyOkRaw', 'qtyNgRaw', 'qtyProcessRaw', 'qtySampleRaw', 'eventNote', 'remark',
            'problems', 'nextActions', 'feedbacks', 'attachments',
        ], true)) {
            return;
        }
        $this->persistDraft();
    }

    /**
     * Simpan sebagai draft secara eksplisit (D11).
     * Tidak mempengaruhi hitungan Open/Closed dan dapat dilanjutkan nanti.
     */
    public function saveDraft(): void
    {
        $this->persistDraft();
    }

    /**
     * Buang draft yang sedang aktif (AT19).
     */
    public function discardCurrentDraft()
    {
        if ($this->draftUuid) {
            FakeDraftStore::discard($this->draftUuid);
            $this->draftUuid = null;
        }

        return redirect()->to('?tab=drafts');
    }

    public function cancel()
    {
        $this->pendingUrl = route('prototype.index', ['tab' => 'dashboard']);
        return $this->leaveEditor();
    }

    /**
     * Buka kembali progress Closed agar dapat diedit (D10, AT21, AT22).
     */
    public function reopenAndEnableEdit(): void
    {
        if ($this->progressId) {
            FakeStore::reopenProgress($this->progressId);
            $this->isClosed = false;
            $this->successMessage = "Progress #{$this->progressId} telah dibuka kembali (Open). Seluruh data terjaga dan mode edit telah aktif.";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Nested List Operations (FR07-FR11, AT07-AT11, D06-D09)
    |--------------------------------------------------------------------------
    */

    public function addProblem(): void
    {
        if (! $this->writable()) return;
        $newId = 'prob-' . Str::lower(Str::random(5));
        $this->problems[] = [
            'id' => $newId,
            'description' => '',
            'order' => count($this->problems) + 1,
            'photos' => [],
            'corrective_actions' => [
                [
                    'id' => 'act-' . Str::lower(Str::random(5)),
                    'description' => '',
                    'pic' => '',
                    'actual_date' => null,
                    'order' => 1,
                ],
            ],
        ];
        $this->activeSection = 'problems';
        $this->updated('problems');
        $this->dispatch('editor-focus', selector: '#problem-' . (count($this->problems) - 1));
    }

    /**
     * Cek apakah baris boleh dihapus (Handoff 7.8, UI-06 Langkah 4).
     * Record tersimpan di database TIDAK boleh dihapus melalui form.
     * Hanya baris baru yang belum diterapkan yang boleh dihapus.
     */
    public function canRemove(string $type, string $id, ?string $parentId = null): bool
    {
        if (empty($this->progressId)) {
            return true;
        }

        $current = FakeStore::findProgress($this->progressId);
        if (! $current) {
            return true;
        }

        if ($type === 'problem') {
            $savedIds = array_column($current['problems'] ?? [], 'id');
            return ! in_array($id, $savedIds, true);
        }

        if ($type === 'corrective_action') {
            foreach ($current['problems'] ?? [] as $cp) {
                if ($parentId && ($cp['id'] ?? '') !== $parentId) {
                    continue;
                }
                $savedActionIds = array_column($cp['corrective_actions'] ?? [], 'id');
                if (in_array($id, $savedActionIds, true)) {
                    return false;
                }
            }
            return true;
        }

        if ($type === 'next_action') {
            $savedIds = array_column($current['next_actions'] ?? [], 'id');
            return ! in_array($id, $savedIds, true);
        }

        if ($type === 'feedback') {
            $savedIds = array_column($current['feedbacks'] ?? [], 'id');
            return ! in_array($id, $savedIds, true);
        }

        return true;
    }

    /**
     * Cari daftar tindakan dari suatu problem.
     */
    public function findProblemActions(?string $problemId): array
    {
        if (! $problemId) {
            return [];
        }
        foreach ($this->problems as $p) {
            if (($p['id'] ?? '') === $problemId) {
                return $p['corrective_actions'] ?? [];
            }
        }
        return [];
    }

    /**
     * Minta konfirmasi penghapusan baris baru (UI-06 Langkah 4).
     */
    public function promptRemove(string $type, string $id, ?string $parentId = null): void
    {
        if (! $this->canRemove($type, $id, $parentId)) {
            $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');
            return;
        }

        $count = match ($type) {
            'problem' => count($this->problems),
            'corrective_action' => count($this->findProblemActions($parentId)),
            'next_action' => count($this->nextActions),
            'feedback' => count($this->feedbacks),
            default => 0,
        };

        if ($count <= 1) {
            return;
        }

        $this->pendingRemoval = [
            'type' => $type,
            'id' => $id,
            'parentId' => $parentId,
        ];
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

        $type = $this->pendingRemoval['type'];
        $id = $this->pendingRemoval['id'];
        $parentId = $this->pendingRemoval['parentId'] ?? null;

        if ($type === 'problem') {
            $this->removeProblem($id);
        } elseif ($type === 'corrective_action') {
            $this->removeCorrectiveAction($parentId, $id);
        } elseif ($type === 'next_action') {
            $this->removeNextAction($id);
        } elseif ($type === 'feedback') {
            $this->removeFeedback($id);
        }

        $this->pendingRemoval = null;
        $this->updated('problems');
    }

    public function removeProblem(string $problemId): void
    {
        if (! $this->writable()) return;
        if (! $this->canRemove('problem', $problemId)) {
            $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');
            return;
        }

        $this->problems = array_values(array_filter(
            $this->problems,
            fn ($p) => ($p['id'] ?? '') !== $problemId
        ));
        $this->updated('problems');
    }

    public function addCorrectiveAction(string $problemId): void
    {
        if (! $this->writable()) return;
        foreach ($this->problems as &$prob) {
            if (($prob['id'] ?? '') === $problemId) {
                $prob['corrective_actions'][] = [
                    'id' => 'act-' . Str::lower(Str::random(5)),
                    'description' => '',
                    'pic' => '',
                    'actual_date' => null,
                    'order' => count($prob['corrective_actions'] ?? []) + 1,
                ];
                break;
            }
        }
        unset($prob);
        $this->updated('problems');
        $index = array_search($problemId, array_column($this->problems, 'id'), true);
        if ($index !== false) {
            $last = count($this->problems[$index]['corrective_actions']) - 1;
            $this->dispatch('editor-focus', selector: "#f-ca-{$index}-{$last}-desc");
        }
    }

    public function removeCorrectiveAction(string $problemId, string $actionId): void
    {
        if (! $this->writable()) return;
        if (! $this->canRemove('corrective_action', $actionId, $problemId)) {
            $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');
            return;
        }

        foreach ($this->problems as &$prob) {
            if (($prob['id'] ?? '') === $problemId) {
                $prob['corrective_actions'] = array_values(array_filter(
                    $prob['corrective_actions'] ?? [],
                    fn ($a) => ($a['id'] ?? '') !== $actionId
                ));
                break;
            }
        }
        unset($prob);
        $this->updated('problems');
    }

    public function addSimulationPhoto(string $problemId): void
    {
        if (! $this->writable()) return;
        foreach ($this->problems as &$prob) {
            if (($prob['id'] ?? '') === $problemId) {
                $nextNum = count($prob['photos'] ?? []) + 1;
                $prob['photos'][] = [
                    'id' => 'ph-' . Str::lower(Str::random(5)),
                    'filename' => "simulasi_foto_{$nextNum}.jpg",
                    'caption' => '',
                    'path' => null,
                    'thumbnail' => null,
                    'is_simulation' => true,
                ];
                break;
            }
        }
        unset($prob);
        $this->updated('problems');
    }

    public function removeSimulationPhoto(string $problemId, string $photoId): void
    {
        if (! $this->writable()) return;
        foreach ($this->problems as &$prob) {
            if (($prob['id'] ?? '') === $problemId) {
                $prob['photos'] = array_values(array_filter(
                    $prob['photos'] ?? [],
                    fn ($ph) => ($ph['id'] ?? '') !== $photoId
                ));
                break;
            }
        }
        unset($prob);
        $this->updated('problems');
    }

    public function addNextAction(): void
    {
        if (! $this->writable()) return;
        $this->nextActions[] = [
            'id' => 'next-' . Str::lower(Str::random(5)),
            'description' => '',
            'pic' => '',
            'actual_date' => null,
            'order' => count($this->nextActions) + 1,
        ];
        $this->activeSection = 'next_actions';
        $this->updated('nextActions');
        $this->dispatch('editor-focus', selector: '#f-na-' . (count($this->nextActions) - 1) . '-desc');
    }

    public function removeNextAction(string $actionId): void
    {
        if (! $this->writable()) return;
        if (! $this->canRemove('next_action', $actionId)) {
            $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');
            return;
        }

        $this->nextActions = array_values(array_filter(
            $this->nextActions,
            fn ($a) => ($a['id'] ?? '') !== $actionId
        ));
        $this->updated('nextActions');
    }

    public function addFeedback(): void
    {
        if (! $this->writable()) return;
        $this->feedbacks[] = [
            'id' => 'fb-' . Str::lower(Str::random(5)),
            'date' => '',
            'pic' => '',
            'content' => '',
            'order' => count($this->feedbacks) + 1,
            'files' => [],
        ];
        $this->activeSection = 'feedback';
        $this->updated('feedbacks');
        $this->dispatch('editor-focus', selector: '#f-fb-' . (count($this->feedbacks) - 1) . '-content');
    }

    public function removeFeedback(string $feedbackId): void
    {
        if (! $this->writable()) return;
        if (! $this->canRemove('feedback', $feedbackId)) {
            $this->addError('remove', 'Catatan tersimpan dapat dikoreksi melalui edit.');
            return;
        }

        $this->feedbacks = array_values(array_filter(
            $this->feedbacks,
            fn ($f) => ($f['id'] ?? '') !== $feedbackId
        ));
        $this->updated('feedbacks');
    }

    public function addSimulationFeedbackFile(string $feedbackId, string $filename = 'dokumen_feedback.pdf'): void
    {
        if (! $this->writable()) return;
        foreach ($this->feedbacks as &$fb) {
            if (($fb['id'] ?? '') === $feedbackId) {
                $fb['files'] = $fb['files'] ?? [];
                $fb['files'][] = [
                    'id' => 'file-' . Str::lower(Str::random(5)),
                    'name' => $filename,
                    'size' => 1024 * 45,
                ];
                break;
            }
        }
        unset($fb);
        $this->updated('feedbacks');
    }

    public function removeSimulationFeedbackFile(string $feedbackId, string $fileId): void
    {
        if (! $this->writable()) return;
        foreach ($this->feedbacks as &$fb) {
            if (($fb['id'] ?? '') === $feedbackId) {
                $fb['files'] = array_values(array_filter(
                    $fb['files'] ?? [],
                    fn ($f) => ($f['id'] ?? '') !== $fileId
                ));
                break;
            }
        }
        unset($fb);
        $this->updated('feedbacks');
    }


    private function payloadFingerprint(): string
    {
        $payload = $this->cleanProgressPayload($this->getDraftPayload());
        unset($payload['uuid']);
        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private function writable(): bool
    {
        $current = $this->progressId ? FakeStore::findProgress($this->progressId) : null;
        $this->isClosed = $this->isClosed || strtolower($current['status'] ?? '') === 'closed';
        if ($this->isClosed) {
            $this->addError('save', 'Reopen progress terlebih dahulu untuk mengedit.');
            return false;
        }
        return true;
    }

    private function persistDraft(): bool
    {
        if (! $this->writable()) {
            return false;
        }

        try {
            $fingerprint = $this->payloadFingerprint();

            if ($fingerprint === $this->draftFingerprint) {
                $this->resetErrorBag('save');
                $this->autosaveStatus = $this->draftUuid ? 'saved' : 'unsaved';
                return true;
            }

            if ($this->simulateSaveError) {
                $this->autosaveStatus = 'error';
                $this->addError('save', 'Simulasi kegagalan simpan draft. Silakan klik Coba Lagi.');
                return false;
            }

            $this->resetErrorBag('save');
            $draft = FakeDraftStore::save(
                $this->cleanProgressPayload($this->getDraftPayload())
            );
            $this->draftUuid = $draft['uuid'];
            $this->lastSavedTime = UiFormat::time($draft['updated_at'] ?? null);
            $this->draftFingerprint = $fingerprint;
            $this->autosaveStatus = 'saved';
            $this->dispatch('draft-changed');
            return true;
        } catch (\Throwable $e) {
            report($e);
            $this->autosaveStatus = 'error';
            return false;
        }
    }

    public function leaveEditor()
    {
        $url = $this->pendingUrl ?: route('prototype.index', ['tab' => 'dashboard']);
        $actual = parse_url($url);
        $base = parse_url(route('prototype.index'));
        abort_unless(is_array($actual)
            && ($actual['scheme'] ?? '') === ($base['scheme'] ?? '')
            && ($actual['host'] ?? '') === ($base['host'] ?? '')
            && ($actual['port'] ?? null) === ($base['port'] ?? null)
            && rtrim($actual['path'] ?? '', '/') === rtrim($base['path'] ?? '', '/'), 422);
        if (! $this->persistDraft()) return null;
        return redirect()->to($url);
    }

    public function openProblemPhoto(string $problemId, string $photoId): void
    {
        foreach ($this->problems as $problem) {
            if (($problem['id'] ?? '') !== $problemId) continue;
            foreach ($problem['photos'] ?? [] as $photo) {
                if (($photo['id'] ?? '') !== $photoId) continue;
                $this->activePhoto = [
                    'src' => $photo['path'] ?? $photo['src'] ?? '',
                    'name' => $photo['filename'] ?? $photo['name'] ?? 'Foto problem',
                ];
                return;
            }
        }
    }

    public function closePhoto(): void { $this->activePhoto = null; }

    // Prototype parity only: store the selected image's data URL, not a fake thumbnail.
    public function attachProblemPhoto(string $problemId, array $photo): void
    {
        if (! $this->writable()) return;
        $valid = validator($photo, [
            'name' => ['required', 'string', 'max:255'],
            'src' => ['required', 'string', 'max:2800000', 'regex:/\Adata:image\/(jpeg|png|webp);base64,[A-Za-z0-9+\/=]+\z/'],
        ])->validate();
        $bytes = base64_decode(explode(',', $valid['src'], 2)[1], true);
        abort_unless($bytes !== false && strlen($bytes) <= 2 * 1024 * 1024 && @getimagesizefromstring($bytes) !== false, 422);
        foreach ($this->problems as &$problem) {
            if (($problem['id'] ?? '') !== $problemId) continue;
            $problem['photos'][] = [
                'id' => (string) Str::uuid(), 'filename' => $valid['name'],
                'path' => $valid['src'], 'caption' => '', 'is_simulation' => true,
            ];
            unset($problem);
            $this->persistDraft();
            return;
        }
        unset($problem);
        abort(422, 'Problem tidak ditemukan.');
    }

    // Metadata only, exactly as the approved prototype (not a production upload).
    public function attachFiles(string $owner, array $files): void
    {
        if (! $this->writable()) return;
        $valid = validator(['files' => $files], [
            'files' => ['required', 'array'],
            'files.*.name' => ['required', 'string', 'max:255'],
            'files.*.size' => ['required', 'integer', 'min:0'],
        ])->validate()['files'];
        $items = array_map(fn ($file) => [
            'id' => (string) Str::uuid(), 'name' => $file['name'], 'size' => $file['size'],
        ], $valid);
        if ($owner === 'progress') {
            $this->attachments = array_merge($this->attachments, $items);
        } else {
            $index = array_search($owner, array_column($this->feedbacks, 'id'), true);
            abort_if($index === false, 422, 'Feedback tidak ditemukan.');
            $this->feedbacks[$index]['files'] = array_merge($this->feedbacks[$index]['files'] ?? [], $items);
        }
        $this->persistDraft();
    }

    public function render()
    {
        $projects = FakeStore::getProjects();
        $this->selectedProject = filled($this->projectId) ? FakeStore::findProject($this->projectId) : null;

        return view('livewire.prototype.progress-form', [
            'projects' => $projects,
        ]);
    }
}
