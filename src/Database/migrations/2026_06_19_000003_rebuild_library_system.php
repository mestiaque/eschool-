<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Library Settings ───────────────────────────────────────────────
        Schema::create('library_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('fine_type', ['fixed', 'per_day'])->default('per_day');
            $table->decimal('fine_per_day', 8, 2)->default(2.00);   // per day fine
            $table->decimal('fixed_fine', 8, 2)->default(50.00);    // fixed fine if overdue
            $table->integer('max_issue_days')->default(14);          // default loan period
            $table->integer('max_books_per_member')->default(3);
            $table->timestamps();
        });
        DB::table('library_settings')->insert([
            'fine_type' => 'per_day', 'fine_per_day' => 2.00,
            'fixed_fine' => 50.00, 'max_issue_days' => 14,
            'max_books_per_member' => 3,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── Book Copies ────────────────────────────────────────────────────
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->string('copy_id')->unique();        // BK-0001-01
            $table->string('copy_number');              // 01, 02 ...
            $table->enum('status', ['available','issued','lost','damaged'])->default('available');
            $table->string('condition')->default('good'); // good, fair, poor
            $table->date('purchased_at')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Modify book_issues ─────────────────────────────────────────────
        Schema::table('book_issues', function (Blueprint $table) {
            $table->unsignedBigInteger('book_copy_id')->nullable()->after('book_id');
            $table->string('member_type')->default('student')->after('book_id'); // student/teacher/staff
            $table->unsignedBigInteger('staff_id')->nullable()->after('teacher_id');
            $table->decimal('fine_amount', 10, 2)->default(0)->after('return_date');
            $table->decimal('fine_paid', 10, 2)->default(0)->after('fine_amount');
            $table->enum('fine_status', ['none','pending','paid','waived'])->default('none')->after('fine_paid');
            $table->boolean('is_lost')->default(false)->after('fine_status');
            $table->text('return_note')->nullable()->after('is_lost');
        });

        // ── Book Purchases ─────────────────────────────────────────────────
        Schema::create('book_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->integer('copies_count')->default(1);
            $table->date('purchase_date');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('vendor')->nullable();
            $table->string('invoice_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            $table->dropColumn(['book_copy_id','member_type','staff_id','fine_amount','fine_paid','fine_status','is_lost','return_note']);
        });
        Schema::dropIfExists('book_purchases');
        Schema::dropIfExists('book_copies');
        Schema::dropIfExists('library_settings');
    }
};
