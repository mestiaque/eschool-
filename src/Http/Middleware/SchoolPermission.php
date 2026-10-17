<?php

namespace ME\SchoolManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Enforces the permission catalog (Config/permissions.php) on this package's
 * routes. Each route name maps to "module.action" and is checked with the
 * host theme's User::hasPermission() (super admins always pass).
 *
 * Routes not listed here (dashboard, the teacher's own "my.*" pages, small
 * AJAX lookups used by forms) only require a login.
 */
class SchoolPermission
{
    /** Route-name prefix => permission module. Longest prefix wins. */
    private const MODULES = [
        'academic.years'          => 'academic_year',
        'academic.classes.routine'=> 'class_routine',
        'academic.classes'        => 'class',
        'academic.sections'       => 'section',
        'academic.subject-groups' => 'subject_group',
        'academic.subjects'       => 'subject',
        'academic.routines'       => 'class_routine',
        'academic.routine-titles' => 'class_routine',
        'academic.rooms.schedule' => 'class_routine',
        'academic.rooms'          => 'room',
        'academic.syllabi'        => 'syllabus',
        'academic.class-tests'    => 'class_test',
        'academic.class-texts'    => 'class_text',
        'academic.assignments'    => 'assignment',
        'academic.tours'          => 'school_tour',

        'students.attendance'     => 'student_attendance',
        'students.promotion'      => 'student_promotion',
        'students.transfer'       => 'student_transfer',
        'students.documents'      => 'student_document',
        'students.leaves'         => 'student_leave',
        'students.early-exits'    => 'student_early_exit',
        'students'                => 'student',

        'teachers.attendance'     => 'teacher_attendance',
        'teachers.leaves'         => 'teacher_leave',
        'teachers.leave-types'    => 'teacher_leave',
        'teachers.subjects'       => 'teacher_subject',
        'teachers.classes'        => 'teacher_class',
        'teachers.salary'         => 'teacher_salary',
        'teachers'                => 'teacher',

        'staff'                   => 'staff',
        'committee'               => 'committee',

        'fees.types'              => 'fee_type',
        'fees.structure'          => 'fee_structure',
        'fees.discounts'          => 'fee_discount',
        'fees'                    => 'fee_collection',

        'exams.types'             => 'exam',
        'exams.schedule'          => 'exam_schedule',
        'exams.grades'            => 'exam_grade',
        'exams.marks'             => 'exam_marks',
        'exams.results'           => 'exam_result',
        'exams.marksheet'         => 'exam_marksheet',
        'exams'                   => 'exam',

        'expenses.categories'        => 'expense_category',
        'expenses.income-categories' => 'income_category',
        'expenses.income'            => 'income',
        'expenses.staff-salary'      => 'staff_salary',
        'expenses.report'            => 'financial_report',
        'expenses.profit-loss'       => 'financial_report',
        'expenses'                   => 'expense',

        'notices'                 => 'notice',
        'events'                  => 'event',
        'holidays'                => 'holiday',

        'library.books'           => 'library_book',
        'library.purchase'        => 'library_book',
        'library.availability'    => 'library_book',
        'library.settings'        => 'library_book',
        'library.issues'          => 'library_issue',
        'library.members'         => 'library_member',

        'transport.vehicles'      => 'transport_vehicle',
        'transport.drivers'       => 'transport_vehicle',
        'transport.fee'           => 'transport_fee',
        'transport'               => 'transport_route',

        'inventory.products'      => 'inventory_product',
        'inventory.stock'         => 'inventory_stock',
        'inventory.purchase'      => 'inventory_purchase',
        'inventory.suppliers'     => 'inventory_supplier',

        'reports.financial'       => 'financial_report',
        'reports'                 => 'report',
        'settings'                => 'setting',
        'users.activity'          => 'activity_log',
    ];

