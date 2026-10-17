<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'school_notices';
    protected $fillable = ['title', 'content', 'publish_date', 'end_date', 'type', 'for', 'class_id', 'file', 'is_active'];
    protected $casts = ['publish_date' => 'date', 'end_date' => 'date', 'is_active' => 'boolean'];
}
