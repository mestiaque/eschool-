<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Class Tests Table
        Schema::create('class_tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('academic_year_id')->nullable()->constrained()->onDelete('set null');
            $table->date('test_date');
            $table->decimal('total_marks', 8, 2)->default(100);
            $table->decimal('pass_marks', 8, 2)->default(33);
            $table->string('duration')->nullable(); // e.g. "30 min", "1 hour"
            $table->text('description')->nullable();
            $table->string('file')->nullable();
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Assignments Table
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('academic_year_id')->nullable()->constrained()->onDelete('set null');
            $table->date('assigned_date');
            $table->date('due_date');
            $table->decimal('total_marks', 8, 2)->default(100);
            $table->string('file')->nullable();
            $table->enum('status', ['active', 'closed', 'cancelled'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('class_tests');
    }
};
