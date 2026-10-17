<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'staff_id', 'first_name', 'last_name', 'gender', 'date_of_birth',
        'religion', 'blood_group', 'photo', 'phone', 'email', 'nid',
        'present_address', 'permanent_address', 'designation', 'joining_date',
        'leaving_date', 'salary', 'bank_name', 'bank_account_no',
        'emergency_contact_name', 'emergency_contact_phone', 'relationship',
        'notes', 'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date'  => 'date',
        'leaving_date'  => 'date',
        'salary'        => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    public static function designations(): array
    {
        return [
            'pion'        => 'পিয়ন (Pion)',
            'cleaner'     => 'ক্লিনার (Cleaner)',
            'aya'         => 'আয়া (Aya)',
            'night_guard' => 'নাইট গার্ড (Night Guard)',
            'guard'       => 'গার্ড (Guard)',
            'cook'        => 'বাবুর্চি (Cook)',
            'driver'      => 'ড্রাইভার (Driver)',
            'electrician' => 'ইলেক্ট্রিশিয়ান',
            'plumber'     => 'প্লাম্বার',
            'gardener'    => 'মালি (Gardener)',
            'other'       => 'অন্যান্য (Other)',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function tourRegistrations(): HasMany
    {
        return $this->hasMany(TourRegistration::class, 'staff_id');
    }
}
