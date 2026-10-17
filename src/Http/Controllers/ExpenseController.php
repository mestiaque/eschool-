<?php

namespace ME\SchoolManagement\Http\Controllers;
use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Http\Controllers\Concerns\GuardsDeletes;

use Illuminate\Http\Request;
use ME\SchoolManagement\Models\ExpenseCategory;
use ME\SchoolManagement\Models\Expense;
use ME\SchoolManagement\Models\IncomeCategory;
use ME\SchoolManagement\Models\Income;
use ME\SchoolManagement\Models\AcademicYear;

class ExpenseController extends Controller
{
    use GuardsDeletes;

    // Expense Categories
    public function categoryIndex()
    {
        $categories = ExpenseCategory::orderBy('id', 'desc')->paginate(20);
        return view('school-management::expenses.categories', compact('categories'));
    }

    public function categoryStore(Request $request)
    {
        ExpenseCategory::create($this->validateCategory($request));
        return back()->with('success', 'Category created');
    }

    public function categoryDestroy($id)
    {
        $cat = ExpenseCategory::findOrFail($id);
        return $this->deleteUnlessUsed($cat, [
            'expenses' => $this->uses('expenses', 'expense_category_id', $id),
        ], $cat->name, 'Category deleted');
    }

    // Expenses
    public function expenseIndex(Request $request)
    {
        $query = Expense::with('expenseCategory');
        if ($request->category_id) $query->where('expense_category_id', $request->category_id);
        if ($request->from_date) $query->whereDate('date', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('date', '<=', $request->to_date);

        $expenses = $query->orderBy('id', 'desc')->paginate(20);
        $categories = ExpenseCategory::where('is_active', true)->get();

        return view('school-management::expenses.index', compact('expenses', 'categories'));
    }

    public function expenseStore(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount'              => 'required|numeric|min:0.01',
            'date'                => 'required|date',
        ]);
        if (!$yearId = $this->currentAcademicYearId()) {
            return back()->withInput()->with('error', 'Create an academic year first.');
        }
        Expense::create([
            'expense_category_id' => $request->expense_category_id,
            'academic_year_id'    => $yearId,
            'amount'              => $request->amount,
            'date'                => $request->date,
            'description'         => $request->description,
            'vendor_name'         => $request->vendor_name,
        ]);
        return back()->with('success', 'Expense added');
    }

    public function expenseUpdate(Request $request, $id)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount'              => 'required|numeric|min:0.01',
            'date'                => 'required|date',
        ]);
        Expense::findOrFail($id)->update([
            'expense_category_id' => $request->expense_category_id,
            'amount'              => $request->amount,
            'date'                => $request->date,
            'description'         => $request->description,
            'vendor_name'         => $request->vendor_name,
        ]);
        return back()->with('success', 'Expense updated');
    }

    public function expenseDestroy($id)
    {
        $expense = Expense::findOrFail($id);
        if ($expense->salary_payment_id) {
            return back()->with('error', 'This expense was posted by a salary payment and cannot be deleted here.');
        }
        $this->logDelete($expense, 'Expense ' . number_format($expense->amount, 2), fn () => $expense->delete());
        return back()->with('success', 'Expense deleted');
    }

    // Income Categories
    public function incomeCategoryIndex()
    {
        $categories = IncomeCategory::orderBy('id', 'desc')->paginate(20);
        return view('school-management::expenses.income-categories', compact('categories'));
    }

    public function incomeCategoryStore(Request $request)
    {
        IncomeCategory::create($this->validateCategory($request));
        return back()->with('success', 'Income category created');
    }

    public function incomeCategoryUpdate(Request $request, $id)
    {
        IncomeCategory::findOrFail($id)->update($this->validateCategory($request));
        return back()->with('success', 'Income category updated');
    }

    public function incomeCategoryDestroy($id)
    {
        $cat = IncomeCategory::findOrFail($id);
        return $this->deleteUnlessUsed($cat, [
            'income entries' => $this->uses('incomes', 'income_category_id', $id),
        ], $cat->name, 'Income category deleted');
    }

    // Income
    public function incomeIndex(Request $request)
    {
        $query = Income::with('incomeCategory');
        if ($request->category_id) $query->where('income_category_id', $request->category_id);
        if ($request->from_date) $query->whereDate('date', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('date', '<=', $request->to_date);

        $incomes = $query->orderBy('id', 'desc')->paginate(20);
        $incomeCategories = IncomeCategory::where('is_active', true)->get();

        return view('school-management::expenses.income', compact('incomes', 'incomeCategories'));
    }

    public function incomeStore(Request $request)
    {
        $request->validate([
            'income_category_id' => 'required|exists:income_categories,id',
            'amount'             => 'required|numeric|min:0.01',
            'date'               => 'required|date',
        ]);
        if (!$yearId = $this->currentAcademicYearId()) {
            return back()->withInput()->with('error', 'Create an academic year first.');
        }
        Income::create([
            'income_category_id' => $request->income_category_id,
            'academic_year_id'   => $yearId,
            'amount'             => $request->amount,
            'date'               => $request->date,
            'description'        => $request->description,
        ]);
        return back()->with('success', 'Income added');
    }

    public function incomeUpdate(Request $request, $id)
    {
        $request->validate([
            'income_category_id' => 'required|exists:income_categories,id',
            'amount'             => 'required|numeric|min:0.01',
            'date'               => 'required|date',
        ]);
        Income::findOrFail($id)->update([
            'income_category_id' => $request->income_category_id,
            'amount'             => $request->amount,
            'date'               => $request->date,
            'description'        => $request->description,
        ]);
        return back()->with('success', 'Income updated');
    }

    public function incomeDestroy($id)
    {
        $income = Income::findOrFail($id);
        $this->logDelete($income, 'Income ' . number_format($income->amount, 2), fn () => $income->delete());
        return back()->with('success', 'Income deleted');
    }

    private function validateCategory(Request $request): array
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        return $data;
    }

    private function currentAcademicYearId()
    {
        return AcademicYear::where('is_current', true)->value('id')
            ?? AcademicYear::orderBy('id', 'desc')->value('id');
    }

    // Reports
    public function report(Request $request)
    {
        $expenseQuery = Expense::query();
        $incomeQuery = Income::query();

        if ($request->from_date) {
            $expenseQuery->whereDate('date', '>=', $request->from_date);
            $incomeQuery->whereDate('date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $expenseQuery->whereDate('date', '<=', $request->to_date);
            $incomeQuery->whereDate('date', '<=', $request->to_date);
        }

        $totalExpense = $expenseQuery->sum('amount');
        $totalIncome = $incomeQuery->sum('amount');
        $profit = $totalIncome - $totalExpense;

        $expenses = $expenseQuery->get();
        $incomes = $incomeQuery->get();

        return view('school-management::expenses.report', compact('expenses', 'incomes', 'totalExpense', 'totalIncome', 'profit'));
    }

    public function reportPrint(Request $request)
    {
        $expenseQuery = Expense::query();
        $incomeQuery = Income::query();

        if ($request->from_date) {
            $expenseQuery->whereDate('date', '>=', $request->from_date);
            $incomeQuery->whereDate('date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $expenseQuery->whereDate('date', '<=', $request->to_date);
            $incomeQuery->whereDate('date', '<=', $request->to_date);
        }

        $totalExpense = $expenseQuery->sum('amount');
        $totalIncome = $incomeQuery->sum('amount');
        $profit = $totalIncome - $totalExpense;

        $expenses = $expenseQuery->get();
        $incomes = $incomeQuery->get();

        return view('school-management::expenses.report-print', compact('expenses', 'incomes', 'totalExpense', 'totalIncome', 'profit'));
    }

    // Staff Salary
    public function staffSalaryIndex(Request $request)
    {
        $query = \ME\SchoolManagement\Models\SalaryPayment::with(['teacher', 'staffMember'])->orderBy('id', 'desc');
        if ($request->staff_type) $query->where('staff_type', $request->staff_type);
        if ($request->month) $query->where('month', $request->month);
        if ($request->year) $query->where('year', $request->year);
        $payments = $query->paginate(20)->withQueryString();
        $teachers = \ME\SchoolManagement\Models\Teacher::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'salary']);
        $staff    = \ME\SchoolManagement\Models\Staff::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'designation', 'salary']);
        return view('school-management::expenses.staff-salary', compact('payments', 'teachers', 'staff'));
    }

    public function staffSalaryStore(Request $request)
    {
        $request->validate([
            'payee'        => ['required', 'regex:/^(teacher|staff):\d+$/'],
            'basic_salary' => 'required|numeric|min:0',
            'allowance'    => 'nullable|numeric|min:0',
            'deduction'    => 'nullable|numeric|min:0',
            'month'        => 'required|integer|min:1|max:12',
            'year'         => 'required|integer|min:2000|max:2100',
            'payment_date' => 'nullable|date',
        ], ['payee.required' => 'Choose who is being paid.']);

        [$type, $id] = explode(':', $request->payee);
        $exists = $type === 'teacher'
            ? \ME\SchoolManagement\Models\Teacher::whereKey($id)->exists()
            : \ME\SchoolManagement\Models\Staff::whereKey($id)->exists();
        if (!$exists) return back()->withInput()->with('error', 'That person no longer exists.');

        $alreadyPaid = \ME\SchoolManagement\Models\SalaryPayment::where('staff_type', $type)->where('staff_id', $id)
            ->where('month', $request->month)->where('year', $request->year)->exists();
        if ($alreadyPaid) {
            return back()->withInput()->with('error', 'Salary for this person and month has already been paid.');
        }

        $allowance = (float) ($request->allowance ?? 0);
        $deduction = (float) ($request->deduction ?? 0);
        $gross     = (float) $request->basic_salary + $allowance;

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $type, $id, $allowance, $deduction, $gross) {
            $payment = \ME\SchoolManagement\Models\SalaryPayment::create([
                'staff_id'     => $id,
                'staff_type'   => $type,
                'basic_salary' => $request->basic_salary,
                'allowance'    => $allowance,
                'deduction'    => $deduction,
                'gross_salary' => $gross,
                'net_salary'   => $gross - $deduction,
                'month'        => $request->month,
                'year'         => $request->year,
                'payment_date' => $request->payment_date ?? now(),
            ]);
            \ME\SchoolManagement\Services\SalaryPoster::post($payment);
        });

        return back()->with('success', 'Salary payment recorded and posted to expenses');
    }

    // Profit/Loss
    public function profitLossIndex()
    {
        // Fees count as income. Salaries are already in expenses (each payment posts one).
        $incomes  = Income::sum('amount') + \ME\SchoolManagement\Models\FeePayment::sum('amount');
        $expenses = Expense::sum('amount');
        $profit   = $incomes - $expenses;
        return view('school-management::expenses.profit-loss', compact('expenses', 'incomes', 'profit'));
    }
}
