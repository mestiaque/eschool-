<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('class_id')->nullable()->after('student_id');
            $table->unsignedBigInteger('section_id')->nullable()->after('class_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropColumn(['class_id', 'section_id']);
        });
    }
};
