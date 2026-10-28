<?php

// Sidebar shown to a logged-in user whose account is linked to a teacher
// record — swapped in for config('sidebar') by
// ME\SchoolManagement\Http\Middleware\TeacherSidebar at request time.
// See src/SchoolManagementServiceProvider.php.

return [
    [
        'title'      => 'Dashboard',
        'icon'       => 'fas fa-tachometer-alt',
        'route'      => 'my.dashboard',
        'for_active' => 'my.dashboard',
        'icon_color' => 'icc-1',
        'sl'         => 5,
    ],
    [
        'title'      => 'My Classes',
        'icon'       => 'fas fa-chalkboard-teacher',
        'route'      => 'my.classes',
        'for_active' => 'my.classes',
        'icon_color' => 'icc-1',
        'sl'         => 10,
    ],
    [
        'title'      => 'My Schedule',
        'icon'       => 'fas fa-calendar-alt',
        'route'      => 'my.schedule',
        'for_active' => 'my.schedule',
        'icon_color' => 'icc-2',
        'sl'         => 20,
    ],
    [
        'title'      => 'Class Tests',
        'icon'       => 'fas fa-pencil-alt',
        'route'      => 'my.class-tests',
        'for_active' => 'my.class-tests',
        'icon_color' => 'icc-3',
        'sl'         => 30,
    ],
    [
        'title'      => 'Assignments',
        'icon'       => 'fas fa-tasks',
        'route'      => 'my.assignments',
        'for_active' => 'my.assignments',
        'icon_color' => 'icc-4',
        'sl'         => 40,
    ],
    [
        'title'      => 'Notice Board',
        'icon'       => 'fas fa-bullhorn',
        'route'      => 'my.notices',
        'for_active' => 'my.notices',
        'icon_color' => 'icc-5',
        'sl'         => 50,
    ],
    [
        'title'      => 'My Leaves',
        'icon'       => 'fas fa-calendar-minus',
        'route'      => 'my.leaves',
        'for_active' => 'my.leaves',
        'icon_color' => 'icc-7',
        'sl'         => 55,
    ],
    [
        'title'      => 'My Profile',
        'icon'       => 'fas fa-user',
        'route'      => 'profile.edit',
        'for_active' => 'profile.edit',
        'icon_color' => 'icc-6',
        'sl'         => 60,
    ],
];
