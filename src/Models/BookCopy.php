<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookCopy extends Model
{
    protected $fillable = [
        'book_id', 'copy_id', 'copy_number', 'status',
        'condition', 'purchased_at', 'purchase_price', 'notes',
    ];

    protected $casts = [
        'purchased_at'   => 'date',
        'purchase_price' => 'decimal:2',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BookIssue::class, 'book_copy_id');
    }

    public function activeIssue(): HasOne
    {
        return $this->hasOne(BookIssue::class, 'book_copy_id')
            ->where('status', 'issued');
    }

    public static function statusColors(): array
    {
        return [
            'available' => 'success',
            'issued'    => 'warning',
            'lost'      => 'danger',
            'damaged'   => 'secondary',
        ];
    }
}
