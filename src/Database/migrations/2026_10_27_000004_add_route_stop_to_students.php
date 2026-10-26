<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // A student's pick-up stop, so the stop's extra fee can be billed with the route fee.
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'route_stop_id')) {
            Schema::table('students', function (Blueprint $t) {
                $t->foreignId('route_stop_id')->nullable()->after('transport_route_id')->constrained('route_stops')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('students', fn (Blueprint $t) => $t->dropConstrainedForeignId('route_stop_id'));
    }
};
