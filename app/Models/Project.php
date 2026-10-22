<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $guarded = ['id'];

    public const PROCESS_OPTIONS = [
        'Stamping Progressive',
        'Stamping Manual 1',
        'Stamping Manual 2',
        'Stamping Manual 3',
        'Stamping Manual 4',
        'Stamping Manual 5',
        'Spot Welding',
        'Tig Welding',
        'Mig Welding',
        'Forming',
        'Tapping',
    ];

    protected function casts(): array
    {
        return ['date_receive' => 'date:Y-m-d', 't0_target' => 'date:Y-m-d', 't0_actual' => 'date:Y-m-d', 'plan_start' => 'date:Y-m-d', 'plan_finish' => 'date:Y-m-d', 'actual_start' => 'date:Y-m-d', 'actual_finish' => 'date:Y-m-d', 'event_1' => 'date:Y-m-d', 'event_2' => 'date:Y-m-d', 'event_3' => 'date:Y-m-d', 'legacy_incomplete' => 'boolean'];
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }
}
