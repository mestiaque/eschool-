<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\BookCopy;
use ME\SchoolManagement\Models\BookIssue;
use ME\SchoolManagement\Models\LibraryMember;
use ME\SchoolManagement\Models\LibrarySetting;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 4.6 / 4.7 */
class MembershipAndRollTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->admin());
        LibrarySetting::get()->update(['max_books_per_member' => 50, 'require_membership' => false]);
    }

    private function issue(Student $s)
    {
        $book = \ME\SchoolManagement\Models\Book::first();
        $book->generateCopies(1, now()->toDateString(), 100);
        $copy = BookCopy::where('book_id', $book->id)->where('status', 'available')->latest('id')->first();
        return $this->post('/library/issues', ['book_copy_id' => $copy->id, 'member_type' => 'student', 'member_id' => $s->id,
            'issue_date' => now()->toDateString(), 'due_date' => now()->addWeek()->toDateString()]);
    }

    private function borrower(): Student
    {
        $s = Student::where('is_active', true)->whereNotIn('id', BookIssue::where('status', 'issued')->whereNotNull('student_id')->select('student_id'))->first();
        LibraryMember::where('student_id', $s->id)->delete();
        return $s;
    }

    public function test_expired_membership_blocks_borrowing(): void
    {
        $s = $this->borrower();
        LibraryMember::create(['member_type' => 'student', 'student_id' => $s->id, 'membership_date' => '2024-01-01', 'expire_date' => '2025-01-01', 'is_active' => true]);
        $this->issue($s)->assertSessionHas('error', fn ($m) => str_contains($m, 'expired'));
    }

    public function test_membership_only_required_when_switched_on(): void
    {
        $s = $this->borrower();
        $this->issue($s)->assertSessionHas('success');

        $s2 = $this->borrower();
        $this->post('/library/settings', ['fine_type' => 'per_day', 'fine_per_day' => 2, 'max_issue_days' => 14,
            'max_books_per_member' => 50, 'require_membership' => 1])->assertSessionHas('success');
        $this->issue($s2)->assertSessionHas('error', fn ($m) => str_contains($m, 'Not a library member'));

        LibraryMember::create(['member_type' => 'student', 'student_id' => $s2->id, 'membership_date' => now()->subMonth(), 'expire_date' => now()->addYear(), 'is_active' => true]);
        $this->issue($s2)->assertSessionHas('success');
    }

    public function test_roll_number_must_be_unique_in_class_section_and_year(): void
    {
        $a = Student::where('is_active', true)->whereNotNull('roll_number')->whereNotNull('section_id')->first();
        $b = Student::where('is_active', true)->where('class_id', $a->class_id)->where('section_id', $a->section_id)
            ->where('academic_year_id', $a->academic_year_id)->whereKeyNot($a->id)->first();

        $this->put("/students/{$b->id}", ['first_name' => $b->first_name, 'last_name' => $b->last_name, 'gender' => 'male',
            'date_of_birth' => '2010-01-01', 'class_id' => $b->class_id, 'section_id' => $b->section_id,
            'academic_year_id' => $b->academic_year_id, 'roll_number' => $a->roll_number])->assertSessionHasErrors('roll_number');

        $this->post('/students', ['first_name' => 'New', 'last_name' => 'Kid', 'gender' => 'female', 'date_of_birth' => '2012-02-02',
            'class_id' => $a->class_id, 'section_id' => $a->section_id, 'academic_year_id' => $a->academic_year_id,
            'roll_number' => $a->roll_number])->assertSessionHasErrors('roll_number');
    }
}
