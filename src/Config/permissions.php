<?php

// Permission catalog for the school-management module, merged into
// config('permissions') (see SchoolManagementServiceProvider::register()).
// This is the same config key metheme's own Role create/edit screens read
// (ME\Http\Controllers\RoleController::getPermissionsFromConfig()) to build
// the permission checkboxes — each module here becomes a group of
// "module.action" checkboxes a role can be granted.
//
// Users, Roles and Activity Log are metheme's own screens; their real
// enforced slugs (me_user.*, me_role.*) are included here so they also show
// up as checkboxes on the Role form.

return [
    'me_user' => [
        'title'   => 'Users',
        'actions' => 'view,create,edit,delete',
    ],
    'me_role' => [
        'title'   => 'Roles',
        'actions' => 'view,create,edit,delete',
    ],
    'activity_log' => [
        'title'   => 'Activity Log',
        'actions' => 'view',
    ],

    // ── Academic ──────────────────────────────────────────────────────
    'academic_year' => [
        'title'   => 'Academic Years',
        'actions' => 'view,create,edit,delete',
    ],
    'class' => [
        'title'   => 'Classes',
        'actions' => 'view,create,edit,delete',
    ],
    'section' => [
        'title'   => 'Sections',
        'actions' => 'view,create,edit,delete',
    ],
    'subject_group' => [
        'title'   => 'Subject Groups',
        'actions' => 'view,create,edit,delete',
    ],
    'subject' => [
        'title'   => 'Subjects',
        'actions' => 'view,create,edit,delete',
    ],
    'class_routine' => [
        'title'   => 'Class Routine',
        'actions' => 'view,create,edit,delete,print,manage_versions',
    ],
    'room' => [
        'title'   => 'Rooms',
        'actions' => 'view,create,edit,delete',
    ],
    'syllabus' => [
        'title'   => 'Syllabus',
        'actions' => 'view,create,edit,delete,download',
    ],
    'class_test' => [
        'title'   => 'Class Tests',
        'actions' => 'view,create,edit,delete,marks',
    ],
    'class_text' => [
        'title'   => 'Class Texts',
        'actions' => 'view,create,edit,delete',
    ],
    'assignment' => [
        'title'   => 'Assignments',
        'actions' => 'view,create,edit,delete,submissions',
    ],
    'school_tour' => [
        'title'   => 'School Tours',
        'actions' => 'view,create,edit,delete,badge',
    ],

    // ── Students ──────────────────────────────────────────────────────
    'student' => [
        'title'   => 'Students',
        'actions' => 'view,create,edit,delete,print',
    ],
    'student_attendance' => [
        'title'   => 'Student Attendance',
        'actions' => 'view,create,print',
    ],
    'student_promotion' => [
        'title'   => 'Student Promotion',
        'actions' => 'view,create',
    ],
    'student_transfer' => [
        'title'   => 'Student Transfer',
        'actions' => 'view,create',
    ],
    'student_document' => [
        'title'   => 'Student Documents',
        'actions' => 'view,create,delete,verify',
    ],
    'student_leave' => [
        'title'   => 'Student Leave',
        'actions' => 'view,create,approve,reject',
    ],
    'student_early_exit' => [
        'title'   => 'Student Early Exit',
        'actions' => 'view,create,approve,reject,delete',
    ],

    // ── Teachers & Staff ──────────────────────────────────────────────
    'teacher' => [
        'title'   => 'Teachers',
        'actions' => 'view,create,edit,delete,link_login',
    ],
    'teacher_attendance' => [
        'title'   => 'Teacher Attendance',
        'actions' => 'view,create',
    ],
    'teacher_leave' => [
        'title'   => 'Teacher Leaves',
        'actions' => 'view,create,approve,reject',
    ],
    'teacher_subject' => [
        'title'   => 'Teacher Subjects',
        'actions' => 'view,create,delete',
    ],
    'teacher_class' => [
        'title'   => 'Teacher Class Assignment',
        'actions' => 'view,create,delete',
    ],
    'teacher_salary' => [
        'title'   => 'Teacher Salary',
        'actions' => 'view,create',
    ],
    'staff' => [
        'title'   => 'Staff',
        'actions' => 'view,create,edit,delete',
    ],
    'staff_salary' => [
        'title'   => 'Staff Salary',
        'actions' => 'view,create',
    ],
    'committee' => [
        'title'   => 'Committee',
        'actions' => 'view,create,edit,delete,print',
    ],

    // ── Fee Management ────────────────────────────────────────────────
    'fee_type' => [
        'title'   => 'Fee Types',
        'actions' => 'view,create,edit,delete',
    ],
    'fee_structure' => [
        'title'   => 'Fee Structure',
        'actions' => 'view,create,edit,delete',
    ],
    'fee_collection' => [
        'title'   => 'Fee Collection',
        'actions' => 'view,create,pay,print',
    ],
    'fee_discount' => [
        'title'   => 'Fee Discounts',
        'actions' => 'view,create,delete',
    ],

    // ── Examinations ──────────────────────────────────────────────────
    'exam' => [
        'title'   => 'Exams',
        'actions' => 'view,create,edit,delete',
    ],
    'exam_schedule' => [
        'title'   => 'Exam Schedule',
        'actions' => 'view,create,edit,delete',
    ],
    'exam_marks' => [
        'title'   => 'Exam Marks Entry',
        'actions' => 'view,create',
    ],
    'exam_grade' => [
        'title'   => 'Exam Grades',
        'actions' => 'view,create,edit,delete',
    ],
    'exam_result' => [
        'title'   => 'Exam Results',
        'actions' => 'view,generate,publish',
    ],
    'exam_marksheet' => [
        'title'   => 'Marksheet',
        'actions' => 'view,print',
    ],

    // ── Accounts ──────────────────────────────────────────────────────
    'expense' => [
        'title'   => 'Expenses',
        'actions' => 'view,create,edit,delete',
    ],
    'expense_category' => [
        'title'   => 'Expense Categories',
        'actions' => 'view,create,delete',
    ],
    'income' => [
        'title'   => 'Income',
        'actions' => 'view,create,edit,delete',
    ],
    'income_category' => [
        'title'   => 'Income Categories',
        'actions' => 'view,create,edit,delete',
    ],
    'financial_report' => [
        'title'   => 'Profit / Loss Report',
        'actions' => 'view',
    ],

    // ── Library ───────────────────────────────────────────────────────
    'library_book' => [
        'title'   => 'Library Books',
        'actions' => 'view,create,edit,delete',
    ],
    'library_issue' => [
        'title'   => 'Book Issue / Return',
        'actions' => 'view,create,edit,waive_fine',
    ],
    'library_member' => [
        'title'   => 'Library Members',
        'actions' => 'view,create,delete',
    ],

    // ── Transport ─────────────────────────────────────────────────────
    'transport_route' => [
        'title'   => 'Transport Routes',
        'actions' => 'view,create,edit,delete',
    ],
    'transport_vehicle' => [
        'title'   => 'Vehicles & Drivers',
        'actions' => 'view,create,edit,delete',
    ],
    'transport_fee' => [
        'title'   => 'Transport Fee',
        'actions' => 'view,edit',
    ],

    // ── Inventory ─────────────────────────────────────────────────────
    'inventory_product' => [
        'title'   => 'Inventory Products',
        'actions' => 'view,create,edit,delete',
    ],
    'inventory_stock' => [
        'title'   => 'Inventory Stock',
        'actions' => 'view,create',
    ],
    'inventory_purchase' => [
        'title'   => 'Inventory Purchase',
        'actions' => 'view,create,delete',
    ],
    'inventory_supplier' => [
        'title'   => 'Suppliers',
        'actions' => 'view,create,delete',
    ],

    // ── Notice & Events ───────────────────────────────────────────────
    'notice' => [
        'title'   => 'Notices',
        'actions' => 'view,create,edit,delete',
    ],
    'event' => [
        'title'   => 'Events',
        'actions' => 'view,create,edit,delete',
    ],
    'holiday' => [
        'title'   => 'Holidays',
        'actions' => 'view,create,delete',
    ],

    // ── Reports ───────────────────────────────────────────────────────
    'report' => [
        'title'   => 'Reports',
        'actions' => 'view,print',
    ],

    // ── Settings ──────────────────────────────────────────────────────
    'setting' => [
        'title'   => 'Settings',
        'actions' => 'view,edit',
    ],
];
