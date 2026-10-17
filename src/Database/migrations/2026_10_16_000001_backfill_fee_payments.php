<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Older code never recorded the payment taken when an invoice was
     * created, so fee_payments is missing rows. Add one payment for whatever
     * part of each invoice's paid_amount isn't already in the ledger.
     */
    public function up(): void
    {
        DB::table('fee_collections')->where('paid_amount', '>', 0)->orderBy('id')
            ->chunkById(200, function ($collections) {
                $logged = DB::table('fee_payments')
                    ->whereIn('fee_collection_id', $collections->pluck('id'))
                    ->groupBy('fee_collection_id')
                    ->pluck(DB::raw('SUM(amount)'), 'fee_collection_id');

                $rows = [];
                foreach ($collections as $c) {
                    $missing = round((float) $c->paid_amount - (float) ($logged[$c->id] ?? 0), 2);
                    if ($missing <= 0) continue;
                    $rows[] = [
                        'fee_collection_id' => $c->id,
                        'amount'            => $missing,
                        'date'              => $c->payment_date ?? substr($c->updated_at ?? $c->created_at ?? now(), 0, 10),
                        'payment_method'    => $c->payment_method,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
                if ($rows) DB::table('fee_payments')->insert($rows);
            });
    }

    public function down(): void
    {
        // Backfilled rows are indistinguishable from real payments; nothing to undo safely.
    }
};
