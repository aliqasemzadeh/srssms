<?php

namespace App\Support;

use Carbon\Carbon;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;
use Throwable;

class JalaliDate
{
    /**
     * Years in this range are treated as Jalali when parsing user input
     * from the Persian date picker (e.g. 1405/05/03).
     */
    private const JALALI_YEAR_MIN = 1200;

    private const JALALI_YEAR_MAX = 1500;

    public static function parse(?string $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            $normalized = CalendarUtils::convertNumbers(
                str_replace('-', '/', trim((string) $value)),
                true
            );

            $parts = preg_split('/[\/]/', $normalized) ?: [];

            if (count($parts) !== 3) {
                return Carbon::parse((string) $value)->startOfDay();
            }

            $year = (int) $parts[0];
            $date = sprintf('%04d/%02d/%02d', $year, (int) $parts[1], (int) $parts[2]);

            if (self::isJalaliYear($year)) {
                return Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->startOfDay();
            }

            return Carbon::createFromFormat('Y/m/d', $date)?->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    public static function toGregorianString(?string $value): ?string
    {
        return self::parse($value)?->format('Y-m-d');
    }

    public static function format(?Carbon $date, string $format = 'Y/m/d'): ?string
    {
        if (! $date) {
            return null;
        }

        if (app()->getLocale() !== 'fa') {
            return $date->format(str_replace('/', '-', $format));
        }

        // Already stored as Jalali numerals (legacy bug) — show as-is.
        if (self::isJalaliYear($date->year)) {
            return $date->format($format);
        }

        try {
            return Jalalian::fromCarbon($date)->format($format);
        } catch (Throwable) {
            return $date->format($format);
        }
    }

    public static function isJalaliYear(int $year): bool
    {
        return $year >= self::JALALI_YEAR_MIN && $year <= self::JALALI_YEAR_MAX;
    }
}
