@extends('me::printMaster2')
@section('title', 'Tour Badge – ' . $registration->student->first_name . ' ' . $registration->student->last_name)

@push('css')
<style>
    body { font-family: 'Arial', sans-serif; }
    .container { display: flex; justify-content: center; }

    .badge-back {
        text-align: center;
        margin-bottom: 10px;
    }
    .badge-back a { color: #6c757d; text-decoration: none; font-size: 13px; }

    .badge-card {
        width: 85mm;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        position: relative;
    }

    .badge-header {
        background: linear-gradient(135deg, #1a3a6b 0%, #2d6a9f 100%);
        color: white;
        text-align: center;
        padding: 16px 12px 12px;
        position: relative;
    }
    .badge-header::after {
        content: '';
        position: absolute;
        bottom: -12px;
        left: 0;
        right: 0;
        height: 24px;
        background: white;
        border-radius: 50% 50% 0 0 / 100% 100% 0 0;
    }
    .school-name {
        font-size: 11pt;
        font-weight: bold;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }
    .tour-label {
        font-size: 7pt;
        opacity: 0.85;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 6px;
    }
    .tour-title {
        font-size: 13pt;
        font-weight: bold;
        line-height: 1.2;
    }

    .badge-body {
        padding: 20px 16px 14px;
        text-align: center;
    }

    .photo-wrap {
        width: 70px;
        height: 70px;
        margin: 0 auto 12px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #1a3a6b;
        background: #e8f0fe;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .photo-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .photo-initial {
        font-size: 26pt;
        font-weight: bold;
        color: #1a3a6b;
    }

    .student-name {
        font-size: 14pt;
        font-weight: bold;
        color: #1a3a6b;
        margin-bottom: 3px;
        line-height: 1.2;
    }
    .student-id {
        font-size: 8pt;
        color: #777;
        margin-bottom: 10px;
        letter-spacing: 0.5px;
    }

    .info-grid {
        background: #f8f9fc;
        border-radius: 8px;
        padding: 10px;
        text-align: left;
        font-size: 8.5pt;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
        border-bottom: 1px solid #e9ecef;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #888; }
    .info-value { font-weight: 600; color: #333; }

    .badge-footer {
        background: linear-gradient(135deg, #1a3a6b 0%, #2d6a9f 100%);
        color: white;
        text-align: center;
        padding: 10px 12px;
        font-size: 8pt;
        margin-top: 10px;
    }
    .badge-footer .destination {
        font-size: 10pt;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    .badge-footer .dates {
        opacity: 0.85;
        margin-top: 2px;
    }

    .badge-type-strip {
        background: #ffc107;
        color: #1a3a6b;
        text-align: center;
        padding: 4px;
        font-size: 8pt;
        font-weight: bold;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    @media print {
        .badge-card { box-shadow: none; border: 1px solid #ddd; }
    }
</style>
@endpush

@section('contents')

<div class="badge-back no-print">
    <a href="{{ route('academic.tours.show', $tour->id) }}">&#8592; Back to Tour</a>
</div>

<div class="badge-card">
    {{-- Header --}}
    <div class="badge-header">
        @php
            $schoolName = \ME\SchoolManagement\Models\SchoolSetting::where('key','school_name')->value('value') ?? config('app.name','School');
        @endphp
        <div class="school-name">{{ $schoolName }}</div>
        <div class="tour-label">School Tour Participant</div>
        <div class="tour-title">{{ $tour->title }}</div>
    </div>

    {{-- Body --}}
    <div class="badge-body">
        @php $student = $registration->student; @endphp

        {{-- Photo --}}
        <div class="photo-wrap">
            @if($student->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->photo))
                <img src="{{ \Illuminate\Support\Facades\Storage::url($student->photo) }}" alt="">
            @else
                <span class="photo-initial">{{ strtoupper(substr($student->first_name, 0, 1)) }}</span>
            @endif
        </div>

        <div class="student-name">{{ $student->first_name }} {{ $student->last_name }}</div>
        <div class="student-id">ID: {{ $student->student_id ?? str_pad($student->id, 5, '0', STR_PAD_LEFT) }}</div>

        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Class</span>
                <span class="info-value">{{ $student->classe?->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Section</span>
                <span class="info-value">{{ $student->section?->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Roll No</span>
                <span class="info-value">{{ $student->roll_number ?? '—' }}</span>
            </div>
            @if($student->contact_number)
            <div class="info-row">
                <span class="info-label">Contact</span>
                <span class="info-value">{{ $student->contact_number }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Payment</span>
                <span class="info-value" style="color:{{ $registration->payment_status === 'paid' ? '#28a745' : ($registration->payment_status === 'partial' ? '#fd7e14' : '#dc3545') }}">
                    {{ ucfirst($registration->payment_status) }}
                </span>
            </div>
            @if($tour->organizer)
            <div class="info-row">
                <span class="info-label">Organizer</span>
                <span class="info-value">{{ $tour->organizer }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Type Strip --}}
    <div class="badge-type-strip">Participant</div>

    {{-- Footer --}}
    <div class="badge-footer">
        <div class="destination">&#9992; {{ $tour->destination }}</div>
        <div class="dates">
            {{ $tour->start_date->format('d M') }} – {{ sm_date($tour->end_date) }}
            &nbsp;&bull;&nbsp;
            {{ $tour->start_date->diffInDays($tour->end_date) + 1 }} Days
        </div>
    </div>
</div>

@endsection
