<?php

namespace ME\SchoolManagement\Services;

use Illuminate\Support\Collection;
use ME\SchoolManagement\Models\ClassRoutine;

/**
 * Turns flat routine rows into printable timetables: one chart per routine
 * title → class → section, with time slots as columns and days as rows.
 */
class RoutineGrid
{
    private const DAY_NUMBER = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];

    /**
     * @return Collection title label => list of ['class', 'section', 'routines']
     */
    public static function charts(Collection $routines): Collection
    {
        return $routines
            ->sortBy(fn ($r) => $r->title ?? '')
            ->groupBy(fn ($r) => $r->title ?? '')
            ->map(function ($byTitle) {
                return $byTitle
                    ->sortBy(fn ($r) => [$r->class->numeric_name ?? 0, $r->class->name ?? '', $r->class_id])
                    ->groupBy('class_id')
                    ->flatMap(fn ($byClass) => self::sectionCharts($byClass))
                    ->values();
            });
    }

    /**
     * A class whose periods are all class-wide gets one chart; otherwise one
     * chart per section, each also showing the class-wide periods.
     */
    public static function sectionCharts(Collection $classRoutines): Collection
    {
        $class = $classRoutines->first()->class;
        $classWide = $classRoutines->whereNull('section_id');
        $bySection = $classRoutines->whereNotNull('section_id')->groupBy('section_id');

        if ($bySection->isEmpty()) {
            return collect([['class' => $class, 'section' => null, 'routines' => $classRoutines]]);
        }

        return $bySection
            ->sortBy(fn ($rows) => $rows->first()->section->name ?? '')
            ->map(fn ($rows) => ['class' => $class, 'section' => $rows->first()->section, 'routines' => $rows->merge($classWide)])
            ->values();
    }

    /**
     * Distinct start–end slots in time order, with a 'break' entry wherever
     * one period ends before the next begins.
     *
     * @return array<int, array{type: string, start: string, end: string, number?: int}>
     */
    public static function slots(Collection $routines): array
    {
        $periods = $routines
            ->map(fn ($r) => ['start' => substr($r->start_time, 0, 5), 'end' => substr($r->end_time, 0, 5)])
            ->unique(fn ($s) => $s['start'] . '-' . $s['end'])
            ->sortBy(fn ($s) => $s['start'] . $s['end'])
            ->values();

        $slots = [];
        $number = 0;
        $lastEnd = null;
        foreach ($periods as $p) {
            if ($lastEnd !== null && $p['start'] > $lastEnd) {
                $slots[] = ['type' => 'break', 'start' => $lastEnd, 'end' => $p['start']];
            }
            $slots[] = ['type' => 'period', 'number' => ++$number] + $p;
            $lastEnd = max($lastEnd ?? $p['end'], $p['end']);
        }

        return $slots;
    }

    /**
     * Days to print, Saturday first: every day that has periods, plus the
     * open school days, plus weekly holidays (shown as a closed row).
     *
     * @return array<string, bool> day => is weekly holiday without periods
     */
    public static function days(Collection $routines, ?string $onlyDay = null): array
    {
        $weekend = SchoolCalendar::weekendDays();
        $used = $routines->pluck('day')->unique()->all();

        $days = [];
        foreach (ClassRoutine::WEEK as $day) {
            if ($onlyDay && $day !== $onlyDay) continue;
            $days[$day] = !in_array($day, $used, true) && in_array(self::DAY_NUMBER[$day], $weekend, true);
        }
        return $days;
    }

    /** Periods in one cell (normally one; more means a clash worth seeing). */
    public static function cell(Collection $routines, string $day, array $slot): Collection
    {
        return $routines->filter(fn ($r) => $r->day === $day
            && substr($r->start_time, 0, 5) === $slot['start']
            && substr($r->end_time, 0, 5) === $slot['end'])->values();
    }
}
