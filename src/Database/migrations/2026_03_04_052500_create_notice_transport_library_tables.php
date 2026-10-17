<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Notices
        Schema::create('school_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->date('publish_date');
            $table->date('end_date')->nullable();
            $table->string('type'); // notice, announcement, news
            $table->string('for'); // all, student, teacher, guardian
            $table->foreignId('class_id')->nullable()->constrained()->onDelete('set null');
            $table->string('file')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Events
        Schema::create('school_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->string('photo')->nullable();
            $table->string('for')->default('all');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Holidays
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Library Books
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('book_id')->unique();
            $table->string('isbn')->nullable();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('edition')->nullable();
            $table->string('language')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('rack_no')->nullable();
            $table->string('type'); // text, reference
            $table->decimal('price', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Book Issues
        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['issued', 'returned', 'lost', 'damaged'])->default('issued');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Library Members
        Schema::create('library_members', function (Blueprint $table) {
            $table->id();
            $table->string('member_type'); // student, teacher
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('membership_date');
            $table->date('expire_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Transport Routes
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('start_point');
            $table->string('end_point');
            $table->decimal('distance', 5, 2)->nullable();
            $table->decimal('fee', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Vehicles
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number');
            $table->string('model')->nullable();
            $table->string('type'); // bus, van, car
            $table->string('capacity');
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('driver_license')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Route Stops
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('transport_routes')->onDelete('cascade');
            $table->string('stop_name');
            $table->time('arrival_time');
            $table->time('departure_time');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('extra_fee', 10, 2)->default(0);
            $table->timestamps();
        });

        // Vehicle Routes (Assignment)
        Schema::create('vehicle_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained('transport_routes')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_routes');
        Schema::dropIfExists('route_stops');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('library_members');
        Schema::dropIfExists('book_issues');
        Schema::dropIfExists('books');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('school_events');
        Schema::dropIfExists('school_notices');
    }
};
