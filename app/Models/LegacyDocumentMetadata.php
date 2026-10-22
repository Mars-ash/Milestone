<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegacyDocumentMetadata extends Model
{
    use HasFactory;

    protected $table = 'legacy_document_metadata';

    protected $guarded = ['id'];

    public function progress(): BelongsTo
    {
        return $this->belongsTo(Progress::class);
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }
}
