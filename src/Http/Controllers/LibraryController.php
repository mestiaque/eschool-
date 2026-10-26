<?php

namespace ME\SchoolManagement\Http\Controllers;

use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Http\Controllers\Concerns\GuardsDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\SchoolManagement\Models\Book;
use ME\SchoolManagement\Models\BookCopy;
use ME\SchoolManagement\Models\BookIssue;
use ME\SchoolManagement\Models\BookPurchase;
use ME\SchoolManagement\Models\LibraryMember;
use ME\SchoolManagement\Models\LibrarySetting;
use ME\SchoolManagement\Models\Staff;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Services\Ledger;

class LibraryController extends Controller
{
    use GuardsDeletes;

    // ── Books ──────────────────────────────────────────────────────────────

    public function bookIndex(Request $request)
    {
        $query = Book::withCount([
            'copies',
            'copies as available_count' => fn($q) => $q->where('status','available'),
            'copies as issued_count'    => fn($q) => $q->where('status','issued'),
            'copies as lost_count'      => fn($q) => $q->where('status','lost'),
        ]);

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title','like',"%$s%")
                ->orWhere('author','like',"%$s%")
                ->orWhere('book_id','like',"%$s%")
                ->orWhere('isbn','like',"%$s%"));
        }
        if ($request->type) $query->where('type', $request->type);

        $books    = $query->orderBy('book_id')->paginate(20)->withQueryString();
        $settings = LibrarySetting::get();
        return view('school-management::library.books.index', compact('books','settings'));
    }

    public function bookStore(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:1000',
            'price'    => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $book = Book::create([
                'book_id'     => Book::generateBookId(),
                'isbn'        => $request->isbn,
                'title'       => $request->title,
                'author'      => $request->author,
                'publisher'   => $request->publisher,
                'edition'     => $request->edition,
                'language'    => $request->language ?? 'Bangla',
                'quantity'    => 0,  // incremented by generateCopies
                'rack_no'     => $request->rack_no,
                'type'        => $request->type ?? 'text',
                'price'       => $request->price ?? 0,
                'description' => $request->description,
                'is_active'   => 1,
            ]);
            $book->generateCopies((int)$request->quantity, now()->toDateString(), $request->price);

            if ($request->quantity > 0) {
                BookPurchase::create([
                    'book_id'       => $book->id,
                    'copies_count'  => $request->quantity,
                    'purchase_date' => now()->toDateString(),
                    'unit_price'    => $request->price ?? 0,
                    'total_price'   => ($request->price ?? 0) * $request->quantity,
                    'vendor'        => $request->vendor,
                    'invoice_no'    => $request->invoice_no,
                ]);
            }
        });

        return back()->with('success', 'Book added with copies successfully.');
    }

    public function bookUpdate(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
        ]);
        $book->update($request->only(['title','isbn','author','publisher','edition','language','rack_no','type','price','description']));
        return back()->with('success', 'Book updated.');
    }

    public function bookDestroy($id)
    {
        $book = Book::findOrFail($id);
        return $this->deleteUnlessUsed($book, [
            'issue records' => $this->uses('book_issues', 'book_id', $id),
        ], $book->title, 'Book deleted.');
    }

    public function bookCopies($bookId)
    {
        $book   = Book::with(['copies.activeIssue.student','copies.activeIssue.teacher','copies.activeIssue.staff'])->findOrFail($bookId);
        $issues = BookIssue::where('book_id',$bookId)->with(['copy','student','teacher','staff'])->orderBy('issue_date','desc')->get();
        return view('school-management::library.books.copies', compact('book','issues'));
    }

    // ── Book Purchases (add copies) ────────────────────────────────────────

    public function purchaseStore(Request $request)
    {
        $request->validate([
            'book_id'       => 'required|exists:books,id',
            'copies_count'  => 'required|integer|min:1|max:1000',
            'purchase_date' => 'required|date',
            'unit_price'    => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $book = Book::findOrFail($request->book_id);
            $book->generateCopies((int)$request->copies_count, $request->purchase_date, $request->unit_price);

            BookPurchase::create([
                'book_id'       => $book->id,
                'copies_count'  => $request->copies_count,
                'purchase_date' => $request->purchase_date,
                'unit_price'    => $request->unit_price ?? 0,
                'total_price'   => ($request->unit_price ?? 0) * $request->copies_count,
                'vendor'        => $request->vendor,
                'invoice_no'    => $request->invoice_no,
                'notes'         => $request->notes,
            ]);
        });

        return back()->with('success', 'Purchase recorded and new copies generated.');
    }

    // ── Issue ──────────────────────────────────────────────────────────────

    public function issueIndex(Request $request)
    {
        $query = BookIssue::with(['book','copy','student','teacher','staff'])
            ->orderBy('issue_date','desc');

        if ($request->status)      $query->where('status', $request->status);
        if ($request->member_type) $query->where('member_type', $request->member_type);
        if ($request->search) {
            $s = $request->search;
            // Grouped so the ORs don't bypass the status/member filters.
            $query->where(fn($w) => $w
                ->whereHas('book', fn($q) => $q->where('title','like',"%$s%")->orWhere('book_id','like',"%$s%"))
                ->orWhereHas('student', fn($q) => $q->where('first_name','like',"%$s%")->orWhere('last_name','like',"%$s%"))
                ->orWhereHas('copy', fn($q) => $q->where('copy_id','like',"%$s%")));
        }

        $issues   = $query->paginate(25)->withQueryString();
        $books    = Book::where('is_active',true)->orderBy('title')->get();
        $students = Student::where('is_active',true)->orderBy('first_name')->get();
        $teachers = Teacher::where('is_active',true)->orderBy('first_name')->get();
        $staffAll = Staff::where('is_active',true)->orderBy('first_name')->get();
        $settings = LibrarySetting::get();

        return view('school-management::library.issues.index',
            compact('issues','books','students','teachers','staffAll','settings'));
    }

    public function issueStore(Request $request)
    {
        $request->validate([
            'book_copy_id'  => 'required|exists:book_copies,id',
            'member_type'   => 'required|in:student,teacher,staff',
            'issue_date'    => 'required|date',
            'due_date'      => 'required|date|after_or_equal:issue_date',
            'member_id'     => ['required', 'integer', match ($request->member_type) {
                'teacher' => 'exists:teachers,id',
                'staff'   => 'exists:staff,id',
                default   => 'exists:students,id',
            }],
        ]);

        $memberCol = $request->member_type . '_id';
        $max = (int) LibrarySetting::get()->max_books_per_member;
        $current = BookIssue::where($memberCol, $request->member_id)->where('status', 'issued')->count();
        if ($max > 0 && $current >= $max) {
            return back()->with('error', "This member already has {$current} book(s) issued (limit {$max}).");
        }

        return DB::transaction(function () use ($request) {
            $copy = BookCopy::lockForUpdate()->findOrFail($request->book_copy_id);
            if ($copy->status !== 'available') {
                return back()->with('error', 'This copy is not available (Status: '.$copy->status.')');
            }

            BookIssue::create([
                'book_id'      => $copy->book_id,
                'book_copy_id' => $copy->id,
                'member_type'  => $request->member_type,
                'student_id'   => $request->member_type === 'student' ? $request->member_id : null,
                'teacher_id'   => $request->member_type === 'teacher' ? $request->member_id : null,
                'staff_id'     => $request->member_type === 'staff'   ? $request->member_id : null,
                'issue_date'   => $request->issue_date,
                'due_date'     => $request->due_date,
                'status'       => 'issued',
                'note'         => $request->note,
                'fine_status'  => 'none',
            ]);

            $copy->update(['status' => 'issued']);
            return back()->with('success', 'Book issued: '.$copy->copy_id);
        });
    }

    public function returnBook(Request $request, $id)
    {
        $issue    = BookIssue::with('copy')->findOrFail($id);
        if ($issue->status !== 'issued') {
            return back()->with('error', 'This book has already been ' . $issue->status . '.');
        }
        $settings = LibrarySetting::get();
        $today    = now()->toDateString();

        $overdueDays = $issue->overdue_days;
        $fine        = $settings->calculateFine($overdueDays);

        $isLost = (bool)$request->is_lost;

        $issue->update([
            'return_date' => $today,
            'status'      => $isLost ? 'lost' : 'returned',
            'is_lost'     => $isLost,
            'fine_amount' => $isLost ? max($fine, (float)$issue->book?->price) : $fine,
            'fine_status' => $fine > 0 || $isLost ? 'pending' : 'none',
            'return_note' => $request->return_note,
        ]);

        $issue->copy?->update([
            'status' => $isLost ? 'lost' : 'available',
        ]);

        return back()->with('success', $isLost
            ? 'Book marked as lost. Fine: '.sm_money($issue->fine_amount,2)
            : 'Book returned.' . ($fine > 0 ? ' Fine: '.sm_money($fine,2) : ''));
    }

    public function payFine(Request $request, $id)
    {
        $issue = BookIssue::findOrFail($id);
        $balance = max(0, (float)$issue->fine_amount - (float)$issue->fine_paid);
        $request->validate(['fine_paid' => 'required|numeric|min:0.01|max:' . max(0.01, $balance)],
            ['fine_paid.max' => 'Payment cannot exceed the outstanding fine (' . sm_money($balance, 2) . ').']);

        $totalPaid = (float)$issue->fine_paid + (float)$request->fine_paid;
        $status = $totalPaid >= (float)$issue->fine_amount ? 'paid' : 'pending';

        $issue->update([
            'fine_paid'   => $totalPaid,
            'fine_status' => $status,
        ]);
        Ledger::income('book_issue', $issue->id, $totalPaid, now()->toDateString(),
            'Library fine: ' . ($issue->book->title ?? 'book') . ' (' . ($issue->copy->copy_id ?? '#' . $issue->id) . ')');
        return back()->with('success', 'Fine payment recorded.');
    }

    public function waiveFine($id)
    {
        // Waive what is still owed; money already paid stays recorded.
        BookIssue::findOrFail($id)->update(['fine_status' => 'waived']);
        return back()->with('success', 'Remaining fine waived.');
    }

    // ── Availability Chart ─────────────────────────────────────────────────

    public function availabilityChart(Request $request)
    {
        $query = Book::with(['copies.activeIssue.student','copies.activeIssue.teacher','copies.activeIssue.staff'])
            ->withCount(['copies as available_count' => fn($q) => $q->where('status','available')]);

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title','like',"%$s%")->orWhere('book_id','like',"%$s%"));
        }
        if ($request->type) $query->where('type', $request->type);
        if ($request->availability === 'available') $query->having('available_count','>',0);
        if ($request->availability === 'unavailable') $query->having('available_count','=',0);

        $books = $query->orderBy('book_id')->paginate(15)->withQueryString();
        return view('school-management::library.books.availability', compact('books'));
    }

    // ── Settings ───────────────────────────────────────────────────────────

    public function settingsUpdate(Request $request)
    {
        $data = $request->validate([
            'fine_type'            => 'required|in:per_day,fixed',
            'fine_per_day'         => 'nullable|numeric|min:0',
            'fixed_fine'           => 'nullable|numeric|min:0',
            'max_issue_days'       => 'required|integer|min:1',
            'max_books_per_member' => 'required|integer|min:1',
        ]);
        // Hidden fields are still submitted; never write null into NOT NULL columns.
        LibrarySetting::get()->update(array_filter($data, fn($v) => $v !== null));
        return back()->with('success', 'Library settings updated.');
    }

    // ── AJAX helpers ───────────────────────────────────────────────────────

    public function getAvailableBooks()
    {
        return Book::where('is_active',true)
            ->whereHas('copies', fn($q) => $q->where('status','available'))
            ->with(['copies' => fn($q) => $q->where('status','available')])
            ->orderBy('title')->get();
    }

    public function getBookCopies($bookId)
    {
        $copies = BookCopy::where('book_id', $bookId)
            ->where('status','available')
            ->orderBy('copy_number')
            ->get(['id','copy_id','copy_number','status']);
        return response()->json($copies);
    }

    // ── Members ────────────────────────────────────────────────────────────

    public function memberIndex()
    {
        $members  = LibraryMember::with(['student','teacher'])->orderBy('id','desc')->paginate(20);
        $teachers = Teacher::where('is_active',true)->get();
        return view('school-management::library.members.index', compact('members','teachers'));
    }

    public function memberStore(Request $request)
    {
        $data = $request->validate([
            'member_type'     => 'required|in:student,teacher',
            'student_id'      => 'required_if:member_type,student|nullable|exists:students,id',
            'teacher_id'      => 'required_if:member_type,teacher|nullable|exists:teachers,id',
            'membership_date' => 'required|date',
            'expire_date'     => 'required|date|after:membership_date',
        ]);
        // Only keep the id that matches the chosen member type.
        $data[$data['member_type'] === 'student' ? 'teacher_id' : 'student_id'] = null;
        $data['is_active'] = true;
        LibraryMember::create($data);
        return back()->with('success', 'Member added.');
    }

    public function memberDestroy($id)
    {
        LibraryMember::findOrFail($id)->delete();
        return back()->with('success', 'Member removed.');
    }
}