    /** Exact route names whose action isn't derivable from the suffix. */
    private const OVERRIDES = [
        'students.get'                    => 'student.view',
        'students.admission'              => 'student.create',
        'students.portal-pin'             => 'student.edit',
        'students.idcard'                 => 'student.print',
        'students.idcard.print'           => 'student.print',
        'students.attendance.cell'        => 'student_attendance.create',
        'students.promotion.students'     => 'student_promotion.view',
        'students.transfer.approve'       => 'student_transfer.create',
        'students.transfer.reject'        => 'student_transfer.create',
        'students.documents.verify'       => 'student_document.verify',
        'teachers.link-user'              => 'teacher.link_login',
        'teachers.unlink-user'            => 'teacher.link_login',
        'teachers.leaves.update'          => 'teacher_leave.approve',
        'teachers.leave-types.store'      => 'teacher_leave.create',
        'academic.routine-titles'         => 'class_routine.manage_versions',
        'academic.routine-titles.store'   => 'class_routine.manage_versions',
        'academic.routine-titles.update'  => 'class_routine.manage_versions',
        'academic.routine-titles.destroy' => 'class_routine.manage_versions',
        'academic.syllabi.download'       => 'syllabus.download',
        'academic.tours.badge'            => 'school_tour.badge',
        'academic.tours.badges'           => 'school_tour.badge',
        'academic.class-tests.marks'      => 'class_test.marks',
        'academic.class-tests.marks.store'=> 'class_test.marks',
        'academic.assignments.submissions'       => 'assignment.submissions',
        'academic.assignments.submissions.store' => 'assignment.submissions',
        'fees.collection.create'          => 'fee_collection.create',
        'fees.collection.pay'             => 'fee_collection.pay',
        'fees.generate-monthly'           => 'fee_collection.create',
        'fees.reminders'                  => 'fee_collection.create',
        'fees.report.print'               => 'fee_collection.print',
        'exams.marks.print'               => 'exam_marks.view',
        'exams.results.generate'          => 'exam_result.generate',
        'exams.results.publish'           => 'exam_result.publish',
        'exams.marksheet.view'            => 'exam_marksheet.print',
        'library.purchase.store'          => 'library_book.create',
        'library.settings.update'         => 'library_book.edit',
        'library.issues.return'           => 'library_issue.edit',
        'library.issues.pay-fine'         => 'library_issue.edit',
        'library.issues.waive-fine'       => 'library_issue.waive_fine',
        'transport.fee.store'             => 'transport_fee.edit',
        'committee.show.print'            => 'committee.print',
        'committee.members.store'         => 'committee.edit',
        'committee.members.update'        => 'committee.edit',
        'committee.members.destroy'       => 'committee.edit',
        'committee.reorder'               => 'committee.edit',
        'settings.backup.create'          => 'setting.edit',
        'settings.backup.restore'         => 'setting.edit',
        'trash.index'                     => 'setting.edit',
        'trash.restore'                   => 'setting.edit',
        'trash.force'                     => 'setting.edit',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $permission = $this->permissionFor((string) $request->route()?->getName());

        if ($permission && $user && method_exists($user, 'hasPermission') && !$user->hasPermission($permission)) {
            abort(403, 'You do not have permission to do this (' . $permission . ').');
        }

        return $next($request);
    }

    public function permissionFor(string $name): ?string
    {
        if ($name === '') return null;
        if (isset(self::OVERRIDES[$name])) return self::OVERRIDES[$name];

        $module = null;
        $rest = '';
        foreach (self::MODULES as $prefix => $mod) {
            if (($name === $prefix || str_starts_with($name, $prefix . '.')) && (!$module || strlen($prefix) > strlen($module[0]))) {
                $module = [$prefix, $mod];
            }
        }
        if (!$module) return null;

        $suffix = substr($name, strlen($module[0]) + 1) ?: '';
        $last = $suffix === '' ? '' : substr(strrchr('.' . $suffix, '.'), 1);
        $action = match ($last) {
            'store', 'create'      => 'create',
            'update', 'edit'       => 'edit',
            'destroy'              => 'delete',
            'approve'              => 'approve',
            'reject'               => 'reject',
            'print'                => $this->hasAction($module[1], 'print') ? 'print' : 'view',
            default                => 'view',
        };
        return $module[1] . '.' . $action;
    }

    private function hasAction(string $module, string $action): bool
    {
        $actions = config("permissions.$module.actions", '');
        return in_array($action, explode(',', $actions), true);
    }
}
