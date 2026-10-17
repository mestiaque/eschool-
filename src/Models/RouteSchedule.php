<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteSchedule extends Model
{
    protected $fillable = [
        'route_id', 'vehicle_id', 'departure_time', 'arrival_time',
        'direction', 'days', 'notes', 'is_active',
    ];

    protected $casts = [
        'days'      => 'array',
        'is_active' => 'boolean',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public static function directions(): array
    {
        return [
            'morning'   => 'Morning (সকাল)',
            'afternoon' => 'Afternoon (দুপুর)',
            'evening'   => 'Evening (বিকাল)',
            'night'     => 'Night (রাত)',
        ];
    }

    public static function allDays(): array
    {
        return [
            'sat' => 'শনি', 'sun' => 'রবি', 'mon' => 'সোম',
            'tue' => 'মঙ্গল', 'wed' => 'বুধ', 'thu' => 'বৃহস্পতি', 'fri' => 'শুক্র',
        ];
    }

    public function getDaysLabelAttribute(): string
    {
        if (empty($this->days)) return 'Every day';
        $all = self::allDays();
        return implode(', ', array_map(fn($d) => $all[$d] ?? $d, $this->days));
    }
}
