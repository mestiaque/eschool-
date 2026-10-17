<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->time('departure_time');           // bus ছাড়ার সময়
            $table->time('arrival_time')->nullable();  // গন্তব্যে পৌঁছানোর সময়
            $table->string('direction')->default('morning'); // morning, afternoon, evening, night
            $table->json('days')->nullable();          // ["sat","sun","mon","tue","wed","thu","fri"]
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_schedules');
    }
};
