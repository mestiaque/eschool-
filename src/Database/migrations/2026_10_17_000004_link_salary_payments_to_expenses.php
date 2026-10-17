<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Salaries are counted once: each salary payment posts one linked expense.
        if (!Schema::hasColumn('expenses', 'salary_payment_id')) {
            Schema::table('expenses', function (Blueprint $t) {
                $t->foreignId('salary_payment_id')->nullable()->unique()->after('vendor_name')
                    ->constrained('salary_payments')->nullOnDelete();
            });
        }

        // Months were saved both as numbers ("3") and names ("March"); store numbers.
        foreach (range(1, 12) as $m) {
            $name = date('F', mktime(0, 0, 0, $m, 1));
            DB::table('salary_payments')->whereIn('month', [$name, substr($name, 0, 3)])->update(['month' => (string) $m]);
        }
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $t) {
            $t->dropConstrainedForeignId('salary_payment_id');
        });
    }
};
