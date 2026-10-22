<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class UiFormat
{
    public static function date(?string $value): string
    {
        return filled($value)
            ? Carbon::parse($value, 'Asia/Jakarta')->locale('id')->isoFormat('DD MMM YYYY')
            : 'Belum diisi';
    }

    public static function time(?string $value): string
    {
        return filled($value)
            ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('H:i')
            : '';
    }
}
