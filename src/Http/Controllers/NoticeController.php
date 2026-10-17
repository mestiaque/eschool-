<?php

namespace ME\SchoolManagement\Http\Controllers;
use ME\Http\Controllers\Controller;

use Illuminate\Http\Request;
use ME\SchoolManagement\Models\Notice;
use ME\SchoolManagement\Models\Event;
use ME\SchoolManagement\Models\Holiday;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Services\SchoolNotifier;

class NoticeController extends Controller
{
    // Notices
    public function noticeIndex()
    {
        $notices = Notice::orderBy('id', 'desc')->paginate(20);
        return view('school-management::notices.index', compact('notices'));
    }

    public function noticeStore(Request $request)
    {
        $notice = Notice::create($this->validateNotice($request, true));

        $queued = ($request->boolean('send_sms') || $request->boolean('send_email'))
            ? $this->broadcast($notice, $request->boolean('send_sms'), $request->boolean('send_email'))
            : null;

        return back()->with('success', 'Notice published' . ($queued !== null ? " and is being sent to {$queued} recipient(s)" : ''));
    }

    public function noticeUpdate(Request $request, $id)
    {
        Notice::findOrFail($id)->update($this->validateNotice($request));
        return back()->with('success', 'Notice updated');
    }

    public function noticeDestroy($id)
    {
        Notice::findOrFail($id)->delete();
        return back()->with('success', 'Notice deleted');
    }

    // Events
    public function eventIndex()
    {
        $events = Event::orderBy('id', 'desc')->paginate(20);
        return view('school-management::events.index', compact('events'));
    }

    public function eventStore(Request $request)
    {
        Event::create($this->validateEvent($request, true));
        return back()->with('success', 'Event created');
    }

    public function eventUpdate(Request $request, $id)
    {
        Event::findOrFail($id)->update($this->validateEvent($request));
        return back()->with('success', 'Event updated');
    }

    public function eventDestroy($id)
    {
        Event::findOrFail($id)->delete();
        return back()->with('success', 'Event deleted');
    }

    // Holidays
    public function holidayIndex()
    {
        $holidays = Holiday::orderBy('date')->paginate(25)->withQueryString();
        return view('school-management::holidays.index', compact('holidays'));
    }

    public function holidayStore(Request $request)
    {
        Holiday::create($request->validate([
            'title'       => 'required|string|max:255',
            'date'        => 'required|date',
            'description' => 'nullable|string',
        ]));
        return back()->with('success', 'Holiday added');
    }

    public function holidayDestroy($id)
    {
        Holiday::findOrFail($id)->delete();
        return back()->with('success', 'Holiday deleted');
    }

    /** Send a notice by SMS / e-mail to its audience. Returns how many recipients were found. */
    private function broadcast(Notice $notice, bool $sms, bool $mail): int
    {
        $recipients = collect();
        if (in_array($notice->for, ['all', 'student', 'students', 'guardian'])) {
            Student::with('guardian')->where('is_active', true)
                ->when($notice->class_id, fn ($q) => $q->where('class_id', $notice->class_id))
                ->get()->each(fn ($st) => $recipients->push([
                    'phone' => SchoolNotifier::guardianPhone($st), 'email' => SchoolNotifier::guardianEmail($st)]));
        }
        if (in_array($notice->for, ['all', 'teacher'])) {
            Teacher::where('is_active', true)->get(['phone', 'email'])
                ->each(fn ($t) => $recipients->push(['phone' => $t->phone, 'email' => $t->email]));
        }
        $recipients = $recipients->unique(fn ($r) => ($r['phone'] ?? '') . '|' . ($r['email'] ?? ''));

        $school = SchoolNotifier::schoolName();
        $text   = \Illuminate\Support\Str::limit(strip_tags($notice->title . ': ' . $notice->content), 300) . "\n- {$school}";
        $html   = '<h3>' . e($notice->title) . '</h3><p>' . nl2br(e($notice->content)) . '</p>';

        SchoolNotifier::later(function () use ($recipients, $sms, $mail, $text, $html, $notice, $school) {
            foreach ($recipients as $r) {
                if ($sms)  SchoolNotifier::sms($r['phone'], $text);
                if ($mail) SchoolNotifier::mail($r['email'], "{$notice->title} - {$school}", $html);
            }
        });

        return $recipients->count();
    }

    private function validateNotice(Request $request, bool $creating = false): array
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'publish_date' => 'required|date',
            'end_date'     => 'nullable|date|after_or_equal:publish_date',
            'type'         => 'required|string|max:255',
            'for'          => 'required|string|max:255',
            'class_id'     => 'nullable|exists:classes,id',
        ]);
        // Create forms have no checkbox; edit forms send it only when ticked.
        $data['is_active'] = $creating && !$request->has('is_active') ? true : $request->boolean('is_active');
        return $data;
    }

    private function validateEvent(Request $request, bool $creating = false): array
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'start_time'  => 'nullable',
            'end_time'    => 'nullable',
            'location'    => 'nullable|string|max:255',
            'for'         => 'nullable|string|max:255',
        ]);
        $data['for'] = $data['for'] ?? 'all';
        // Create forms have no checkbox; edit forms send it only when ticked.
        $data['is_active'] = $creating && !$request->has('is_active') ? true : $request->boolean('is_active');
        return $data;
    }

    // Public notice board
    public function publicNotices()
    {
        $notices = Notice::where('is_active', true)
            ->whereDate('publish_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now());
            })
            ->orderBy('publish_date', 'desc')
            ->get();

        return view('school-management::notices.public', compact('notices'));
    }
}
