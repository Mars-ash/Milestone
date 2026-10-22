<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class VersionConflict extends HttpException
{
    public function __construct()
    {
        parent::__construct(409, 'Data berubah pada tab lain. Buka versi terbaru; draft tetap dipertahankan.');
    }
}
