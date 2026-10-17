<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    protected $fillable = ['student_id', 'title', 'file', 'type', 'description', 'is_verified', 'verified_by'];
    protected $casts    = ['is_verified' => 'boolean'];

    public static function types(): array
    {
        return [
            'birth_certificate'  => 'Birth Certificate',
            'previous_tc'        => 'Transfer Certificate (TC)',
            'previous_marksheet' => 'Previous Marksheet',
            'photo'              => 'Student Photo',
            'nid'                => 'NID / Passport',
            'guardian_nid'       => 'Guardian NID',
            'medical'            => 'Medical Certificate',
            'other'              => 'Other',
        ];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class)->withTrashed(); }
}
