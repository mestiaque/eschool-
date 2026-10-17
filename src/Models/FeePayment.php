<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    protected $fillable = ['fee_collection_id', 'amount', 'date', 'payment_method', 'transaction_id', 'note', 'collected_by'];
    protected $casts = ['amount' => 'decimal:2', 'date' => 'date'];

    public function feeCollection(): BelongsTo
    {
        return $this->belongsTo(FeeCollection::class);
    }
}
