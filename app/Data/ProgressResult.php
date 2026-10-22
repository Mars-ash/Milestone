<?php

namespace App\Data;

use App\Models\Progress;

final readonly class ProgressResult
{
    public function __construct(public Progress $progress, public string $outcome, public string $message) {}
}
