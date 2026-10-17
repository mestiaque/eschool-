<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStop extends Model
{
    protected $fillable = ['route_id', 'stop_name', 'arrival_time', 'departure_time', 'latitude', 'longitude', 'extra_fee'];
    protected $casts = ['extra_fee' => 'decimal:2'];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class);
    }
}
