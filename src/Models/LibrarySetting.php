<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    protected $table = 'library_settings';

    protected $fillable = [
        'fine_type', 'fine_per_day', 'fixed_fine',
        'max_issue_days', 'max_books_per_member', 'require_membership',
    ];

    protected $casts = [
        'fine_per_day' => 'decimal:2',
        'fixed_fine'   => 'decimal:2',
        'require_membership' => 'boolean',
    ];

    public static function get(): self
    {
        return static::firstOrCreate([], [
            'fine_type'             => 'per_day',
            'fine_per_day'          => 2.00,
            'fixed_fine'            => 50.00,
            'max_issue_days'        => 14,
            'max_books_per_member'  => 3,
        ]);
    }

    public function calculateFine(int $overdueDays): float
    {
        if ($overdueDays <= 0) return 0;
        if ($this->fine_type === 'fixed') return (float) $this->fixed_fine;
        return round($overdueDays * (float) $this->fine_per_day, 2);
    }
}
