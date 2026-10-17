<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourFeeCategory extends Model
{
    protected $fillable = [
        'tour_id', 'category_type', 'category_label', 'class_id', 'fee', 'serial',
    ];

    protected $casts = [
        'fee'    => 'decimal:2',
        'serial' => 'integer',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(SchoolTour::class, 'tour_id');
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }
}
