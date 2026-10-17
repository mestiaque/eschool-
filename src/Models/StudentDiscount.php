<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDiscount extends Model
{
    protected $fillable = ['student_id', 'fee_type_id', 'discount_amount', 'discount_type', 'is_active'];
    protected $casts = ['discount_amount' => 'decimal:2', 'is_active' => 'boolean'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }
}
