<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Exam Types
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Monthly, Mid-term, Final
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Exams
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('exam_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('notice')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Exam Schedules
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->decimal('full_mark', 5, 2);
            $table->decimal('pass_mark', 5, 2);
            $table->timestamps();
            $table->unique(['exam_id', 'class_id', 'subject_id']);
        });

        // Grades
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // A+, A, A-, B+, B, C, D, F
            $table->decimal('grade_point', 3, 2);
            $table->decimal('mark_from', 5, 2);
            $table->decimal('mark_to', 5, 2);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Student Marks
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->decimal('written', 5, 2)->nullable();
            $table->decimal('mcq', 5, 2)->nullable();
            $table->decimal('practical', 5, 2)->nullable();
            $table->decimal('ca', 5, 2)->nullable();
            $table->decimal('total_mark', 5, 2)->nullable();
            $table->foreignId('grade_id')->nullable();
            $table->decimal('grade_point', 3, 2)->nullable();
            $table->boolean('is_absent')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'exam_id', 'subject_id']);
        });

        // Results
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('total_mark', 8, 2)->nullable();
            $table->decimal('obtained_mark', 8, 2)->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->string('grade')->nullable();
            $table->integer('position')->nullable();
            $table->boolean('is_published')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'exam_id']);
        });

        // Result Publishes
        Schema::create('result_publishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'class_id']);
        });
    }

    public function down()
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
