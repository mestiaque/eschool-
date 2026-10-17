<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'book_id', 'isbn', 'title', 'author', 'publisher', 'edition',
        'language', 'quantity', 'rack_no', 'type', 'price',
        'description', 'cover_image', 'is_active',
    ];

    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class)->orderBy('copy_number');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BookIssue::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(BookPurchase::class);
    }

    public function getAvailableCopiesCountAttribute(): int
    {
        return $this->copies()->where('status', 'available')->count();
    }

    public function getIssuedCopiesCountAttribute(): int
    {
        return $this->copies()->where('status', 'issued')->count();
    }

    // Generate next Book ID: BK-0001, BK-0002 ...
    public static function generateBookId(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        if (!$last || !preg_match('/BK-(\d+)/', $last->book_id ?? '', $m)) {
            return 'BK-0001';
        }
        return 'BK-' . str_pad((int)$m[1] + 1, 4, '0', STR_PAD_LEFT);
    }

    // Generate copy IDs for a book: BK-0001-01 ... BK-0001-10
    public function generateCopies(int $count, ?string $purchasedAt = null, ?float $price = null): void
    {
        $existing = $this->copies()->max(\DB::raw('CAST(copy_number AS UNSIGNED)')) ?? 0;
        for ($i = 1; $i <= $count; $i++) {
            $num = $existing + $i;
            BookCopy::create([
                'book_id'        => $this->id,
                'copy_id'        => $this->book_id . '-' . str_pad($num, 2, '0', STR_PAD_LEFT),
                'copy_number'    => str_pad($num, 2, '0', STR_PAD_LEFT),
                'status'         => 'available',
                'purchased_at'   => $purchasedAt,
                'purchase_price' => $price,
            ]);
        }
        // Keep quantity in sync
        $this->increment('quantity', $count);
    }
}
