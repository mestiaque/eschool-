<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('school_tours')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('participant_type')->default('student');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->unsignedBigInteger('tour_fee_category_id')->nullable();
            $table->date('registration_date')->default(DB::raw('(CURRENT_DATE)'));
            $table->decimal('fee', 10, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->boolean('is_approved')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['tour_id', 'student_id']);
        });

        Schema::create('tour_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('school_tours')->cascadeOnDelete();
            $table->string('category');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('paid_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_expenses');
        Schema::dropIfExists('tour_registrations');
    }
};
