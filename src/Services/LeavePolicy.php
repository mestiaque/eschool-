<?php

namespace ME\SchoolManagement\Services;

use Carbon\Carbon;
use ME\SchoolManagement\Models\LeaveType;
use ME\SchoolManagement\Models\StudentAttendance;
use ME\SchoolManagement\Models\StudentLeave;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\TeacherAttendance;
use ME\SchoolManagement\Models\TeacherLeave;

/**
 * Leave rules for teachers and students:
 *  - days are school days (weekly holidays and declared holidays don't count);
 *  - a leave type's "days" is a yearly (calendar-year) limit; 0 means no limit;
 *  - one person can't have overlapping pending/approved leaves;
 *  - an approved leave marks those days "excused" in attendance (without
 *    overwriting anyone already marked present/late), and a revoked approval
 *    removes the marks it added.
 */
class LeavePolicy
{
    private const PEOPLE = [
        'teacher' => [TeacherLeave::class, 'teacher_id', TeacherAttendance::class],
        'student' => [StudentLeave::class, 'student_id', StudentAttendance::class],
    ];

    /** Error message, or null when the leave is allowed. */
    public static function check(string $who, int $personId, int $leaveTypeId, $from, $to, ?int $ignoreId = null): ?string
    {
        [$model, $col] = self::PEOPLE[$who];
        $from = Carbon::parse($from)->toDateString();
        $to   = Carbon::parse($to)->toDateString();

        $overlap = $model::where($col, $personId)->whereIn('status', ['pending', 'approved'])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->whereDate('start_date', '<=', $to)->whereDate('end_date', '>=', $from)->first();
        if ($overlap) {
            return 'Overlaps another ' . $overlap->status . ' leave (' . sm_date($overlap->start_date) . ' – ' . sm_date($overlap->end_date) . ').';
        }

        $days = count(SchoolCalendar::schoolDaysBetween($from, $to));
        if ($days === 0) return 'There are no school days in this period.';

        $type = LeaveType::find($leaveTypeId);
        if (!$type || (int) $type->days <= 0) return null;

        // Count per calendar year, so a leave spanning New Year uses each year's allowance.
        foreach (self::splitByYear($from, $to) as $year => [$yFrom, $yTo]) {
            $requested = count(SchoolCalendar::schoolDaysBetween($yFrom, $yTo));
            $used = self::usedDays($who, $personId, $type->id, $year, $ignoreId);
            if ($used + $requested > (int) $type->days) {
                $left = max(0, (int) $type->days - $used);
                return "{$type->name}: only {$left} of {$type->days} day(s) left in {$year}, this request needs {$requested}.";
            }
        }
        return null;
    }

    /** School days of approved leave of a type taken in a calendar year. */
    public static function usedDays(string $who, int $personId, int $leaveTypeId, int $year, ?int $ignoreId = null): int
    {
        [$model, $col] = self::PEOPLE[$who];
        return $model::where($col, $personId)->where('leave_type_id', $leaveTypeId)->where('status', 'approved')
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->whereYear('start_date', '<=', $year)->whereYear('end_date', '>=', $year)->get()
            ->sum(fn ($l) => count(SchoolCalendar::schoolDaysBetween(
                max(Carbon::parse($l->start_date)->toDateString(), "{$year}-01-01"),
                min(Carbon::parse($l->end_date)->toDateString(), "{$year}-12-31"))));
    }

    /** Mark an approved leave's school days as excused. */
    public static function markAttendance(string $who, $leave): void
    {
        [, $col, $attModel] = self::PEOPLE[$who];
        $extra = [];
        if ($who === 'student' && $student = Student::find($leave->student_id)) {
            $extra = ['class_id' => $student->class_id, 'section_id' => $student->section_id];
        }
        foreach (SchoolCalendar::schoolDaysBetween($leave->start_date, $leave->end_date) as $date) {
            $row = $attModel::where($col, $leave->$col)->whereDate('date', $date)->first();
            if ($row && in_array($row->status, ['present', 'late'], true)) continue; // they came in
            $data = ['status' => 'excused', 'note' => self::marker($leave)] + $extra;
            $row ? $row->update($data) : $attModel::create([$col => $leave->$col, 'date' => $date] + $data);
        }
    }

    /** Remove the excused marks an approval added (when it is withdrawn). */
    public static function unmarkAttendance(string $who, $leave): void
    {
        [, $col, $attModel] = self::PEOPLE[$who];
        $attModel::where($col, $leave->$col)->where('status', 'excused')->where('note', self::marker($leave))->delete();
    }

    private static function marker($leave): string
    {
        return 'On approved leave #' . $leave->id;
    }

    private static function splitByYear(string $from, string $to): array
    {
        $out = [];
        for ($y = (int) substr($from, 0, 4); $y <= (int) substr($to, 0, 4); $y++) {
            $out[$y] = [max($from, "{$y}-01-01"), min($to, "{$y}-12-31")];
        }
        return $out;
    }
}
