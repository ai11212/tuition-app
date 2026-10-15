<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Single place where stored timestamps are turned into local display time.
 *
 * Storage stays UTC (config('app.timezone')). Display uses a NAMED zone
 * (config('app.display_timezone')), so the UK BST/GMT change is applied
 * automatically using the rule in force on each record's own date — a July
 * payment keeps showing BST even when viewed in December.
 */
class DisplayTime
{
    public static function zone(): string
    {
        return config('app.display_timezone', 'Europe/London');
    }

    /** A stored UTC timestamp as a Carbon in the display zone, or null */
    public static function local($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value)->setTimezone(self::zone());
    }

    /**
     * Combine a date-only business field with the time-of-day from a stored
     * timestamp.
     *
     * Date-only columns (paid_on, period_from, …) are the business record and
     * are never converted — billing periods, filters and edit forms all key off
     * them. Only the clock time is taken from the timestamp. Without this, a
     * payment keyed at 23:48 UTC would display on the following day and
     * disagree with its own invoice period.
     */
    public static function onDate($date, $timestamp): ?Carbon
    {
        $time = self::local($timestamp);

        if (empty($date)) {
            return $time;
        }

        $day = Carbon::parse($date);

        return $time
            ? $day->setTime((int) $time->format('H'), (int) $time->format('i'), (int) $time->format('s'))
            : $day;
    }
}
