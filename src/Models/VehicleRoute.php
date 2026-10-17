<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleRoute extends Model
{
    protected $fillable = ['vehicle_id', 'route_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class);
    }
}
