<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'school_events';
    protected $fillable = ['title', 'description', 'start_date', 'end_date', 'start_time', 'end_time', 'location', 'photo', 'for', 'is_active'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'is_active' => 'boolean'];
}
