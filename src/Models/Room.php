<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['name', 'building', 'capacity', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function routines(): HasMany
    {
        return $this->hasMany(ClassRoutine::class);
    }
}
