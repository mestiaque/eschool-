<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add portal PIN to students
        Schema::table('students', function (Blueprint $table) {
            $table->string('portal_pin', 10)->nullable()->after('is_active');
            $table->string('previous_school')->nullable()->after('registration_number');
            $table->string('previous_class')->nullable()->after('previous_school');
        });

        // Enhance student_documents
        Schema::table('student_documents', function (Blueprint $table) {
            $table->text('description')->nullable()->after('type');
            $table->boolean('is_verified')->default(false)->after('description');
            $table->string('verified_by')->nullable()->after('is_verified');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['portal_pin', 'previous_school', 'previous_class']);
        });
        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_verified', 'verified_by']);
        });
    }
};
