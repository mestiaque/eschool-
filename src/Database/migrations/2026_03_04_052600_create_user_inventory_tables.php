<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('school_products', function (Blueprint $table) {
            $table->id();
            $table->string('item_code');
            $table->string('name');
            $table->foreignId('category_id')->constrained('school_inventory_categories')->cascadeOnDelete();
            $table->string('unit')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('item_code');
        });

        Schema::create('school_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('school_products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('type');
            $table->text('note')->nullable();
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('school_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('balance')->default('0');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('school_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no');
            $table->foreignId('supplier_id')->nullable()->constrained('school_suppliers')->nullOnDelete();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('due_amount', 10, 2)->default(0.00);
            $table->date('purchase_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'received', 'cancelled'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique('invoice_no');
        });

        Schema::create('school_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('school_purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('school_products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });

        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type');
            $table->timestamps();
            $table->unique('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
        Schema::dropIfExists('school_purchase_items');
        Schema::dropIfExists('school_purchases');
        Schema::dropIfExists('school_suppliers');
        Schema::dropIfExists('school_stocks');
        Schema::dropIfExists('school_products');
        Schema::dropIfExists('school_inventory_categories');
    }
};
