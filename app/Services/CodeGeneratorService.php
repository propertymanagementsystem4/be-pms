<?php

namespace App\Services;

class CodeGeneratorService
{
    public static function generatePropertyCode(): string
    {
        $date = now()->format('Ymd');
        $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return "PRP-{$date}-{$randomNumber}";
    }

}