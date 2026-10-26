<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Off by default: existing borrowers have no membership records yet.
    public function up(): void
    {
        if (!Schema::hasColumn('library_settings', 'require_membership')) {
            Schema::table('library_settings', fn (Blueprint $t) => $t->boolean('require_membership')->default(false));
        }
    }

    public function down(): void
    {
        Schema::table('library_settings', fn (Blueprint $t) => $t->dropColumn('require_membership'));
    }
};
