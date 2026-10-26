<?php

namespace ME\SchoolManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SchoolManagementDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->seedRolesPermissions();
        $this->seedSettings();
        $this->seedAcademic();
        $this->seedTeachers();
        $this->seedStudents();
        $this->seedFees();
        $this->seedExams();
        $this->seedExpensesIncome();
        $this->seedLibrary();
        $this->seedTransport();
        $this->seedInventory();
        $this->seedNoticesEvents();
        $this->seedHolidays();
        $this->seedClassTestsAssignments();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Demo seeder completed successfully!');
    }

    // ─── Roles & Permissions ───────────────────────────────────────────────────

    private function seedRolesPermissions(): void
    {
        // Roles belong to the theme; never truncate them (that would remove
        // every user's access). Only add the school roles.
        $this->call(SchoolRolesSeeder::class);
    }

    // ─── Settings ─────────────────────────────────────────────────────────────

    private function seedSettings(): void
    {
        DB::table('school_settings')->truncate();

        $settings = [
            ['key' => 'school_name',      'value' => 'Dhrubo Academy',                     'type' => 'text'],
            ['key' => 'school_address',   'value' => 'Mirpur-10, Dhaka-1216, Bangladesh',  'type' => 'text'],
            ['key' => 'school_phone',     'value' => '+880 1700-000000',                   'type' => 'text'],
            ['key' => 'school_email',     'value' => 'info@dhruboacademy.edu.bd',          'type' => 'text'],
            ['key' => 'school_website',   'value' => 'www.dhruboacademy.edu.bd',           'type' => 'text'],
            ['key' => 'school_motto',     'value' => 'Knowledge is Power',                 'type' => 'text'],
            ['key' => 'established_year', 'value' => '1998',                               'type' => 'number'],
            ['key' => 'currency',         'value' => '৳',                                  'type' => 'text'],
            ['key' => 'currency_code',    'value' => 'BDT',                                'type' => 'text'],
            ['key' => 'academic_start',   'value' => 'January',                            'type' => 'text'],
            ['key' => 'academic_end',     'value' => 'December',                           'type' => 'text'],
            ['key' => 'working_days',     'value' => 'Saturday,Sunday,Monday,Tuesday,Wednesday', 'type' => 'text'],
            ['key' => 'sms_sender_id',    'value' => 'DhruboAcad',                         'type' => 'text'],
        ];

        foreach ($settings as $s) {
            DB::table('school_settings')->insert(array_merge($s, [
                'created_at' => now(), 'updated_at' => now()
            ]));
        }

        $this->command->info('  → Settings seeded');
    }

    // ─── Academic ─────────────────────────────────────────────────────────────

    private function seedAcademic(): void
    {
        DB::table('syllabi')->truncate();
        DB::table('class_routines')->truncate();
        DB::table('sections')->truncate();
        DB::table('subjects')->truncate();
        DB::table('classes')->truncate();
        DB::table('academic_years')->truncate();
        DB::table('school_tours')->truncate();

        // Academic Years
        DB::table('academic_years')->insert([
            ['name' => '2023', 'start_date' => '2023-01-01', 'end_date' => '2023-12-31', 'is_active' => false, 'is_current' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2024', 'start_date' => '2024-01-01', 'end_date' => '2024-12-31', 'is_active' => false, 'is_current' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2025', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'is_active' => true,  'is_current' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_active' => true,  'is_current' => true,  'created_at' => now(), 'updated_at' => now()],
        ]);

        // Classes (Six to Ten + College)
        $classes = [
            ['name' => 'Class Six',    'numeric_name' => 6,  'description' => 'Junior Secondary'],
            ['name' => 'Class Seven',  'numeric_name' => 7,  'description' => 'Junior Secondary'],
            ['name' => 'Class Eight',  'numeric_name' => 8,  'description' => 'Junior Secondary'],
            ['name' => 'Class Nine',   'numeric_name' => 9,  'description' => 'Secondary - SSC'],
            ['name' => 'Class Ten',    'numeric_name' => 10, 'description' => 'Secondary - SSC'],
            ['name' => 'Class Eleven', 'numeric_name' => 11, 'description' => 'Higher Secondary - HSC'],
            ['name' => 'Class Twelve', 'numeric_name' => 12, 'description' => 'Higher Secondary - HSC'],
        ];

        foreach ($classes as $class) {
            DB::table('classes')->insert(array_merge($class, [
                'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]));
        }

        // Sections (A, B for each class)
        for ($classId = 1; $classId <= 7; $classId++) {
            DB::table('sections')->insert([
                ['class_id' => $classId, 'name' => 'A', 'room_number' => "R{$classId}01", 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['class_id' => $classId, 'name' => 'B', 'room_number' => "R{$classId}02", 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Subjects (common + class-specific)
        $commonSubjects = [
            ['name' => 'Bangla',         'code' => 'BNG', 'pass_mark' => 33, 'full_mark' => 100],
            ['name' => 'English',        'code' => 'ENG', 'pass_mark' => 33, 'full_mark' => 100],
            ['name' => 'Mathematics',    'code' => 'MTH', 'pass_mark' => 33, 'full_mark' => 100],
            ['name' => 'Science',        'code' => 'SCI', 'pass_mark' => 33, 'full_mark' => 100],
            ['name' => 'Social Science', 'code' => 'SSC', 'pass_mark' => 33, 'full_mark' => 100],
            ['name' => 'Religion',       'code' => 'REL', 'pass_mark' => 33, 'full_mark' => 100],
            ['name' => 'ICT',            'code' => 'ICT', 'pass_mark' => 33, 'full_mark' => 100],
        ];

        $sscSubjects = [
            ['name' => 'Physics',        'code' => 'PHY', 'pass_mark' => 33, 'full_mark' => 100],
            ['name' => 'Chemistry',      'code' => 'CHM', 'pass_mark' => 33, 'full_mark' => 100],
            ['name' => 'Biology',        'code' => 'BIO', 'pass_mark' => 33, 'full_mark' => 100],
            ['name' => 'Higher Math',    'code' => 'HMT', 'pass_mark' => 33, 'full_mark' => 100],
            ['name' => 'Accounting',     'code' => 'ACC', 'pass_mark' => 33, 'full_mark' => 100],
        ];

        for ($classId = 1; $classId <= 7; $classId++) {
            foreach ($commonSubjects as $sub) {
                DB::table('subjects')->insert(array_merge($sub, [
                    'class_id' => $classId, 'credit_hour' => 1.0,
                    'is_active' => true, 'created_at' => now(), 'updated_at' => now()
                ]));
            }
            if ($classId >= 4) {
                foreach ($sscSubjects as $sub) {
                    DB::table('subjects')->insert(array_merge($sub, [
                        'class_id' => $classId, 'credit_hour' => 1.0,
                        'is_active' => true, 'created_at' => now(), 'updated_at' => now()
                    ]));
                }
            }
        }

        // Class Routines
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday'];
        $timeslots = [
            ['08:00', '09:00'], ['09:00', '10:00'], ['10:00', '11:00'],
            ['11:30', '12:30'], ['12:30', '13:30'],
        ];

        for ($classId = 1; $classId <= 7; $classId++) {
            $subjectIds = DB::table('subjects')->where('class_id', $classId)->pluck('id')->toArray();
            foreach ($days as $dayIndex => $day) {
                foreach ($timeslots as $slotIndex => $slot) {
                    $subjectId = $subjectIds[($dayIndex * 5 + $slotIndex) % count($subjectIds)];
                    DB::table('class_routines')->insert([
                        'class_id' => $classId, 'section_id' => null,
                        'subject_id' => $subjectId,
                        'day' => $day,
                        'start_time' => $slot[0], 'end_time' => $slot[1],
                        'room_id' => DB::table('rooms')->where('name', "Room " . (100 + $classId))->value('id')
                            ?? DB::table('rooms')->insertGetId(['name' => "Room " . (100 + $classId), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]),
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }
            }
        }

        // School Tours
        $tours = [
            ['title' => 'Annual Science Fair Visit', 'destination' => 'Bangabandhu Science & Technology University, Gopalganj', 'start_date' => '2026-02-15', 'end_date' => '2026-02-15', 'budget' => 25000, 'status' => 'completed'],
            ['title' => 'National Museum Tour',       'destination' => 'Bangladesh National Museum, Dhaka',                     'start_date' => '2026-03-10', 'end_date' => '2026-03-10', 'budget' => 18000, 'status' => 'completed'],
            ['title' => 'Sundarban Eco-Tour',         'destination' => 'Sundarban, Khulna',                                     'start_date' => '2026-12-20', 'end_date' => '2026-12-23', 'budget' => 85000, 'status' => 'planning'],
            ['title' => 'Cox\'s Bazar Annual Trip',   'destination' => 'Cox\'s Bazar Sea Beach',                               'start_date' => '2026-11-10', 'end_date' => '2026-11-12', 'budget' => 120000, 'status' => 'approved'],
        ];

        foreach ($tours as $tour) {
            DB::table('school_tours')->insert(array_merge($tour, [
                'organizer' => 'School Administration', 'contact_phone' => '01700-000001',
                'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]));
        }

        $this->command->info('  → Academic data seeded (7 classes, 14 sections, ' . DB::table('subjects')->count() . ' subjects)');
    }

    // ─── Teachers ─────────────────────────────────────────────────────────────

    private function seedTeachers(): void
    {
        DB::table('teacher_classes')->truncate();
        DB::table('teacher_subjects')->truncate();
        DB::table('teacher_attendances')->truncate();
        DB::table('teacher_leaves')->truncate();
        DB::table('teacher_documents')->truncate();
        DB::table('leave_types')->truncate();
        DB::table('salary_payments')->truncate();
        DB::table('teachers')->truncate();

        // Leave Types
        $leaveTypes = [
            ['name' => 'Casual Leave',   'days' => 10],
            ['name' => 'Sick Leave',     'days' => 14],
            ['name' => 'Earned Leave',   'days' => 20],
            ['name' => 'Maternity Leave','days' => 90],
            ['name' => 'Paternity Leave','days' => 15],
        ];

        foreach ($leaveTypes as $lt) {
            DB::table('leave_types')->insert(array_merge($lt, [
                'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]));
        }

        $teachers = [
            ['first_name' => 'Mohammad',  'last_name' => 'Rafiqul Islam',  'gender' => 'male',   'designation' => 'Headmaster',          'qualification' => 'M.A. (Bangla)',    'salary' => 55000, 'email' => 'rafiq@dhruboacademy.edu.bd'],
            ['first_name' => 'Fatema',    'last_name' => 'Begum',          'gender' => 'female', 'designation' => 'Assistant Headmaster', 'qualification' => 'M.Sc. (Math)',     'salary' => 48000, 'email' => 'fatema@dhruboacademy.edu.bd'],
            ['first_name' => 'Abdul',     'last_name' => 'Karim',          'gender' => 'male',   'designation' => 'Senior Teacher',       'qualification' => 'M.Sc. (Physics)', 'salary' => 42000, 'email' => 'karim@dhruboacademy.edu.bd'],
            ['first_name' => 'Nargis',    'last_name' => 'Sultana',        'gender' => 'female', 'designation' => 'Senior Teacher',       'qualification' => 'M.A. (English)',  'salary' => 40000, 'email' => 'nargis@dhruboacademy.edu.bd'],
            ['first_name' => 'Anisur',    'last_name' => 'Rahman',         'gender' => 'male',   'designation' => 'Teacher',              'qualification' => 'B.Ed.',            'salary' => 35000, 'email' => 'anis@dhruboacademy.edu.bd'],
            ['first_name' => 'Shirin',    'last_name' => 'Akter',          'gender' => 'female', 'designation' => 'Teacher',              'qualification' => 'B.Sc. (Chemistry)', 'salary' => 35000, 'email' => 'shirin@dhruboacademy.edu.bd'],
            ['first_name' => 'Kamrul',    'last_name' => 'Hasan',          'gender' => 'male',   'designation' => 'Teacher',              'qualification' => 'M.Com.',           'salary' => 33000, 'email' => 'kamrul@dhruboacademy.edu.bd'],
            ['first_name' => 'Lovely',    'last_name' => 'Khanam',         'gender' => 'female', 'designation' => 'Teacher',              'qualification' => 'M.A. (History)',  'salary' => 33000, 'email' => 'lovely@dhruboacademy.edu.bd'],
            ['first_name' => 'Monirul',   'last_name' => 'Islam',          'gender' => 'male',   'designation' => 'Junior Teacher',       'qualification' => 'B.Ed.',            'salary' => 28000, 'email' => 'monirul@dhruboacademy.edu.bd'],
            ['first_name' => 'Roksana',   'last_name' => 'Parvin',         'gender' => 'female', 'designation' => 'Junior Teacher',       'qualification' => 'B.Sc.',            'salary' => 28000, 'email' => 'roksana@dhruboacademy.edu.bd'],
            ['first_name' => 'Shahidul',  'last_name' => 'Alam',           'gender' => 'male',   'designation' => 'Teacher',              'qualification' => 'M.A. (Islamic Studies)', 'salary' => 30000, 'email' => 'shahidul@dhruboacademy.edu.bd'],
            ['first_name' => 'Nasrin',    'last_name' => 'Hossain',        'gender' => 'female', 'designation' => 'Teacher',              'qualification' => 'M.Sc. (Biology)', 'salary' => 32000, 'email' => 'nasrin@dhruboacademy.edu.bd'],
            ['first_name' => 'Mizanur',   'last_name' => 'Rahman',         'gender' => 'male',   'designation' => 'Junior Teacher',       'qualification' => 'B.Sc.',            'salary' => 27000, 'email' => 'mizan@dhruboacademy.edu.bd'],
            ['first_name' => 'Taslima',   'last_name' => 'Khatun',         'gender' => 'female', 'designation' => 'Junior Teacher',       'qualification' => 'B.A.',             'salary' => 26000, 'email' => 'taslima@dhruboacademy.edu.bd'],
            ['first_name' => 'Badrul',    'last_name' => 'Alam',           'gender' => 'male',   'designation' => 'PT Teacher',           'qualification' => 'B.P.Ed.',          'salary' => 25000, 'email' => 'badrul@dhruboacademy.edu.bd'],
        ];

        $religions = ['Islam', 'Islam', 'Islam', 'Islam', 'Hinduism', 'Christianity'];
        $addresses = [
            'House 12, Road 5, Mirpur-10, Dhaka',
            'Flat 3B, Mohammadpur, Dhaka-1207',
            'Village: Shimulia, PO: Dhamrai, Dhaka',
            'House 45, Section 6, Mirpur, Dhaka',
            'Flat 7A, Uttara Sector-7, Dhaka-1230',
        ];
        $bloodGroups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-'];

        foreach ($teachers as $i => $teacher) {
            $joiningDate = Carbon::create(2020, rand(1, 12), rand(1, 28))->subYears(rand(0, 5));
            DB::table('teachers')->insert(array_merge($teacher, [
                'teacher_id'       => 'TCH-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'date_of_birth'    => Carbon::create(1975 + ($i % 20), rand(1, 12), rand(1, 28))->toDateString(),
                'religion'         => $religions[$i % count($religions)],
                'blood_group'      => $bloodGroups[$i % count($bloodGroups)],
                'nationality'      => 'Bangladeshi',
                'phone'            => '017' . str_pad(rand(10000000, 99999999), 8, '0'),
                'present_address'  => $addresses[$i % count($addresses)],
                'permanent_address'=> $addresses[($i + 1) % count($addresses)],
                'joining_date'     => $joiningDate->toDateString(),
                'experience'       => rand(2, 20) . ' years',
                'bank_name'        => ['Dutch-Bangla Bank', 'Sonali Bank', 'Islami Bank', 'BRAC Bank'][$i % 4],
                'bank_account_no'  => rand(1000000000, 9999999999),
                'is_active'        => true,
                'created_at'       => now(), 'updated_at' => now()
            ]));
        }

        // Assign subjects to teachers
        $teacherIds  = DB::table('teachers')->pluck('id')->toArray();
        $subjectIds  = DB::table('subjects')->pluck('id', 'class_id');

        // Each teacher assigned to 2 classes, 2 subjects each
        foreach ($teacherIds as $tIndex => $teacherId) {
            $classId1 = ($tIndex % 7) + 1;
            $classId2 = (($tIndex + 1) % 7) + 1;

            foreach ([$classId1, $classId2] as $classId) {
                $classSubjects = DB::table('subjects')->where('class_id', $classId)->pluck('id')->take(2);
                foreach ($classSubjects as $subjectId) {
                    try {
                        DB::table('teacher_subjects')->insert([
                            'teacher_id' => $teacherId, 'subject_id' => $subjectId, 'class_id' => $classId,
                            'created_at' => now(), 'updated_at' => now()
                        ]);
                    } catch (\Exception $e) { /* skip duplicates */ }
                }

                // Class teacher assignment
                $sectionId = DB::table('sections')->where('class_id', $classId)->first()?->id;
                try {
                    DB::table('teacher_classes')->insert([
                        'teacher_id' => $teacherId, 'class_id' => $classId,
                        'section_id' => $sectionId, 'is_class_teacher' => ($tIndex < 7),
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                } catch (\Exception $e) { /* skip */ }
            }
        }

        // Attendance last 30 days
        for ($d = 29; $d >= 0; $d--) {
            $date = Carbon::today()->subDays($d);
            if (in_array($date->dayOfWeek, [4, 5])) continue; // skip Fri/Sat

            foreach ($teacherIds as $teacherId) {
                $rand = rand(1, 10);
                $status = $rand <= 8 ? 'present' : ($rand == 9 ? 'late' : 'absent');
                try {
                    DB::table('teacher_attendances')->insert([
                        'teacher_id' => $teacherId, 'date' => $date->toDateString(),
                        'status'     => $status,
                        'in_time'    => $status !== 'absent' ? ($status === 'late' ? '09:15' : '08:00') : null,
                        'out_time'   => $status !== 'absent' ? '16:00' : null,
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                } catch (\Exception $e) {}
            }
        }

        // Teacher Leaves
        $leaveTypeIds = DB::table('leave_types')->pluck('id')->toArray();
        $leaveData = [
            ['teacher_id' => $teacherIds[0], 'start_date' => '2026-01-15', 'end_date' => '2026-01-17', 'reason' => 'Medical treatment',      'status' => 'approved'],
            ['teacher_id' => $teacherIds[1], 'start_date' => '2026-02-05', 'end_date' => '2026-02-06', 'reason' => 'Family emergency',        'status' => 'approved'],
            ['teacher_id' => $teacherIds[2], 'start_date' => '2026-03-10', 'end_date' => '2026-03-12', 'reason' => 'Attending workshop',      'status' => 'approved'],
            ['teacher_id' => $teacherIds[3], 'start_date' => '2026-04-01', 'end_date' => '2026-04-02', 'reason' => 'Personal work',           'status' => 'rejected'],
            ['teacher_id' => $teacherIds[4], 'start_date' => '2026-06-20', 'end_date' => '2026-06-22', 'reason' => 'Sick leave',              'status' => 'pending'],
            ['teacher_id' => $teacherIds[5], 'start_date' => '2026-07-01', 'end_date' => '2026-07-03', 'reason' => 'Annual leave',            'status' => 'pending'],
            ['teacher_id' => $teacherIds[6], 'start_date' => '2025-11-10', 'end_date' => '2025-11-11', 'reason' => 'Religious ceremony',      'status' => 'approved'],
            ['teacher_id' => $teacherIds[7], 'start_date' => '2026-05-05', 'end_date' => '2026-05-06', 'reason' => 'Visiting ailing parent',  'status' => 'approved'],
        ];

        foreach ($leaveData as $leave) {
            DB::table('teacher_leaves')->insert(array_merge($leave, [
                'leave_type_id' => $leaveTypeIds[rand(0, count($leaveTypeIds) - 1)],
                'created_at' => now(), 'updated_at' => now()
            ]));
        }

        // Salary payments (last 6 months)
        $months = ['January', 'February', 'March', 'April', 'May', 'June'];
        $year = '2026';

        foreach ($teacherIds as $teacherId) {
            $teacher = DB::table('teachers')->find($teacherId);
            foreach ($months as $mIndex => $month) {
                $basic   = $teacher->salary;
                $allowance = $basic * 0.30;
                $deduction = $basic * 0.05;
                $gross   = $basic + $allowance;
                $net     = $gross - $deduction;
                DB::table('salary_payments')->insert([
                    'staff_id'       => $teacherId,
                    'staff_type'     => 'teacher',
                    'basic_salary'   => $basic,
                    'allowance'      => $allowance,
                    'deduction'      => $deduction,
                    'gross_salary'   => $gross,
                    'net_salary'     => $net,
                    'month'          => (string) ($mIndex + 1),
                    'year'           => $year,
                    'payment_date'   => Carbon::create(2026, $mIndex + 1, 28)->toDateString(),
                    'payment_method' => 'bank',
                    'created_at'     => now(), 'updated_at' => now()
                ]);
            }
        }

        $this->command->info('  → Teachers seeded (' . count($teachers) . ' teachers, attendance + salary + leaves)');
    }

    // ─── Students ─────────────────────────────────────────────────────────────

    private function seedStudents(): void
    {
        DB::table('student_attendances')->truncate();
        DB::table('student_transfers')->truncate();
        DB::table('student_documents')->truncate();
        DB::table('guardians')->truncate();
        DB::table('students')->truncate();

        $currentYearId = DB::table('academic_years')->where('is_current', true)->value('id');
        $classIds      = DB::table('classes')->pluck('id')->toArray();
        $sectionData   = DB::table('sections')->get();

        $firstNames = [
            'Rahim', 'Karim', 'Jamal', 'Rasel', 'Milon', 'Sumon', 'Arif', 'Raju', 'Sabbir', 'Tanvir',
            'Sakib', 'Farhan', 'Nabeel', 'Imran', 'Faisal', 'Tarek', 'Robiul', 'Mehedi', 'Shamim', 'Asif',
            'Fatema', 'Nusrat', 'Sadia', 'Ritu', 'Puja', 'Mitu', 'Nipa', 'Lima', 'Tanha', 'Farida',
            'Sumaiya', 'Bristy', 'Mim', 'Asha', 'Riya', 'Joly', 'Soma', 'Dipa', 'Tina', 'Priya',
        ];
        $lastNames = [
            'Islam', 'Rahman', 'Hossain', 'Ahmed', 'Khan', 'Akter', 'Begum', 'Sarkar', 'Miah',
            'Bhuiyan', 'Chowdhury', 'Mondal', 'Das', 'Roy', 'Paul', 'Dey', 'Saha',
        ];
        $religions   = ['Islam', 'Islam', 'Islam', 'Hinduism', 'Christianity', 'Buddhism'];
        $bloodGroups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'];
        $shifts      = ['Morning', 'Day'];
        $groups      = ['Science', 'Humanities', 'Commerce'];

        $addresses = [
            'House 5, Road 3, Mirpur-1, Dhaka',
            'Village: Bagherpara, PO: Shimulia, Manikganj',
            'Flat 2C, Dhanmondi, Dhaka-1205',
            'House 77, Sector 7, Uttara, Dhaka-1230',
            'Village: Keraniganj, Dhaka District',
            'House 12, Mohammadpur, Dhaka-1207',
            'Flat 4D, Bashundhara R/A, Dhaka-1229',
        ];

        $studentCount = 0;

        foreach ($classIds as $classId) {
            $sections = $sectionData->where('class_id', $classId);

            foreach ($sections as $section) {
                $numStudents = rand(28, 35);

                for ($s = 0; $s < $numStudents; $s++) {
                    $gender    = $s < ($numStudents / 2) ? 'male' : 'female';
                    $firstName = $firstNames[($s + ($gender === 'female' ? 20 : 0)) % count($firstNames)];
                    $lastName  = $lastNames[rand(0, count($lastNames) - 1)];
                    $studentCount++;

                    $admissionDate = Carbon::create(2020 + ($classId - 1), 1, rand(1, 15));
                    $dob = Carbon::create(2010 + $classId, rand(1, 12), rand(1, 28));

                    $studentId = DB::table('students')->insertGetId([
                        'student_id'          => 'STU-' . str_pad($studentCount, 5, '0', STR_PAD_LEFT),
                        'first_name'          => $firstName,
                        'last_name'           => $lastName,
                        'gender'              => $gender,
                        'date_of_birth'       => $dob->toDateString(),
                        'birth_certificate_no'=> 'BC' . rand(10000000, 99999999),
                        'religion'            => $religions[rand(0, count($religions) - 1)],
                        'blood_group'         => $bloodGroups[rand(0, count($bloodGroups) - 1)],
                        'nationality'         => 'Bangladeshi',
                        'phone'               => '018' . rand(10000000, 99999999),
                        'present_address'     => $addresses[rand(0, count($addresses) - 1)],
                        'permanent_address'   => $addresses[rand(0, count($addresses) - 1)],
                        'class_id'            => $classId,
                        'section_id'          => $section->id,
                        'academic_year_id'    => $currentYearId,
                        'subject_group_id'    => $classId >= 4
                            ? (DB::table('subject_groups')->where('name', $groups[rand(0, 2)])->value('id')
                                ?? DB::table('subject_groups')->insertGetId(['name' => $groups[rand(0, 2)], 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]))
                            : null,
                        'shift'               => $shifts[rand(0, 1)],
                        'roll_number'         => str_pad($s + 1, 2, '0', STR_PAD_LEFT),
                        'registration_number' => 'REG' . rand(100000, 999999),
                        'admission_date'      => $admissionDate->toDateString(),
                        'is_active'           => true,
                        'created_at'          => now(), 'updated_at' => now()
                    ]);

                    // Guardian
                    $fatherFirstNames = ['Md. Abdul', 'Md. Rafiqul', 'Md. Anisur', 'Md. Kabirul', 'Md. Jalal'];
                    $motherFirstNames = ['Fatema', 'Rahela', 'Nasrin', 'Shirin', 'Lovely'];

                    DB::table('guardians')->insert([
                        'student_id'        => $studentId,
                        'father_name'       => $fatherFirstNames[rand(0, 4)] . ' ' . $lastName,
                        'father_phone'      => '017' . rand(10000000, 99999999),
                        'father_occupation' => ['Businessman', 'Farmer', 'Service Holder', 'Teacher', 'Driver', 'Garments Worker'][rand(0, 5)],
                        'mother_name'       => $motherFirstNames[rand(0, 4)] . ' Begum',
                        'mother_phone'      => '018' . rand(10000000, 99999999),
                        'mother_occupation' => ['Housewife', 'Teacher', 'Nurse', 'Garments Worker'][rand(0, 3)],
                        'guardian_name'     => null,
                        'guardian_relation' => 'Father',
                        'guardian_phone'    => '017' . rand(10000000, 99999999),
                        'created_at'        => now(), 'updated_at' => now()
                    ]);
                }
            }
        }

        // Attendance last 30 days for first 3 classes
        $studentIds = DB::table('students')->where('class_id', '<=', 3)->pluck('id')->toArray();
        for ($d = 29; $d >= 0; $d--) {
            $date = Carbon::today()->subDays($d);
            if (in_array($date->dayOfWeek, [4, 5])) continue;

            foreach ($studentIds as $studentId) {
                $rand   = rand(1, 10);
                $status = $rand <= 8 ? 'present' : ($rand == 9 ? 'late' : 'absent');
                try {
                    DB::table('student_attendances')->insert([
                        'student_id' => $studentId, 'date' => $date->toDateString(),
                        'status'     => $status,
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                } catch (\Exception $e) {}
            }
        }

        // Transfers
        $transferStudents = DB::table('students')->limit(5)->get();
        foreach ($transferStudents as $i => $stu) {
            DB::table('student_transfers')->insert([
                'student_id'   => $stu->id,
                'from_school'  => 'Dhrubo Academy',
                'to_school'    => ['Sunnydale School', 'Green Land School', 'City School', 'BN School', 'Model School'][$i],
                'transfer_date'=> Carbon::now()->subDays(rand(10, 60))->toDateString(),
                'reason'       => ['Family relocation', 'Better opportunity', 'Distance issue', 'Personal reason', 'Academic preference'][$i],
                'status'       => ['pending', 'approved', 'rejected', 'approved', 'pending'][$i],
                'created_at'   => now(), 'updated_at' => now()
            ]);
        }

        $this->command->info("  → Students seeded ($studentCount students + guardians + attendance)");
    }

    // ─── Fees ─────────────────────────────────────────────────────────────────

    private function seedFees(): void
    {
        DB::table('fee_payments')->truncate();
        DB::table('student_discounts')->truncate();
        DB::table('fee_collections')->truncate();
        DB::table('fee_structures')->truncate();
        DB::table('fee_types')->truncate();

        $currentYearId = DB::table('academic_years')->where('is_current', true)->value('id');
        $classIds      = DB::table('classes')->pluck('id')->toArray();

        // Fee Types
        $feeTypes = [
            ['name' => 'Monthly Tuition Fee', 'type' => 'monthly',   'amount' => 2000],
            ['name' => 'Admission Fee',        'type' => 'admission', 'amount' => 5000],
            ['name' => 'Exam Fee',             'type' => 'exam',      'amount' => 500],
            ['name' => 'Library Fee',          'type' => 'library',   'amount' => 200],
            ['name' => 'Transport Fee',        'type' => 'transport', 'amount' => 1500],
            ['name' => 'Development Fee',      'type' => 'admission', 'amount' => 3000],
            ['name' => 'Sports Fee',           'type' => 'monthly',   'amount' => 300],
        ];

        foreach ($feeTypes as $ft) {
            DB::table('fee_types')->insert(array_merge($ft, [
                'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]));
        }

        $feeTypeIds = DB::table('fee_types')->pluck('id')->toArray();

        // Fee Structures per class (higher classes pay more)
        $classAmountMultiplier = [1.0, 1.1, 1.2, 1.3, 1.4, 1.6, 1.7];
        foreach ($classIds as $cIdx => $classId) {
            foreach ($feeTypeIds as $ftId) {
                $baseAmount = DB::table('fee_types')->find($ftId)->amount;
                $amount = round($baseAmount * $classAmountMultiplier[$cIdx], 0);
                DB::table('fee_structures')->insert([
                    'fee_type_id'     => $ftId, 'class_id' => $classId,
                    'academic_year_id'=> $currentYearId, 'amount' => $amount,
                    'is_active' => true, 'created_at' => now(), 'updated_at' => now()
                ]);
            }
        }

        // Fee collections (6 months × first 3 classes)
        $students    = DB::table('students')->where('class_id', '<=', 4)->get();
        $months      = ['January', 'February', 'March', 'April', 'May', 'June'];
        $monthlyFtId = DB::table('fee_types')->where('type', 'monthly')->first()->id;
        $invoiceNo   = 1000;

        foreach ($students->take(120) as $student) {
            $feeStructure = DB::table('fee_structures')
                ->where('class_id', $student->class_id)
                ->where('fee_type_id', $monthlyFtId)
                ->first();

            if (!$feeStructure) continue;

            foreach ($months as $mIdx => $month) {
                $payDate  = Carbon::create(2026, $mIdx + 1, rand(5, 25));
                $status   = rand(1, 10) <= 8 ? 'paid' : (rand(1, 10) <= 5 ? 'partial' : 'unpaid');
                $paid     = $status === 'paid' ? $feeStructure->amount : ($status === 'partial' ? $feeStructure->amount * 0.5 : 0);
                $due      = $feeStructure->amount - $paid;

                DB::table('fee_collections')->insert([
                    'student_id'      => $student->id,
                    'fee_type_id'     => $monthlyFtId,
                    'academic_year_id'=> $currentYearId,
                    'class_id'        => $student->class_id,
                    'amount'          => $feeStructure->amount,
                    'month'           => (string) ($mIdx + 1),
                    'year'            => '2026',
                    'fine'            => 0,
                    'discount'        => 0,
                    'paid_amount'     => $paid,
                    'due_amount'      => $due,
                    'due_date'        => Carbon::create(2026, $mIdx + 1, 10)->toDateString(),
                    'payment_date'    => $status !== 'unpaid' ? $payDate->toDateString() : null,
                    'status'          => $status,
                    'invoice_no'      => 'INV-' . str_pad($invoiceNo++, 6, '0', STR_PAD_LEFT),
                    'payment_method'  => ['cash', 'bank', 'mobile_banking'][rand(0, 2)],
                    'created_at'      => now(), 'updated_at' => now()
                ]);
            }

            // Admission fee
            DB::table('fee_collections')->insert([
                'student_id'      => $student->id,
                'fee_type_id'     => DB::table('fee_types')->where('type', 'admission')->first()->id,
                'academic_year_id'=> $currentYearId,
                'class_id'        => $student->class_id,
                'amount'          => 5000,
                'month'           => null,
                'year'            => '2026',
                'fine'            => 0,
                'discount'        => rand(0, 500),
                'paid_amount'     => 5000,
                'due_amount'      => 0,
                'due_date'        => '2026-01-15',
                'payment_date'    => '2026-01-10',
                'status'          => 'paid',
                'invoice_no'      => 'INV-' . str_pad($invoiceNo++, 6, '0', STR_PAD_LEFT),
                'payment_method'  => 'cash',
                'created_at'      => now(), 'updated_at' => now()
            ]);
        }

        $this->command->info('  → Fees seeded (fee types, structures, ' . ($invoiceNo - 1000) . ' collections)');
    }

    // ─── Exams ────────────────────────────────────────────────────────────────

    private function seedExams(): void
    {
        DB::table('result_publishes')->truncate();
        DB::table('results')->truncate();
        DB::table('student_marks')->truncate();
        DB::table('grades')->truncate();
        DB::table('exam_schedules')->truncate();
        DB::table('exams')->truncate();
        DB::table('exam_types')->truncate();

        $currentYearId = DB::table('academic_years')->where('is_current', true)->value('id');

        // Exam Types
        $examTypes = [
            ['name' => 'Monthly Test',   'is_active' => true],
            ['name' => 'Mid-term Exam',  'is_active' => true],
            ['name' => 'Annual Exam',    'is_active' => true],
            ['name' => 'Trial Exam',     'is_active' => true],
            ['name' => 'Weekly Test',    'is_active' => true],
        ];

        foreach ($examTypes as $et) {
            DB::table('exam_types')->insert(array_merge($et, [
                'created_at' => now(), 'updated_at' => now()
            ]));
        }

        $examTypeIds = DB::table('exam_types')->pluck('id')->toArray();

        // Grades (Bangladesh GPA System)
        $grades = [
            ['name' => 'A+', 'grade_point' => 5.00, 'mark_from' => 80, 'mark_to' => 100, 'description' => 'Excellent'],
            ['name' => 'A',  'grade_point' => 4.00, 'mark_from' => 70, 'mark_to' => 79,  'description' => 'Very Good'],
            ['name' => 'A-', 'grade_point' => 3.50, 'mark_from' => 60, 'mark_to' => 69,  'description' => 'Good'],
            ['name' => 'B',  'grade_point' => 3.00, 'mark_from' => 50, 'mark_to' => 59,  'description' => 'Satisfactory'],
            ['name' => 'C',  'grade_point' => 2.00, 'mark_from' => 40, 'mark_to' => 49,  'description' => 'Average'],
            ['name' => 'D',  'grade_point' => 1.00, 'mark_from' => 33, 'mark_to' => 39,  'description' => 'Below Average'],
            ['name' => 'F',  'grade_point' => 0.00, 'mark_from' => 0,  'mark_to' => 32,  'description' => 'Fail'],
        ];

        foreach ($grades as $grade) {
            DB::table('grades')->insert(array_merge($grade, [
                'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]));
        }

        // Exams
        $examsData = [
            ['name' => 'January Monthly Test 2026', 'type_idx' => 0, 'start' => '2026-01-25', 'end' => '2026-01-30', 'published' => true],
            ['name' => 'February Monthly Test 2026','type_idx' => 0, 'start' => '2026-02-22', 'end' => '2026-02-27', 'published' => true],
            ['name' => 'First Term Exam 2026',      'type_idx' => 1, 'start' => '2026-04-10', 'end' => '2026-04-25', 'published' => true],
            ['name' => 'April Monthly Test 2026',   'type_idx' => 0, 'start' => '2026-04-27', 'end' => '2026-04-30', 'published' => true],
            ['name' => 'May Monthly Test 2026',     'type_idx' => 0, 'start' => '2026-05-25', 'end' => '2026-05-30', 'published' => false],
            ['name' => 'Pre-Annual Trial Exam',     'type_idx' => 3, 'start' => '2026-10-01', 'end' => '2026-10-15', 'published' => false],
            ['name' => 'Annual Exam 2026',          'type_idx' => 2, 'start' => '2026-11-15', 'end' => '2026-12-05', 'published' => false],
        ];

        foreach ($examsData as $exam) {
            DB::table('exams')->insert([
                'name'             => $exam['name'],
                'exam_type_id'     => $examTypeIds[$exam['type_idx']],
                'academic_year_id' => $currentYearId,
                'start_date'       => $exam['start'],
                'end_date'         => $exam['end'],
                'is_published'     => $exam['published'],
                'is_active'        => true,
                'created_at'       => now(), 'updated_at' => now()
            ]);
        }

        $examIds   = DB::table('exams')->pluck('id')->toArray();
        $classIds  = DB::table('classes')->pluck('id')->toArray();

        // Exam Schedules (first 3 exams × first 3 classes)
        foreach (array_slice($examIds, 0, 3) as $examId) {
            foreach (array_slice($classIds, 0, 3) as $classId) {
                $subjects = DB::table('subjects')->where('class_id', $classId)->take(5)->get();
                $datePtr  = Carbon::parse(DB::table('exams')->find($examId)->start_date);

                foreach ($subjects as $subject) {
                    try {
                        DB::table('exam_schedules')->insert([
                            'exam_id'    => $examId, 'class_id' => $classId, 'subject_id' => $subject->id,
                            'date'       => $datePtr->toDateString(),
                            'start_time' => '09:00', 'end_time' => '12:00',
                            'room'       => 'Exam Hall ' . rand(1, 5),
                            'full_mark'  => 100, 'pass_mark' => 33,
                            'created_at' => now(), 'updated_at' => now()
                        ]);
                        $datePtr->addDay();
                    } catch (\Exception $e) {}
                }
            }
        }

        // Student Marks (for first published exam, first 2 classes)
        $publishedExam = DB::table('exams')->where('is_published', true)->first();
        if ($publishedExam) {
            $students    = DB::table('students')->where('class_id', '<=', 2)->get();
            $gradeRecords= DB::table('grades')->orderByDesc('mark_from')->get();

            foreach ($students as $student) {
                $subjects = DB::table('subjects')->where('class_id', $student->class_id)->take(5)->get();
                $totalObtained = 0;

                foreach ($subjects as $subject) {
                    $marks = rand(33, 100);
                    $totalObtained += $marks;

                    $gradeRecord = $gradeRecords->first(function ($g) use ($marks) {
                        return $marks >= $g->mark_from && $marks <= $g->mark_to;
                    });

                    try {
                        DB::table('student_marks')->insert([
                            'student_id'  => $student->id,
                            'exam_id'     => $publishedExam->id,
                            'subject_id'  => $subject->id,
                            'class_id'    => $student->class_id,
                            'written'     => $marks,
                            'total_mark'  => $marks,
                            'grade_id'    => $gradeRecord?->id,
                            'grade_point' => $gradeRecord?->grade_point,
                            'is_absent'   => false,
                            'created_at'  => now(), 'updated_at' => now()
                        ]);
                    } catch (\Exception $e) {}
                }

                $numSubjects = $subjects->count();
                $avgMark     = $numSubjects > 0 ? round($totalObtained / $numSubjects, 2) : 0;
                $gradeRecord = $gradeRecords->first(function ($g) use ($avgMark) {
                    return $avgMark >= $g->mark_from && $avgMark <= $g->mark_to;
                });

                try {
                    DB::table('results')->insert([
                        'student_id'    => $student->id,
                        'exam_id'       => $publishedExam->id,
                        'class_id'      => $student->class_id,
                        'section_id'    => $student->section_id,
                        'total_mark'    => $numSubjects * 100,
                        'obtained_mark' => $totalObtained,
                        'gpa'           => $gradeRecord?->grade_point ?? 0,
                        'grade'         => $gradeRecord?->name ?? 'F',
                        'position'      => rand(1, 30),
                        'is_published'  => true,
                        'created_at'    => now(), 'updated_at' => now()
                    ]);
                } catch (\Exception $e) {}
            }
        }

        $this->command->info('  → Exams seeded (7 exams, schedules, marks, results)');
    }

    // ─── Expenses & Income ────────────────────────────────────────────────────

    private function seedExpensesIncome(): void
    {
        DB::table('expenses')->truncate();
        DB::table('expense_categories')->truncate();
        DB::table('incomes')->truncate();
        DB::table('income_categories')->truncate();

        $currentYearId = DB::table('academic_years')->where('is_current', true)->value('id');

        // Expense Categories
        $expCats = [
            'Salary & Benefits', 'Utilities (Water, Electricity)', 'Maintenance & Repair',
            'Stationery & Office Supplies', 'Books & Library', 'Sports & Games',
            'Cleaning & Sanitation', 'IT & Technology', 'Security', 'Miscellaneous',
        ];

        foreach ($expCats as $cat) {
            DB::table('expense_categories')->insert([
                'name' => $cat, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        $expCatIds = DB::table('expense_categories')->pluck('id')->toArray();

        // Expenses (last 6 months)
        $expenseData = [
            ['cat' => 0, 'amount' => 425000, 'desc' => 'Monthly staff salaries',         'vendor' => null],
            ['cat' => 1, 'amount' => 18500,  'desc' => 'Electricity bill - January',     'vendor' => 'DESCO'],
            ['cat' => 1, 'amount' => 4200,   'desc' => 'Water bill - January',            'vendor' => 'DWASA'],
            ['cat' => 2, 'amount' => 35000,  'desc' => 'Classroom furniture repair',      'vendor' => 'Rahman Furniture'],
            ['cat' => 3, 'amount' => 12000,  'desc' => 'Office stationery purchase',      'vendor' => 'New Stationery Mart'],
            ['cat' => 4, 'amount' => 28000,  'desc' => 'New books for library',           'vendor' => 'Book Haven'],
            ['cat' => 5, 'amount' => 15000,  'desc' => 'Annual sports equipment',         'vendor' => 'Sports World'],
            ['cat' => 6, 'amount' => 8000,   'desc' => 'Cleaning materials',              'vendor' => null],
            ['cat' => 7, 'amount' => 45000,  'desc' => 'Computer lab maintenance',        'vendor' => 'TechCare BD'],
            ['cat' => 8, 'amount' => 12000,  'desc' => 'Security guard salary',           'vendor' => null],
            ['cat' => 9, 'amount' => 5000,   'desc' => 'Miscellaneous expenses',          'vendor' => null],
            ['cat' => 1, 'amount' => 19200,  'desc' => 'Electricity bill - February',    'vendor' => 'DESCO'],
            ['cat' => 0, 'amount' => 425000, 'desc' => 'Monthly staff salaries - Feb',   'vendor' => null],
            ['cat' => 2, 'amount' => 22000,  'desc' => 'Roof repair',                     'vendor' => 'BRB Construction'],
            ['cat' => 3, 'amount' => 8500,   'desc' => 'Exam stationery',                 'vendor' => 'Paper House'],
            ['cat' => 7, 'amount' => 18000,  'desc' => 'Internet service payment',        'vendor' => 'Link3 ISP'],
            ['cat' => 4, 'amount' => 12000,  'desc' => 'Magazine subscriptions',          'vendor' => 'Press Club'],
            ['cat' => 0, 'amount' => 430000, 'desc' => 'Monthly staff salaries - Mar',   'vendor' => null],
            ['cat' => 1, 'amount' => 17800,  'desc' => 'Electricity bill - March',       'vendor' => 'DESCO'],
            ['cat' => 9, 'amount' => 9500,   'desc' => 'Science fair expenses',           'vendor' => null],
            ['cat' => 5, 'amount' => 7500,   'desc' => 'Cricket equipment',               'vendor' => 'Sports Corner'],
            ['cat' => 6, 'amount' => 6200,   'desc' => 'Pest control',                   'vendor' => 'CleanPro BD'],
            ['cat' => 0, 'amount' => 430000, 'desc' => 'Monthly staff salaries - Apr',   'vendor' => null],
            ['cat' => 1, 'amount' => 21000,  'desc' => 'Electricity bill - April',       'vendor' => 'DESCO'],
            ['cat' => 2, 'amount' => 16000,  'desc' => 'Plumbing repair - washrooms',    'vendor' => 'Quick Fix'],
            ['cat' => 0, 'amount' => 435000, 'desc' => 'Monthly staff salaries - May',   'vendor' => null],
            ['cat' => 1, 'amount' => 22500,  'desc' => 'Electricity bill - May',         'vendor' => 'DESCO'],
            ['cat' => 3, 'amount' => 14000,  'desc' => 'Printing & photocopying',        'vendor' => 'Quick Print'],
        ];

        $monthDates = ['2026-01-28','2026-01-15','2026-01-20','2026-02-10','2026-01-12',
                       '2026-01-25','2026-02-05','2026-01-18','2026-01-30','2026-01-15',
                       '2026-01-22','2026-02-28','2026-02-28','2026-02-15','2026-03-01',
                       '2026-02-20','2026-02-18','2026-03-28','2026-03-28','2026-03-15',
                       '2026-03-20','2026-03-25','2026-04-28','2026-04-28','2026-04-18',
                       '2026-05-28','2026-05-28','2026-05-12'];

        foreach ($expenseData as $eIdx => $exp) {
            DB::table('expenses')->insert([
                'expense_category_id' => $expCatIds[$exp['cat']],
                'academic_year_id'    => $currentYearId,
                'amount'              => $exp['amount'],
                'date'                => $monthDates[$eIdx % count($monthDates)],
                'description'         => $exp['desc'],
                'vendor_name'         => $exp['vendor'],
                'created_at'          => now(), 'updated_at' => now()
            ]);
        }

        // Income Categories
        $incCats = ['Donation', 'Government Grant', 'Event Income', 'Canteen', 'Publication', 'Other'];
        foreach ($incCats as $cat) {
            DB::table('income_categories')->insert([
                'name' => $cat, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        $incCatIds = DB::table('income_categories')->pluck('id')->toArray();

        $incomeData = [
            ['cat' => 0, 'amount' => 100000, 'date' => '2026-01-10', 'desc' => 'Annual donation from alumni association'],
            ['cat' => 1, 'amount' => 250000, 'date' => '2026-02-01', 'desc' => 'Government education grant Q1'],
            ['cat' => 2, 'amount' => 35000,  'date' => '2026-02-20', 'desc' => 'Annual sports day ticket sales'],
            ['cat' => 3, 'amount' => 18000,  'date' => '2026-01-31', 'desc' => 'Canteen revenue - January'],
            ['cat' => 3, 'amount' => 17500,  'date' => '2026-02-28', 'desc' => 'Canteen revenue - February'],
            ['cat' => 3, 'amount' => 19000,  'date' => '2026-03-31', 'desc' => 'Canteen revenue - March'],
            ['cat' => 4, 'amount' => 12000,  'date' => '2026-03-15', 'desc' => 'Annual magazine sales'],
            ['cat' => 0, 'amount' => 50000,  'date' => '2026-03-20', 'desc' => 'Donation for library development'],
            ['cat' => 5, 'amount' => 8000,   'date' => '2026-04-10', 'desc' => 'Miscellaneous income'],
            ['cat' => 2, 'amount' => 22000,  'date' => '2026-05-12', 'desc' => 'Cultural program ticket income'],
            ['cat' => 1, 'amount' => 200000, 'date' => '2026-05-01', 'desc' => 'Government education grant Q2'],
            ['cat' => 3, 'amount' => 20000,  'date' => '2026-04-30', 'desc' => 'Canteen revenue - April'],
            ['cat' => 3, 'amount' => 21000,  'date' => '2026-05-31', 'desc' => 'Canteen revenue - May'],
        ];

        foreach ($incomeData as $inc) {
            DB::table('incomes')->insert([
                'income_category_id' => $incCatIds[$inc['cat']],
                'academic_year_id'   => $currentYearId,
                'amount'             => $inc['amount'],
                'date'               => $inc['date'],
                'description'        => $inc['desc'],
                'created_at'         => now(), 'updated_at' => now()
            ]);
        }

        $this->command->info('  → Expenses (' . count($expenseData) . ') and Income (' . count($incomeData) . ') seeded');
    }

    // ─── Library ──────────────────────────────────────────────────────────────

    private function seedLibrary(): void
    {
        DB::table('library_members')->truncate();
        DB::table('book_issues')->truncate();
        DB::table('books')->truncate();

        $books = [
            ['title' => 'Bangla Sahitto Itihas',          'author' => 'Dr. Ahmed Sharif',         'publisher' => 'Muktadhara',          'type' => 'reference', 'qty' => 5,  'price' => 450],
            ['title' => 'Advanced Mathematics',            'author' => 'R.D. Sharma',               'publisher' => 'Dhanpat Rai',         'type' => 'text',      'qty' => 8,  'price' => 380],
            ['title' => 'English Grammar in Use',          'author' => 'Raymond Murphy',            'publisher' => 'Cambridge UP',        'type' => 'reference', 'qty' => 6,  'price' => 650],
            ['title' => 'Physics for Class 9-10',          'author' => 'NCTB',                      'publisher' => 'NCTB',                'type' => 'text',      'qty' => 20, 'price' => 120],
            ['title' => 'Chemistry Fundamentals',          'author' => 'Dr. P. Atkins',             'publisher' => 'Oxford UP',           'type' => 'text',      'qty' => 5,  'price' => 780],
            ['title' => 'Biology Today',                   'author' => 'Gupta & Gupta',             'publisher' => 'S Chand',             'type' => 'text',      'qty' => 7,  'price' => 420],
            ['title' => 'Bangladesh History & Culture',    'author' => 'Dr. Ramesh Chandra',        'publisher' => 'Agami Prakashani',    'type' => 'reference', 'qty' => 4,  'price' => 350],
            ['title' => 'Sheshher Kobita',                 'author' => 'Rabindranath Tagore',       'publisher' => 'Visva Bharati',       'type' => 'reference', 'qty' => 3,  'price' => 200],
            ['title' => 'A Brief History of Time',         'author' => 'Stephen Hawking',           'publisher' => 'Bantam Books',        'type' => 'reference', 'qty' => 2,  'price' => 500],
            ['title' => 'Feluda Samagra',                  'author' => 'Satyajit Ray',              'publisher' => 'Ananda Publishers',   'type' => 'reference', 'qty' => 2,  'price' => 600],
            ['title' => 'ICT for Class 9-10',              'author' => 'NCTB',                      'publisher' => 'NCTB',                'type' => 'text',      'qty' => 18, 'price' => 90],
            ['title' => 'Accounting Principles',           'author' => 'Jerry Weygandt',            'publisher' => 'Wiley',               'type' => 'reference', 'qty' => 4,  'price' => 950],
            ['title' => 'Environmental Science',           'author' => 'Dr. Zahirul Islam',         'publisher' => 'Hasan Book House',    'type' => 'text',      'qty' => 10, 'price' => 180],
            ['title' => 'World Atlas 2025',                'author' => 'National Geographic',       'publisher' => 'Nat Geo Society',     'type' => 'reference', 'qty' => 2,  'price' => 1200],
            ['title' => 'Padma Nadir Majhi',               'author' => 'Manik Bandyopadhyay',      'publisher' => 'Sahitya Prakash',     'type' => 'reference', 'qty' => 5,  'price' => 180],
            ['title' => 'Data Structures in C',            'author' => 'Yedidyah Langsam',         'publisher' => 'Pearson',             'type' => 'reference', 'qty' => 3,  'price' => 750],
            ['title' => 'Religious Studies - Islam',       'author' => 'NCTB',                      'publisher' => 'NCTB',                'type' => 'text',      'qty' => 15, 'price' => 100],
            ['title' => 'Sanchita (Poem Collection)',      'author' => 'Kazi Nazrul Islam',         'publisher' => 'National Publications','type' => 'reference', 'qty' => 4,  'price' => 250],
            ['title' => 'General Knowledge Bangladesh',    'author' => 'Ahsan Habib',               'publisher' => 'Somoy Prokashon',     'type' => 'reference', 'qty' => 6,  'price' => 220],
            ['title' => 'Oxford English Dictionary',       'author' => 'Oxford University Press',   'publisher' => 'Oxford UP',           'type' => 'reference', 'qty' => 2,  'price' => 3500],
        ];

        foreach ($books as $i => $book) {
            DB::table('books')->insert([
                'book_id'     => 'BK-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'isbn'        => '978-' . rand(1000000000, 9999999999),
                'title'       => $book['title'],
                'author'      => $book['author'],
                'publisher'   => $book['publisher'],
                'edition'     => rand(1, 5) . 'th Edition',
                'language'    => rand(0, 1) ? 'Bengali' : 'English',
                'quantity'    => $book['qty'],
                'rack_no'     => rand(1, 10),
                'type'        => $book['type'],
                'price'       => $book['price'],
                'is_active'   => true,
                'created_at'  => now(), 'updated_at' => now()
            ]);
        }

        $bookIds    = DB::table('books')->pluck('id')->toArray();
        $studentIds = DB::table('students')->limit(30)->pluck('id')->toArray();
        $teacherIds = DB::table('teachers')->limit(10)->pluck('id')->toArray();

        // Library Members
        foreach (array_slice($studentIds, 0, 20) as $studentId) {
            DB::table('library_members')->insert([
                'member_type'     => 'student',
                'student_id'      => $studentId,
                'teacher_id'      => null,
                'membership_date' => Carbon::create(2026, 1, 10)->toDateString(),
                'expire_date'     => Carbon::create(2026, 12, 31)->toDateString(),
                'is_active'       => true,
                'created_at'      => now(), 'updated_at' => now()
            ]);
        }

        foreach (array_slice($teacherIds, 0, 5) as $teacherId) {
            DB::table('library_members')->insert([
                'member_type'     => 'teacher',
                'student_id'      => null,
                'teacher_id'      => $teacherId,
                'membership_date' => Carbon::create(2026, 1, 1)->toDateString(),
                'expire_date'     => Carbon::create(2026, 12, 31)->toDateString(),
                'is_active'       => true,
                'created_at'      => now(), 'updated_at' => now()
            ]);
        }

        // Book Issues
        $issueStatuses = ['issued', 'returned', 'issued', 'returned', 'issued'];
        foreach (array_slice($studentIds, 0, 25) as $idx => $studentId) {
            $issueDate  = Carbon::today()->subDays(rand(1, 30));
            $dueDate    = $issueDate->copy()->addDays(14);
            $status     = $issueStatuses[$idx % count($issueStatuses)];
            $returnDate = $status === 'returned' ? $dueDate->copy()->subDays(rand(1, 5))->toDateString() : null;

            DB::table('book_issues')->insert([
                'book_id'     => $bookIds[rand(0, count($bookIds) - 1)],
                'student_id'  => $studentId,
                'teacher_id'  => null,
                'issue_date'  => $issueDate->toDateString(),
                'due_date'    => $dueDate->toDateString(),
                'return_date' => $returnDate,
                'status'      => $status,
                'created_at'  => now(), 'updated_at' => now()
            ]);
        }

        $this->command->info('  → Library seeded (' . count($books) . ' books, issues, members)');
    }

    // ─── Transport ────────────────────────────────────────────────────────────

    private function seedTransport(): void
    {
        DB::table('vehicle_routes')->truncate();
        DB::table('route_stops')->truncate();
        DB::table('vehicles')->truncate();
        DB::table('transport_routes')->truncate();

        $routes = [
            ['route_name' => 'Route A - Mirpur',    'start_point' => 'School Gate',    'end_point' => 'Mirpur-1 Circle',    'distance' => 8.5,  'fee' => 1500],
            ['route_name' => 'Route B - Uttara',    'start_point' => 'School Gate',    'end_point' => 'Uttara Sector 10',   'distance' => 12.0, 'fee' => 1800],
            ['route_name' => 'Route C - Dhanmondi', 'start_point' => 'School Gate',    'end_point' => 'Dhanmondi 27',       'distance' => 6.5,  'fee' => 1400],
            ['route_name' => 'Route D - Mohammadpur','start_point' => 'School Gate',   'end_point' => 'Mohammadpur Bus Stop','distance' => 5.0,  'fee' => 1200],
            ['route_name' => 'Route E - Gulshan',   'start_point' => 'School Gate',    'end_point' => 'Gulshan-2 Circle',   'distance' => 10.0, 'fee' => 1700],
        ];

        foreach ($routes as $route) {
            DB::table('transport_routes')->insert(array_merge($route, [
                'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]));
        }

        $routeIds = DB::table('transport_routes')->pluck('id')->toArray();

        $vehicles = [
            ['vehicle_number' => 'Dhaka Metro-G 11-1234', 'model' => 'Tata Starbus', 'type' => 'bus', 'capacity' => '45', 'driver_name' => 'Md. Anowar Hossain', 'driver_phone' => '01700-001122'],
            ['vehicle_number' => 'Dhaka Metro-G 11-5678', 'model' => 'Ashok Leyland','type' => 'bus', 'capacity' => '40', 'driver_name' => 'Md. Kamal Miah',     'driver_phone' => '01700-003344'],
            ['vehicle_number' => 'Dhaka Metro-Ca 11-0011','model' => 'Toyota HiAce', 'type' => 'van', 'capacity' => '15', 'driver_name' => 'Md. Ripon Ahmed',    'driver_phone' => '01700-005566'],
            ['vehicle_number' => 'Dhaka Metro-Ca 11-2233','model' => 'Toyota HiAce', 'type' => 'van', 'capacity' => '15', 'driver_name' => 'Md. Jahirul Islam',  'driver_phone' => '01700-007788'],
            ['vehicle_number' => 'Dhaka Metro-G 11-9900', 'model' => 'Hino Bus',     'type' => 'bus', 'capacity' => '50', 'driver_name' => 'Md. Bellal Hossain', 'driver_phone' => '01700-009900'],
        ];

        foreach ($vehicles as $vehicle) {
            DB::table('vehicles')->insert(array_merge($vehicle, [
                'driver_license' => 'DL-' . rand(100000, 999999),
                'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]));
        }

        $vehicleIds = DB::table('vehicles')->pluck('id')->toArray();

        // Route Stops
        $stopsByRoute = [
            [
                ['stop_name' => 'Mirpur-1 Circle',   'arrival_time' => '07:00', 'departure_time' => '07:05', 'extra_fee' => 0],
                ['stop_name' => 'Mirpur-6',           'arrival_time' => '07:10', 'departure_time' => '07:12', 'extra_fee' => 100],
                ['stop_name' => 'Sheorapara',         'arrival_time' => '07:18', 'departure_time' => '07:20', 'extra_fee' => 150],
                ['stop_name' => 'School Gate',        'arrival_time' => '07:30', 'departure_time' => '07:30', 'extra_fee' => 0],
            ],
            [
                ['stop_name' => 'Uttara Sector 10',  'arrival_time' => '06:50', 'departure_time' => '06:55', 'extra_fee' => 0],
                ['stop_name' => 'Abdullahpur',        'arrival_time' => '07:00', 'departure_time' => '07:02', 'extra_fee' => 100],
                ['stop_name' => 'Airport Road',       'arrival_time' => '07:10', 'departure_time' => '07:12', 'extra_fee' => 150],
                ['stop_name' => 'School Gate',        'arrival_time' => '07:30', 'departure_time' => '07:30', 'extra_fee' => 0],
            ],
        ];

        foreach ($routeIds as $rIdx => $routeId) {
            $stops = $stopsByRoute[$rIdx % count($stopsByRoute)];
            foreach ($stops as $stop) {
                DB::table('route_stops')->insert(array_merge($stop, [
                    'route_id' => $routeId, 'created_at' => now(), 'updated_at' => now()
                ]));
            }

            DB::table('vehicle_routes')->insert([
                'vehicle_id' => $vehicleIds[$rIdx % count($vehicleIds)],
                'route_id'   => $routeId,
                'is_active'  => true,
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        $this->command->info('  → Transport seeded (5 routes, 5 vehicles, stops)');
    }

    // ─── Inventory ────────────────────────────────────────────────────────────

    private function seedInventory(): void
    {
        DB::table('school_purchase_items')->truncate();
        DB::table('school_purchases')->truncate();
        DB::table('school_stocks')->truncate();
        DB::table('school_products')->truncate();
        DB::table('school_suppliers')->truncate();
        DB::table('school_inventory_categories')->truncate();

        $categories = [
            ['name' => 'Office Furniture',    'type' => 'furniture'],
            ['name' => 'Electronic Equipment','type' => 'equipment'],
            ['name' => 'Stationery',          'type' => 'product'],
            ['name' => 'Sports Equipment',    'type' => 'product'],
            ['name' => 'Lab Equipment',       'type' => 'equipment'],
            ['name' => 'Cleaning Supplies',   'type' => 'product'],
        ];

        foreach ($categories as $cat) {
            DB::table('school_inventory_categories')->insert(array_merge($cat, [
                'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]));
        }

        $catIds = DB::table('school_inventory_categories')->pluck('id')->toArray();

        $products = [
            ['name' => 'Office Chair',         'cat' => 0, 'unit' => 'piece',  'price' => 4500,  'qty' => 50,  'min' => 10],
            ['name' => 'Student Bench',         'cat' => 0, 'unit' => 'piece',  'price' => 3200,  'qty' => 200, 'min' => 20],
            ['name' => 'Whiteboard',            'cat' => 0, 'unit' => 'piece',  'price' => 8000,  'qty' => 20,  'min' => 5],
            ['name' => 'Computer (Desktop)',    'cat' => 1, 'unit' => 'piece',  'price' => 45000, 'qty' => 30,  'min' => 5],
            ['name' => 'Projector',             'cat' => 1, 'unit' => 'piece',  'price' => 55000, 'qty' => 10,  'min' => 2],
            ['name' => 'Printer',               'cat' => 1, 'unit' => 'piece',  'price' => 18000, 'qty' => 5,   'min' => 1],
            ['name' => 'A4 Paper (Ream)',       'cat' => 2, 'unit' => 'ream',   'price' => 380,   'qty' => 100, 'min' => 20],
            ['name' => 'Pen (Box)',             'cat' => 2, 'unit' => 'box',    'price' => 150,   'qty' => 50,  'min' => 10],
            ['name' => 'Marker (Set)',          'cat' => 2, 'unit' => 'set',    'price' => 120,   'qty' => 30,  'min' => 10],
            ['name' => 'Football',              'cat' => 3, 'unit' => 'piece',  'price' => 800,   'qty' => 10,  'min' => 3],
            ['name' => 'Cricket Bat',           'cat' => 3, 'unit' => 'piece',  'price' => 1500,  'qty' => 8,   'min' => 2],
            ['name' => 'Badminton Racket',      'cat' => 3, 'unit' => 'piece',  'price' => 600,   'qty' => 12,  'min' => 4],
            ['name' => 'Microscope',            'cat' => 4, 'unit' => 'piece',  'price' => 12000, 'qty' => 8,   'min' => 2],
            ['name' => 'Test Tube Set',         'cat' => 4, 'unit' => 'set',    'price' => 500,   'qty' => 20,  'min' => 5],
            ['name' => 'Broom (Jharu)',         'cat' => 5, 'unit' => 'piece',  'price' => 80,    'qty' => 20,  'min' => 5],
            ['name' => 'Floor Cleaner (Liter)', 'cat' => 5, 'unit' => 'liter',  'price' => 180,   'qty' => 30,  'min' => 10],
        ];

        foreach ($products as $i => $product) {
            DB::table('school_products')->insert([
                'item_code'     => 'ITEM-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'name'          => $product['name'],
                'category_id'   => $catIds[$product['cat']],
                'unit'          => $product['unit'],
                'price'         => $product['price'],
                'quantity'      => $product['qty'],
                'minimum_stock' => $product['min'],
                'is_active'     => true,
                'created_at'    => now(), 'updated_at' => now()
            ]);
        }

        $productIds = DB::table('school_products')->pluck('id')->toArray();

        // Stock movements
        foreach ($productIds as $productId) {
            DB::table('school_stocks')->insert([
                'product_id' => $productId,
                'quantity'   => rand(5, 50),
                'type'       => 'in',
                'note'       => 'Initial stock entry',
                'date'       => '2026-01-01',
                'created_at' => now(), 'updated_at' => now()
            ]);

            DB::table('school_stocks')->insert([
                'product_id' => $productId,
                'quantity'   => rand(1, 10),
                'type'       => 'out',
                'note'       => 'Distributed to departments',
                'date'       => '2026-03-01',
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // Suppliers
        $suppliers = [
            ['name' => 'Rahman Traders',     'company_name' => 'Rahman Trading Co.',    'phone' => '01700-111111', 'email' => 'rahman@traders.com'],
            ['name' => 'Tech Solutions BD',  'company_name' => 'Tech Solutions Ltd.',   'phone' => '01700-222222', 'email' => 'tech@solutions.bd'],
            ['name' => 'Office Mate',        'company_name' => 'Office Mate Suppliers', 'phone' => '01700-333333', 'email' => 'info@officemate.bd'],
            ['name' => 'Sports World',       'company_name' => 'Sports World BD',       'phone' => '01700-444444', 'email' => 'sports@world.bd'],
        ];

        foreach ($suppliers as $sup) {
            DB::table('school_suppliers')->insert(array_merge($sup, [
                'address' => 'Dhaka, Bangladesh', 'balance' => 0,
                'is_active' => true, 'created_at' => now(), 'updated_at' => now()
            ]));
        }

        $supplierIds = DB::table('school_suppliers')->pluck('id')->toArray();

        // Purchases
        $purchaseData = [
            ['total' => 95000, 'paid' => 95000, 'due' => 0,     'date' => '2026-01-15', 'status' => 'received', 'sup_idx' => 1],
            ['total' => 38000, 'paid' => 20000, 'due' => 18000, 'date' => '2026-02-10', 'status' => 'received', 'sup_idx' => 2],
            ['total' => 15000, 'paid' => 15000, 'due' => 0,     'date' => '2026-03-05', 'status' => 'received', 'sup_idx' => 3],
            ['total' => 22000, 'paid' => 0,     'due' => 22000, 'date' => '2026-04-20', 'status' => 'pending',  'sup_idx' => 0],
            ['total' => 8500,  'paid' => 8500,  'due' => 0,     'date' => '2026-05-12', 'status' => 'received', 'sup_idx' => 3],
        ];

        foreach ($purchaseData as $pIdx => $purchase) {
            $purchaseId = DB::table('school_purchases')->insertGetId([
                'invoice_no'    => 'PO-' . str_pad($pIdx + 1, 5, '0', STR_PAD_LEFT),
                'supplier_id'   => $supplierIds[$purchase['sup_idx']],
                'total_amount'  => $purchase['total'],
                'paid_amount'   => $purchase['paid'],
                'due_amount'    => $purchase['due'],
                'purchase_date' => $purchase['date'],
                'payment_date'  => $purchase['paid'] > 0 ? $purchase['date'] : null,
                'status'        => $purchase['status'],
                'created_at'    => now(), 'updated_at' => now()
            ]);

            // Purchase Items
            $selectedProducts = array_slice($productIds, $pIdx * 2, 3);
            foreach ($selectedProducts as $productId) {
                $unitPrice = DB::table('school_products')->find($productId)->price;
                $qty = rand(1, 5);
                DB::table('school_purchase_items')->insert([
                    'purchase_id' => $purchaseId,
                    'product_id'  => $productId,
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                    'total_price' => $unitPrice * $qty,
                    'created_at'  => now(), 'updated_at' => now()
                ]);
            }
        }

        $this->command->info('  → Inventory seeded (' . count($products) . ' products, suppliers, purchases)');
    }

    // ─── Notices & Events ─────────────────────────────────────────────────────

    private function seedNoticesEvents(): void
    {
        DB::table('school_notices')->truncate();
        DB::table('school_events')->truncate();

        $notices = [
            ['title' => 'Annual Sports Day 2026',                     'type' => 'announcement', 'for' => 'all',      'publish_date' => '2026-01-10', 'end_date' => '2026-02-28'],
            ['title' => 'First Term Exam Schedule Published',          'type' => 'notice',       'for' => 'student',  'publish_date' => '2026-03-25', 'end_date' => '2026-04-10'],
            ['title' => 'School Closed on 26th March (Independence)', 'type' => 'notice',       'for' => 'all',      'publish_date' => '2026-03-20', 'end_date' => '2026-03-27'],
            ['title' => 'New Library Books Available',                 'type' => 'announcement', 'for' => 'all',      'publish_date' => '2026-04-01', 'end_date' => null],
            ['title' => 'Guardian Meeting - Class 9 & 10',            'type' => 'notice',       'for' => 'guardian', 'publish_date' => '2026-04-15', 'end_date' => '2026-04-20'],
            ['title' => 'Fee Submission Deadline Reminder',           'type' => 'notice',       'for' => 'guardian', 'publish_date' => '2026-05-01', 'end_date' => '2026-05-10'],
            ['title' => 'Science Fair Registration Open',             'type' => 'announcement', 'for' => 'student',  'publish_date' => '2026-05-05', 'end_date' => '2026-05-20'],
            ['title' => 'Teacher Training Workshop Next Week',        'type' => 'notice',       'for' => 'teacher',  'publish_date' => '2026-05-10', 'end_date' => '2026-05-17'],
            ['title' => 'Eid Vacation Schedule 2026',                 'type' => 'announcement', 'for' => 'all',      'publish_date' => '2026-05-15', 'end_date' => '2026-06-10'],
            ['title' => 'New Admission Open for 2026 Session',        'type' => 'announcement', 'for' => 'all',      'publish_date' => '2026-06-01', 'end_date' => null],
        ];

        foreach ($notices as $notice) {
            DB::table('school_notices')->insert([
                'title'        => $notice['title'],
                'content'      => 'Dear ' . ucfirst($notice['for']) . ', please be informed that ' . strtolower($notice['title']) . '. Please contact the administration for further details.',
                'publish_date' => $notice['publish_date'],
                'end_date'     => $notice['end_date'],
                'type'         => $notice['type'],
                'for'          => $notice['for'],
                'is_active'    => true,
                'created_at'   => now(), 'updated_at' => now()
            ]);
        }

        $events = [
            ['title' => 'Annual Prize Giving Ceremony',    'start_date' => '2026-02-15', 'end_date' => '2026-02-15', 'location' => 'School Auditorium'],
            ['title' => 'International Mother Language Day','start_date' => '2026-02-21', 'end_date' => '2026-02-21', 'location' => 'School Ground'],
            ['title' => 'Independence Day Celebration',    'start_date' => '2026-03-26', 'end_date' => '2026-03-26', 'location' => 'School Ground'],
            ['title' => 'Annual Science Fair',             'start_date' => '2026-04-25', 'end_date' => '2026-04-26', 'location' => 'School Auditorium'],
            ['title' => 'Bangabandhu Birth Anniversary',   'start_date' => '2026-03-17', 'end_date' => '2026-03-17', 'location' => 'Assembly Hall'],
            ['title' => 'Annual Cultural Program',         'start_date' => '2026-05-20', 'end_date' => '2026-05-20', 'location' => 'School Auditorium'],
            ['title' => 'Annual Sports Day',               'start_date' => '2026-02-28', 'end_date' => '2026-02-28', 'location' => 'School Playground'],
            ['title' => 'Victory Day Celebration',         'start_date' => '2026-12-16', 'end_date' => '2026-12-16', 'location' => 'School Ground'],
        ];

        foreach ($events as $event) {
            DB::table('school_events')->insert([
                'title'       => $event['title'],
                'description' => 'All students, teachers, and guardians are invited to join this celebration.',
                'start_date'  => $event['start_date'],
                'end_date'    => $event['end_date'],
                'start_time'  => '09:00',
                'end_time'    => '17:00',
                'location'    => $event['location'],
                'for'         => 'all',
                'is_active'   => true,
                'created_at'  => now(), 'updated_at' => now()
            ]);
        }

        $this->command->info('  → Notices (' . count($notices) . ') and Events (' . count($events) . ') seeded');
    }

    // ─── Holidays ─────────────────────────────────────────────────────────────

    private function seedHolidays(): void
    {
        DB::table('holidays')->truncate();

        $holidays = [
            ['title' => 'International Mother Language Day', 'date' => '2026-02-21'],
            ['title' => 'National Independence Day',         'date' => '2026-03-26'],
            ['title' => 'Bangabandhu Birth Anniversary',     'date' => '2026-03-17'],
            ['title' => 'Pahela Baishakh (Bengali New Year)', 'date' => '2026-04-14'],
            ['title' => 'Eid ul-Fitr (Day 1)',               'date' => '2026-03-31'],
            ['title' => 'Eid ul-Fitr (Day 2)',               'date' => '2026-04-01'],
            ['title' => 'Eid ul-Fitr (Day 3)',               'date' => '2026-04-02'],
            ['title' => 'May Day (International Labour Day)','date' => '2026-05-01'],
            ['title' => 'Buddha Purnima',                    'date' => '2026-05-12'],
            ['title' => 'Eid ul-Adha (Day 1)',               'date' => '2026-06-07'],
            ['title' => 'Eid ul-Adha (Day 2)',               'date' => '2026-06-08'],
            ['title' => 'Eid ul-Adha (Day 3)',               'date' => '2026-06-09'],
            ['title' => 'Muharram (Ashura)',                 'date' => '2026-07-05'],
            ['title' => 'National Mourning Day',             'date' => '2026-08-15'],
            ['title' => 'National Revolution Day',           'date' => '2026-11-07'],
            ['title' => 'Victory Day',                       'date' => '2026-12-16'],
            ['title' => 'Christmas Day',                     'date' => '2026-12-25'],
            ['title' => 'Summer Vacation (Start)',           'date' => '2026-07-15'],
            ['title' => 'Summer Vacation (End)',             'date' => '2026-07-31'],
            ['title' => 'Annual Exam Preparation Break',     'date' => '2026-10-20'],
        ];

        foreach ($holidays as $holiday) {
            DB::table('holidays')->insert(array_merge($holiday, [
                'description' => 'National/School holiday. School will remain closed.',
                'is_active'   => true,
                'created_at'  => now(), 'updated_at' => now()
            ]));
        }

        $this->command->info('  → Holidays seeded (' . count($holidays) . ' days)');
    }

    // ─── Class Tests & Assignments ────────────────────────────────────────────

    private function seedClassTestsAssignments(): void
    {
        DB::table('class_tests')->truncate();
        DB::table('assignments')->truncate();

        $currentYearId = DB::table('academic_years')->where('is_current', true)->value('id');
        $classIds      = DB::table('classes')->pluck('id')->toArray();
        $teacherIds    = DB::table('teachers')->pluck('id')->toArray();

        $classTestsData = [
            ['title' => 'Bangla 1st Class Test',        'class_idx' => 0, 'sub_code' => 'BNG', 'date' => '2026-01-20', 'total' => 20, 'pass' => 8,  'duration' => '30 min',  'status' => 'completed'],
            ['title' => 'English Grammar Test',         'class_idx' => 0, 'sub_code' => 'ENG', 'date' => '2026-01-22', 'total' => 25, 'pass' => 10, 'duration' => '40 min',  'status' => 'completed'],
            ['title' => 'Mathematics Chapters 1-3',     'class_idx' => 1, 'sub_code' => 'MTH', 'date' => '2026-02-05', 'total' => 30, 'pass' => 12, 'duration' => '45 min',  'status' => 'completed'],
            ['title' => 'Science Unit Test',            'class_idx' => 1, 'sub_code' => 'SCI', 'date' => '2026-02-12', 'total' => 20, 'pass' => 8,  'duration' => '30 min',  'status' => 'completed'],
            ['title' => 'ICT Practical Test',           'class_idx' => 2, 'sub_code' => 'ICT', 'date' => '2026-02-20', 'total' => 50, 'pass' => 20, 'duration' => '1 hour',  'status' => 'completed'],
            ['title' => 'Physics Chapter Test',         'class_idx' => 3, 'sub_code' => 'PHY', 'date' => '2026-03-08', 'total' => 30, 'pass' => 12, 'duration' => '45 min',  'status' => 'completed'],
            ['title' => 'Chemistry Periodic Table Test','class_idx' => 3, 'sub_code' => 'CHM', 'date' => '2026-03-15', 'total' => 25, 'pass' => 10, 'duration' => '30 min',  'status' => 'completed'],
            ['title' => 'Biology Cell Structure Test',  'class_idx' => 4, 'sub_code' => 'BIO', 'date' => '2026-03-22', 'total' => 20, 'pass' => 8,  'duration' => '30 min',  'status' => 'completed'],
            ['title' => 'Higher Math Test 1',           'class_idx' => 4, 'sub_code' => 'HMT', 'date' => '2026-04-05', 'total' => 40, 'pass' => 16, 'duration' => '1 hour',  'status' => 'completed'],
            ['title' => 'English Literature Test',      'class_idx' => 5, 'sub_code' => 'ENG', 'date' => '2026-04-12', 'total' => 30, 'pass' => 12, 'duration' => '45 min',  'status' => 'completed'],
            ['title' => 'Bangla 2nd Class Test',        'class_idx' => 0, 'sub_code' => 'BNG', 'date' => '2026-04-18', 'total' => 20, 'pass' => 8,  'duration' => '30 min',  'status' => 'completed'],
            ['title' => 'Mathematics Test 2',           'class_idx' => 2, 'sub_code' => 'MTH', 'date' => '2026-05-02', 'total' => 30, 'pass' => 12, 'duration' => '45 min',  'status' => 'ongoing'],
            ['title' => 'Social Science Chapter 5-7',   'class_idx' => 1, 'sub_code' => 'SSC', 'date' => '2026-05-08', 'total' => 25, 'pass' => 10, 'duration' => '40 min',  'status' => 'scheduled'],
            ['title' => 'Physics Optics Test',          'class_idx' => 3, 'sub_code' => 'PHY', 'date' => '2026-06-10', 'total' => 30, 'pass' => 12, 'duration' => '45 min',  'status' => 'scheduled'],
            ['title' => 'Accounting Test 1',            'class_idx' => 5, 'sub_code' => 'ACC', 'date' => '2026-06-15', 'total' => 50, 'pass' => 20, 'duration' => '1 hour',  'status' => 'scheduled'],
        ];

        foreach ($classTestsData as $ct) {
            $classId   = $classIds[$ct['class_idx']];
            $subject   = DB::table('subjects')->where('class_id', $classId)->where('code', $ct['sub_code'])->first();
            if (!$subject) continue;

            DB::table('class_tests')->insert([
                'title'           => $ct['title'],
                'class_id'        => $classId,
                'subject_id'      => $subject->id,
                'teacher_id'      => $teacherIds[rand(0, count($teacherIds) - 1)],
                'academic_year_id'=> $currentYearId,
                'test_date'       => $ct['date'],
                'total_marks'     => $ct['total'],
                'pass_marks'      => $ct['pass'],
                'duration'        => $ct['duration'],
                'status'          => $ct['status'],
                'is_active'       => true,
                'created_at'      => now(), 'updated_at' => now()
            ]);
        }

        $assignmentsData = [
            ['title' => 'Write a Bangla Essay on Liberation War',         'class_idx' => 0, 'sub_code' => 'BNG', 'assigned' => '2026-01-15', 'due' => '2026-01-25', 'marks' => 20, 'status' => 'closed'],
            ['title' => 'English Paragraph Writing Exercise',             'class_idx' => 0, 'sub_code' => 'ENG', 'assigned' => '2026-01-18', 'due' => '2026-01-28', 'marks' => 15, 'status' => 'closed'],
            ['title' => 'Mathematics Problem Set Chapter 2',              'class_idx' => 1, 'sub_code' => 'MTH', 'assigned' => '2026-02-01', 'due' => '2026-02-10', 'marks' => 25, 'status' => 'closed'],
            ['title' => 'Science: Draw and Label Plant Cell',             'class_idx' => 1, 'sub_code' => 'SCI', 'assigned' => '2026-02-08', 'due' => '2026-02-15', 'marks' => 15, 'status' => 'closed'],
            ['title' => 'ICT: Create a Simple Presentation on Climate',   'class_idx' => 2, 'sub_code' => 'ICT', 'assigned' => '2026-02-15', 'due' => '2026-02-25', 'marks' => 30, 'status' => 'closed'],
            ['title' => 'Physics: Numerical on Newton\'s Laws',           'class_idx' => 3, 'sub_code' => 'PHY', 'assigned' => '2026-03-01', 'due' => '2026-03-10', 'marks' => 20, 'status' => 'closed'],
            ['title' => 'Chemistry: Lab Report on Acid-Base Reaction',    'class_idx' => 3, 'sub_code' => 'CHM', 'assigned' => '2026-03-05', 'due' => '2026-03-15', 'marks' => 25, 'status' => 'closed'],
            ['title' => 'Biology: Research on Human Immune System',       'class_idx' => 4, 'sub_code' => 'BIO', 'assigned' => '2026-03-15', 'due' => '2026-03-25', 'marks' => 20, 'status' => 'active'],
            ['title' => 'Higher Math: Matrix Calculation Problems',        'class_idx' => 4, 'sub_code' => 'HMT', 'assigned' => '2026-04-01', 'due' => '2026-04-12', 'marks' => 30, 'status' => 'active'],
            ['title' => 'English: Book Review - "The Old Man and the Sea"','class_idx' => 5, 'sub_code' => 'ENG', 'assigned' => '2026-04-05', 'due' => '2026-04-20', 'marks' => 25, 'status' => 'active'],
            ['title' => 'Bangla: Kobita Bisleshan (Poem Analysis)',       'class_idx' => 0, 'sub_code' => 'BNG', 'assigned' => '2026-04-10', 'due' => '2026-04-22', 'marks' => 20, 'status' => 'active'],
            ['title' => 'Social Science: Map Work - Bangladesh Districts', 'class_idx' => 1, 'sub_code' => 'SSC', 'assigned' => '2026-04-15', 'due' => '2026-04-30', 'marks' => 15, 'status' => 'active'],
            ['title' => 'Mathematics: Statistics Data Collection Project', 'class_idx' => 2, 'sub_code' => 'MTH', 'assigned' => '2026-05-01', 'due' => '2026-05-15', 'marks' => 35, 'status' => 'active'],
            ['title' => 'Accounting: Prepare Trial Balance from Data',    'class_idx' => 5, 'sub_code' => 'ACC', 'assigned' => '2026-05-05', 'due' => '2026-05-20', 'marks' => 40, 'status' => 'active'],
            ['title' => 'ICT: Design a School Website Mockup',            'class_idx' => 2, 'sub_code' => 'ICT', 'assigned' => '2026-05-10', 'due' => '2026-06-01', 'marks' => 50, 'status' => 'active'],
        ];

        foreach ($assignmentsData as $asgn) {
            $classId = $classIds[$asgn['class_idx']];
            $subject = DB::table('subjects')->where('class_id', $classId)->where('code', $asgn['sub_code'])->first();
            if (!$subject) continue;

            DB::table('assignments')->insert([
                'title'           => $asgn['title'],
                'description'     => 'Complete the assignment as instructed in class. Submit neatly written in the assignment notebook.',
                'class_id'        => $classId,
                'subject_id'      => $subject->id,
                'teacher_id'      => $teacherIds[rand(0, count($teacherIds) - 1)],
                'academic_year_id'=> $currentYearId,
                'assigned_date'   => $asgn['assigned'],
                'due_date'        => $asgn['due'],
                'total_marks'     => $asgn['marks'],
                'status'          => $asgn['status'],
                'is_active'       => true,
                'created_at'      => now(), 'updated_at' => now()
            ]);
        }

        $this->command->info('  → Class Tests (' . count($classTestsData) . ') and Assignments (' . count($assignmentsData) . ') seeded');
    }
}
