<?php

namespace App\Livewire\Projects;

use App\Actions\SaveProject;
use App\Models\Project;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProjectForm extends Component
{
    public ?int $projectId = null;

    public ?Project $project = null;

    // Identitas Wajib & Unik (FR01, AT01, AT02)
    public string $jo_no = '';

    public string $part_no = '';

    public string $part_name = '';

    // Identitas Opsional
    public ?string $customer = '';

    public ?string $model = '';

    public ?string $no_doc = '';

    public ?string $date_receive = null;

    // Spesifikasi & Proses (D15 raw text)
    public ?string $material = '';

    public ?string $material_thickness_raw = '';

    public ?string $material_width_raw = '';

    public ?string $material_pitch_raw = '';

    public ?string $process = '';

    // Jadwal Master Manual (FR03, AT04, D14)
    public ?string $t0_target = null;

    public ?string $t0_actual = null;

    public ?string $plan_start = null;

    public ?string $plan_finish = null;

    public ?string $actual_start = null;

    public ?string $actual_finish = null;

    public ?string $event_1 = null;

    public ?string $event_2 = null;

    public ?string $event_3 = null;

    // Catatan Project
    public ?string $remark = '';

    public ?string $successMessage = null;

    public bool $returnToDetail = false;

    public function mount(?int $projectId = null): void
    {
        $this->projectId = $projectId;
        $this->returnToDetail = filled(request()->query('detail'));

        if ($this->projectId) {
            $this->project = Project::find($this->projectId);
            if ($this->project) {
                $this->jo_no = (string) ($this->project->jo_no ?? '');
                $this->part_no = (string) ($this->project->part_no ?? '');
                $this->part_name = (string) ($this->project->part_name ?? '');
                $this->customer = $this->project->customer ?? '';
                $this->model = $this->project->model ?? '';
                $this->no_doc = $this->project->no_doc ?? '';
                $this->date_receive = $this->project->date_receive?->format('Y-m-d');
                $this->material = $this->project->material ?? '';
                $this->material_thickness_raw = $this->project->material_thickness_raw ?? '';
                $this->material_width_raw = $this->project->material_width_raw ?? '';
                $this->material_pitch_raw = $this->project->material_pitch_raw ?? '';
                $this->process = $this->project->process ?? '';
                $this->t0_target = $this->project->t0_target?->format('Y-m-d');
                $this->t0_actual = $this->project->t0_actual?->format('Y-m-d');
                $this->plan_start = $this->project->plan_start?->format('Y-m-d');
                $this->plan_finish = $this->project->plan_finish?->format('Y-m-d');
                $this->actual_start = $this->project->actual_start?->format('Y-m-d');
                $this->actual_finish = $this->project->actual_finish?->format('Y-m-d');
                $this->event_1 = $this->project->event_1?->format('Y-m-d');
                $this->event_2 = $this->project->event_2?->format('Y-m-d');
                $this->event_3 = $this->project->event_3?->format('Y-m-d');
                $this->remark = $this->project->remark ?? '';
            }
        }
    }

    public function save(SaveProject $action)
    {
        $this->resetErrorBag();

        $payload = [
            'jo_no' => $this->jo_no,
            'part_no' => $this->part_no,
            'part_name' => $this->part_name,
            'customer' => $this->customer,
            'model' => $this->model,
            'no_doc' => $this->no_doc,
            'date_receive' => $this->date_receive ?: null,
            'material' => $this->material,
            'material_thickness_raw' => $this->material_thickness_raw,
            'material_width_raw' => $this->material_width_raw,
            'material_pitch_raw' => $this->material_pitch_raw,
            'process' => $this->process,
            't0_target' => $this->t0_target ?: null,
            't0_actual' => $this->t0_actual ?: null,
            'plan_start' => $this->plan_start ?: null,
            'plan_finish' => $this->plan_finish ?: null,
            'actual_start' => $this->actual_start ?: null,
            'actual_finish' => $this->actual_finish ?: null,
            'event_1' => $this->event_1 ?: null,
            'event_2' => $this->event_2 ?: null,
            'event_3' => $this->event_3 ?: null,
            'remark' => $this->remark,
        ];

        try {
            $saved = $action->handle($payload, $this->project);
            $this->project = $saved;
            $this->projectId = $saved->id;
            $this->successMessage = 'Data Master Project berhasil disimpan.';
            $this->dispatch('project-saved', projectId: $saved->id);
            $this->dispatch('close-project-dialog', projectId: $saved->id);

            session()->flash('ui-toast', 'Project tersimpan. Identitas seluruh progress mengikuti data terbaru.');

            return $saved;
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $this->addError($field, $msg);
                }
            }
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-project-dialog');
    }

    public function render()
    {
        return view('livewire.projects.project-form');
    }
}
