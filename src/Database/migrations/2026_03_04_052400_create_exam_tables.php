<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('exam_type_id')->constrained('exam_types')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('notice')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->decimal('full_mark', 5, 2);
            $table->decimal('pass_mark', 5, 2);
            $table->timestamps();
            $table->decimal('written_full', 5, 2)->nullable();
            $table->decimal('written_pass', 5, 2)->nullable();
            $table->decimal('mcq_full', 5, 2)->nullable();
            $table->decimal('mcq_pass', 5, 2)->nullable();
            $table->decimal('practical_full', 5, 2)->nullable();
            $table->decimal('practical_pass', 5, 2)->nullable();
            $table->unique(['exam_id', 'class_id', 'subject_id']);
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('grade_point', 3, 2);
            $table->decimal('mark_from', 5, 2);
            $table->decimal('mark_to', 5, 2);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->decimal('written', 5, 2)->nullable();
            $table->decimal('mcq', 5, 2)->nullable();
            $table->decimal('practical', 5, 2)->nullable();
            $table->decimal('ca', 5, 2)->nullable();
            $table->decimal('total_mark', 5, 2)->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->decimal('grade_point', 3, 2)->nullable();
            $table->boolean('is_absent')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'exam_id', 'subject_id']);
        });

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->decimal('total_mark', 8, 2)->nullable();
            $table->decimal('obtained_mark', 8, 2)->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->string('grade')->nullable();
            $table->integer('position')->nullable();
            $table->integer('section_position')->nullable();
            $table->boolean('is_published')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'exam_id']);
        });

        Schema::create('result_publishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_publishes');
        Schema::dropIfExists('results');
        Schema::dropIfExists('student_marks');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('exam_schedules');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_types');
    }
};
