<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UploadNotReady extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, 'Unggahan belum siap. Tunggu konfirmasi unggahan sebelum Simpan.');
    }
}
