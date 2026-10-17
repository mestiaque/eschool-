<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookPurchase extends Model
{
    protected $fillable = [
        'book_id', 'copies_count', 'purchase_date',
        'unit_price', 'total_price', 'vendor', 'invoice_no', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'unit_price'    => 'decimal:2',
        'total_price'   => 'decimal:2',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
