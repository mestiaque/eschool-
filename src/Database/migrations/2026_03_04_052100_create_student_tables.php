<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Students Table
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique(); // Auto generated admission number
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('date_of_birth');
            $table->string('birth_certificate_no')->nullable();
            $table->string('religion');
            $table->string('blood_group')->nullable();
            $table->string('nationality')->default('Bangladeshi');
            $table->string('photo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('present_address');
            $table->text('permanent_address');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->string('group')->nullable(); // Science, Commerce, Arts
            $table->string('shift')->nullable(); // Morning, Day, Evening
            $table->string('roll_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->date('admission_date');
            $table->foreignId('transport_route_id')->nullable();
            $table->string('vehicle_id')->nullable();
            $table->boolean('is_hostel')->default(false);
            $table->string('room_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Guardians Table
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('father_name');
            $table->string('father_phone')->nullable();
            $table->string('father_nid')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_photo')->nullable();
            $table->string('mother_name');
            $table->string('mother_phone')->nullable();
            $table->string('mother_nid')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_photo')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation')->nullable();
            $table->string('guardian_phone');
            $table->string('guardian_nid')->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('guardian_photo')->nullable();
            $table->timestamps();
        });

        // Student Documents
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('file');
            $table->string('type'); // birth_certificate, photo, testimonial, etc.
            $table->timestamps();
        });

        // Student Promotions
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('to_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('from_section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->foreignId('to_section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->foreignId('from_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('to_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('from_roll')->nullable();
            $table->string('to_roll')->nullable();
            $table->boolean('is_promoted')->default(true);
            $table->timestamps();
        });

        // Student Transfers
        Schema::create('student_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('from_school')->nullable();
            $table->string('to_school')->nullable();
            $table->date('transfer_date');
            $table->text('reason')->nullable();
            $table->string('certificate_file')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // Student Attendances
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_attendances');
        Schema::dropIfExists('student_transfers');
        Schema::dropIfExists('student_promotions');
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('guardians');
        Schema::dropIfExists('students');
    }
};
