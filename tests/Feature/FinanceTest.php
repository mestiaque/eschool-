<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\Expense;
use ME\SchoolManagement\Models\FeeCollection;
use ME\SchoolManagement\Models\FeePayment;
use ME\SchoolManagement\Models\FeeType;
use ME\SchoolManagement\Models\Product;
use ME\SchoolManagement\Models\SalaryPayment;
use ME\SchoolManagement\Models\Staff;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentDiscount;
use ME\SchoolManagement\Tests\TestCase;

class FinanceTest extends TestCase
{
    public function test_invoice_with_upfront_payment_records_a_payment(): void
    {
        $this->actingAs($this->admin());
        $student = Student::first();
        $this->post('/fees/collection', [
            'student_id' => $student->id, 'fee_type_id' => FeeType::first()->id, 'amount' => 1000, 'discount' => 0, 'paid_amount' => 400,
        ])->assertRedirect();
        $invoice = FeeCollection::latest('id')->first();
        $this->assertSame('partial', $invoice->status);
        $this->assertEquals(400, (float) FeePayment::where('fee_collection_id', $invoice->id)->sum('amount'));
    }

    public function test_overpayment_is_rejected(): void
    {
        $this->actingAs($this->admin());
        $invoice = FeeCollection::create([
            'student_id' => Student::first()->id, 'fee_type_id' => FeeType::first()->id, 'academic_year_id' => \ME\SchoolManagement\Models\AcademicYear::first()->id,
            'class_id' => Student::first()->class_id, 'amount' => 500, 'due_amount' => 500, 'status' => 'unpaid', 'invoice_no' => uniqid('T'),
        ]);
        $this->post("/fees/collection/{$invoice->id}/pay", ['paid_amount' => 900])->assertSessionHasErrors('paid_amount');
        $this->post("/fees/collection/{$invoice->id}/pay", ['paid_amount' => 500])->assertSessionHas('success');
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_blank_discount_applies_the_students_saved_discount(): void
    {
        $this->actingAs($this->admin());
        $student = Student::first();
        $type = FeeType::first();
        StudentDiscount::where('student_id', $student->id)->delete();
        StudentDiscount::create(['student_id' => $student->id, 'fee_type_id' => $type->id, 'discount_amount' => 10, 'discount_type' => 'percent', 'is_active' => true]);

        $this->post('/fees/collection', ['student_id' => $student->id, 'fee_type_id' => $type->id, 'amount' => 1000, 'paid_amount' => 0]);
        $this->assertEquals(100, (float) FeeCollection::latest('id')->value('discount'));
    }

    public function test_salary_payment_is_posted_to_expenses_once(): void
    {
        $this->actingAs($this->admin());
        $staff = Staff::first() ?? Staff::create(['first_name' => 'Test', 'designation' => 'guard']);
        $before = Expense::sum('amount');

        $this->post('/expenses/staff-salary', ['payee' => "staff:{$staff->id}", 'basic_salary' => 10000, 'allowance' => 500,
            'deduction' => 200, 'month' => 2, 'year' => 2031])->assertSessionHas('success');
        $payment = SalaryPayment::latest('id')->first();
        $this->assertEquals(10300, (float) $payment->net_salary);
        $this->assertEquals($before + 10300, (float) Expense::sum('amount'));
        $this->assertSame(1, Expense::where('salary_payment_id', $payment->id)->count());

        // Same person and month again is refused.
        $this->post('/expenses/staff-salary', ['payee' => "staff:{$staff->id}", 'basic_salary' => 10000, 'month' => 2, 'year' => 2031])
            ->assertSessionHas('error');
    }

    public function test_purchase_adds_stock_and_delete_takes_it_back(): void
    {
        $this->actingAs($this->admin());
        $product = Product::first();
        $qty = (int) $product->quantity;

        $this->post('/inventory/purchase', ['invoice_no' => uniqid('P'), 'purchase_date' => now()->toDateString(), 'paid_amount' => 0,
            'items' => [['product_id' => $product->id, 'quantity' => 7, 'unit_price' => 10]]])->assertSessionHas('success');
        $this->assertSame($qty + 7, (int) $product->fresh()->quantity);

        $purchase = \ME\SchoolManagement\Models\Purchase::latest('id')->first();
        $this->assertEquals(70, (float) $purchase->total_amount);
        $this->delete("/inventory/purchase/{$purchase->id}")->assertSessionHas('success');
        $this->assertSame($qty, (int) $product->fresh()->quantity);
    }

    public function test_stock_out_cannot_exceed_stock(): void
    {
        $this->actingAs($this->admin());
        $product = Product::first();
        $this->post('/inventory/stock', ['product_id' => $product->id, 'type' => 'out', 'quantity' => $product->quantity + 1, 'date' => now()->toDateString()])
            ->assertSessionHasErrors('quantity');
    }
}
