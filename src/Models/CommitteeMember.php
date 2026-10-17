<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeMember extends Model
{
    protected $fillable = [
        'committee_id', 'name', 'role', 'serial', 'phone', 'email',
        'address', 'photo', 'profession', 'notes',
    ];

    protected $casts = [
        'serial' => 'integer',
    ];

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }
}
