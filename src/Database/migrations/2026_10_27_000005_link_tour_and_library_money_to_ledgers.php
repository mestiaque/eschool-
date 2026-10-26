<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['incomes', 'expenses'] as $table) {
            if (!Schema::hasColumn($table, 'source_type')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('source_type', 40)->nullable();
                    $t->unsignedBigInteger('source_id')->nullable();
                    $t->unique(['source_type', 'source_id']);
                });
            }
        }

        // Registrations marked paid with a fee of 0: the amount paid is the fee.
        DB::table('tour_registrations')->where('fee', 0)->where('paid_amount', '>', 0)->update(['fee' => DB::raw('paid_amount')]);

        // Bring money already recorded in tours and the library into the ledgers.
        $yearId = DB::table('academic_years')->where('is_current', true)->value('id') ?? DB::table('academic_years')->max('id');
        if (!$yearId) return;
        $category = function (string $table, string $name) {
            return DB::table($table)->where('name', $name)->value('id')
                ?? DB::table($table)->insertGetId(['name' => $name, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        };

        $regs = DB::table('tour_registrations as r')->join('school_tours as t', 't.id', '=', 'r.tour_id')
            ->where('r.paid_amount', '>', 0)->select('r.id', 'r.paid_amount', 'r.updated_at', 't.title')->get();
        if ($regs->isNotEmpty()) {
            $cat = $category('income_categories', 'Tour Fees');
            foreach ($regs as $r) {
                DB::table('incomes')->updateOrInsert(['source_type' => 'tour_registration', 'source_id' => $r->id], [
                    'income_category_id' => $cat, 'academic_year_id' => $yearId, 'amount' => $r->paid_amount,
                    'date' => substr($r->updated_at ?? now(), 0, 10), 'description' => "Tour fee: {$r->title}",
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        $exps = DB::table('tour_expenses as e')->join('school_tours as t', 't.id', '=', 'e.tour_id')
            ->select('e.id', 'e.amount', 'e.expense_date', 'e.description', 't.title')->get();
        if ($exps->isNotEmpty()) {
            $cat = $category('expense_categories', 'Tour Expenses');
            foreach ($exps as $e) {
                DB::table('expenses')->updateOrInsert(['source_type' => 'tour_expense', 'source_id' => $e->id], [
                    'expense_category_id' => $cat, 'academic_year_id' => $yearId, 'amount' => $e->amount,
                    'date' => $e->expense_date, 'description' => "{$e->title}: {$e->description}",
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        $fines = DB::table('book_issues')->where('fine_paid', '>', 0)->get(['id', 'fine_paid', 'updated_at']);
        if ($fines->isNotEmpty()) {
            $cat = $category('income_categories', 'Library Fines');
            foreach ($fines as $f) {
                DB::table('incomes')->updateOrInsert(['source_type' => 'book_issue', 'source_id' => $f->id], [
                    'income_category_id' => $cat, 'academic_year_id' => $yearId, 'amount' => $f->fine_paid,
                    'date' => substr($f->updated_at ?? now(), 0, 10), 'description' => "Library fine (issue #{$f->id})",
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('incomes')->whereNotNull('source_type')->delete();
        DB::table('expenses')->whereNotNull('source_type')->delete();
        foreach (['incomes', 'expenses'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropUnique(['source_type', 'source_id']);
                $t->dropColumn(['source_type', 'source_id']);
            });
        }
    }
};
