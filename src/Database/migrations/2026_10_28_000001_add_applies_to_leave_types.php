<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Teacher and student leave types were one list (students could pick
    // maternity or earned leave). Existing types become teacher-only, except
    // sick leave and any type students already used, which stay for both.
    public function up(): void
    {
        if (Schema::hasColumn('leave_types', 'applies_to')) return;

        Schema::table('leave_types', fn (Blueprint $t) => $t->string('applies_to', 10)->default('both')->after('days'));

        $usedByStudents = DB::table('student_leaves')->whereNotNull('leave_type_id')->distinct()->pluck('leave_type_id')->all();
        foreach (DB::table('leave_types')->get() as $type) {
            $both = in_array($type->id, $usedByStudents) || preg_match('/sick|অসুস্থ/iu', $type->name);
            DB::table('leave_types')->where('id', $type->id)->update(['applies_to' => $both ? 'both' : 'teacher']);
        }
    }

    public function down(): void
    {
        Schema::table('leave_types', fn (Blueprint $t) => $t->dropColumn('applies_to'));
    }
};
