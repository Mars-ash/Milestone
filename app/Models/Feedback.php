<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['feedback_date' => 'date:Y-m-d'];
    }

    public function progress(): BelongsTo
    {
        return $this->belongsTo(Progress::class);
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
