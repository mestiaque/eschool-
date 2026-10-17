<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Teachers Table
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('date_of_birth');
            $table->string('religion');
            $table->string('blood_group')->nullable();
            $table->string('nationality')->default('Bangladeshi');
            $table->string('photo')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('present_address');
            $table->text('permanent_address');
            $table->string('nid')->nullable();
            $table->string('joining_date');
            $table->string('designation')->nullable();
            $table->string('qualification')->nullable();
            $table->string('experience')->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Teacher Subjects
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['teacher_id', 'subject_id', 'class_id']);
        });

        // Teacher Classes (Class Teacher)
        Schema::create('teacher_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_class_teacher')->default(false);
            $table->timestamps();
            $table->unique(['class_id', 'section_id', 'is_class_teacher']);
        });

        // Teacher Attendances
        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'date']);
        });

        // Teacher Leaves
        Schema::create('teacher_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Teacher Documents
        Schema::create('teacher_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('file');
            $table->string('type');
            $table->timestamps();
        });

        // Leave Types
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teacher_documents');
        Schema::dropIfExists('teacher_leaves');
        Schema::dropIfExists('teacher_classes');
        Schema::dropIfExists('teacher_subjects');
        Schema::dropIfExists('teacher_attendances');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('teachers');
    }
};
