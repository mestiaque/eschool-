<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRoute extends Model
{
    protected $fillable = ['route_name', 'start_point', 'end_point', 'distance', 'fee', 'is_active'];
    protected $casts = ['distance' => 'decimal:2', 'fee' => 'decimal:2', 'is_active' => 'boolean'];

    public function stops(): HasMany
    {
        return $this->hasMany(RouteStop::class, 'route_id');
    }

    public function vehicleRoutes(): HasMany
    {
        return $this->hasMany(VehicleRoute::class, 'route_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(RouteSchedule::class, 'route_id')->orderBy('departure_time');
    }
}
