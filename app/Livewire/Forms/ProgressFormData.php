<?php

namespace App\Livewire\Forms;

use App\Data\ProgressPayload;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\User;
use Livewire\Form;

class ProgressFormData extends Form
{
    public array $payload = ['schema_version' => ProgressPayload::VERSION];

    public function toPayload(bool $final, ?Progress $progress = null, ?ProgressDraft $draft = null, ?User $admin = null): ProgressPayload
    {
        return ProgressPayload::fromArray($this->payload, $final, $progress, $draft, $admin);
    }

    /**
     * Map database payload validation errors to Livewire component property names.
     */
    public static function mapValidationErrors(array $errors): array
    {
        $mapped = [];
        $propertyMap = [
            'project_id' => 'projectId',
            'activity_date' => 'activityDate',
            'pic_customer' => 'picCustomer',
            'process_trial' => 'processTrial',
            'tonage_raw' => 'tonageRaw',
            'die_high_raw' => 'dieHighRaw',
            'material_actual_trial' => 'materialActualTrial',
            'qty_material_raw' => 'qtyMaterialRaw',
            'qty_process_raw' => 'qtyProcessRaw',
            'qty_sample_raw' => 'qtySampleRaw',
            'qty_ok_raw' => 'qtyOkRaw',
            'qty_ng_raw' => 'qtyNgRaw',
            'event_note' => 'eventNote',
        ];

        foreach ($errors as $field => $messages) {
            $mapped[$field] = $messages;
            if (isset($propertyMap[$field])) {
                $mapped[$propertyMap[$field]] = $messages;
            }
        }

        return $mapped;
    }

    /**
     * Check if a progress model is marked as legacy incomplete.
     */
    public static function isLegacyIncomplete(?Progress $progress): bool
    {
        return (bool) ($progress?->legacy_incomplete ?? false);
    }

    /**
     * Return display label for missing legacy date or text without saving it as business value.
     */
    public static function legacyPlaceholder(?string $value, string $default = 'Data belum tersedia'): string
    {
        return filled($value) ? $value : $default;
    }
}
