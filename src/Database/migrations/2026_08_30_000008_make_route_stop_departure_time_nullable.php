<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE route_stops MODIFY departure_time TIME NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE route_stops MODIFY departure_time TIME NOT NULL');
    }
};
