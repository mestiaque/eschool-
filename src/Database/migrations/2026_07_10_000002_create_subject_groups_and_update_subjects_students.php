<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Subject Groups table
        Schema::create('subject_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Add type and group_id to subjects
        Schema::table('subjects', function (Blueprint $table) {
            $table->enum('type', ['compulsory', 'group', 'optional'])->default('compulsory')->after('name');
            $table->foreignId('group_id')->nullable()->constrained('subject_groups')->onDelete('set null')->after('type');
        });

        // 3. Replace string 'group' with subject_group_id FK and optional_subject_id on students
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('subject_group_id')->nullable()->constrained('subject_groups')->onDelete('set null')->after('section_id');
            $table->foreignId('optional_subject_id')->nullable()->constrained('subjects')->onDelete('set null')->after('subject_group_id');
            $table->dropColumn('group');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['subject_group_id']);
            $table->dropForeign(['optional_subject_id']);
            $table->dropColumn(['subject_group_id', 'optional_subject_id']);
            $table->string('group')->nullable();
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn(['type', 'group_id']);
        });

        Schema::dropIfExists('subject_groups');
    }
};
