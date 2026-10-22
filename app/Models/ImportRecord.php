<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_table',
        'source_id',
        'target_table',
        'target_id',
        'checksum',
        'status',
        'notes',
    ];
}
