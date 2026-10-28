<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('exam_schedules', 'written_full')) return;
        Schema::table('exam_schedules', function (Blueprint $t) {
            foreach (['written', 'mcq', 'practical'] as $part) {
                $t->decimal("{$part}_full", 5, 2)->nullable();
                $t->decimal("{$part}_pass", 5, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_schedules', fn (Blueprint $t) => $t->dropColumn(['written_full', 'written_pass', 'mcq_full', 'mcq_pass', 'practical_full', 'practical_pass']));
    }
};
