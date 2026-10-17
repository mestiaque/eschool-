<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassText extends Model
{
    protected $fillable = ['class_id', 'subject_id', 'title', 'content', 'file', 'author', 'edition', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
