<?php

namespace App\Livewire\Prototype;

use App\Prototype\FakeStore;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProgressForm extends Component
{
    public ?string $progressId = null;

    // Field Wajib Progress (FR04, AT05)
    public string $projectId = '';
    public string $activityDate = '';
    public string $activity = 'Trial';

    // Field Utama & PIC Teks Bebas (FR05, AT06)
    public string $notes = '';
    public string $picProgress = '';
    public ?string $picCustomer = '';

    // Field Rincian Parameter Trial (D15 Raw Text - Tersedia untuk semua Activity)
    public ?string $processTrial = '';
    public ?string $machine = '';
    public ?string $dieHighRaw = '';
    public ?string $materialActualTrial = '';
    public ?string $qtyMaterialRaw = '';
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

    public ?string $successMessage = null;

    public function mount(?string $progressId = null, ?string $projectId = null): void
    {
        $this->progressId = $progressId ?? request()->query('id');
        $initProjectId = $projectId ?? request()->query('project_id');
        $initDraftUuid = request()->query('draft');

        if ($this->progressId) {
            $progress = FakeStore::findProgress($this->progressId);
            if ($progress) {
                $this->projectId = $progress['project_id'] ?? '';
                $this->activity = $progress['activity'] ?? 'Trial';
                $this->activityDate = $progress['activity_date'] ?? date('Y-m-d');
                $this->notes = $progress['notes'] ?? $progress['description'] ?? '';
                $this->picProgress = $progress['pic_progress'] ?? '';
                $this->picCustomer = $progress['pic_customer'] ?? '';
                $this->processTrial = $progress['process_trial'] ?? '';
                $this->machine = $progress['machine'] ?? '';
                $this->dieHighRaw = $progress['die_high_raw'] ?? $progress['dh'] ?? '';
                $this->materialActualTrial = $progress['material_actual_trial'] ?? '';
                $this->qtyMaterialRaw = $progress['qty_material_raw'] ?? '';
                $this->qtyProcessRaw = $progress['qty_process_raw'] ?? '';
                $this->qtySampleRaw = $progress['qty_sample_raw'] ?? '';
                $this->eventNote = $progress['event_note'] ?? '';
                $this->remark = $progress['remark'] ?? '';
                $this->problems = $progress['problems'] ?? [];
                $this->nextActions = $progress['next_actions'] ?? [];
                $this->feedbacks = $progress['feedbacks'] ?? [];
                $this->attachments = $progress['attachments'] ?? [];
            }
        } elseif ($initDraftUuid) {
            $draft = FakeStore::findDraft($initDraftUuid);
            if ($draft) {
                $this->draftUuid = $draft['uuid'];
                $this->projectId = $draft['project_id'] ?? '';
                $this->activity = $draft['activity'] ?? 'Trial';
                $this->activityDate = $draft['activity_date'] ?? date('Y-m-d');
                $this->notes = $draft['notes'] ?? '';
                $this->picProgress = $draft['pic_progress'] ?? '';
                $this->picCustomer = $draft['pic_customer'] ?? '';
                $this->processTrial = $draft['process_trial'] ?? '';
                $this->machine = $draft['machine'] ?? '';
                $this->dieHighRaw = $draft['die_high_raw'] ?? '';
                $this->materialActualTrial = $draft['material_actual_trial'] ?? '';
                $this->qtyMaterialRaw = $draft['qty_material_raw'] ?? '';
                $this->qtyProcessRaw = $draft['qty_process_raw'] ?? '';
                $this->qtySampleRaw = $draft['qty_sample_raw'] ?? '';
                $this->eventNote = $draft['event_note'] ?? '';
                $this->remark = $draft['remark'] ?? '';
                $this->problems = $draft['problems'] ?? [];
                $this->nextActions = $draft['next_actions'] ?? [];
                $this->feedbacks = $draft['feedbacks'] ?? [];
                $this->attachments = $draft['attachments'] ?? [];
                $this->autosaveStatus = 'saved';
            }
        } else {
            $this->activityDate = date('Y-m-d');
            if ($initProjectId) {
                $this->projectId = $initProjectId;
            }
        }

        if (! empty($this->projectId)) {
            $this->selectedProject = FakeStore::findProject($this->projectId);
        }
    }

    public function updatedProjectId(string $value): void
    {
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
        if (empty(trim($this->projectId))) {
            $errors['projectId'] = 'JO No wajib dipilih.';
        }
        if (empty(trim($this->activityDate))) {
            $errors['activityDate'] = 'Tanggal Aktivitas wajib diisi.';
        }
        $allowed = ['Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'];
        if (! in_array($this->activity, $allowed, true)) {
            $errors['activity'] = 'Pilihan Activity tidak valid.';
        }

        if (! empty($errors)) {
            foreach ($errors as $field => $msg) {
                $this->addError($field, $msg);
            }
            return;
        }

        $payload = [
            'id' => $this->progressId,
            'project_id' => $this->projectId,
            'activity' => $this->activity,
            'activity_date' => $this->activityDate,
            'notes' => $this->notes,
            'description' => $this->notes,
            'pic_progress' => $this->picProgress,
            'pic_customer' => $this->picCustomer,
            'process_trial' => $this->processTrial,
            'machine' => $this->machine,
            'die_high_raw' => $this->dieHighRaw,
            'material_actual_trial' => $this->materialActualTrial,
            'qty_material_raw' => $this->qtyMaterialRaw,
            'qty_process_raw' => $this->qtyProcessRaw,
            'qty_sample_raw' => $this->qtySampleRaw,
            'event_note' => $this->eventNote,
            'remark' => $this->remark,
            'problems' => $this->problems,
            'next_actions' => $this->nextActions,
            'feedbacks' => $this->feedbacks,
            'attachments' => $this->attachments,
        ];

        try {
            $saved = FakeStore::saveProgress($payload);
            if ($this->draftUuid) {
                FakeStore::deleteDraft($this->draftUuid);
            }
            $this->progressId = $saved['id'];
            $this->successMessage = 'Progress kegiatan berhasil disimpan.';
            $this->dispatch('progress-saved', progressId: $saved['id']);
            return redirect()->to('?tab=progresses&id=' . $saved['id']);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $this->addError($field, $msg);
                }
            }
        }
    }

    /**
     * Simpan sebagai draft (D11):
     * Tidak mempengaruhi hitungan Open/Closed dan dapat dilanjutkan nanti.
     */
    public function saveDraft(): void
    {
        $payload = [
            'uuid' => $this->draftUuid,
            'type' => $this->progressId ? 'edit' : 'new',
            'progress_id' => $this->progressId,
            'project_id' => $this->projectId,
            'activity' => $this->activity,
            'activity_date' => $this->activityDate,
            'notes' => $this->notes,
            'pic_progress' => $this->picProgress,
            'pic_customer' => $this->picCustomer,
            'process_trial' => $this->processTrial,
            'machine' => $this->machine,
            'die_high_raw' => $this->dieHighRaw,
            'material_actual_trial' => $this->materialActualTrial,
            'qty_material_raw' => $this->qtyMaterialRaw,
            'qty_process_raw' => $this->qtyProcessRaw,
            'qty_sample_raw' => $this->qtySampleRaw,
            'event_note' => $this->eventNote,
            'remark' => $this->remark,
            'problems' => $this->problems,
            'next_actions' => $this->nextActions,
            'feedbacks' => $this->feedbacks,
            'attachments' => $this->attachments,
        ];

        $draft = FakeStore::saveDraft($payload);
        $this->draftUuid = $draft['uuid'];
        $this->autosaveStatus = 'saved';
        $this->lastSavedTime = date('H:i');
        $this->successMessage = 'Draft berhasil disimpan (tidak mempengaruhi status Open/Closed).';
    }

    public function cancel()
    {
        return redirect()->to('?tab=dashboard');
    }

    public function render()
    {
        $projects = FakeStore::getProjects();

        return view('livewire.prototype.progress-form', [
            'projects' => $projects,
        ]);
    }
}
