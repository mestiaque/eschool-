<?php

namespace ME\SchoolManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Ready-made school roles with permissions from Config/permissions.php,
 * stored the way the theme's Role screen does (role_permissions.permissions JSON).
 *
 *   php artisan db:seed --class="ME\SchoolManagement\Database\Seeders\SchoolRolesSeeder"
 *
 * Safe to re-run: roles are matched by slug, and a role that already has
 * permissions is left exactly as the admin configured it.
 */
class SchoolRolesSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = collect(config('permissions'))
            ->flatMap(fn ($def, $module) => collect(explode(',', $def['actions']))->map(fn ($a) => "$module." . trim($a)));

        // A role gets every action of the listed modules, plus single permissions.
        $pick = fn (array $modules, array $extra = []) => $catalog
            ->filter(fn ($p) => in_array(explode('.', $p)[0], $modules, true))
            ->merge($extra)->unique()->values()->all();

        $roles = [
            'office-admin' => ['Office Admin', 'Day-to-day school administration (no user/role or system settings)',
                $catalog->reject(fn ($p) => str_starts_with($p, 'me_') || $p === 'setting.edit')->values()->all()],
            'accountant' => ['Accountant', 'Fees, income, expenses and salaries',
                $pick(['fee_type', 'fee_structure', 'fee_collection', 'fee_discount', 'expense', 'expense_category',
                       'income', 'income_category', 'financial_report', 'staff_salary', 'teacher_salary',
                       'inventory_product', 'inventory_stock', 'inventory_purchase', 'inventory_supplier', 'transport_fee'],
                      ['student.view', 'report.view', 'report.print'])],
            'librarian' => ['Librarian', 'Library books, issues and members',
                $pick(['library_book', 'library_issue', 'library_member'], ['student.view', 'teacher.view'])],
            'exam-controller' => ['Exam Controller', 'Exams, schedules, marks and results',
                $pick(['exam', 'exam_schedule', 'exam_marks', 'exam_grade', 'exam_result', 'exam_marksheet'],
                      ['student.view', 'class.view', 'subject.view', 'class_routine.view', 'report.view', 'report.print'])],
        ];

        foreach ($roles as $slug => [$name, $description, $permissions]) {
            $roleId = DB::table('roles')->where('slug', $slug)->value('id')
                ?? DB::table('roles')->insertGetId([
                    'name' => $name, 'slug' => $slug, 'description' => $description,
                    'created_at' => now(), 'updated_at' => now(),
                ]);

            $existing = DB::table('role_permissions')->where('role_id', $roleId)->value('permissions');
            if ($existing && json_decode($existing, true)) continue; // already configured by an admin

            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $roleId],
                ['permissions' => json_encode($permissions), 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command?->info('  → School roles ready: Office Admin, Accountant, Librarian, Exam Controller');
    }
}
