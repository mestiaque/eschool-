<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_routines', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('subject_id')->constrained('teachers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('class_routines', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
};
