<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Committee extends Model
{
    protected $fillable = [
        'name', 'academic_year', 'formed_date', 'expiry_date', 'description', 'is_active',
    ];

    protected $casts = [
        'formed_date' => 'date',
        'expiry_date' => 'date',
        'is_active'   => 'boolean',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(CommitteeMember::class)->orderBy('serial');
    }

    public static function roles(): array
    {
        return [
            'chairperson'       => 'সভাপতি (Chairperson)',
            'vice_chairperson'  => 'সহ-সভাপতি (Vice Chairperson)',
            'secretary'         => 'সম্পাদক (Secretary)',
            'treasurer'         => 'কোষাধ্যক্ষ (Treasurer)',
            'joint_secretary'   => 'যুগ্ম সম্পাদক (Joint Secretary)',
            'member'            => 'সদস্য (Member)',
            'teacher_rep'       => 'শিক্ষক প্রতিনিধি (Teacher Rep)',
            'guardian_rep'      => 'অভিভাবক প্রতিনিধি (Guardian Rep)',
            'govt_rep'          => 'সরকারি প্রতিনিধি (Govt Rep)',
        ];
    }
}
