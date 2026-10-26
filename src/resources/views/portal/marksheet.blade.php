@extends('me::printMaster2')
@section('title', 'Marksheet – ' . $student->first_name . ' ' . $student->last_name)

@push('css')
<style>
    .info-row { display: flex; gap: 24px; margin-bottom: 10px; font-size: 12px; flex-wrap: wrap; }
    .info-row span strong { margin-right: 4px; }
    .summary-row { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 12px; margin-bottom: 16px; }
    .summary-box { border: 2px solid #333; padding: 10px 16px; border-radius: 4px; display: inline-block; text-align: center; min-width: 120px; }
    .summary-box .val { font-size: 22px; font-weight: 700; }
    .back-link { margin-bottom: 8px; }
</style>
@endpush

@section('contents')

<div class="no-print back-link">
    <a href="{{ route('portal.results') }}" style="text-decoration:none;color:#0047ab;font-size:13px">← Back</a>
</div>

@include('school-management::partials.print-header', [
    'title' => 'Mark Sheet',
    'subtitle' => ($exam->examType?->name ?? 'Examination') . ' – ' . $exam->name . ' | Academic Year: ' . ($exam->academicYear?->year ?? '—'),
])

<div class="info-row">
    <span><strong>{{ sm_t('Name') }}:</strong> {{ $student->first_name }} {{ $student->last_name }}</span>
    <span><strong>{{ sm_t('Student ID') }}:</strong> {{ $student->student_id }}</span>
    <span><strong>{{ sm_t('Class') }}:</strong> {{ $student->classe?->name }}</span>
    @if($student->section)<span><strong>{{ sm_t('Section') }}:</strong> {{ $student->section?->name }}</span>@endif
    <span><strong>{{ sm_t('Roll No.') }}:</strong> {{ $student->roll_number ?? '—' }}</span>
</div>

<table>
    <thead>
        <tr>
            <th style="width:40%">{{ sm_t('Subject') }}</th>
            <th>{{ sm_t('Full Mark') }}</th>
            <th>{{ sm_t('Pass Mark') }}</th>
            <th>{{ sm_t('Obtained') }}</th>
            <th>{{ sm_t('Grade') }}</th>
            <th>{{ sm_t('Remarks') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($marks as $mark)
        <tr>
            <td>{{ $mark->subject?->name }}@if($mark->subject_id && $mark->subject_id == $student->optional_subject_id) <small class="text-muted">({{ sm_t('4th subject') }})</small>@endif</td>
            <td class="text-center">{{ $scheduleMap[$mark->subject_id]?->full_mark ?? '—' }}</td>
            <td class="text-center">{{ $scheduleMap[$mark->subject_id]?->pass_mark ?? '—' }}</td>
            <td class="text-center fw-bold">{{ $mark->marks_obtained }}</td>
            <td class="text-center">{{ $mark->grade_name ?? "—" }}</td>
            <td class="text-center small">
                @php $pass = $scheduleMap[$mark->subject_id]?->pass_mark ?? 0; @endphp
                @if($mark->marks_obtained >= $pass) Pass @else <span style="color:red">{{ sm_t('Fail') }}</span> @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($result)
<div class="summary-row">
    <div class="summary-box">
        <div style="font-size:11px;color:#555">{{ sm_t('Total Obtained') }}</div>
        <div class="val">{{ $result->obtained_mark }}</div>
        <div style="font-size:11px">out of {{ $result->total_mark }}</div>
    </div>
    <div class="summary-box">
        <div style="font-size:11px;color:#555">{{ sm_t('Percentage') }}</div>
        <div class="val">{{ $result->total_mark > 0 ? round(($result->obtained_mark/$result->total_mark)*100,1) : 0 }}%</div>
    </div>
    <div class="summary-box">
        <div style="font-size:11px;color:#555">{{ sm_t('GPA') }}</div>
        <div class="val">{{ $result->gpa }}</div>
    </div>
    <div class="summary-box">
        <div style="font-size:11px;color:#555">{{ sm_t('Grade') }}</div>
        <div class="val">{{ $result->grade }}</div>
    </div>
    @if($result->position)
    <div class="summary-box">
        <div style="font-size:11px;color:#555">{{ sm_t('Position') }}</div>
        <div class="val">{{ $result->position }}</div>
        @if($result->section_position)<div style="font-size:11px;color:#555">{{ sm_t('Section') }}: {{ $result->section_position }}</div>@endif
    </div>
    @endif
</div>
@endif

<div class="print-footer">
    <div class="signature-box">
        <div class="signature-line">{{ sm_t('Class Teacher') }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-line">{{ sm_t('Exam Controller') }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-line">{{ sm_t('Principal') }}</div>
    </div>
</div>

@endsection
