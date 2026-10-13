<?php

namespace App\Livewire\Prototype;

use App\Prototype\FakeStore;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProjectForm extends Component
{
    public ?string $projectId = null;

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
    public ?string $spec = '';
    public ?string $material_thickness_raw = '';
    public ?string $material_width_raw = '';
    public ?string $process = '';
    public ?string $dh = '';
    public ?string $size = '';
    public ?string $qty = '';

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

    public function mount(?string $projectId = null): void
    {
        $this->projectId = $projectId ?? request()->query('id');

        if ($this->projectId) {
            $project = FakeStore::findProject($this->projectId);
            if ($project) {
                $this->jo_no = (string) ($project['jo_no'] ?? '');
                $this->part_no = (string) ($project['part_no'] ?? '');
                $this->part_name = (string) ($project['part_name'] ?? '');
                $this->customer = $project['customer'] ?? '';
                $this->model = $project['model'] ?? '';
                $this->no_doc = $project['no_doc'] ?? '';
                $this->date_receive = $project['date_receive'] ?? null;
                $this->material = $project['material'] ?? '';
                $this->spec = $project['spec'] ?? '';
                $this->material_thickness_raw = $project['material_thickness_raw'] ?? '';
                $this->material_width_raw = $project['material_width_raw'] ?? '';
                $this->process = $project['process'] ?? '';
                $this->dh = $project['dh'] ?? '';
                $this->size = $project['size'] ?? '';
                $this->qty = $project['qty'] ?? '';
                $this->t0_target = $project['t0_target'] ?? $project['t0_target_date'] ?? null;
                $this->t0_actual = $project['t0_actual'] ?? $project['t0_actual_date'] ?? null;
                $this->plan_start = $project['plan_start'] ?? null;
                $this->plan_finish = $project['plan_finish'] ?? null;
                $this->actual_start = $project['actual_start'] ?? null;
                $this->actual_finish = $project['actual_finish'] ?? null;
                $this->event_1 = $project['event_1'] ?? $project['event_1_date'] ?? null;
                $this->event_2 = $project['event_2'] ?? $project['event_2_date'] ?? null;
                $this->event_3 = $project['event_3'] ?? $project['event_3_date'] ?? null;
                $this->remark = $project['remark'] ?? '';
            }
        }
    }

    public function save()
    {
        $this->resetErrorBag();

        $payload = [
            'id' => $this->projectId,
            'jo_no' => $this->jo_no,
            'part_no' => $this->part_no,
            'part_name' => $this->part_name,
            'customer' => $this->customer,
            'model' => $this->model,
            'no_doc' => $this->no_doc,
            'date_receive' => $this->date_receive ?: null,
            'material' => $this->material,
            'spec' => $this->spec,
            'material_thickness_raw' => $this->material_thickness_raw,
            'material_width_raw' => $this->material_width_raw,
            'process' => $this->process,
            'dh' => $this->dh,
            'size' => $this->size,
            'qty' => $this->qty,
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
            $saved = FakeStore::saveProject($payload);
            $this->projectId = $saved['id'];
            $this->successMessage = 'Data Master Project berhasil disimpan.';
            $this->dispatch('project-saved', projectId: $saved['id']);
            return redirect()->to('?tab=projects&detail=' . $saved['id']);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $this->addError($field, $msg);
                }
            }
        }
    }

    public function cancel()
    {
        return redirect()->to('?tab=projects');
    }

    public function render()
    {
        return view('livewire.prototype.project-form');
    }
}
