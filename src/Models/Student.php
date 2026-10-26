<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'first_name', 'last_name', 'gender', 'date_of_birth',
        'birth_certificate_no', 'religion', 'blood_group', 'nationality', 'photo',
        'email', 'phone', 'present_address', 'permanent_address', 'class_id',
        'section_id', 'subject_group_id', 'optional_subject_id', 'academic_year_id',
        'shift', 'roll_number', 'registration_number', 'previous_school', 'previous_class',
        'admission_date', 'transport_route_id', 'route_stop_id', 'vehicle_id',
        'is_hostel', 'room_number', 'is_active', 'portal_pin',
    ];

    // The portal PIN is stored hashed and never sent to the browser.
    protected $hidden = ['portal_pin'];

    protected $casts = [
        'portal_pin'     => 'hashed',
        'date_of_birth'  => 'date',
        'admission_date' => 'date',
        'is_hostel'      => 'boolean',
        'is_active'      => 'boolean',
    ];

    public function classe(): BelongsTo    { return $this->belongsTo(Classe::class, 'class_id'); }
    public function section(): BelongsTo   { return $this->belongsTo(Section::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function subjectGroup(): BelongsTo { return $this->belongsTo(SubjectGroup::class, 'subject_group_id'); }
    public function optionalSubject(): BelongsTo { return $this->belongsTo(Subject::class, 'optional_subject_id'); }
    public function guardian(): HasOne     { return $this->hasOne(Guardian::class); }
    public function attendances(): HasMany { return $this->hasMany(StudentAttendance::class); }
    public function feeCollections(): HasMany { return $this->hasMany(FeeCollection::class); }
    public function marks(): HasMany       { return $this->hasMany(StudentMark::class); }
    public function documents(): HasMany   { return $this->hasMany(StudentDocument::class); }
    public function promotions(): HasMany  { return $this->hasMany(StudentPromotion::class); }
    public function discounts(): HasMany   { return $this->hasMany(StudentDiscount::class); }
    public function libraryIssues(): HasMany { return $this->hasMany(BookIssue::class); }
    public function results(): HasMany     { return $this->hasMany(Result::class); }
    public function leaves(): HasMany      { return $this->hasMany(StudentLeave::class); }
    public function earlyExits(): HasMany  { return $this->hasMany(StudentEarlyExit::class); }
    public function transportRoute(): BelongsTo { return $this->belongsTo(TransportRoute::class, 'transport_route_id'); }
    public function routeStop(): BelongsTo { return $this->belongsTo(RouteStop::class, 'route_stop_id'); }

    /** Monthly transport charge: route fee plus the pick-up stop's extra fee. */
    public function getTransportFeeAttribute(): float
    {
        if (!$this->transportRoute) return 0.0;
        $stop = $this->routeStop && $this->routeStop->route_id == $this->transport_route_id ? $this->routeStop : null;
        return round((float) $this->transportRoute->fee + (float) ($stop->extra_fee ?? 0), 2);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Returns all subjects this student is eligible to take:
     * compulsory subjects for their class + group subjects for their group + their optional subject.
     */
    public function getEligibleSubjects()
    {
        return Subject::where('class_id', $this->class_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('type', 'compulsory')
                  ->orWhere(function ($q2) {
                      $q2->where('type', 'group')
                         ->where('group_id', $this->subject_group_id);
                  })
                  ->orWhere(function ($q3) {
                      $q3->where('type', 'optional')
                         ->where('id', $this->optional_subject_id);
                  });
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    /**
     * Students who sit a subject: everyone for a compulsory subject, the
     * subject's group (Science / Commerce / Humanities) for a group subject,
     * and only those who chose it for an optional (4th) subject.
     */
    public function scopeTakingSubject($query, Subject $subject)
    {
        return match ($subject->type) {
            'group'    => $subject->group_id ? $query->where('subject_group_id', $subject->group_id) : $query,
            'optional' => $query->where(fn ($q) => $q->where('optional_subject_id', $subject->id)
                // A group-specific optional with nobody assigned yet: fall back to the group.
                ->when($subject->group_id && !static::where('optional_subject_id', $subject->id)->exists(),
                    fn ($q2) => $q2->orWhere('subject_group_id', $subject->group_id))),
            default    => $query,
        };
    }
}
