<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Rack labels are things like "A-3"; the form is free text.
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('rack_no', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->integer('rack_no')->nullable()->change();
        });
    }
};
