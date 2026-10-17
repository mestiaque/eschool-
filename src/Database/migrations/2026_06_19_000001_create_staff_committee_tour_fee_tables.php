<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Non-Teaching Staff ─────────────────────────────────────────────
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('staff_id')->nullable()->unique();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->enum('gender', ['male','female','other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('photo')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('nid')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            // Designation: pion, cleaner, aya, night_guard, guard, cook, driver, other
            $table->string('designation');
            $table->date('joining_date')->nullable();
            $table->date('leaving_date')->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('relationship')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Managing Committee ─────────────────────────────────────────────
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // e.g. "পরিচালনা কমিটি ২০২৪"
            $table->string('academic_year')->nullable();
            $table->date('formed_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained('committees')->cascadeOnDelete();
            $table->string('name');
            $table->string('role');           // chairperson, vice_chairperson, secretary, member, etc.
            $table->integer('serial')->default(0);  // display order
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->string('profession')->nullable();   // teacher, guardian, govt_rep, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Tour Fee Master (per tour, per category) ───────────────────────
        Schema::create('tour_fee_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('school_tours')->cascadeOnDelete();
            $table->string('category_type');      // student_class, teacher, staff, management, custom
            $table->string('category_label');     // "Class 6", "Senior Teacher", "Staff", etc.
            $table->unsignedBigInteger('class_id')->nullable();  // for class-specific fees
            $table->decimal('fee', 10, 2)->default(0);
            $table->integer('serial')->default(0);
            $table->timestamps();
        });

        // Add participant_type to tour_registrations
        Schema::table('tour_registrations', function (Blueprint $table) {
            $table->string('participant_type')->default('student')->after('student_id');  // student, teacher, staff, management
            $table->unsignedBigInteger('staff_id')->nullable()->after('participant_type');
            $table->unsignedBigInteger('teacher_id')->nullable()->after('staff_id');
            $table->unsignedBigInteger('tour_fee_category_id')->nullable()->after('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('tour_registrations', function (Blueprint $table) {
            $table->dropColumn(['participant_type','staff_id','teacher_id','tour_fee_category_id']);
        });
        Schema::dropIfExists('tour_fee_categories');
        Schema::dropIfExists('committee_members');
        Schema::dropIfExists('committees');
        Schema::dropIfExists('staff');
    }
};
