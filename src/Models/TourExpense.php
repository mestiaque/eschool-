<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourExpense extends Model
{
    protected $fillable = ['tour_id', 'category', 'description', 'amount', 'expense_date', 'paid_by', 'notes'];
    protected $casts = ['expense_date' => 'date', 'amount' => 'decimal:2'];

    public function tour(): BelongsTo { return $this->belongsTo(SchoolTour::class); }
}
