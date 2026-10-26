<?php

namespace ME\SchoolManagement\Http\Controllers;

use Illuminate\Http\Request;
use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Http\Controllers\Concerns\GuardsDeletes;
use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\ClassRoutine;
use ME\SchoolManagement\Models\ClassText;
use ME\SchoolManagement\Models\Room;
use ME\SchoolManagement\Models\RoutineTitle;
use ME\SchoolManagement\Models\Section;
use ME\SchoolManagement\Models\Subject;
use ME\SchoolManagement\Models\SubjectGroup;
use ME\SchoolManagement\Models\Syllabus;
use ME\SchoolManagement\Models\Teacher;

class AcademicController extends Controller
{
    use GuardsDeletes;

    // Academic Years
    public function academicYearIndex()
    {
        $query = AcademicYear::orderBy('id', 'desc');

        // Filter by search name
        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        // Filter by status
        if (request('status') == 'active') {
            $query->where('is_active', true);
        } elseif (request('status') == 'inactive') {
            $query->where('is_active', false);
        }

        // Filter by current year
        if (request('current') == '1') {
            $query->where('is_current', true);
        }

        $years = $query->paginate(20)->withQueryString();
        return view('school-management::academic.years.index', compact('years'));
    }

    public function academicYearStore(Request $request)
    {
        $data = $this->validateYear($request);
        // The first year becomes current automatically.
        if (!AcademicYear::where('is_current', true)->exists()) $data['is_current'] = true;
        if ($data['is_current']) {
            AcademicYear::where('is_current', true)->update(['is_current' => false]);
        }

        AcademicYear::create($data);
        return back()->with('success', 'Academic Year created successfully');
    }

    public function academicYearUpdate(Request $request, $id)
    {
        $year = AcademicYear::findOrFail($id);
        $data = $this->validateYear($request, $id);
        if ($data['is_current']) {
            AcademicYear::where('is_current', true)->where('id', '!=', $id)->update(['is_current' => false]);
        } elseif ($year->is_current) {
            // Keep exactly one current year; switch it from another year instead.
            $data['is_current'] = true;
        }
        $year->update($data);
        return back()->with('success', 'Academic Year updated successfully');
    }

    public function academicYearDestroy($id)
    {
        $year = AcademicYear::findOrFail($id);
        if ($year->is_current) {
            return back()->with('error', 'The current academic year cannot be deleted. Make another year current first.');
        }
        return $this->deleteUnlessUsed($year, [
            'students'      => $this->uses('students', 'academic_year_id', $id),
            'exams'         => $this->uses('exams', 'academic_year_id', $id),
            'fee invoices'  => $this->uses('fee_collections', 'academic_year_id', $id),
            'fee structures'=> $this->uses('fee_structures', 'academic_year_id', $id),
            'expenses'      => $this->uses('expenses', 'academic_year_id', $id),
            'income entries'=> $this->uses('incomes', 'academic_year_id', $id),
            'syllabi'       => $this->uses('syllabi', 'academic_year_id', $id),
        ], $year->name, 'Academic Year deleted successfully');
    }

    // Classes
    public function classIndex()
    {
        $query = Classe::with('sections')->orderBy('numeric_name');

        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        if (request('status') == 'active') {
            $query->where('is_active', true);
        } elseif (request('status') == 'inactive') {
            $query->where('is_active', false);
        }

        $classes = $query->paginate(20)->withQueryString();
        return view('school-management::academic.classes.index', compact('classes'));
    }

    public function classStore(Request $request)
    {
        Classe::create($this->validateClass($request));
        return back()->with('success', 'Class created successfully');
    }

    public function classUpdate(Request $request, $id)
    {
        Classe::findOrFail($id)->update($this->validateClass($request, $id));
        return back()->with('success', 'Class updated successfully');
    }

    public function classDestroy($id)
    {
        $class = Classe::findOrFail($id);
        return $this->deleteUnlessUsed($class, [
            'students'       => $this->uses('students', 'class_id', $id),
            'sections'       => $this->uses('sections', 'class_id', $id),
            'subjects'       => $this->uses('subjects', 'class_id', $id),
            'fee invoices'   => $this->uses('fee_collections', 'class_id', $id),
            'exam marks'     => $this->uses('student_marks', 'class_id', $id),
            'results'        => $this->uses('results', 'class_id', $id),
            'routine periods'=> $this->uses('class_routines', 'class_id', $id),
            'class tests'    => $this->uses('class_tests', 'class_id', $id),
            'assignments'    => $this->uses('assignments', 'class_id', $id),
        ], $class->name, 'Class deleted successfully');
    }

