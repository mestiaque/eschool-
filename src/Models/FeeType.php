<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    protected $fillable = ['name', 'type', 'amount', 'description', 'is_active'];
    protected $casts = ['amount' => 'decimal:2', 'is_active' => 'boolean'];
}
