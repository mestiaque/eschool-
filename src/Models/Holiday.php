<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['title', 'date', 'type', 'description', 'is_active'];
    protected $casts = ['date' => 'date', 'is_active' => 'boolean'];
}
