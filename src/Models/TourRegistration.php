<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourRegistration extends Model
{
    protected $fillable = [
        'tour_id', 'student_id', 'participant_type', 'staff_id', 'teacher_id',
        'tour_fee_category_id', 'registration_date', 'fee', 'payment_status',
        'paid_amount', 'is_approved', 'notes',
    ];
    protected $casts = ['is_approved' => 'boolean', 'registration_date' => 'date'];

    public function tour(): BelongsTo        { return $this->belongsTo(SchoolTour::class); }
    public function student(): BelongsTo     { return $this->belongsTo(Student::class)->withTrashed(); }
    public function staff(): BelongsTo       { return $this->belongsTo(Staff::class)->withTrashed(); }
    public function teacher(): BelongsTo     { return $this->belongsTo(Teacher::class)->withTrashed(); }
    public function feeCategory(): BelongsTo { return $this->belongsTo(TourFeeCategory::class, 'tour_fee_category_id'); }

    public function getParticipantNameAttribute(): string
    {
        return match($this->participant_type) {
            'teacher' => optional($this->teacher)->first_name . ' ' . optional($this->teacher)->last_name,
            'staff'   => optional($this->staff)->first_name . ' ' . optional($this->staff)->last_name,
            default   => optional($this->student)->first_name . ' ' . optional($this->student)->last_name,
        };
    }

    public function getParticipantClassAttribute(): string
    {
        return match($this->participant_type) {
            'teacher' => optional($this->teacher)->designation ?? 'Teacher',
            'staff'   => optional($this->staff)->designation ?? 'Staff',
            default   => optional($this->student?->classe)->name ?? '—',
        };
    }
}
