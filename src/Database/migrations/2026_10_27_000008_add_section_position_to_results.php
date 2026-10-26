<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Merit position within the section, alongside the whole-class position.
    public function up(): void
    {
        if (!Schema::hasColumn('results', 'section_position')) {
            Schema::table('results', fn (Blueprint $t) => $t->integer('section_position')->nullable()->after('position'));
        }
    }

    public function down(): void
    {
        Schema::table('results', fn (Blueprint $t) => $t->dropColumn('section_position'));
    }
};
