<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if sections table exists and add class_id if not
        if (Schema::hasTable('sections')) {
            if (!Schema::hasColumn('sections', 'class_id')) {
                Schema::table('sections', function (Blueprint $table) {
                    $table->foreignId('class_id')->nullable()->constrained()->onDelete('cascade')->after('id');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn('class_id');
        });
    }
};
