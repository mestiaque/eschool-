<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_test_id')->constrained('class_tests')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->decimal('obtained_marks', 8, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->enum('status', ['pass', 'fail', 'absent'])->default('absent');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['class_test_id', 'student_id']);
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('status', ['submitted', 'late', 'not_submitted'])->default('not_submitted');
            $table->decimal('obtained_marks', 8, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->date('submitted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('class_test_results');
    }
};
