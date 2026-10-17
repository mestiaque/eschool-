<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Portal PINs were stored as plain text. Widen the column and hash them;
    // students keep logging in with the same PIN.
    public function up(): void
    {
        Schema::table('students', fn (Blueprint $t) => $t->string('portal_pin', 255)->nullable()->change());

        DB::table('students')->whereNotNull('portal_pin')->where('portal_pin', 'not like', '$2y$%')
            ->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('students')->where('id', $row->id)->update(['portal_pin' => Hash::make($row->portal_pin)]);
                }
            });
    }

    public function down(): void
    {
        // Hashes can't be turned back into PINs.
    }
};
