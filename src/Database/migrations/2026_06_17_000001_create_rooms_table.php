<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('building')->nullable();
            $table->integer('capacity')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Migrate existing room strings from class_routines to rooms table
        $existingRooms = DB::table('class_routines')
            ->whereNotNull('room')
            ->where('room', '!=', '')
            ->distinct()
            ->pluck('room');

        foreach ($existingRooms as $roomName) {
            DB::table('rooms')->insertOrIgnore([
                'name' => $roomName,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('class_routines', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('end_time')->constrained('rooms')->onDelete('set null');
        });

        // Map existing room strings to room_id
        $rooms = DB::table('rooms')->get()->keyBy('name');
        foreach ($rooms as $name => $room) {
            DB::table('class_routines')
                ->where('room', $name)
                ->update(['room_id' => $room->id]);
        }

        Schema::table('class_routines', function (Blueprint $table) {
            $table->dropColumn('room');
        });
    }

    public function down(): void
    {
        Schema::table('class_routines', function (Blueprint $table) {
            $table->string('room')->nullable()->after('end_time');
        });

        // Restore room names from room_id
        $routines = DB::table('class_routines')->whereNotNull('room_id')->get();
        foreach ($routines as $routine) {
            $room = DB::table('rooms')->find($routine->room_id);
            if ($room) {
                DB::table('class_routines')
                    ->where('id', $routine->id)
                    ->update(['room' => $room->name]);
            }
        }

        Schema::table('class_routines', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');
        });

        Schema::dropIfExists('rooms');
    }
};
