<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Students, teachers and staff go to the Recycle Bin instead of being
    // hard-deleted (a hard delete cascades to fees, marks, attendance, ...).
    public function up(): void
    {
        foreach (['students', 'teachers', 'staff'] as $table) {
            if (!Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->softDeletes());
            }
        }
    }

    public function down(): void
    {
        foreach (['students', 'teachers', 'staff'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropSoftDeletes());
        }
    }
};