    // Sections
    public function sectionStore(Request $request)
    {
        Section::create($this->validateSection($request));
        return back()->with('success', 'Section created successfully');
    }

    public function sectionUpdate(Request $request, $id)
    {
        Section::findOrFail($id)->update($this->validateSection($request, $id));
        return back()->with('success', 'Section updated successfully');
    }

    public function sectionDestroy($id)
    {
        $section = Section::findOrFail($id);
        return $this->deleteUnlessUsed($section, [
            'students'        => $this->uses('students', 'section_id', $id),
            'routine periods' => $this->uses('class_routines', 'section_id', $id),
        ], $section->name, 'Section deleted successfully');
    }

    public function sectionIndex()
    {
        $query = Section::with('class')->orderBy('id', 'desc');

        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        if (request('class_id')) {
            $query->where('class_id', request('class_id'));
        }

        if (request('status') == 'active') {
            $query->where('is_active', true);
        } elseif (request('status') == 'inactive') {
            $query->where('is_active', false);
        }

        $sections = $query->paginate(25)->withQueryString();
        $classes = Classe::where('is_active', true)->get();
        return view('school-management::academic.sections.index', compact('sections', 'classes'));
    }

    // Subject Groups
    public function subjectGroupIndex()
    {
        $groups = SubjectGroup::withCount('subjects', 'students')->orderBy('name')->paginate(20);
        return view('school-management::academic.subject-groups.index', compact('groups'));
    }

    public function subjectGroupStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:subject_groups,name']);
        SubjectGroup::create($request->only(['name', 'description']) + ['is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Subject Group created successfully');
    }

    public function subjectGroupUpdate(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100|unique:subject_groups,name,' . $id]);
        SubjectGroup::findOrFail($id)->update($request->only(['name', 'description']) + ['is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Subject Group updated successfully');
    }

    public function subjectGroupDestroy($id)
    {
        $group = SubjectGroup::findOrFail($id);
        return $this->deleteUnlessUsed($group, [
            'subjects' => $this->uses('subjects', 'group_id', $id),
            'students' => $this->uses('students', 'subject_group_id', $id),
        ], $group->name, 'Subject Group deleted successfully');
    }

    // Subjects
    public function subjectIndex()
    {
        $query = Subject::with('class');

        if (request('search')) {
            $s = request('search');
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%"));
        }

        if (request('class_id')) {
            $query->where('class_id', request('class_id'));
        }

        if (request('status') == 'active') {
            $query->where('is_active', true);
        } elseif (request('status') == 'inactive') {
            $query->where('is_active', false);
        }

        $subjects      = $query->paginate(25)->withQueryString();
        $classes       = Classe::where('is_active', true)->get();
        $subjectGroups = SubjectGroup::where('is_active', true)->orderBy('name')->get();
        return view('school-management::academic.subjects.index', compact('subjects', 'classes', 'subjectGroups'));
    }

    public function subjectStore(Request $request)
    {
        Subject::create($this->validateSubject($request));
        return back()->with('success', 'Subject created successfully');
    }

    public function subjectUpdate(Request $request, $id)
    {
        Subject::findOrFail($id)->update($this->validateSubject($request));
        return back()->with('success', 'Subject updated successfully');
    }

    public function subjectDestroy($id)
    {
        $subject = Subject::findOrFail($id);
        return $this->deleteUnlessUsed($subject, [
            'exam marks'      => $this->uses('student_marks', 'subject_id', $id),
            'exam schedules'  => $this->uses('exam_schedules', 'subject_id', $id),
            'routine periods' => $this->uses('class_routines', 'subject_id', $id),
            'class tests'     => $this->uses('class_tests', 'subject_id', $id),
            'assignments'     => $this->uses('assignments', 'subject_id', $id),
            'syllabi'         => $this->uses('syllabi', 'subject_id', $id),
        ], $subject->name, 'Subject deleted successfully');
    }

    // Class Texts (Curriculum)
    public function classTextIndex()
    {
        $query = ClassText::with(['class', 'subject']);

        if (request('class_id')) {
            $query->where('class_id', request('class_id'));
        }

        if (request('subject_id')) {
            $query->where('subject_id', request('subject_id'));
        }

        if (request('search')) {
            $query->where('title', 'like', '%' . request('search') . '%');
        }

        $classTexts = $query->orderBy('title')->paginate(25)->withQueryString();
        $classes    = Classe::where('is_active', true)->get();
        $subjects   = Subject::where('is_active', true)->get();
        return view('school-management::academic.class-texts', compact('classTexts', 'classes', 'subjects'));
    }

    public function classTextStore(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        ClassText::create(array_merge($request->only(['title', 'class_id', 'subject_id', 'author', 'edition', 'content']), ['is_active' => $request->boolean('is_active')]));
        return back()->with('success', 'Class text created successfully');
    }

    public function classTextUpdate(Request $request, $id)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        ClassText::findOrFail($id)->update(array_merge($request->only(['title', 'class_id', 'subject_id', 'author', 'edition', 'content']), ['is_active' => $request->boolean('is_active')]));
        return back()->with('success', 'Class text updated successfully');
    }

