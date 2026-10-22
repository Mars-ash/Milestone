<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Progress extends Model
{
    use HasFactory;

    protected $table = 'progresses';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['activity_date' => 'date:Y-m-d', 'legacy_incomplete' => 'boolean', 'revision' => 'integer'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class)->orderBy('position')->orderBy('id');
    }

    public function nextActions(): HasMany
    {
        return $this->hasMany(NextAction::class)->orderBy('position')->orderBy('id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class)->orderBy('position')->orderBy('id');
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(ProgressDraft::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function legacyDocuments(): HasMany
    {
        return $this->hasMany(LegacyDocumentMetadata::class);
    }
}
