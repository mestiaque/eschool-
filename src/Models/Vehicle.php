<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['vehicle_number', 'model', 'type', 'capacity', 'driver_name', 'driver_phone', 'driver_license', 'photo', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