    public function classTextDestroy($id)
    {
        ClassText::findOrFail($id)->delete();
        return back()->with('success', 'Class text deleted successfully');
    }

    // Class Routines
    public function routineIndex()
    {
        $classes = Classe::where('is_active', true)->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        $teachers = Teacher::where('is_active', true)->orderBy('first_name')->get();
        $titles = RoutineTitle::where('is_active', true)->orderBy('name')->pluck('name');

        $query = ClassRoutine::with(['class', 'section', 'subject', 'room', 'teacher']);

        if (request('class_id')) {
            $query->where('class_id', request('class_id'));
        }
        if (request('day')) {
            $query->where('day', request('day'));
        }
        if (request('room_id')) {
            $query->where('room_id', request('room_id'));
        }
        if (request('title')) {
            $query->where('title', request('title'));
        }

        $routines = $query->orderByWeekday()->orderBy('start_time')->paginate(30)->withQueryString();

        return view('school-management::academic.routines.index', compact('classes', 'rooms', 'teachers', 'routines', 'titles'));
    }

    public function routineIndexPrint()
    {
        $classes = Classe::where('is_active', true)->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();

        $query = ClassRoutine::with(['class', 'section', 'subject', 'room']);

        if (request('class_id')) {
            $query->where('class_id', request('class_id'));
        }
        if (request('day')) {
            $query->where('day', request('day'));
        }
        if (request('room_id')) {
            $query->where('room_id', request('room_id'));
        }
        if (request('title')) {
            $query->where('title', request('title'));
        }

        $routines = $query->orderByWeekday()->orderBy('start_time')->get();

        return view('school-management::academic.routines.print', compact('classes', 'rooms', 'routines'));
    }

    public function classRoutine($class_id)
    {
        $class = Classe::with(['sections'])->findOrFail($class_id);
        $titles = ClassRoutine::where('class_id', $class_id)->whereNotNull('title')->distinct()->orderBy('title')->pluck('title');
        $selectedTitle = request('title', $titles->first());

        $routines = ClassRoutine::with(['subject', 'section', 'teacher'])
            ->where('class_id', $class_id)
            ->when($selectedTitle, fn($q) => $q->where('title', $selectedTitle), fn($q) => $q->whereNull('title'))
            ->orderByWeekday()
            ->orderBy('start_time')
            ->get();

        // Organize routines by day and period
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $routineByDay = [];

        foreach ($days as $day) {
            $routineByDay[$day] = $routines->where('day', $day)->groupBy(function($item) {
                return $item->section_id ?? 'all';
            });
        }

        return view('school-management::academic.classes.routine', compact('class', 'routines', 'routineByDay', 'days', 'titles', 'selectedTitle'));
    }

    public function classRoutinePrint($class_id)
    {
        $class = Classe::with(['sections'])->findOrFail($class_id);
        $titles = ClassRoutine::where('class_id', $class_id)->whereNotNull('title')->distinct()->orderBy('title')->pluck('title');
        $selectedTitle = request('title', $titles->first());

        $routines = ClassRoutine::with(['subject', 'section'])
            ->where('class_id', $class_id)
            ->when($selectedTitle, fn($q) => $q->where('title', $selectedTitle), fn($q) => $q->whereNull('title'))
            ->orderByWeekday()
            ->orderBy('start_time')
            ->get();

        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        return view('school-management::academic.classes.routine-print', compact('class', 'routines', 'days', 'selectedTitle'));
    }

