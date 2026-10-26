<?php

use Illuminate\Support\Facades\Route;
use ME\Http\Middleware\LocaleMiddleware;
use ME\SchoolManagement\Http\Controllers\DashboardController;
use ME\SchoolManagement\Http\Controllers\AssignmentController;
use ME\SchoolManagement\Http\Controllers\AcademicController;
use ME\SchoolManagement\Http\Controllers\StudentController;
use ME\SchoolManagement\Http\Controllers\TeacherController;
use ME\SchoolManagement\Http\Controllers\FeeController;
use ME\SchoolManagement\Http\Controllers\ExamController;
use ME\SchoolManagement\Http\Controllers\ExpenseController;
use ME\SchoolManagement\Http\Controllers\NoticeController;
use ME\SchoolManagement\Http\Controllers\LibraryController;
use ME\SchoolManagement\Http\Controllers\TransportController;
use ME\SchoolManagement\Http\Controllers\TourController;
use ME\SchoolManagement\Http\Controllers\StaffController;
use ME\SchoolManagement\Http\Controllers\CommitteeController;
use ME\SchoolManagement\Http\Controllers\ClassTextController;
use ME\SchoolManagement\Http\Controllers\UserController;
use ME\SchoolManagement\Http\Controllers\StudentPortalController;
use ME\SchoolManagement\Http\Controllers\TrashController;
use ME\SchoolManagement\Http\Middleware\SchoolPermission;
use ME\SchoolManagement\Http\Middleware\TranslateFlash;


// Authentication, logout and profile management (login, logout, profile.edit,
// profile.update, profile.destroy, password.update) are provided by the
// metheme host theme (ME\Http\Controllers\Auth\AuthController /
// ME\Http\Controllers\ProfileController) and are intentionally not
// duplicated here.

