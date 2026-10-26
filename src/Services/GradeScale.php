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

    public static function fail(): array
    {
        $rows = self::rows();
        return end($rows) ?: ['id' => null, 'name' => 'F', 'point' => 0.0, 'from' => 0];
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