    public function routineStore(Request $request)
    {
        $request->validate($this->routineRules(), ['end_time.after' => 'End time must be after start time.']);

        $title = $request->filled('title') ? trim($request->title) : null;

        // Check for duplicate subject on the same day (within the same routine title/version)
        $existingSubject = ClassRoutine::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('day', $request->day)
            ->where('subject_id', $request->subject_id)
            ->where('title', $title)
            ->first();

        if ($existingSubject) {
            return back()->with('error', 'This subject is already scheduled for this class/section on this day!');
        }

        // Check for class time conflict (within the same routine title/version)
        $timeConflict = ClassRoutine::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('day', $request->day)
            ->where('title', $title)
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
                });
            })
            ->first();

        if ($timeConflict) {
            return back()->with('error', 'There is a time conflict with another subject for this class at this time!');
        }

        // Check for room conflict (within the same routine title/version)
        if ($request->room_id) {
            $roomConflict = ClassRoutine::where('room_id', $request->room_id)
                ->where('day', $request->day)
                ->where('title', $title)
                ->where(function($query) use ($request) {
                    $query->where(function($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>', $request->start_time);
                    });
                })
                ->first();

            if ($roomConflict) {
                return back()->with('error', 'This room is already booked at this time on ' . $request->day . '!');
            }
        }

        // Check for teacher conflict (within the same routine title/version)
        if ($request->teacher_id) {
            $teacherConflict = ClassRoutine::where('teacher_id', $request->teacher_id)
                ->where('day', $request->day)
                ->where('title', $title)
                ->where(function($query) use ($request) {
                    $query->where(function($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>', $request->start_time);
                    });
                })
                ->first();

            if ($teacherConflict) {
                return back()->with('error', 'This teacher is already scheduled elsewhere at this time on ' . $request->day . '!');
            }
        }

        ClassRoutine::create(array_merge(
            $request->only(['class_id', 'section_id', 'subject_id', 'teacher_id', 'day', 'start_time', 'end_time', 'room_id']),
            ['title' => $title]
        ));
        return back()->with('success', 'Routine created successfully');
    }

    public function routineDestroy($id)
    {
        ClassRoutine::findOrFail($id)->delete();
        return back()->with('success', 'Routine deleted successfully');
    }

    public function routineUpdate(Request $request, $id)
    {
        $routine = ClassRoutine::findOrFail($id);

        $request->validate($this->routineRules(), ['end_time.after' => 'End time must be after start time.']);

        $title = $request->filled('title') ? trim($request->title) : null;

        // Check for duplicate subject on the same day (excluding current routine, within the same routine title/version)
        $existingSubject = ClassRoutine::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('day', $request->day)
            ->where('subject_id', $request->subject_id)
            ->where('title', $title)
            ->where('id', '!=', $id)
            ->first();

        if ($existingSubject) {
            return back()->with('error', 'This subject is already scheduled for this class/section on this day!');
        }

        // Check for class time conflict (excluding current routine, within the same routine title/version)
        $timeConflict = ClassRoutine::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('day', $request->day)
            ->where('title', $title)
            ->where('id', '!=', $id)
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
                });
            })
            ->first();

        if ($timeConflict) {
            return back()->with('error', 'There is a time conflict with another subject for this class at this time!');
        }

        // Check for room conflict (excluding current routine, within the same routine title/version)
        if ($request->room_id) {
            $roomConflict = ClassRoutine::where('room_id', $request->room_id)
                ->where('day', $request->day)
                ->where('title', $title)
                ->where('id', '!=', $id)
                ->where(function($query) use ($request) {
                    $query->where(function($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>', $request->start_time);
                    });
                })
                ->first();

            if ($roomConflict) {
                return back()->with('error', 'This room is already booked at this time on ' . $request->day . '!');
            }
        }

        // Check for teacher conflict (excluding current routine, within the same routine title/version)
        if ($request->teacher_id) {
            $teacherConflict = ClassRoutine::where('teacher_id', $request->teacher_id)
                ->where('day', $request->day)
                ->where('title', $title)
                ->where('id', '!=', $id)
                ->where(function($query) use ($request) {
                    $query->where(function($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>', $request->start_time);
                    });
                })
                ->first();

            if ($teacherConflict) {
                return back()->with('error', 'This teacher is already scheduled elsewhere at this time on ' . $request->day . '!');
            }
        }

        $routine->update(array_merge(
            $request->only(['class_id', 'section_id', 'subject_id', 'teacher_id', 'day', 'start_time', 'end_time', 'room_id']),
            ['title' => $title]
        ));
        return back()->with('success', 'Routine updated successfully');
    }

    // Routine Titles (Versions) — master list used to fill the "Routine Title" dropdown
    public function routineTitleIndex()
    {
        $routineTitles = RoutineTitle::orderBy('name')->get();
        return view('school-management::academic.routine-titles.index', compact('routineTitles'));
    }

    public function routineTitleStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:routine_titles,name']);
        RoutineTitle::create(['name' => trim($request->name), 'is_active' => $request->boolean('is_active', true)]);
        return back()->with('success', 'Routine title created successfully');
    }

    public function routineTitleUpdate(Request $request, $id)
    {
        $routineTitle = RoutineTitle::findOrFail($id);
        $request->validate(['name' => 'required|string|max:255|unique:routine_titles,name,' . $id]);

        $newName = trim($request->name);
        if ($newName !== $routineTitle->name) {
            ClassRoutine::where('title', $routineTitle->name)->update(['title' => $newName]);
        }

        $routineTitle->update(['name' => $newName, 'is_active' => $request->boolean('is_active', true)]);
        return back()->with('success', 'Routine title updated successfully');
    }

    public function routineTitleDestroy($id)
    {
        RoutineTitle::findOrFail($id)->delete();
        return back()->with('success', 'Routine title deleted successfully');
    }

    // Rooms
    public function roomIndex()
    {
        $rooms = Room::withCount(['routines'])->orderBy('name')->get();
        return view('school-management::academic.rooms.index', compact('rooms'));
    }

    public function roomStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:rooms,name']);
        $request->validate(['capacity' => 'nullable|integer|min:1']);
        Room::create($request->only(['name', 'building', 'capacity', 'description']) + ['is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Room added successfully');
    }

    public function roomUpdate(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $request->validate(['name' => 'required|string|max:255|unique:rooms,name,' . $id]);
        $request->validate(['capacity' => 'nullable|integer|min:1']);
        $room->update($request->only(['name', 'building', 'capacity', 'description']) + ['is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Room updated successfully');
    }

    public function roomDestroy($id)
    {
        $room = Room::findOrFail($id);
        return $this->deleteUnlessUsed($room, [
            'routine periods' => $this->uses('class_routines', 'room_id', $id),
        ], $room->name, 'Room deleted successfully');
    }

    public function roomSchedule($id)
    {
        $room = Room::findOrFail($id);
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $routines = ClassRoutine::with(['class', 'section', 'subject'])
            ->where('room_id', $id)
            ->orderBy('start_time')
            ->get();

        $scheduleByDay = [];
        foreach ($days as $day) {
            $scheduleByDay[$day] = $routines->where('day', $day)->sortBy('start_time')->values();
        }

        return view('school-management::academic.rooms.schedule', compact('room', 'days', 'scheduleByDay'));
    }

    public function roomSchedulePrint($id)
    {
        $room = Room::findOrFail($id);
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $routines = ClassRoutine::with(['class', 'section', 'subject'])
            ->where('room_id', $id)
            ->orderBy('start_time')
            ->get();

        $scheduleByDay = [];
        foreach ($days as $day) {
            $scheduleByDay[$day] = $routines->where('day', $day)->sortBy('start_time')->values();
        }

        return view('school-management::academic.rooms.schedule-print', compact('room', 'days', 'scheduleByDay'));
    }

    public function getRooms()
    {
        return Room::where('is_active', true)->orderBy('name')->get(['id', 'name', 'building', 'capacity']);
    }

    public function getSections($class_id)
    {
        return Section::where('class_id', $class_id)->where('is_active', true)->get();
    }

    public function getSubjects($class_id)
    {
        return Subject::where('class_id', $class_id)->where('is_active', true)->get(['id', 'name', 'type', 'group_id']);
    }

    public function getOptionalSubjects(Request $request)
    {
        return Subject::where('class_id', $request->class_id)
            ->where('type', 'optional')
            ->where(function ($q) use ($request) {
                // group-specific optionals (e.g. Biology for Science) OR cross-group (e.g. কৃষি শিক্ষা)
                $q->where('group_id', $request->group_id)
                  ->orWhereNull('group_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getRoutine(Request $request)
    {
        return ClassRoutine::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('day', $request->day)
            ->with('subject')
            ->get();
    }

    // Syllabi
    public function syllabusIndex()
    {
        $query = Syllabus::with(['classe', 'subject', 'academicYear']);
        if (request('class_id')) $query->where('class_id', request('class_id'));
        if (request('subject_id')) $query->where('subject_id', request('subject_id'));
        if (request('academic_year_id')) $query->where('academic_year_id', request('academic_year_id'));
        $syllabi = $query->latest()->paginate(20)->withQueryString();

        $classes = Classe::where('is_active', true)->get();
        $subjects = Subject::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        return view('school-management::academic.syllabi.index', compact('syllabi', 'classes', 'subjects', 'academicYears'));
    }

    public function syllabusStore(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data = $request->only(['class_id', 'subject_id', 'academic_year_id', 'title', 'description', 'is_active']);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('syllabi', 'public');
        }

        Syllabus::create($data);
        return back()->with('success', 'Syllabus created successfully');
    }

    public function syllabusUpdate(Request $request, $id)
    {
        $syllabus = Syllabus::findOrFail($id);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data = $request->only(['class_id', 'subject_id', 'academic_year_id', 'title', 'description', 'is_active']);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('file')) {
            if ($syllabus->file && \Illuminate\Support\Facades\Storage::disk('public')->exists($syllabus->file)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($syllabus->file);
            }
            $data['file'] = $request->file('file')->store('syllabi', 'public');
        }

        $syllabus->update($data);
        return back()->with('success', 'Syllabus updated successfully');
    }

    public function syllabusDestroy($id)
    {
        $syllabus = Syllabus::findOrFail($id);
        if ($syllabus->file && \Illuminate\Support\Facades\Storage::disk('public')->exists($syllabus->file)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($syllabus->file);
        }
        $syllabus->delete();
        return back()->with('success', 'Syllabus deleted successfully');
    }

    public function syllabusDownload($id)
    {
        $syllabus = Syllabus::findOrFail($id);
        abort_unless($syllabus->file, 404);
        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($syllabus->file);
        abort_unless(file_exists($path), 404);
        return response()->download($path, basename($syllabus->file));
    }

    // ── Validation helpers ─────────────────────────────────────────────────

    private function validateYear(Request $request, $id = null): array
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:academic_years,name' . ($id ? ",$id" : ''),
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);
        $data['is_active']  = $request->boolean('is_active');
        $data['is_current'] = $request->boolean('is_current');
        return $data;
    }

    private function validateClass(Request $request, $id = null): array
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'numeric_name' => 'required|integer|min:0|unique:classes,numeric_name' . ($id ? ",$id" : ''),
            'description'  => 'nullable|string',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    private function validateSection(Request $request, $id = null): array
    {
        $data = $request->validate([
            'class_id'    => 'required|exists:classes,id',
            'name'        => ['required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('sections')->where('class_id', $request->class_id)->ignore($id)],
            'room_number' => 'nullable|string|max:50',
        ], ['name.unique' => 'This class already has a section with that name.']);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    private function validateSubject(Request $request): array
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'class_id'  => 'required|exists:classes,id',
            'code'      => 'nullable|string|max:50',
            'type'      => 'nullable|in:compulsory,group,optional',
            'group_id'  => 'nullable|exists:subject_groups,id',
            'full_mark' => 'nullable|numeric|min:1',
            'pass_mark' => 'nullable|numeric|min:0' . ($request->filled('full_mark') ? '|lte:full_mark' : ''),
        ]);
        // NOT NULL columns; fall back to the table defaults.
        $data['type']      = $data['type']      ?? 'compulsory';
        $data['full_mark'] = $data['full_mark'] ?? 100;
        $data['pass_mark'] = $data['pass_mark'] ?? 33;
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    private function routineRules(): array
    {
        return [
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'room_id'    => 'nullable|exists:rooms,id',
            'day'        => 'required|in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i,H:i:s',
            'end_time'   => 'required|date_format:H:i,H:i:s|after:start_time',
        ];
    }
}
