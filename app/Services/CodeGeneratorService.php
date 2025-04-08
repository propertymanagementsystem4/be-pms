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

    public static function generateRoomCode(string $propertyCode): string
    {
        $random = str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
        $timestamp = substr((string) now()->getTimestampMs(), -3); // Get last 3 digits

        return "{$propertyCode}-RM{$random}{$timestamp}";
    }

}