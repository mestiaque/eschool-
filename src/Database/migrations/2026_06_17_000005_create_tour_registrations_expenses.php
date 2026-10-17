<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('school_tours')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('registration_date')->default(now());
            $table->decimal('fee', 10, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->boolean('is_approved')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['tour_id', 'student_id']);
        });

        Schema::create('tour_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('school_tours')->onDelete('cascade');
            $table->string('category'); // bus_fare, venue_rent, food, accommodation, entry_fee, other
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
