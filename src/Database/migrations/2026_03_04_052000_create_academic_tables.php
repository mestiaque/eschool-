<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Academic Years
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        // Classes
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('numeric_name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Sections
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('room_number')->nullable();
            $table->string('teacher_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Subjects
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->string('teacher_id')->nullable();
            $table->decimal('pass_mark', 5, 2)->default(33.00);
            $table->decimal('full_mark', 5, 2)->default(100.00);
            $table->decimal('credit_hour', 3, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Class Routines
        Schema::create('class_routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('day'); // Monday, Tuesday, etc.
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->timestamps();
        });

        // Syllabi
        Schema::create('syllabi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->text('content')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });

        // School Tours
        Schema::create('school_tours', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('destination');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('budget', 10, 2)->default(0);
            $table->string('organizer')->nullable();
            $table->string('contact_phone')->nullable();
            $table->enum('status', ['planning', 'approved', 'ongoing', 'completed', 'cancelled'])->default('planning');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Class Texts (Curriculum)
        Schema::create('class_texts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->text('content')->nullable();
            $table->string('file')->nullable();
            $table->string('edition')->nullable();
            $table->string('author')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('syllabi');
        Schema::dropIfExists('class_routines');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('academic_years');
    }
};
