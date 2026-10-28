<?php

namespace ME\SchoolManagement\Tests\Feature;

use Illuminate\Support\Facades\DB;
use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\FeeCollection;
use ME\SchoolManagement\Models\FeeStructure;
use ME\SchoolManagement\Models\FeeType;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 2.1 — invoice months saved as names showed as "Dec" and broke filters / duplicate checks. */
class FeeMonthTest extends TestCase
{
    private function invoice(array $attrs = []): FeeCollection
    {
        $student = Student::first();
        return FeeCollection::create(array_merge([
            'student_id' => $student->id, 'fee_type_id' => FeeType::first()->id,
            'academic_year_id' => AcademicYear::first()->id, 'class_id' => $student->class_id,
            'amount' => 100, 'due_amount' => 100, 'status' => 'unpaid', 'invoice_no' => uniqid('M'), 'year' => '2026',
        ], $attrs));
    }

    public function test_month_saved_as_a_name_or_number_displays_correctly(): void
    {
        $this->assertSame('March', $this->invoice(['month' => 'March'])->monthLabel('F'));
        $this->assertSame('March', $this->invoice(['month' => '3'])->monthLabel('F'));
        $this->assertSame('Mar 2026', $this->invoice(['month' => 'Mar'])->monthLabel('M Y'));
        $this->assertNull($this->invoice(['month' => null])->monthLabel());
    }

    public function test_collection_page_shows_the_real_month_and_filters_by_it(): void
    {
        $this->actingAs($this->admin());
        $inv = $this->invoice(['month' => '3', 'invoice_no' => 'INV-MONTH-TEST']);

        $this->get('/fees/collection?search=INV-MONTH-TEST')->assertOk()->assertSee('Mar');
        // The search box echoes the text once; a matching table row adds a second occurrence.
        $count = fn ($q) => substr_count($this->get("/fees/collection?{$q}&search=INV-MONTH-TEST")->getContent(), 'INV-MONTH-TEST');
        $this->assertGreaterThan(1, $count('month=3'));
        $this->assertSame(1, $count('month=4'));
    }

    public function test_generating_the_same_month_twice_creates_no_duplicates(): void
    {
        $this->actingAs($this->admin());
        $structure = FeeStructure::whereIn('class_id', Student::where('is_active', true)->select('class_id'))->first();
        $params = ['class_id' => $structure->class_id, 'academic_year_id' => $structure->academic_year_id,
                   'fee_type_id' => $structure->fee_type_id, 'month' => 11];

        $this->post('/fees/generate-monthly', $params);
        $after1 = FeeCollection::where('fee_type_id', $structure->fee_type_id)->where('month', '11')->count();
        $this->post('/fees/generate-monthly', $params);
        $after2 = FeeCollection::where('fee_type_id', $structure->fee_type_id)->where('month', '11')->count();

        $this->assertSame($after1, $after2);
        $dupes = DB::table('fee_collections')->where('month', '11')->where('fee_type_id', $structure->fee_type_id)
            ->select('student_id', 'academic_year_id')->groupBy('student_id', 'academic_year_id')->havingRaw('COUNT(*) > 1')->count();
        $this->assertSame(0, $dupes);
    }
}
