<?php

namespace ME\SchoolManagement\Services;

use ME\SchoolManagement\Models\Grade;

/**
 * The school's grade scale (Examinations → Grades), or the standard
 * Bangladesh scale when none is configured. Used by exams, class tests and
 * assignments so a percentage always maps to the same letter.
 */
class GradeScale
{
    public const DEFAULT_SCALE = [
        ['name' => 'A+', 'point' => 5.00, 'from' => 80],
        ['name' => 'A',  'point' => 4.00, 'from' => 70],
        ['name' => 'A-', 'point' => 3.50, 'from' => 60],
        ['name' => 'B',  'point' => 3.00, 'from' => 50],
        ['name' => 'C',  'point' => 2.00, 'from' => 40],
        ['name' => 'D',  'point' => 1.00, 'from' => 33],
        ['name' => 'F',  'point' => 0.00, 'from' => 0],
    ];

    private static ?array $scale = null;

    /** [id, name, point, from] rows, highest first; "from" is a percentage. */
    public static function rows(): array
    {
        return self::$scale ??= ($grades = Grade::where('is_active', true)->orderByDesc('mark_from')->get())->isNotEmpty()
            ? $grades->map(fn ($g) => ['id' => $g->id, 'name' => $g->name, 'point' => (float) $g->grade_point, 'from' => (float) $g->mark_from])->all()
            : array_map(fn ($g) => $g + ['id' => null], self::DEFAULT_SCALE);
    }

    /** Forget the cached scale (after grades change, and between tests). */
    public static function flush(): void
    {
        self::$scale = null;
    }

    public static function forPercent(float $percent): array
    {
        foreach (self::rows() as $g) {
            if ($percent >= $g['from']) return $g;
        }
        return self::fail();
    }

    /**
     * The failing grade: the scale's 0-point row, or a plain F when the
     * configured scale has none (never the lowest passing grade).
     */
    public static function fail(): array
    {
        foreach (array_reverse(self::rows()) as $g) {
            if ($g['point'] <= 0) return $g;
        }
        return ['id' => null, 'name' => 'F', 'point' => 0.0, 'from' => 0];
    }

    public static function isFail(?string $grade): bool
    {
        return $grade === null || $grade === self::fail()['name'];
    }

    /** Problems with the configured scale, for the Grade Setup page (translated). */
    public static function problems(): array
    {
        $grades = Grade::where('is_active', true)->orderBy('mark_from')->get()->values();
        if ($grades->isEmpty()) return [];
        $say = fn (string $text, array $vars) => strtr(sm_t($text), $vars);
        $out = [];
        if ((float) $grades->first()->mark_from > 0) {
            $out[] = $say('No grade starts at 0; marks below :from% get ":grade".', [':from' => (float) $grades->first()->mark_from, ':grade' => self::fail()['name']]);
        }
        if (!$grades->contains(fn ($g) => (float) $g->grade_point <= 0)) {
            $out[] = $say('No grade has 0 grade point, so a failing subject is shown as "F".', []);
        }
        foreach ($grades as $i => $g) {
            $next = $grades[$i + 1] ?? null;
            if (!$next) break;
            if ((float) $next->mark_from - (float) $g->mark_to > 1) {
                $out[] = $say('Gap between :to% and :from%; marks in it take grade ":grade".', [':to' => (float) $g->mark_to, ':from' => (float) $next->mark_from, ':grade' => $g->name]);
            }
            if ((float) $next->grade_point < (float) $g->grade_point) {
                $out[] = $say('Grade ":high" has a higher mark range but a lower point than ":low".', [':high' => $next->name, ':low' => $g->name]);
            }
        }
        return $out;
    }

    public static function forGpa(float $gpa): string
    {
        foreach (self::rows() as $g) {
            if ($gpa >= $g['point']) return $g['name'];
        }
        return self::fail()['name'];
    }

    /**
     * Letter for a mark out of $total; below the test's own pass mark is always
     * the fail grade, so "pass" and the letter can never disagree.
     */
    public static function letter(float $marks, float $total, ?float $passMark = null): ?string
    {
        if ($total <= 0) return null;
        if ($passMark !== null && $marks < $passMark) return self::fail()['name'];
        return self::forPercent($marks / $total * 100)['name'];
    }
}
