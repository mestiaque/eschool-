@extends('me::printMaster2')
@section('title', 'Tour Badges – ' . $tour->title)

@push('css')
<style>
    body { font-family: 'Arial', sans-serif; }

    .badge-back {
        text-align: center;
        margin-bottom: 10px;
    }
    .badge-back a { color: #6c757d; text-decoration: none; font-size: 13px; }

    .filter-bar {
        background: #f8f9fc;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        display: flex;
        gap: 12px;
        align-items: center;
        font-size: 13px;
        flex-wrap: wrap;
    }
    .filter-bar label { font-weight: 600; margin-right: 4px; }

    .badges-count {
        text-align: center;
        color: #666;
        font-size: 10pt;
        margin-bottom: 16px;
    }

    .badges-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: flex-start;
    }

    .badge-card {
        width: 85mm;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.12);
        page-break-inside: avoid;
    }

    .badge-header {
        background: linear-gradient(135deg, #1a3a6b 0%, #2d6a9f 100%);
        color: white;
        text-align: center;
        padding: 11px 10px 9px;
        position: relative;
    }
    .badge-header::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        right: 0;
        height: 20px;
        background: white;
        border-radius: 50% 50% 0 0 / 100% 100% 0 0;
    }
    .school-name {
        font-size: 9pt;
        font-weight: bold;
        letter-spacing: 0.3px;
        margin-bottom: 2px;
    }
    .tour-label {
        font-size: 6pt;
        opacity: 0.8;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        margin-bottom: 4px;
    }
    .tour-title {
        font-size: 10.5pt;
        font-weight: bold;
        line-height: 1.2;
    }

    .badge-body {
        padding: 16px 13px 10px;
        text-align: center;
    }

    .photo-wrap {
        width: 58px;
        height: 58px;
        margin: 0 auto 9px;
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
        font-size: 22pt;
        font-weight: bold;
        color: #1a3a6b;
    }

    .student-name {
        font-size: 11pt;
        font-weight: bold;
        color: #1a3a6b;
        margin-bottom: 2px;
        line-height: 1.2;
    }
    .student-id {
        font-size: 7pt;
        color: #888;
        margin-bottom: 8px;
    }

    .info-grid {
        background: #f8f9fc;
        border-radius: 6px;
        padding: 7px 9px;
        text-align: left;
        font-size: 7.5pt;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 2px 0;
        border-bottom: 1px solid #eee;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #999; }
    .info-value { font-weight: 600; color: #333; }

    .badge-footer {
        background: linear-gradient(135deg, #1a3a6b 0%, #2d6a9f 100%);
        color: white;
        text-align: center;
        padding: 8px 10px;
        font-size: 7pt;
        margin-top: 8px;
    }
    .badge-footer .destination {
        font-size: 8.5pt;
        font-weight: bold;
    }
    .badge-footer .dates {
        opacity: 0.82;
        margin-top: 1px;
    }

    .badge-type-strip {
        background: #ffc107;
        color: #1a3a6b;
        text-align: center;
        padding: 3px;
        font-size: 7pt;
        font-weight: bold;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    @media print {
        .badges-grid { gap: 5mm; }
        .badge-card { box-shadow: none; border: 0.5pt solid #bbb; }
    }
</style>
@endpush

@section('contents')

<div class="badge-back no-print">
    <a href="{{ route('academic.tours.show', $tour->id) }}">&#8592; Back to Tour</a>
</div>

<div class="filter-bar no-print">
    <span><label>{{ sm_t('Tour:') }}</label> <strong>{{ $tour->title }}</strong></span>
    <span><label>{{ sm_t('Destination:') }}</label> {{ $tour->destination }}</span>
    <span><label>{{ sm_t('Date:') }}</label> {{ $tour->start_date->format('d M') }} – {{ sm_date($tour->end_date) }}</span>
</div>

<div class="badges-count">Total Participants: {{ $registrations->count() }}</div>

@php $schoolName = \ME\SchoolManagement\Models\SchoolSetting::where('key','school_name')->value('value') ?? config('app.name','School'); @endphp

<div class="badges-grid" id="badgesGrid">
    @forelse($registrations as $reg)
    @php $student = $reg->student; @endphp
    <div class="badge-card">
        {{-- Header --}}
        <div class="badge-header">
            <div class="school-name">{{ $schoolName }}</div>
            <div class="tour-label">School Tour Participant</div>
            <div class="tour-title">{{ $tour->title }}</div>
        </div>

        {{-- Body --}}
        <div class="badge-body">
            <div class="photo-wrap">
                @if($student?->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->photo))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($student->photo) }}" alt="">
                @else
                    <span class="photo-initial">{{ strtoupper(substr($student?->first_name ?? 'S', 0, 1)) }}</span>
                @endif
            </div>

            <div class="student-name">{{ $student?->first_name }} {{ $student?->last_name }}</div>
            <div class="student-id">ID: {{ $student?->student_id ?? str_pad($student?->id ?? 0, 5, '0', STR_PAD_LEFT) }}</div>

            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Class</span>
                    <span class="info-value">{{ $student?->classe?->name ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Section</span>
                    <span class="info-value">{{ $student?->section?->name ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Roll No</span>
                    <span class="info-value">{{ $student?->roll_number ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment</span>
                    <span class="info-value" style="color:{{ $reg->payment_status === 'paid' ? '#28a745' : ($reg->payment_status === 'partial' ? '#fd7e14' : '#dc3545') }}">
                        {{ ucfirst($reg->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="badge-type-strip">Participant</div>

        <div class="badge-footer">
            <div class="destination">&#9992; {{ $tour->destination }}</div>
            <div class="dates">
                {{ $tour->start_date->format('d M') }} – {{ sm_date($tour->end_date) }}
            </div>
        </div>
    </div>
    @empty
    <div style="text-align:center;color:#888;padding:40px;width:100%;">No students registered for this tour.</div>
    @endforelse
</div>

@endsection
