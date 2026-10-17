<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolTour extends Model
{
    protected $fillable = ['title', 'destination', 'description', 'start_date', 'end_date', 'budget', 'organizer', 'contact_phone', 'status', 'is_active'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'budget' => 'decimal:2', 'is_active' => 'boolean'];

    public function registrations(): HasMany  { return $this->hasMany(TourRegistration::class, 'tour_id'); }
    public function expenses(): HasMany       { return $this->hasMany(TourExpense::class, 'tour_id'); }
    public function feeCategories(): HasMany  { return $this->hasMany(TourFeeCategory::class, 'tour_id')->orderBy('serial'); }

    public function getTotalExpenseAttribute(): float
    {
        return (float) $this->expenses()->sum('amount');
    }
    public function getTotalCollectedAttribute(): float
    {
        return (float) $this->registrations()->sum('paid_amount');
    }
}
