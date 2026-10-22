<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProgressDraft extends Model
{
    use HasFactory;

    protected $table = 'progress_drafts';

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return ['payload' => 'array', 'base_revision' => 'integer', 'draft_revision' => 'integer', 'request_sequence' => 'integer', 'schema_version' => 'integer'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function progress(): BelongsTo
    {
        return $this->belongsTo(Progress::class);
    }

    public function appliedProgress(): BelongsTo
    {
        return $this->belongsTo(Progress::class, 'applied_progress_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'draft_id');
    }

    public function isApplied(): bool
    {
        return $this->state === 'applied';
    }

    public function isActive(): bool
    {
        return $this->state === 'active';
    }

    public function isEditDraft(): bool
    {
        return $this->progress_id !== null;
    }

    public function isNewDraft(): bool
    {
        return $this->progress_id === null;
    }
}
