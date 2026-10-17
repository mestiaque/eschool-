<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_discounts', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('discount_amount');
            $table->boolean('is_active')->default(true)->after('reason');
        });
    }

    public function down()
    {
        Schema::table('student_discounts', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'is_active']);
        });
    }
};
