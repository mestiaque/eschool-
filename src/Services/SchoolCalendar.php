<?php

namespace ME\SchoolManagement\Services;

use Carbon\Carbon;
use ME\SchoolManagement\Models\Holiday;

/**
 * Which days the school is open. Weekly holidays come from Settings → General
 * (default Friday + Saturday, the national schedule); the Holidays page adds
 * closed dates ("holiday") and make-up open dates ("school_day", e.g. a
 * Saturday class to recover lost time).
 */
class SchoolCalendar
{
    /** @return int[] days of week, 0 = Sunday … 6 = Saturday */
    public static function weekendDays(): array
    {
        $value = sm_setting('weekend_days', '5,6');
        return $value === '' ? [] : array_map('intval', explode(',', $value));
    }

    /** Holidays and extra school days in a date range, keyed by Y-m-d. */
    public static function exceptions(string $from, string $to): array
    {
        return Holiday::where('is_active', true)->whereBetween('date', [$from, $to])->get()
            ->keyBy(fn ($h) => Carbon::parse($h->date)->toDateString())->all();
    }

    /** Null when the school is open that day, otherwise the reason it is closed. */
    public static function closedReason($date): ?string
    {
        $date = Carbon::parse($date);
        $day  = self::exceptions($date->toDateString(), $date->toDateString())[$date->toDateString()] ?? null;
        if ($day) {
            return $day->type === 'school_day' ? null : 'Holiday: ' . $day->title;
        }
        return in_array($date->dayOfWeek, self::weekendDays(), true) ? 'Weekly holiday (' . $date->format('l') . ')' : null;
    }

    /** Dates (Y-m-d) the school is open between two dates, inclusive. */
    public static function schoolDaysBetween($from, $to): array
    {
        $from = Carbon::parse($from)->startOfDay();
        $to   = Carbon::parse($to)->startOfDay();
        if ($to->lt($from)) return [];
        $exceptions = self::exceptions($from->toDateString(), $to->toDateString());
        $weekend = self::weekendDays();
        $days = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $key = $d->toDateString();
            $ex = $exceptions[$key] ?? null;
            $open = $ex ? $ex->type === 'school_day' : !in_array($d->dayOfWeek, $weekend, true);
            if ($open) $days[] = $key;
        }
        return $days;
    }
}
