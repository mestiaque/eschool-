<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $table = 'school_stocks';

    protected $fillable = ['product_id', 'quantity', 'type', 'note', 'date'];
    protected $casts = ['date' => 'date'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
