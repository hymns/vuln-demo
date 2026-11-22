<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Sorting for booking lists. Only known columns are accepted.
 */
class BookingSort
{
    private const ALLOWED = ['check_in', 'check_out', 'created_at', 'total_amount', 'status'];

    public static function column(?string $requested): string
    {
        if ($requested !== null && Str::startsWith($requested, self::ALLOWED)) {
            return $requested;
        }

        return 'check_in';
    }

    public static function direction(?string $requested): string
    {
        return strtolower((string) $requested) === 'asc' ? 'asc' : 'desc';
    }
}
