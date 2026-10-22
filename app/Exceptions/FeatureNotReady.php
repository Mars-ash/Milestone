<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class FeatureNotReady extends HttpException
{
    public function __construct(string $feature)
    {
        parent::__construct(501, $feature.' belum diimplementasikan. Isian belum diterapkan.');
    }
}
