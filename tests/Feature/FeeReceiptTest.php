<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\FeeCollection;
use ME\SchoolManagement\Models\FeePayment;
use ME\SchoolManagement\Models\FeeType;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Tests\TestCase;

/** Money receipt for each payment and a printable invoice. */
class FeeReceiptTest extends TestCase
{
    public function test_each_payment_gets_a_receipt_showing_due_at_that_time(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $student = Student::first();
        $fee = FeeCollection::create(['student_id' => $student->id, 'fee_type_id' => FeeType::value('id'), 'class_id' => $student->class_id, 'academic_year_id' => $student->academic_year_id,
            'month' => 3, 'year' => 2026, 'amount' => 1500, 'fine' => 0, 'discount' => 0, 'paid_amount' => 0, 'due_amount' => 1500,
            'status' => 'unpaid', 'invoice_no' => 'INV-T-' . uniqid()]);

        $this->post("/fees/collection/{$fee->id}/pay", ['paid_amount' => 1000, 'payment_date' => '2026-03-05', 'payment_method' => 'cash'])
            ->assertSessionHas('receipt_url');
        $this->post("/fees/collection/{$fee->id}/pay", ['paid_amount' => 500, 'payment_date' => '2026-03-20']);
        [$first, $second] = FeePayment::where('fee_collection_id', $fee->id)->orderBy('id')->get();
        $this->assertSame($admin->id, (int) $first->collected_by);

        $r1 = $this->get(route('fees.receipt', $first->id))->assertOk();
        $r1->assertViewHas('before', 0)->assertSee('Student Copy')->assertSee('Office Copy');
        $this->assertStringContainsString(e(sm_amount_words(1000)), $r1->getContent());
        $this->assertStringContainsString(sm_money(500, 2), $r1->getContent()); // due after the first payment

        $this->get(route('fees.receipt', $second->id))->assertOk()->assertViewHas('before', fn ($b) => (float) $b === 1000.0);

        $inv = $this->get(route('fees.collection.print', $fee->id))->assertOk()->getContent();
        $this->assertStringContainsString('MR-' . str_pad($first->id, 6, '0', STR_PAD_LEFT), $inv);
        $this->assertStringContainsString('MR-' . str_pad($second->id, 6, '0', STR_PAD_LEFT), $inv);
    }

    public function test_amount_in_words(): void
    {
        $this->assertSame('বারো হাজার পাঁচশত পঞ্চাশ টাকা মাত্র', sm_amount_words(12550, 'bn'));
        $this->assertSame('One Lakh Twenty Five Thousand Taka Only', sm_amount_words(125000, 'en'));
        $this->assertSame('দুই কোটি পঞ্চাশ লক্ষ টাকা মাত্র', sm_amount_words(25000000, 'bn'));
    }
}
