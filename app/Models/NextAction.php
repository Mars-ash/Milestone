<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NextAction extends Model
{
    use HasFactory;

    protected $table = 'next_actions';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['actual_date' => 'date:Y-m-d'];
    }

    public function progress(): BelongsTo
    {
        return $this->belongsTo(Progress::class);
    }
}
