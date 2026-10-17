<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE students MODIFY COLUMN admission_date DATE NULL');
        DB::statement('ALTER TABLE students MODIFY COLUMN academic_year_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE students MODIFY COLUMN religion VARCHAR(255) NULL');
        DB::statement('ALTER TABLE students MODIFY COLUMN phone VARCHAR(255) NULL');
        DB::statement('ALTER TABLE students MODIFY COLUMN present_address TEXT NULL');
        DB::statement('ALTER TABLE students MODIFY COLUMN permanent_address TEXT NULL');
        DB::statement('ALTER TABLE guardians MODIFY COLUMN guardian_phone VARCHAR(255) NULL');
        DB::statement('ALTER TABLE guardians MODIFY COLUMN mother_name VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE students MODIFY COLUMN admission_date DATE NOT NULL');
        DB::statement('ALTER TABLE students MODIFY COLUMN academic_year_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE students MODIFY COLUMN religion VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE students MODIFY COLUMN phone VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE students MODIFY COLUMN present_address TEXT NOT NULL');
        DB::statement('ALTER TABLE students MODIFY COLUMN permanent_address TEXT NOT NULL');
        DB::statement('ALTER TABLE guardians MODIFY COLUMN guardian_phone VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE guardians MODIFY COLUMN mother_name VARCHAR(255) NOT NULL');
    }
};
