<?php

namespace ME\SchoolManagement\Services;

use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\Expense;
use ME\SchoolManagement\Models\ExpenseCategory;
use ME\SchoolManagement\Models\SalaryPayment;
use ME\SchoolManagement\Models\SchoolSetting;

/**
 * Posts a salary payment to the expense ledger, so salaries are counted once
 * (reports read expenses only). The category is chosen on Settings → General.
 */
class SalaryPoster
{
    public static function categoryId(): int
    {
        $id = SchoolSetting::get('salary_expense_category_id');
        if ($id && ExpenseCategory::whereKey($id)->exists()) return (int) $id;

        return ExpenseCategory::whereIn('name', ['বেতন ও ভাতা', 'Salaries', 'Salary'])->value('id')
            ?? ExpenseCategory::create(['name' => 'Salaries', 'is_active' => true])->id;
    }

    public static function post(SalaryPayment $payment): Expense
    {
        $month = is_numeric($payment->month) ? date('F', mktime(0, 0, 0, (int) $payment->month, 1)) : $payment->month;

        return Expense::create([
            'expense_category_id' => self::categoryId(),
            'academic_year_id'    => AcademicYear::where('is_current', true)->value('id') ?? AcademicYear::orderByDesc('id')->value('id'),
            'amount'              => $payment->net_salary,
            'date'                => $payment->payment_date ?? now(),
            'description'         => "Salary: {$payment->payee_name} — {$month} {$payment->year}",
            'vendor_name'         => $payment->payee_name,
            'salary_payment_id'   => $payment->id,
        ]);
    }
}
