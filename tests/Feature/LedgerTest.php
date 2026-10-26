<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\BookIssue;
use ME\SchoolManagement\Models\Expense;
use ME\SchoolManagement\Models\Income;
use ME\SchoolManagement\Models\SchoolTour;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\TourRegistration;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 4.4 — tour fees, tour expenses and library fines reach the ledgers. */
class LedgerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->admin());
    }

    private function registration(): TourRegistration
    {
        return TourRegistration::create(['tour_id' => SchoolTour::first()->id, 'student_id' => Student::first()->id,
            'participant_type' => 'student', 'fee' => 1000, 'paid_amount' => 0, 'payment_status' => 'unpaid', 'registration_date' => now()]);
    }

    public function test_tour_payment_is_income_and_follows_changes(): void
    {
        $reg = $this->registration();
        $url = "/academic/tours/{$reg->tour_id}/registrations/{$reg->id}";

        $this->patch($url, ['fee' => 1000, 'paid_amount' => 400]);
        $this->assertEquals(400, (float) Income::where(['source_type' => 'tour_registration', 'source_id' => $reg->id])->value('amount'));

        $this->patch($url, ['fee' => 1000, 'paid_amount' => 1000]); // second instalment updates the same row
        $this->assertSame(1, Income::where(['source_type' => 'tour_registration', 'source_id' => $reg->id])->count());
        $this->assertEquals(1000, (float) Income::where(['source_type' => 'tour_registration', 'source_id' => $reg->id])->value('amount'));

        $this->delete($url);
        $this->assertSame(0, Income::where(['source_type' => 'tour_registration', 'source_id' => $reg->id])->count());
    }

    public function test_tour_expense_is_an_expense(): void
    {
        $tour = SchoolTour::first();
        $this->post("/academic/tours/{$tour->id}/expenses", ['category' => 'bus_fare', 'description' => 'Bus', 'amount' => 2500, 'expense_date' => '2026-03-01']);
        $row = Expense::where('source_type', 'tour_expense')->latest('id')->first();
        $this->assertEquals(2500, (float) $row->amount);

        $this->delete("/academic/tours/{$tour->id}/expenses/{$row->source_id}");
        $this->assertNull(Expense::find($row->id));
    }

    public function test_library_fine_is_income_and_waiving_keeps_what_was_paid(): void
    {
        $issue = BookIssue::first();
        $issue->update(['status' => 'returned', 'fine_amount' => 100, 'fine_paid' => 0, 'fine_status' => 'pending']);

        $this->post("/library/issues/{$issue->id}/pay-fine", ['fine_paid' => 40]);
        $this->post("/library/issues/{$issue->id}/waive-fine");

        $this->assertEquals(40, (float) $issue->fresh()->fine_paid);
        $this->assertSame('waived', $issue->fresh()->fine_status);
        $this->assertEquals(40, (float) Income::where(['source_type' => 'book_issue', 'source_id' => $issue->id])->value('amount'));
    }

    public function test_linked_ledger_rows_cannot_be_edited_from_income_page(): void
    {
        $reg = $this->registration();
        $this->patch("/academic/tours/{$reg->tour_id}/registrations/{$reg->id}", ['fee' => 1000, 'paid_amount' => 300]);
        $row = Income::where(['source_type' => 'tour_registration', 'source_id' => $reg->id])->first();

        $this->delete("/expenses/income/{$row->id}")->assertSessionHas('error');
        $this->put("/expenses/income/{$row->id}", ['income_category_id' => $row->income_category_id, 'amount' => 1, 'date' => '2026-01-01'])
            ->assertSessionHas('error');
        $this->assertEquals(300, (float) $row->fresh()->amount);
    }

    public function test_tour_income_shows_in_profit_and_loss(): void
    {
        $before = (float) Income::sum('amount');
        $reg = $this->registration();
        $this->patch("/academic/tours/{$reg->tour_id}/registrations/{$reg->id}", ['fee' => 1000, 'paid_amount' => 700]);
        $this->get('/expenses/profit-loss')->assertOk()->assertViewHas('incomes', fn ($v) => (float) $v >= $before + 700);
    }
}