// SchoolPermission maps each route name to its permission in
// Config/permissions.php (see the middleware for the table).
// LocaleMiddleware applies the language chosen in the theme; activityLog
// records each request in the theme's Activity Log.
Route::middleware(['web', 'auth', LocaleMiddleware::class, 'activityLog', SchoolPermission::class, TranslateFlash::class])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('school/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Academic
    Route::prefix('academic')->group(function () {
        Route::get('/years', [AcademicController::class, 'academicYearIndex'])->name('academic.years');
        Route::post('/years', [AcademicController::class, 'academicYearStore'])->name('academic.years.store');
        Route::delete('/years/{id}', [AcademicController::class, 'academicYearDestroy'])->name('academic.years.destroy');
        Route::put('/years/{id}', [AcademicController::class, 'academicYearUpdate'])->name('academic.years.update');

        Route::get('/classes', [AcademicController::class, 'classIndex'])->name('academic.classes');
        Route::post('/classes', [AcademicController::class, 'classStore'])->name('academic.classes.store');
        Route::put('/classes/{id}', [AcademicController::class, 'classUpdate'])->name('academic.classes.update');
        Route::delete('/classes/{id}', [AcademicController::class, 'classDestroy'])->name('academic.classes.destroy');
        Route::get('/classes/{id}/routine', [AcademicController::class, 'classRoutine'])->name('academic.classes.routine');
        Route::get('/classes/{id}/routine/print', [AcademicController::class, 'classRoutinePrint'])->name('academic.classes.routine.print');

        Route::get('/sections', [AcademicController::class, 'sectionIndex'])->name('academic.sections');
        Route::post('/sections', [AcademicController::class, 'sectionStore'])->name('academic.sections.store');
        Route::put('/sections/{id}', [AcademicController::class, 'sectionUpdate'])->name('academic.sections.update');
        Route::delete('/sections/{id}', [AcademicController::class, 'sectionDestroy'])->name('academic.sections.destroy');

        Route::get('/subjects', [AcademicController::class, 'subjectIndex'])->name('academic.subjects');
        Route::post('/subjects', [AcademicController::class, 'subjectStore'])->name('academic.subjects.store');
        Route::put('/subjects/{id}', [AcademicController::class, 'subjectUpdate'])->name('academic.subjects.update');
        Route::delete('/subjects/{id}', [AcademicController::class, 'subjectDestroy'])->name('academic.subjects.destroy');

        Route::get('/routines', [AcademicController::class, 'routineIndex'])->name('academic.routines');
        Route::get('/routines/print', [AcademicController::class, 'routineIndexPrint'])->name('academic.routines.print');
        Route::post('/routines', [AcademicController::class, 'routineStore'])->name('academic.routines.store');
        Route::put('/routines/{id}', [AcademicController::class, 'routineUpdate'])->name('academic.routines.update');
        Route::delete('/routines/{id}', [AcademicController::class, 'routineDestroy'])->name('academic.routines.destroy');

        Route::get('/routine-titles', [AcademicController::class, 'routineTitleIndex'])->name('academic.routine-titles');
        Route::post('/routine-titles', [AcademicController::class, 'routineTitleStore'])->name('academic.routine-titles.store');
        Route::put('/routine-titles/{id}', [AcademicController::class, 'routineTitleUpdate'])->name('academic.routine-titles.update');
        Route::delete('/routine-titles/{id}', [AcademicController::class, 'routineTitleDestroy'])->name('academic.routine-titles.destroy');

        Route::get('/rooms', [AcademicController::class, 'roomIndex'])->name('academic.rooms');
        Route::post('/rooms', [AcademicController::class, 'roomStore'])->name('academic.rooms.store');
        Route::put('/rooms/{id}', [AcademicController::class, 'roomUpdate'])->name('academic.rooms.update');
        Route::delete('/rooms/{id}', [AcademicController::class, 'roomDestroy'])->name('academic.rooms.destroy');
        Route::get('/rooms/{id}/schedule', [AcademicController::class, 'roomSchedule'])->name('academic.rooms.schedule');
        Route::get('/rooms/{id}/schedule/print', [AcademicController::class, 'roomSchedulePrint'])->name('academic.rooms.schedule.print');

        Route::get('/syllabi', [AcademicController::class, 'syllabusIndex'])->name('academic.syllabi');
        Route::post('/syllabi', [AcademicController::class, 'syllabusStore'])->name('academic.syllabi.store');
        Route::put('/syllabi/{id}', [AcademicController::class, 'syllabusUpdate'])->name('academic.syllabi.update');
        Route::delete('/syllabi/{id}', [AcademicController::class, 'syllabusDestroy'])->name('academic.syllabi.destroy');
        Route::get('/syllabi/{id}/download', [AcademicController::class, 'syllabusDownload'])->name('academic.syllabi.download');

        Route::get('/tours', [TourController::class, 'index'])->name('academic.tours');
        Route::post('/tours', [TourController::class, 'store'])->name('academic.tours.store');
        Route::put('/tours/{id}', [TourController::class, 'update'])->name('academic.tours.update');
        Route::delete('/tours/{id}', [TourController::class, 'destroy'])->name('academic.tours.destroy');
        Route::get('/tours/{id}', [TourController::class, 'show'])->name('academic.tours.show');

        Route::post('/tours/{id}/registrations', [TourController::class, 'storeRegistration'])->name('academic.tours.registrations.store');
        Route::patch('/tours/{id}/registrations/{regId}', [TourController::class, 'updateRegistration'])->name('academic.tours.registrations.update');
        Route::delete('/tours/{id}/registrations/{regId}', [TourController::class, 'destroyRegistration'])->name('academic.tours.registrations.destroy');

        Route::post('/tours/{id}/expenses', [TourController::class, 'storeExpense'])->name('academic.tours.expenses.store');
        Route::delete('/tours/{id}/expenses/{expId}', [TourController::class, 'destroyExpense'])->name('academic.tours.expenses.destroy');

        Route::get('/tours/{id}/badge/{studentId}', [TourController::class, 'badge'])->name('academic.tours.badge');
        Route::get('/tours/{id}/badges', [TourController::class, 'allBadges'])->name('academic.tours.badges');

        Route::get('/subject-groups', [AcademicController::class, 'subjectGroupIndex'])->name('academic.subject-groups');
        Route::post('/subject-groups', [AcademicController::class, 'subjectGroupStore'])->name('academic.subject-groups.store');
        Route::put('/subject-groups/{id}', [AcademicController::class, 'subjectGroupUpdate'])->name('academic.subject-groups.update');
        Route::delete('/subject-groups/{id}', [AcademicController::class, 'subjectGroupDestroy'])->name('academic.subject-groups.destroy');

        Route::get('/class-tests', [ClassTextController::class, 'index'])->name('academic.class-tests');
        Route::post('/class-tests', [ClassTextController::class, 'store'])->name('academic.class-tests.store');
        Route::put('/class-tests/{id}', [ClassTextController::class, 'update'])->name('academic.class-tests.update');
        Route::delete('/class-tests/{id}', [ClassTextController::class, 'destroy'])->name('academic.class-tests.destroy');
        Route::get('/class-tests/{id}/marks', [ClassTextController::class, 'marksIndex'])->name('academic.class-tests.marks');
        Route::post('/class-tests/{id}/marks', [ClassTextController::class, 'marksStore'])->name('academic.class-tests.marks.store');

        Route::get('/class-texts', [AcademicController::class, 'classTextIndex'])->name('academic.class-texts');
        Route::post('/class-texts', [AcademicController::class, 'classTextStore'])->name('academic.class-texts.store');
        Route::put('/class-texts/{id}', [AcademicController::class, 'classTextUpdate'])->name('academic.class-texts.update');
        Route::delete('/class-texts/{id}', [AcademicController::class, 'classTextDestroy'])->name('academic.class-texts.destroy');

        Route::get('/assignments', [AssignmentController::class, 'index'])->name('academic.assignments');
        Route::post('/assignments', [AssignmentController::class, 'store'])->name('academic.assignments.store');
        Route::put('/assignments/{id}', [AssignmentController::class, 'update'])->name('academic.assignments.update');
        Route::delete('/assignments/{id}', [AssignmentController::class, 'destroy'])->name('academic.assignments.destroy');
        Route::get('/assignments/{id}/submissions', [AssignmentController::class, 'submissionsIndex'])->name('academic.assignments.submissions');
        Route::post('/assignments/{id}/submissions', [AssignmentController::class, 'submissionsStore'])->name('academic.assignments.submissions.store');
    });

    Route::get('/sections/{class_id}', [AcademicController::class, 'getSections'])->name('academic.sections.by-class');
    Route::get('/subjects/class/{class_id}', [AcademicController::class, 'getSubjects'])->name('academic.subjects.by-class');
    Route::get('/optional-subjects', [AcademicController::class, 'getOptionalSubjects'])->name('academic.optional-subjects');
    Route::get('/routine', [AcademicController::class, 'getRoutine'])->name('academic.routine');
    Route::get('/rooms-list', [AcademicController::class, 'getRooms'])->name('academic.rooms.list');

    // Students
    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('students.index');
        Route::post('/', [StudentController::class, 'store'])->name('students.store');

        // Specific named paths BEFORE the /{id} wildcard
        Route::get('/admission', [StudentController::class, 'admission'])->name('students.admission');
        Route::get('/transfer', [StudentController::class, 'transferIndex'])->name('students.transfer');
        Route::post('/transfer', [StudentController::class, 'transferStore'])->name('students.transfer.store');
        Route::post('/transfer/{id}/approve', [StudentController::class, 'transferApprove'])->name('students.transfer.approve');
        Route::post('/transfer/{id}/reject', [StudentController::class, 'transferReject'])->name('students.transfer.reject');
        Route::get('/idcard', [StudentController::class, 'idCardIndex'])->name('students.idcard');
        Route::get('/documents', [StudentController::class, 'documentsIndex'])->name('students.documents');

        Route::get('/attendance', [StudentController::class, 'attendanceIndex'])->name('students.attendance');
        Route::get('/attendance/print', [StudentController::class, 'attendancePrint'])->name('students.attendance.print');
        Route::post('/attendance', [StudentController::class, 'attendanceStore'])->name('students.attendance.store');
        Route::post('/attendance/cell', [StudentController::class, 'attendanceCellUpdate'])->name('students.attendance.cell');

        Route::get('/promotion', [StudentController::class, 'promotionIndex'])->name('students.promotion');
        Route::get('/promotion/students', [StudentController::class, 'getPromotionStudents'])->name('students.promotion.students');
        Route::post('/promotion', [StudentController::class, 'promotionStore'])->name('students.promotion.store');

        Route::get('/leaves', [StudentController::class, 'leaveIndex'])->name('students.leaves');
        Route::post('/leaves', [StudentController::class, 'leaveStore'])->name('students.leaves.store');
        Route::post('/leaves/{id}/approve', [StudentController::class, 'leaveApprove'])->name('students.leaves.approve');
        Route::post('/leaves/{id}/reject', [StudentController::class, 'leaveReject'])->name('students.leaves.reject');

        Route::get('/early-exits', [StudentController::class, 'earlyExitIndex'])->name('students.early-exits');
        Route::post('/early-exits', [StudentController::class, 'earlyExitStore'])->name('students.early-exits.store');
        Route::post('/early-exits/{id}/approve', [StudentController::class, 'earlyExitApprove'])->name('students.early-exits.approve');
        Route::post('/early-exits/{id}/reject', [StudentController::class, 'earlyExitReject'])->name('students.early-exits.reject');
        Route::delete('/early-exits/{id}', [StudentController::class, 'earlyExitDestroy'])->name('students.early-exits.destroy');

        Route::get('/idcard/{id}', [StudentController::class, 'idCard'])->name('students.idcard.print');

        Route::get('/{id}', [StudentController::class, 'show'])->name('students.show');
        Route::put('/{id}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/{id}', [StudentController::class, 'destroy'])->name('students.destroy');

        // Documents
        Route::post('/{studentId}/documents', [StudentController::class, 'documentStore'])->name('students.documents.store');
        Route::patch('/{studentId}/documents/{docId}/verify', [StudentController::class, 'documentVerify'])->name('students.documents.verify');
        Route::delete('/{studentId}/documents/{docId}', [StudentController::class, 'documentDestroy'])->name('students.documents.destroy');

        // Portal PIN
        Route::post('/{id}/portal-pin', [StudentController::class, 'setPortalPin'])->name('students.portal-pin');
    });
    Route::get('/student/{id}', [StudentController::class, 'getStudent'])->name('students.get');
    Route::get('/students-search', [StudentController::class, 'search'])->name('students.search');

    // Teachers
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index'])->name('teachers.index');
        Route::post('/', [TeacherController::class, 'store'])->name('teachers.store');

        Route::get('/attendance', [TeacherController::class, 'attendanceIndex'])->name('teachers.attendance');
        Route::post('/attendance', [TeacherController::class, 'attendanceStore'])->name('teachers.attendance.store');

        Route::get('/leaves', [TeacherController::class, 'leaveIndex'])->name('teachers.leaves');
        Route::post('/leaves', [TeacherController::class, 'leaveStore'])->name('teachers.leaves.store');
        Route::put('/leaves/{id}', [TeacherController::class, 'leaveUpdate'])->name('teachers.leaves.update');
        Route::post('/leaves/{id}/approve', [TeacherController::class, 'leaveApprove'])->name('teachers.leaves.approve');
        Route::post('/leaves/{id}/reject', [TeacherController::class, 'leaveReject'])->name('teachers.leaves.reject');

        Route::get('/subjects', [TeacherController::class, 'subjectIndex'])->name('teachers.subjects');
        Route::post('/subjects', [TeacherController::class, 'subjectStore'])->name('teachers.subjects.store');
        Route::delete('/subjects/{id}', [TeacherController::class, 'subjectDestroy'])->name('teachers.subjects.destroy');

        Route::get('/classes', [TeacherController::class, 'classIndex'])->name('teachers.classes');
        Route::post('/classes', [TeacherController::class, 'classStore'])->name('teachers.classes.store');
        Route::delete('/classes/{id}', [TeacherController::class, 'classDestroy'])->name('teachers.classes.destroy');

        Route::get('/salary', [TeacherController::class, 'salaryIndex'])->name('teachers.salary');
        Route::post('/salary', [TeacherController::class, 'salaryStore'])->name('teachers.salary.store');

        Route::put('/{id}/link-user', [UserController::class, 'teacherLinkUser'])->name('teachers.link-user');
        Route::delete('/{id}/link-user', [UserController::class, 'teacherUnlinkUser'])->name('teachers.unlink-user');

        Route::get('/{id}', [TeacherController::class, 'show'])->name('teachers.show');
        Route::put('/{id}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    });

    Route::post('/leave-types', [TeacherController::class, 'leaveTypeStore'])->name('teachers.leave-types.store');

    // Teacher Portal (for a logged-in teacher to manage their own classes)
    Route::prefix('my')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'myDashboard'])->name('my.dashboard');
        Route::get('/classes', [TeacherController::class, 'myClasses'])->name('my.classes');
        Route::get('/attendance/{classId}', [TeacherController::class, 'myAttendance'])->name('my.attendance');
        Route::post('/attendance/{classId}', [TeacherController::class, 'myAttendanceStore'])->name('my.attendance.store');

        Route::get('/schedule', [TeacherController::class, 'mySchedule'])->name('my.schedule');

        Route::get('/class-tests', [TeacherController::class, 'myClassTestIndex'])->name('my.class-tests');
        Route::post('/class-tests', [TeacherController::class, 'myClassTestStore'])->name('my.class-tests.store');
        Route::put('/class-tests/{id}', [TeacherController::class, 'myClassTestUpdate'])->name('my.class-tests.update');
        Route::delete('/class-tests/{id}', [TeacherController::class, 'myClassTestDestroy'])->name('my.class-tests.destroy');
        Route::get('/class-tests/{id}/marks', [TeacherController::class, 'myClassTestMarksIndex'])->name('my.class-tests.marks');
        Route::post('/class-tests/{id}/marks', [TeacherController::class, 'myClassTestMarksStore'])->name('my.class-tests.marks.store');

        Route::get('/assignments', [TeacherController::class, 'myAssignmentIndex'])->name('my.assignments');
        Route::post('/assignments', [TeacherController::class, 'myAssignmentStore'])->name('my.assignments.store');
        Route::put('/assignments/{id}', [TeacherController::class, 'myAssignmentUpdate'])->name('my.assignments.update');
        Route::delete('/assignments/{id}', [TeacherController::class, 'myAssignmentDestroy'])->name('my.assignments.destroy');
        Route::get('/assignments/{id}/submissions', [TeacherController::class, 'myAssignmentSubmissionsIndex'])->name('my.assignments.submissions');
        Route::post('/assignments/{id}/submissions', [TeacherController::class, 'myAssignmentSubmissionsStore'])->name('my.assignments.submissions.store');

        Route::get('/notices', [TeacherController::class, 'myNotices'])->name('my.notices');
    });

    // Fees
    Route::prefix('fees')->group(function () {
        Route::get('/types', [FeeController::class, 'feeTypeIndex'])->name('fees.types');
        Route::post('/types', [FeeController::class, 'feeTypeStore'])->name('fees.types.store');
        Route::put('/types/{id}', [FeeController::class, 'feeTypeUpdate'])->name('fees.types.update');
        Route::delete('/types/{id}', [FeeController::class, 'feeTypeDestroy'])->name('fees.types.destroy');

        Route::get('/structure', [FeeController::class, 'feeStructureIndex'])->name('fees.structure');
        Route::post('/structure', [FeeController::class, 'feeStructureStore'])->name('fees.structure.store');
        Route::delete('/structure/{id}', [FeeController::class, 'feeStructureDestroy'])->name('fees.structure.destroy');

        Route::get('/collection', [FeeController::class, 'collectionIndex'])->name('fees.collection');
        Route::get('/collection/create', [FeeController::class, 'createCollection'])->name('fees.collection.create');
        Route::post('/collection', [FeeController::class, 'storeCollection'])->name('fees.collection.store');
        Route::post('/collection/{id}/pay', [FeeController::class, 'payFee'])->name('fees.collection.pay');
        Route::post('/collection/reminders', [FeeController::class, 'sendReminders'])->name('fees.reminders');

        Route::post('/generate-monthly', [FeeController::class, 'generateMonthlyFee'])->name('fees.generate-monthly');

        Route::get('/discounts', [FeeController::class, 'discountIndex'])->name('fees.discounts');
        Route::post('/discounts', [FeeController::class, 'discountStore'])->name('fees.discounts.store');
        Route::delete('/discounts/{id}', [FeeController::class, 'discountDestroy'])->name('fees.discounts.destroy');

        Route::get('/report', [FeeController::class, 'collectionReport'])->name('fees.report');
        Route::get('/report/print', [FeeController::class, 'collectionReportPrint'])->name('fees.report.print');
    });

    // Exams
    Route::prefix('exams')->group(function () {
        Route::get('/types', [ExamController::class, 'examTypeIndex'])->name('exams.types');
        Route::post('/types', [ExamController::class, 'examTypeStore'])->name('exams.types.store');
        Route::put('/types/{id}', [ExamController::class, 'examTypeUpdate'])->name('exams.types.update');
        Route::delete('/types/{id}', [ExamController::class, 'examTypeDestroy'])->name('exams.types.destroy');

        Route::get('/', [ExamController::class, 'examIndex'])->name('exams.index');
        Route::post('/', [ExamController::class, 'examStore'])->name('exams.store');
        Route::put('/{id}', [ExamController::class, 'examUpdate'])->name('exams.update');
        Route::delete('/{id}', [ExamController::class, 'examDestroy'])->name('exams.destroy');

        Route::get('/schedule', [ExamController::class, 'scheduleIndex'])->name('exams.schedule');
        Route::post('/schedule', [ExamController::class, 'scheduleStore'])->name('exams.schedule.store');
        Route::put('/schedule/{id}', [ExamController::class, 'scheduleUpdate'])->name('exams.schedule.update');
        Route::delete('/schedule/{id}', [ExamController::class, 'scheduleDestroy'])->name('exams.schedule.destroy');

        Route::get('/grades', [ExamController::class, 'gradeIndex'])->name('exams.grades');
        Route::post('/grades', [ExamController::class, 'gradeStore'])->name('exams.grades.store');
        Route::put('/grades/{id}', [ExamController::class, 'gradeUpdate'])->name('exams.grades.update');
        Route::delete('/grades/{id}', [ExamController::class, 'gradeDestroy'])->name('exams.grades.destroy');

        Route::get('/marks', [ExamController::class, 'marksIndex'])->name('exams.marks');
        Route::get('/marks/print', [ExamController::class, 'marksPrint'])->name('exams.marks.print');
        Route::post('/marks', [ExamController::class, 'marksStore'])->name('exams.marks.store');

        Route::get('/results', [ExamController::class, 'resultIndex'])->name('exams.results');
        Route::post('/results/generate', [ExamController::class, 'generateResult'])->name('exams.results.generate');
        Route::post('/results/publish', [ExamController::class, 'publishResult'])->name('exams.results.publish');

        Route::get('/marksheet/{student_id}/{exam_id}', [ExamController::class, 'marksheet'])->name('exams.marksheet.view');
    });

    Route::get('/subjects-list/{class_id}', [ExamController::class, 'getSubjectsByClass'])->name('exams.subjects-list');

    // Expenses
    Route::prefix('expenses')->group(function () {
        Route::get('/categories', [ExpenseController::class, 'categoryIndex'])->name('expenses.categories');
        Route::post('/categories', [ExpenseController::class, 'categoryStore'])->name('expenses.categories.store');
        Route::delete('/categories/{id}', [ExpenseController::class, 'categoryDestroy'])->name('expenses.categories.destroy');

        Route::get('/', [ExpenseController::class, 'expenseIndex'])->name('expenses.index');
        Route::post('/', [ExpenseController::class, 'expenseStore'])->name('expenses.store');
        Route::put('/{id}', [ExpenseController::class, 'expenseUpdate'])->name('expenses.update');
        Route::delete('/{id}', [ExpenseController::class, 'expenseDestroy'])->name('expenses.destroy');

        Route::get('/income-categories', [ExpenseController::class, 'incomeCategoryIndex'])->name('expenses.income-categories');
        Route::post('/income-categories', [ExpenseController::class, 'incomeCategoryStore'])->name('expenses.income-categories.store');
        Route::put('/income-categories/{id}', [ExpenseController::class, 'incomeCategoryUpdate'])->name('expenses.income-categories.update');
        Route::delete('/income-categories/{id}', [ExpenseController::class, 'incomeCategoryDestroy'])->name('expenses.income-categories.destroy');

        Route::get('/income', [ExpenseController::class, 'incomeIndex'])->name('expenses.income');
        Route::post('/income', [ExpenseController::class, 'incomeStore'])->name('expenses.income.store');
        Route::put('/income/{id}', [ExpenseController::class, 'incomeUpdate'])->name('expenses.income.update');
        Route::delete('/income/{id}', [ExpenseController::class, 'incomeDestroy'])->name('expenses.income.destroy');

        Route::get('/report', [ExpenseController::class, 'report'])->name('expenses.report');
        Route::get('/report/print', [ExpenseController::class, 'reportPrint'])->name('expenses.report.print');
    });

    // Notices & Events
    Route::prefix('notices')->group(function () {
        Route::get('/', [NoticeController::class, 'noticeIndex'])->name('notices.index');
        Route::post('/', [NoticeController::class, 'noticeStore'])->name('notices.store');
        Route::put('/{id}', [NoticeController::class, 'noticeUpdate'])->name('notices.update');
        Route::delete('/{id}', [NoticeController::class, 'noticeDestroy'])->name('notices.destroy');
    });

    Route::prefix('events')->group(function () {
        Route::get('/', [NoticeController::class, 'eventIndex'])->name('events.index');
        Route::post('/', [NoticeController::class, 'eventStore'])->name('events.store');
        Route::put('/{id}', [NoticeController::class, 'eventUpdate'])->name('events.update');
        Route::delete('/{id}', [NoticeController::class, 'eventDestroy'])->name('events.destroy');
    });

    Route::prefix('holidays')->group(function () {
        Route::get('/', [NoticeController::class, 'holidayIndex'])->name('holidays.index');
        Route::post('/', [NoticeController::class, 'holidayStore'])->name('holidays.store');
        Route::delete('/{id}', [NoticeController::class, 'holidayDestroy'])->name('holidays.destroy');
    });

    // Library
    Route::prefix('library')->group(function () {
        Route::get('/books',             [LibraryController::class, 'bookIndex'])->name('library.books');
        Route::post('/books',            [LibraryController::class, 'bookStore'])->name('library.books.store');
        Route::put('/books/{id}',        [LibraryController::class, 'bookUpdate'])->name('library.books.update');
        Route::delete('/books/{id}',     [LibraryController::class, 'bookDestroy'])->name('library.books.destroy');
        Route::get('/books/{id}/copies', [LibraryController::class, 'bookCopies'])->name('library.books.copies');

        Route::post('/purchase',         [LibraryController::class, 'purchaseStore'])->name('library.purchase.store');

        Route::get('/issues',            [LibraryController::class, 'issueIndex'])->name('library.issues');
        Route::post('/issues',           [LibraryController::class, 'issueStore'])->name('library.issues.store');
        Route::post('/issues/{id}/return',     [LibraryController::class, 'returnBook'])->name('library.issues.return');
        Route::post('/issues/{id}/pay-fine',   [LibraryController::class, 'payFine'])->name('library.issues.pay-fine');
        Route::post('/issues/{id}/waive-fine', [LibraryController::class, 'waiveFine'])->name('library.issues.waive-fine');

        Route::get('/availability',      [LibraryController::class, 'availabilityChart'])->name('library.availability');
        Route::post('/settings',         [LibraryController::class, 'settingsUpdate'])->name('library.settings.update');

        Route::get('/members',           [LibraryController::class, 'memberIndex'])->name('library.members');
        Route::post('/members',          [LibraryController::class, 'memberStore'])->name('library.members.store');
        Route::delete('/members/{id}',   [LibraryController::class, 'memberDestroy'])->name('library.members.destroy');
    });

    Route::get('/available-books',       [LibraryController::class, 'getAvailableBooks'])->name('library.availableBooks');
    Route::get('/book-copies/{bookId}',  [LibraryController::class, 'getBookCopies'])->name('library.book-copies');

    // Transport
    Route::prefix('transport')->group(function () {
        Route::get('/routes', [TransportController::class, 'routeIndex'])->name('transport.routes');
        Route::post('/routes', [TransportController::class, 'routeStore'])->name('transport.routes.store');
        Route::put('/routes/{id}', [TransportController::class, 'routeUpdate'])->name('transport.routes.update');
        Route::delete('/routes/{id}', [TransportController::class, 'routeDestroy'])->name('transport.routes.destroy');

        Route::get('/vehicles', [TransportController::class, 'vehicleIndex'])->name('transport.vehicles');
        Route::post('/vehicles', [TransportController::class, 'vehicleStore'])->name('transport.vehicles.store');
        Route::put('/vehicles/{id}', [TransportController::class, 'vehicleUpdate'])->name('transport.vehicles.update');
        Route::delete('/vehicles/{id}', [TransportController::class, 'vehicleDestroy'])->name('transport.vehicles.destroy');

        Route::post('/stops', [TransportController::class, 'stopStore'])->name('transport.stops.store');
        Route::delete('/stops/{id}', [TransportController::class, 'stopDestroy'])->name('transport.stops.destroy');

        Route::get('/schedule', [TransportController::class, 'scheduleChart'])->name('transport.schedule');
        Route::get('/schedule/print', [TransportController::class, 'scheduleChartPrint'])->name('transport.schedule.print');
        Route::post('/schedules', [TransportController::class, 'scheduleStore'])->name('transport.schedules.store');
        Route::put('/schedules/{id}', [TransportController::class, 'scheduleUpdate'])->name('transport.schedules.update');
        Route::delete('/schedules/{id}', [TransportController::class, 'scheduleDestroy'])->name('transport.schedules.destroy');

        Route::get('/assignments', [TransportController::class, 'assignmentIndex'])->name('transport.assignments');
        Route::post('/assignments', [TransportController::class, 'assignmentStore'])->name('transport.assignments.store');
        Route::delete('/assignments/{id}', [TransportController::class, 'assignmentDestroy'])->name('transport.assignments.destroy');
    });

    // Users, Roles and Permissions management is provided by the metheme
    // host theme (routes named me.users.* / me.roles.*) and is intentionally
    // not duplicated here.
    Route::prefix('users')->group(function () {
        Route::get('/activity', [UserController::class, 'activityLogs'])->name('users.activity');
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/students', [DashboardController::class, 'studentReport'])->name('reports.students');
        Route::get('/students/print', [DashboardController::class, 'studentReportPrint'])->name('reports.students.print');
        Route::get('/fees', [DashboardController::class, 'feeReport'])->name('reports.fees');
        Route::get('/fees/print', [DashboardController::class, 'feeReportPrint'])->name('reports.fees.print');
        Route::get('/expenses', [DashboardController::class, 'expenseReport'])->name('reports.expenses');
        Route::get('/expenses/print', [DashboardController::class, 'expenseReportPrint'])->name('reports.expenses.print');
        Route::get('/attendance', [DashboardController::class, 'attendanceReport'])->name('reports.attendance');
        Route::get('/exams', [DashboardController::class, 'examReport'])->name('reports.exams');
    });



    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/school', [UserController::class, 'schoolSettings'])->name('settings.school');
        Route::post('/school', [UserController::class, 'updateSchoolSettings'])->name('settings.school.update');
        Route::get('/sms', [UserController::class, 'smsSettings'])->name('settings.sms');
        Route::post('/sms', [UserController::class, 'updateSmsSettings'])->name('settings.sms.update');
        Route::get('/backup', [UserController::class, 'backupSettings'])->name('settings.backup');
        Route::post('/backup', [UserController::class, 'createBackup'])->name('settings.backup.create');
        Route::post('/backup/restore', [UserController::class, 'restoreBackup'])->name('settings.backup.restore');
        Route::get('/session', [UserController::class, 'sessionSettings'])->name('settings.session');
        Route::post('/session', [UserController::class, 'updateSessionSettings'])->name('settings.session.update');
        Route::get('/general', [UserController::class, 'generalSettings'])->name('settings.general');
        Route::post('/general', [UserController::class, 'updateGeneralSettings'])->name('settings.general.update');
    });

    // Recycle Bin (soft-deleted students, teachers, staff)
    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
    Route::post('/trash/{type}/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
    Route::delete('/trash/{type}/{id}', [TrashController::class, 'forceDelete'])->name('trash.force');

    // Inventory
    Route::prefix('inventory')->group(function () {
        Route::get('/products', [UserController::class, 'productIndex'])->name('inventory.products');
        Route::post('/products', [UserController::class, 'productStore'])->name('inventory.products.store');
        Route::put('/products/{id}', [UserController::class, 'productUpdate'])->name('inventory.products.update');
        Route::delete('/products/{id}', [UserController::class, 'productDestroy'])->name('inventory.products.destroy');

        Route::get('/stock', [UserController::class, 'stockIndex'])->name('inventory.stock');
        Route::post('/stock', [UserController::class, 'stockStore'])->name('inventory.stock.store');

        Route::get('/purchase', [UserController::class, 'purchaseIndex'])->name('inventory.purchase');
        Route::post('/purchase', [UserController::class, 'purchaseStore'])->name('inventory.purchase.store');
        Route::delete('/purchase/{id}', [UserController::class, 'purchaseDestroy'])->name('inventory.purchase.destroy');

        Route::get('/suppliers', [UserController::class, 'supplierIndex'])->name('inventory.suppliers');
        Route::post('/suppliers', [UserController::class, 'supplierStore'])->name('inventory.suppliers.store');
        Route::delete('/suppliers/{id}', [UserController::class, 'supplierDestroy'])->name('inventory.suppliers.destroy');
    });

    // Fee additional routes
    Route::get('/fees/monthly', [FeeController::class, 'monthlyIndex'])->name('fees.monthly');
    Route::get('/fees/invoice', [FeeController::class, 'invoiceIndex'])->name('fees.invoice');
    Route::get('/fees/payment-history', [FeeController::class, 'paymentHistory'])->name('fees.payment-history');

    // Transport additional routes
    Route::get('/transport/drivers', [TransportController::class, 'driverIndex'])->name('transport.drivers');
    Route::post('/transport/drivers', [TransportController::class, 'driverStore'])->name('transport.drivers.store');
    Route::put('/transport/drivers/{id}', [TransportController::class, 'driverUpdate'])->name('transport.drivers.update');
    Route::delete('/transport/drivers/{id}', [TransportController::class, 'driverDestroy'])->name('transport.drivers.destroy');
    Route::get('/transport/fee', [TransportController::class, 'transportFeeIndex'])->name('transport.fee');
    Route::post('/transport/fee', [TransportController::class, 'transportFeeStore'])->name('transport.fee.store');
    Route::post('/transport/fee/generate', [TransportController::class, 'transportFeeGenerate'])->name('transport.fee.generate');

    // Exam additional routes
    Route::get('/exams/marksheet', [ExamController::class, 'marksheetIndex'])->name('exams.marksheet.index');

    // Expense additional routes
    Route::get('/expenses/staff-salary', [ExpenseController::class, 'staffSalaryIndex'])->name('expenses.staff-salary');
    Route::post('/expenses/staff-salary', [ExpenseController::class, 'staffSalaryStore'])->name('expenses.staff-salary.store');
    Route::get('/expenses/profit-loss', [ExpenseController::class, 'profitLossIndex'])->name('expenses.profit-loss');

    // Reports additional
    Route::get('/reports/exam', [DashboardController::class, 'examReport'])->name('reports.exam');
    Route::get('/reports/financial', [DashboardController::class, 'financialReport'])->name('reports.financial');

    // ── Staff (Non-Teaching) ──────────────────────────────────────────────
    Route::get('/staff',          [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff',         [StaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{id}',     [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{id}',  [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::get('/staff/all',      [StaffController::class, 'getAll'])->name('staff.all');

    // ── Managing Committee ────────────────────────────────────────────────
    Route::get('/committee',          [CommitteeController::class, 'index'])->name('committee.index');
    Route::post('/committee',         [CommitteeController::class, 'store'])->name('committee.store');
    Route::put('/committee/{id}',     [CommitteeController::class, 'update'])->name('committee.update');
    Route::delete('/committee/{id}',  [CommitteeController::class, 'destroy'])->name('committee.destroy');
    Route::get('/committee/{id}',     [CommitteeController::class, 'show'])->name('committee.show');
    Route::get('/committee/{id}/print', [CommitteeController::class, 'showPrint'])->name('committee.show.print');
    Route::post('/committee/{id}/members',            [CommitteeController::class, 'storeMember'])->name('committee.members.store');
    Route::put('/committee/{id}/members/{memberId}',  [CommitteeController::class, 'updateMember'])->name('committee.members.update');
    Route::delete('/committee/{id}/members/{memberId}', [CommitteeController::class, 'destroyMember'])->name('committee.members.destroy');
    Route::post('/committee/{id}/reorder',            [CommitteeController::class, 'reorder'])->name('committee.reorder');

    // ── Tour Fee Categories ───────────────────────────────────────────────
    Route::post('/tours/{id}/fee-categories',           [TourController::class, 'storeFeeCategory'])->name('academic.tours.fee.store');
    Route::put('/tours/{id}/fee-categories/{catId}',    [TourController::class, 'updateFeeCategory'])->name('academic.tours.fee.update');
    Route::delete('/tours/{id}/fee-categories/{catId}', [TourController::class, 'destroyFeeCategory'])->name('academic.tours.fee.destroy');
    Route::get('/tours/{id}/fee-categories',            [TourController::class, 'getFeeCategories'])->name('academic.tours.fee.list');
});

// ── Student Portal (session-based PIN auth, no Laravel guard) ─────────────
Route::middleware(['web', LocaleMiddleware::class, TranslateFlash::class])->prefix('student-portal')->group(function () {
    Route::get('/login',  [StudentPortalController::class, 'login'])->name('portal.login');
    Route::post('/login', [StudentPortalController::class, 'loginPost'])->name('portal.login.post');
    Route::post('/logout',[StudentPortalController::class, 'logout'])->name('portal.logout');
    Route::get('/',           [StudentPortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::get('/fees',       [StudentPortalController::class, 'fees'])->name('portal.fees');
    Route::get('/results',    [StudentPortalController::class, 'results'])->name('portal.results');
    Route::get('/marksheet/{exam_id}', [StudentPortalController::class, 'marksheet'])->name('portal.marksheet');
    Route::get('/library',    [StudentPortalController::class, 'library'])->name('portal.library');
    Route::get('/notices',    [StudentPortalController::class, 'notices'])->name('portal.notices');
    Route::get('/attendance', [StudentPortalController::class, 'attendance'])->name('portal.attendance');
});
