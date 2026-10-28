<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->date('publish_date');
            $table->date('end_date')->nullable();
            $table->string('type');
            $table->string('for');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('file')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('school_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->string('photo')->nullable();
            $table->string('for')->default('all');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->string('type', 20)->default('holiday');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('book_id');
            $table->string('isbn')->nullable();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('edition')->nullable();
            $table->string('language')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('rack_no', 50)->nullable();
            $table->string('type');
            $table->decimal('price', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('book_id');
        });

        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->string('member_type')->default('student');
            $table->unsignedBigInteger('book_copy_id')->nullable();
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->cascadeOnDelete();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine_amount', 10, 2)->default(0.00);
            $table->decimal('fine_paid', 10, 2)->default(0.00);
            $table->enum('fine_status', ['none', 'pending', 'paid', 'waived'])->default('none');
            $table->boolean('is_lost')->default(false);
            $table->text('return_note')->nullable();
            $table->enum('status', ['issued', 'returned', 'lost', 'damaged'])->default('issued');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('library_members', function (Blueprint $table) {
            $table->id();
            $table->string('member_type');
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->cascadeOnDelete();
            $table->date('membership_date');
            $table->date('expire_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number');
            $table->string('model')->nullable();
            $table->string('type');
            $table->string('capacity');
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('driver_license')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vehicle_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('library_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('fine_type', ['fixed', 'per_day'])->default('per_day');
            $table->decimal('fine_per_day', 8, 2)->default(2.00);
            $table->decimal('fixed_fine', 8, 2)->default(50.00);
            $table->integer('max_issue_days')->default(14);
            $table->integer('max_books_per_member')->default(3);
            $table->timestamps();
            $table->boolean('require_membership')->default(false);
        });
        // One settings row; the library pages read and update it.
        DB::table('library_settings')->insert([
            'fine_type' => 'per_day', 'fine_per_day' => 2.00, 'fixed_fine' => 50.00,
            'max_issue_days' => 14, 'max_books_per_member' => 3, 'require_membership' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->string('copy_id');
            $table->string('copy_number');
            $table->enum('status', ['available', 'issued', 'lost', 'damaged'])->default('available');
            $table->string('condition')->default('good');
            $table->date('purchased_at')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique('copy_id');
        });

        Schema::create('book_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->integer('copies_count')->default(1);
            $table->date('purchase_date');
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->string('vendor')->nullable();
            $table->string('invoice_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_purchases');
        Schema::dropIfExists('book_copies');
        Schema::dropIfExists('library_settings');
        Schema::dropIfExists('vehicle_routes');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('library_members');
        Schema::dropIfExists('book_issues');
        Schema::dropIfExists('books');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('school_events');
        Schema::dropIfExists('school_notices');
    }
};
