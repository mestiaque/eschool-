<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "holiday" closes a date; "school_day" opens one (e.g. a make-up Saturday).
        if (!Schema::hasColumn('holidays', 'type')) {
            Schema::table('holidays', fn (Blueprint $t) => $t->string('type', 20)->default('holiday')->after('date'));
        }

        // Weekly holidays used to be hard-coded as Friday + Saturday. Keep that
        // national default, but a school whose routine runs on Saturday is
        // clearly open then, so start it with Friday only.
        if (!DB::table('school_settings')->where('key', 'weekend_days')->exists()) {
            $saturdayClasses = Schema::hasTable('class_routines') && DB::table('class_routines')->where('day', 'Saturday')->exists();
            DB::table('school_settings')->insert([
                'key' => 'weekend_days', 'value' => $saturdayClasses ? '5' : '5,6', 'type' => 'text',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            \Illuminate\Support\Facades\Cache::forget('school-management.settings');
        }
    }

    public function down(): void
    {
        Schema::table('holidays', fn (Blueprint $t) => $t->dropColumn('type'));
        DB::table('school_settings')->where('key', 'weekend_days')->delete();
    }
};
