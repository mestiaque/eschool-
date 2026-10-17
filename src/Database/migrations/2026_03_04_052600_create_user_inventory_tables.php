<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {


        // Inventory Categories
        Schema::create('school_inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // product, equipment, furniture
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Products/Items
        Schema::create('school_products', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained('school_inventory_categories')->onDelete('cascade');
            $table->string('unit')->nullable(); // piece, kg, liter
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Stock Management
        Schema::create('school_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('school_products')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('type'); // in, out
            $table->text('note')->nullable();
            $table->date('date');
            $table->timestamps();
        });

        // Suppliers
        Schema::create('school_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('balance')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Purchases
        Schema::create('school_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('supplier_id')->nullable()->constrained('school_suppliers')->onDelete('set null');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->date('purchase_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'received', 'cancelled'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Purchase Items
        Schema::create('school_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('school_purchases')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('school_products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });

        // School Settings
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type'); // text, image, number
            $table->timestamps();
        });
    }

    public function down()
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
