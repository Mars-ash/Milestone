<?php

namespace App\Data;

use App\Models\Project;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class ProjectPayload
{
    public const DATES = ['date_receive', 't0_target', 't0_actual', 'plan_start', 'plan_finish', 'actual_start', 'actual_finish', 'event_1', 'event_2', 'event_3'];

    public const TEXT = ['part_name', 'customer', 'model', 'no_doc', 'material', 'spec', 'material_thickness_raw', 'material_width_raw', 'material_pitch_raw', 'process', 'remark'];

    private function __construct(public array $data) {}

    public static function fromArray(array $input, ?Project $project = null): self
    {
        if ($unknown = array_diff(array_keys($input), ['jo_no', 'part_no', ...self::TEXT, ...self::DATES])) {
            throw ValidationException::withMessages(['payload' => 'Field tidak dikenal: '.implode(', ', $unknown)]);
        }
        $rules = [];
        foreach (['jo_no', 'part_no'] as $key) {
            if (isset($input[$key]) && is_string($input[$key])) {
                $input[$key] = trim($input[$key]);
            }
            $rules[$key] = ['required', 'string', 'max:255', Rule::unique('projects', $key)->ignore($project?->id)];
        }
        foreach (self::TEXT as $field) {
            $rules[$field] = [$field === 'part_name' ? 'required' : 'nullable', 'string'];
        }
        foreach (self::DATES as $field) {
            if (array_key_exists($field, $input) && $input[$field] === '') {
                $input[$field] = null;
            }
            $rules[$field] = ['nullable', 'date_format:Y-m-d'];
        }
        Validator::make($input, $rules)->validate();

        return new self($input);
    }
}
