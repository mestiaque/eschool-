<?php
return [
    [
        'title'      => 'Dashboard',
        'icon'       => 'fas fa-tachometer-alt',
        'route'      => 'dashboard',
        'for_active' => 'dashboard',
        'icon_color' => 'icc-1',
        'sl'         => 10,
    ],

    // "My Classes" / attendance is a dedicated Teacher Portal (own layout,
    // see resources/views/teacher-portal/layout.blade.php) that a
    // teacher-linked login is redirected to automatically from the
    // dashboard — intentionally not listed in the admin sidebar.

    // ── Academic ──────────────────────────────────────────────────────────────
    [
        'title'      => 'Academic',
        'icon'       => 'fas fa-graduation-cap',
        'icon_color' => 'icc-6',
        'sl'         => 20,
        'children'   => [
            ['title' => 'Academic Years', 'icon' => 'fas fa-calendar',      'route' => 'academic.years',       'for_active' => 'academic.years',       'icon_color' => 'icc-7',  'permit' => 'academic_year.view'],
            ['title' => 'Classes',        'icon' => 'fas fa-layer-group',   'route' => 'academic.classes',     'for_active' => 'academic.classes',     'icon_color' => 'icc-8',  'permit' => 'class.view'],
            ['title' => 'Sections',       'icon' => 'fas fa-columns',       'route' => 'academic.sections',    'for_active' => 'academic.sections',    'icon_color' => 'icc-9',  'permit' => 'section.view'],
            ['title' => 'Subject Groups', 'icon' => 'fas fa-object-group', 'route' => 'academic.subject-groups', 'for_active' => 'academic.subject-groups', 'icon_color' => 'icc-11', 'permit' => 'subject_group.view'],
            ['title' => 'Subjects',       'icon' => 'fas fa-book',          'route' => 'academic.subjects',       'for_active' => 'academic.subjects',       'icon_color' => 'icc-10', 'permit' => 'subject.view'],
            ['title' => 'Class Routine',  'icon' => 'fas fa-clock',         'route' => 'academic.routines',    'for_active' => 'academic.routines',    'icon_color' => 'icc-11', 'permit' => 'class_routine.view'],
            ['title' => 'Rooms',          'icon' => 'fas fa-door-open',     'route' => 'academic.rooms',       'for_active' => 'academic.rooms',       'icon_color' => 'icc-13', 'permit' => 'room.view'],
            ['title' => 'Syllabus',       'icon' => 'fas fa-list-alt',      'route' => 'academic.syllabi',     'for_active' => 'academic.syllabi',     'icon_color' => 'icc-12', 'permit' => 'syllabus.view'],
            ['title' => 'Class Tests',    'icon' => 'fas fa-pencil-alt',    'route' => 'academic.class-tests', 'for_active' => 'academic.class-tests', 'icon_color' => 'icc-13', 'permit' => 'class_test.view'],
            // ['title' => 'Class Texts',    'icon' => 'fas fa-book-open',     'route' => 'academic.class-texts', 'for_active' => 'academic.class-texts', 'icon_color' => 'icc-13', 'permit' => 'class_text.view'],
            ['title' => 'Assignments',    'icon' => 'fas fa-tasks',         'route' => 'academic.assignments', 'for_active' => 'academic.assignments', 'icon_color' => 'icc-14', 'permit' => 'assignment.view'],
            ['title' => 'School Tours',   'icon' => 'fas fa-plane',         'route' => 'academic.tours',       'for_active' => 'academic.tours',       'icon_color' => 'icc-15', 'permit' => 'school_tour.view'],
        ],
    ],

    // ── Students ─────────────────────────────────────────────────────────────
    [
        'title'      => 'Students',
        'icon'       => 'fas fa-user-graduate',
        'icon_color' => 'icc-16',
        'sl'         => 30,
        'children'   => [
            ['title' => 'All Students', 'icon' => 'fas fa-list',         'route' => 'students.index',      'for_active' => 'students.index',      'icon_color' => 'icc-17', 'permit' => 'student.view'],
            ['title' => 'Admission',    'icon' => 'fas fa-user-plus',    'route' => 'students.admission',  'for_active' => 'students.admission',  'icon_color' => 'icc-18', 'permit' => 'student.create'],
            ['title' => 'Attendance',   'icon' => 'fas fa-user-check',   'route' => 'students.attendance', 'for_active' => 'students.attendance', 'icon_color' => 'icc-19', 'permit' => 'student_attendance.view'],
            ['title' => 'Promotion',    'icon' => 'fas fa-arrow-up',     'route' => 'students.promotion',  'for_active' => 'students.promotion',  'icon_color' => 'icc-20', 'permit' => 'student_promotion.view'],
            ['title' => 'Transfer',     'icon' => 'fas fa-exchange-alt', 'route' => 'students.transfer',   'for_active' => 'students.transfer',   'icon_color' => 'icc-21', 'permit' => 'student_transfer.view'],
            ['title' => 'Leave',        'icon' => 'fas fa-calendar-minus', 'route' => 'students.leaves',   'for_active' => 'students.leaves',     'icon_color' => 'icc-24', 'permit' => 'student_leave.view'],
            ['title' => 'Early Exit',   'icon' => 'fas fa-door-open',    'route' => 'students.early-exits', 'for_active' => 'students.early-exits', 'icon_color' => 'icc-25', 'permit' => 'student_early_exit.view'],
            ['title' => 'ID Card',      'icon' => 'fas fa-id-card',      'route' => 'students.idcard',     'for_active' => 'students.idcard',     'icon_color' => 'icc-22', 'permit' => 'student.print'],
            ['title' => 'Documents',    'icon' => 'fas fa-folder',       'route' => 'students.documents',  'for_active' => 'students.documents',  'icon_color' => 'icc-23', 'permit' => 'student_document.view'],
        ],
    ],

    // ── Teachers ─────────────────────────────────────────────────────────────
    [
        'title'      => 'Teachers',
        'icon'       => 'fas fa-chalkboard-teacher',
        'icon_color' => 'icc-24',
        'sl'         => 40,
        'children'   => [
            ['title' => 'All Teachers',  'icon' => 'fas fa-list',            'route' => 'teachers.index',      'for_active' => 'teachers.index',      'icon_color' => 'icc-25', 'permit' => 'teacher.view'],
            ['title' => 'Attendance',    'icon' => 'fas fa-clipboard-check', 'route' => 'teachers.attendance', 'for_active' => 'teachers.attendance', 'icon_color' => 'icc-26', 'permit' => 'teacher_attendance.view'],
            ['title' => 'Leaves',        'icon' => 'fas fa-calendar-minus',  'route' => 'teachers.leaves',     'for_active' => 'teachers.leaves',     'icon_color' => 'icc-27', 'permit' => 'teacher_leave.view'],
            ['title' => 'Leave Types',   'icon' => 'fas fa-tags',            'route' => 'teachers.leave-types', 'for_active' => 'teachers.leave-types', 'icon_color' => 'icc-27', 'permit' => 'teacher_leave.view'],
            ['title' => 'Salary',        'icon' => 'fas fa-money-bill',      'route' => 'teachers.salary',     'for_active' => 'teachers.salary',     'icon_color' => 'icc-28', 'permit' => 'teacher_salary.view'],
            ['title' => 'Subjects',      'icon' => 'fas fa-book-open',       'route' => 'teachers.subjects',   'for_active' => 'teachers.subjects',   'icon_color' => 'icc-29', 'permit' => 'teacher_subject.view'],
            ['title' => 'Class Teacher', 'icon' => 'fas fa-users',           'route' => 'teachers.classes',    'for_active' => 'teachers.classes',    'icon_color' => 'icc-30', 'permit' => 'teacher_class.view'],
        ],
    ],

    // ── Staff (Non-Teaching) ─────────────────────────────────────────────────
    [
        'title'      => 'Staff',
        'icon'       => 'fas fa-people-carry',
        'icon_color' => 'icc-35',
        'sl'         => 45,
        'children'   => [
            ['title' => 'All Staff',   'icon' => 'fas fa-list',      'route' => 'staff.index',      'for_active' => 'staff.index',      'icon_color' => 'icc-36', 'permit' => 'staff.view'],
            ['title' => 'Committee',   'icon' => 'fas fa-sitemap',   'route' => 'committee.index',  'for_active' => 'committee.index',  'icon_color' => 'icc-37', 'permit' => 'committee.view'],
        ],
    ],

    // ── Fee Management ───────────────────────────────────────────────────────
    [
        'title'      => 'Fee Management',
        'icon'       => 'fas fa-money-bill-wave',
        'icon_color' => 'icc-31',
        'sl'         => 50,
        'children'   => [
            ['title' => 'Fee Types',       'icon' => 'fas fa-tags',              'route' => 'fees.types',           'for_active' => 'fees.types',           'icon_color' => 'icc-32', 'permit' => 'fee_type.view'],
            ['title' => 'Fee Structure',   'icon' => 'fas fa-list',              'route' => 'fees.structure',       'for_active' => 'fees.structure',       'icon_color' => 'icc-33', 'permit' => 'fee_structure.view'],
            ['title' => 'Collection',      'icon' => 'fas fa-hand-holding-usd',  'route' => 'fees.collection',      'for_active' => 'fees.collection',      'icon_color' => 'icc-34', 'permit' => 'fee_collection.view'],
            ['title' => 'Monthly Fee',     'icon' => 'fas fa-calendar-check',    'route' => 'fees.monthly',         'for_active' => 'fees.monthly',         'icon_color' => 'icc-35', 'permit' => 'fee_collection.view'],
            ['title' => 'Invoice',         'icon' => 'fas fa-file-invoice',      'route' => 'fees.invoice',         'for_active' => 'fees.invoice',         'icon_color' => 'icc-36', 'permit' => 'fee_collection.view'],
            ['title' => 'Payment History', 'icon' => 'fas fa-history',           'route' => 'fees.payment-history', 'for_active' => 'fees.payment-history', 'icon_color' => 'icc-37', 'permit' => 'fee_collection.view'],
            ['title' => 'Discounts',       'icon' => 'fas fa-percent',           'route' => 'fees.discounts',       'for_active' => 'fees.discounts',       'icon_color' => 'icc-38', 'permit' => 'fee_discount.view'],
        ],
    ],

    // ── Examinations ─────────────────────────────────────────────────────────
    [
        'title'      => 'Examinations',
        'icon'       => 'fas fa-file-alt',
        'icon_color' => 'icc-39',
        'sl'         => 60,
        'children'   => [
            ['title' => 'All Exams',   'icon' => 'fas fa-file-alt',     'route' => 'exams.index',    'for_active' => 'exams.index',    'icon_color' => 'icc-40', 'permit' => 'exam.view'],
            ['title' => 'Schedule',    'icon' => 'fas fa-calendar-alt', 'route' => 'exams.schedule', 'for_active' => 'exams.schedule', 'icon_color' => 'icc-41', 'permit' => 'exam_schedule.view'],
            ['title' => 'Marks Entry', 'icon' => 'fas fa-pen',          'route' => 'exams.marks',    'for_active' => 'exams.marks',    'icon_color' => 'icc-42', 'permit' => 'exam_marks.view'],
            ['title' => 'Grades',      'icon' => 'fas fa-star',         'route' => 'exams.grades',   'for_active' => 'exams.grades',   'icon_color' => 'icc-43', 'permit' => 'exam_grade.view'],
            ['title' => 'Results',     'icon' => 'fas fa-chart-line',   'route' => 'exams.results',  'for_active' => 'exams.results',  'icon_color' => 'icc-44', 'permit' => 'exam_result.view'],
            ['title' => 'Marksheet',   'icon' => 'fas fa-file-pdf',     'route' => 'exams.marksheet.index','for_active' => 'exams.marksheet.index','icon_color' => 'icc-45', 'permit' => 'exam_marksheet.view'],
        ],
    ],

    // ── Accounts ─────────────────────────────────────────────────────────────
    [
        'title'      => 'Accounts',
        'icon'       => 'fas fa-wallet',
        'icon_color' => 'icc-46',
        'sl'         => 70,
        'children'   => [
            ['title' => 'Expenses',           'icon' => 'fas fa-arrow-circle-down', 'route' => 'expenses.index',             'for_active' => 'expenses.index',             'icon_color' => 'icc-47', 'permit' => 'expense.view'],
            ['title' => 'Expense Categories', 'icon' => 'fas fa-tags',              'route' => 'expenses.categories',        'for_active' => 'expenses.categories',        'icon_color' => 'icc-48', 'permit' => 'expense_category.view'],
            ['title' => 'Income',             'icon' => 'fas fa-arrow-circle-up',   'route' => 'expenses.income',            'for_active' => 'expenses.income',            'icon_color' => 'icc-49', 'permit' => 'income.view'],
            ['title' => 'Income Categories',  'icon' => 'fas fa-folder-plus',       'route' => 'expenses.income-categories', 'for_active' => 'expenses.income-categories', 'icon_color' => 'icc-50', 'permit' => 'income_category.view'],
            ['title' => 'Staff Salary',       'icon' => 'fas fa-money-check-alt',   'route' => 'expenses.staff-salary',      'for_active' => 'expenses.staff-salary',      'icon_color' => 'icc-51', 'permit' => 'staff_salary.view'],
            ['title' => 'Profit / Loss',      'icon' => 'fas fa-chart-bar',         'route' => 'expenses.profit-loss',       'for_active' => 'expenses.profit-loss',       'icon_color' => 'icc-52', 'permit' => 'financial_report.view'],
        ],
    ],

    // ── Library ──────────────────────────────────────────────────────────────
    [
        'title'      => 'Library',
        'icon'       => 'fas fa-book-open',
        'icon_color' => 'icc-53',
        'sl'         => 80,
        'children'   => [
            ['title' => 'All Books',      'icon' => 'fas fa-book',          'route' => 'library.books',        'for_active' => 'library.books',        'icon_color' => 'icc-54', 'permit' => 'library_book.view'],
            ['title' => 'Issue / Return', 'icon' => 'fas fa-hand-holding',  'route' => 'library.issues',       'for_active' => 'library.issues',       'icon_color' => 'icc-55', 'permit' => 'library_issue.view'],
            ['title' => 'Availability',   'icon' => 'fas fa-th',            'route' => 'library.availability', 'for_active' => 'library.availability', 'icon_color' => 'icc-56', 'permit' => 'library_book.view'],
            ['title' => 'Members',        'icon' => 'fas fa-users',         'route' => 'library.members',      'for_active' => 'library.members',      'icon_color' => 'icc-57', 'permit' => 'library_member.view'],
        ],
    ],

    // ── Transport ────────────────────────────────────────────────────────────
    [
        'title'      => 'Transport',
        'icon'       => 'fas fa-bus',
        'icon_color' => 'icc-58',
        'sl'         => 90,
        'children'   => [
            ['title' => 'Routes',      'icon' => 'fas fa-route',       'route' => 'transport.routes',   'for_active' => 'transport.routes',   'icon_color' => 'icc-59', 'permit' => 'transport_route.view'],
            ['title' => 'Schedule',    'icon' => 'fas fa-clock',       'route' => 'transport.schedule', 'for_active' => 'transport.schedule', 'icon_color' => 'icc-63', 'permit' => 'transport_route.view'],
            ['title' => 'Vehicles',    'icon' => 'fas fa-bus',         'route' => 'transport.vehicles', 'for_active' => 'transport.vehicles', 'icon_color' => 'icc-60', 'permit' => 'transport_vehicle.view'],
            ['title' => 'Drivers',     'icon' => 'fas fa-id-card-alt', 'route' => 'transport.drivers',  'for_active' => 'transport.drivers',  'icon_color' => 'icc-61', 'permit' => 'transport_vehicle.view'],
            ['title' => 'Trans. Fee',  'icon' => 'fas fa-dollar-sign', 'route' => 'transport.fee',      'for_active' => 'transport.fee',      'icon_color' => 'icc-62', 'permit' => 'transport_fee.view'],
        ],
    ],

    // ── Inventory ────────────────────────────────────────────────────────────
    [
        'title'      => 'Inventory',
        'icon'       => 'fas fa-boxes',
        'icon_color' => 'icc-63',
        'sl'         => 100,
        'children'   => [
            ['title' => 'Products',  'icon' => 'fas fa-box',           'route' => 'inventory.products',  'for_active' => 'inventory.products',  'icon_color' => 'icc-64', 'permit' => 'inventory_product.view'],
            ['title' => 'Stock',     'icon' => 'fas fa-warehouse',     'route' => 'inventory.stock',     'for_active' => 'inventory.stock',     'icon_color' => 'icc-65', 'permit' => 'inventory_stock.view'],
            ['title' => 'Purchase',  'icon' => 'fas fa-shopping-cart', 'route' => 'inventory.purchase',  'for_active' => 'inventory.purchase',  'icon_color' => 'icc-66', 'permit' => 'inventory_purchase.view'],
            ['title' => 'Suppliers', 'icon' => 'fas fa-truck',         'route' => 'inventory.suppliers', 'for_active' => 'inventory.suppliers', 'icon_color' => 'icc-67', 'permit' => 'inventory_supplier.view'],
        ],
    ],

    // ── Notice & Events ──────────────────────────────────────────────────────
    [
        'title'      => 'Notice & Events',
        'icon'       => 'fas fa-bullhorn',
        'icon_color' => 'icc-42',
        'sl'         => 110,
        'children'   => [
            ['title' => 'Notice Board', 'icon' => 'fas fa-bullhorn',     'route' => 'notices.index',  'for_active' => 'notices.index',  'icon_color' => 'icc-42', 'permit' => 'notice.view'],
            ['title' => 'Events',       'icon' => 'fas fa-calendar-day', 'route' => 'events.index',   'for_active' => 'events.index',   'icon_color' => 'icc-43', 'permit' => 'event.view'],
            ['title' => 'Holidays',     'icon' => 'fas fa-snowflake',    'route' => 'holidays.index', 'for_active' => 'holidays.index', 'icon_color' => 'icc-44', 'permit' => 'holiday.view'],
        ],
    ],

    // ── Reports ──────────────────────────────────────────────────────────────
    [
        'title'      => 'Reports',
        'icon'       => 'fas fa-chart-bar',
        'icon_color' => 'icc-62',
        'sl'         => 120,
        'children'   => [
            ['title' => 'Student Report',    'icon' => 'fas fa-user-graduate',  'route' => 'reports.students',   'for_active' => 'reports.students',   'icon_color' => 'icc-60', 'permit' => 'report.view'],
            ['title' => 'Fee Report',        'icon' => 'fas fa-file-invoice',   'route' => 'reports.fees',       'for_active' => 'reports.fees',       'icon_color' => 'icc-34', 'permit' => 'report.view'],
            ['title' => 'Attendance Report', 'icon' => 'fas fa-clipboard-list', 'route' => 'reports.attendance', 'for_active' => 'reports.attendance', 'icon_color' => 'icc-61', 'permit' => 'report.view'],
            ['title' => 'Exam Report',       'icon' => 'fas fa-file-alt',       'route' => 'reports.exam',       'for_active' => 'reports.exam',       'icon_color' => 'icc-62', 'permit' => 'report.view'],
            ['title' => 'Expense Report',    'icon' => 'fas fa-receipt',        'route' => 'reports.expenses',   'for_active' => 'reports.expenses',   'icon_color' => 'icc-47', 'permit' => 'report.view'],
            ['title' => 'Financial Report',  'icon' => 'fas fa-chart-line',     'route' => 'reports.financial',  'for_active' => 'reports.financial',  'icon_color' => 'icc-63', 'permit' => 'report.view'],
        ],
    ],

    // ── Settings ─────────────────────────────────────────────────────────────
    [
        'title'      => 'Settings',
        'icon'       => 'fas fa-cogs',
        'icon_color' => 'icc-68',
        'sl'         => 140,
        'children'   => [
            ['title' => 'General',        'icon' => 'fas fa-cogs',           'route' => 'settings.general', 'for_active' => 'settings.general', 'icon_color' => 'icc-68', 'permit' => 'setting.view'],
            ['title' => 'School Profile', 'icon' => 'fas fa-school',         'route' => 'settings.school',  'for_active' => 'settings.school',  'icon_color' => 'icc-64', 'permit' => 'setting.view'],
            ['title' => 'Notifications',  'icon' => 'fas fa-bell',           'route' => 'settings.sms',     'for_active' => 'settings.sms',     'icon_color' => 'icc-65', 'permit' => 'setting.view'],
            ['title' => 'Backup',         'icon' => 'fas fa-database',       'route' => 'settings.backup',  'for_active' => 'settings.backup',  'icon_color' => 'icc-66', 'permit' => 'setting.view'],
            ['title' => 'Session',        'icon' => 'fas fa-calendar-check', 'route' => 'settings.session', 'for_active' => 'settings.session', 'icon_color' => 'icc-67', 'permit' => 'setting.view'],
            ['title' => 'Recycle Bin',    'icon' => 'fas fa-trash-restore',  'route' => 'trash.index',      'for_active' => 'trash.',           'icon_color' => 'icc-66', 'permit' => 'setting.edit'],
        ],
    ],
];
