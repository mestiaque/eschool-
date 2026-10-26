<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Older invoices stored the month as a name ("January"), newer ones as a
    // number; pages, filters and the duplicate check all expect the number.
    public function up(): void
    {
        foreach (range(1, 12) as $m) {
            $name = date('F', mktime(0, 0, 0, $m, 1));
            DB::table('fee_collections')
                ->whereIn('month', [$name, strtolower($name), substr($name, 0, 3), strtolower(substr($name, 0, 3))])
                ->update(['month' => (string) $m]);
        }
    }

    public function down(): void
    {
        // Numbers carry the same information; nothing to undo.
    }
};
