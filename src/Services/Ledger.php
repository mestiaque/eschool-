<?php

namespace ME\SchoolManagement\Services;

use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\Expense;
use ME\SchoolManagement\Models\ExpenseCategory;
use ME\SchoolManagement\Models\Income;
use ME\SchoolManagement\Models\IncomeCategory;

/**
 * Keeps money taken or spent in other modules (tour fees, library fines, tour
 * expenses) in the income / expense ledgers, so every report reads one place.
 * Each ledger row is tied to its source (source_type + source_id) and kept in
 * step with it; it can't be edited from the Income/Expense pages.
 */
class Ledger
{
    public const INCOME_CATEGORIES = ['tour_registration' => 'Tour Fees', 'book_issue' => 'Library Fines'];
    public const EXPENSE_CATEGORIES = ['tour_expense' => 'Tour Expenses'];

    public static function income(string $type, int $id, float $amount, $date, string $description): void
    {
        self::sync(Income::class, 'income_category_id', IncomeCategory::class, self::INCOME_CATEGORIES[$type], $type, $id, $amount, $date, $description);
    }

    public static function expense(string $type, int $id, float $amount, $date, string $description): void
    {
        self::sync(Expense::class, 'expense_category_id', ExpenseCategory::class, self::EXPENSE_CATEGORIES[$type], $type, $id, $amount, $date, $description);
    }

    public static function forget(string $type, int $id): void
    {
        Income::where(['source_type' => $type, 'source_id' => $id])->delete();
        Expense::where(['source_type' => $type, 'source_id' => $id])->delete();
    }

    /** Label for a ledger row that another module owns, or null. */
    public static function ownerLabel($row): ?string
    {
        if (!empty($row->salary_payment_id)) return 'Salary payments';
        return match ($row->source_type ?? null) {
            'tour_registration', 'tour_expense' => 'School Tours',
            'book_issue' => 'Library',
            default => null,
        };
    }

    private static function sync(string $model, string $catColumn, string $catModel, string $catName,
                                 string $type, int $id, float $amount, $date, string $description): void
    {
        $key = ['source_type' => $type, 'source_id' => $id];
        if ($amount <= 0) {
            $model::where($key)->delete();
            return;
        }
        $model::updateOrCreate($key, [
            $catColumn         => $catModel::firstOrCreate(['name' => $catName], ['is_active' => true])->id,
            'academic_year_id' => AcademicYear::where('is_current', true)->value('id') ?? AcademicYear::orderByDesc('id')->value('id'),
            'amount'           => round($amount, 2),
            'date'             => $date ?: now()->toDateString(),
            'description'      => $description,
        ]);
    }
}
