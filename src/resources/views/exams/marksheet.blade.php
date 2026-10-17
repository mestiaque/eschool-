@extends('me::printMaster2')
@section('title', 'Marksheet — ' . $student->first_name . ' ' . $student->last_name)

@section('contents')

@include('school-management::partials.print-header', [
    'title' => 'Student Academic Marksheet',
    'subtitle' => $exam->name . ($exam->examType ? ' — ' . $exam->examType->name : '') . ($exam->academicYear ? ' | ' . $exam->academicYear->name : ''),
])

<div class="info-row">
    <div class="info-block">
        <p><strong>Student:</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
        <p><strong>Student ID:</strong> {{ $student->student_id ?? '—' }}</p>
        <p><strong>Class:</strong> {{ $student->classe?->name ?? '—' }}{{ $student->section ? ' / ' . $student->section->name : '' }}</p>
        <p><strong>Roll:</strong> {{ $student->roll_number ?? 'N/A' }}</p>
    </div>
    <div class="info-block" style="text-align:right">
        <p><strong>Exam:</strong> {{ $exam->name }}</p>
        <p><strong>Period:</strong>
            {{ $exam->start_date ? sm_date($exam->start_date) : '' }}
            {{ $exam->end_date   ? ' – ' . sm_date($exam->end_date) : '' }}
        </p>
        <p><strong>Printed:</strong> {{ sm_date(now()) }}</p>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>{{ sm_t('Subject') }}</th>
            <th>{{ sm_t('Full Mark') }}</th>
            <th>{{ sm_t('Pass Mark') }}</th>
            <th>{{ sm_t('Written') }}</th>
            <th>{{ sm_t('MCQ') }}</th>
            <th>{{ sm_t('Practical') }}</th>
            <th>{{ sm_t('CA') }}</th>
            <th>{{ sm_t('Total') }}</th>
            <th>{{ sm_t('Grade') }}</th>
            <th>{{ sm_t('GPA') }}</th>
            <th>{{ sm_t('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @php $totalObtained = 0; $totalPossible = 0; $hasAbsent = false; @endphp
        @foreach($marks as $mark)
        @php
            $sched = $scheduleMap[$mark->subject_id] ?? null;
            $full  = $sched?->full_mark ?? 0;
            $pass  = $sched?->pass_mark ?? 0;
            $totalPossible  += $full;
            $totalObtained  += $mark->is_absent ? 0 : ($mark->total_mark ?? 0);
            $isPassed = !$mark->is_absent && ($mark->total_mark >= $pass || $pass == 0);
            if ($mark->is_absent) $hasAbsent = true;
        @endphp
        <tr>
            <td>{{ $mark->subject?->name }}</td>
            <td class="num">{{ $full > 0 ? $full : '—' }}</td>
            <td class="num">{{ $pass > 0 ? $pass : '—' }}</td>
            @if($mark->is_absent)
            <td class="ab" colspan="5">ABSENT</td>
            @else
            <td class="num">{{ $mark->written   > 0 ? $mark->written   : '—' }}</td>
            <td class="num">{{ $mark->mcq        > 0 ? $mark->mcq       : '—' }}</td>
            <td class="num">{{ $mark->practical  > 0 ? $mark->practical : '—' }}</td>
            <td class="num">{{ $mark->ca         > 0 ? $mark->ca        : '—' }}</td>
            @endif
            <td class="num {{ $mark->is_absent ? 'ab' : ($isPassed ? 'pass' : 'fail') }}">
                <strong>{{ $mark->is_absent ? 'AB' : number_format($mark->total_mark, 0) }}</strong>
            </td>
            <td class="num">{{ $mark->grade_name ?? '—' }}</td>
            <td class="num">{{ $mark->grade_point !== null ? number_format($mark->grade_point, 2) : '—' }}</td>
            <td class="num {{ $mark->is_absent ? 'ab' : ($isPassed ? 'pass' : 'fail') }}">
                {{ $mark->is_absent ? 'AB' : ($isPassed ? 'Pass' : 'Fail') }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="grandtotal-row">
            <td colspan="7" class="text-right">Total</td>
            <td class="num">{{ number_format($totalObtained, 0) }}{{ $totalPossible > 0 ? '/' . number_format($totalPossible, 0) : '' }}</td>
            <td class="num">{{ $result?->grade ?? '—' }}</td>
            <td class="num">{{ $result?->gpa ? number_format($result->gpa, 2) : '—' }}</td>
            <td class="num"></td>
        </tr>
    </tfoot>
</table>

<div class="summary">
    <div class="summary-box">
        <div class="val">{{ $result?->gpa ? number_format($result->gpa, 2) : '—' }}</div>
        <div class="lbl">GPA</div>
    </div>
    <div class="summary-box">
        <div class="val">{{ $result?->grade ?? '—' }}</div>
        <div class="lbl">Grade</div>
    </div>
    <div class="summary-box">
        <div class="val">{{ $result?->position ? '#' . $result->position : '—' }}</div>
        <div class="lbl">Position</div>
    </div>
    <div class="summary-box">
        <div class="val">{{ $totalPossible > 0 ? round(($totalObtained / $totalPossible) * 100, 1) . '%' : '—' }}</div>
        <div class="lbl">Percentage</div>
    </div>
    <div class="summary-box">
        <div class="val" style="color:{{ ($result && !$hasAbsent && $result->grade !== 'F') ? 'green' : 'red' }}">
            {{ ($result && !$hasAbsent && $result->grade !== 'F') ? 'Pass' : 'Fail' }}
        </div>
        <div class="lbl">Result</div>
    </div>
</div>

<div class="print-footer">
    <div class="signature-box">
        <div class="signature-line">Class Teacher</div>
    </div>
    <div class="signature-box">
        <div class="signature-line">Headmaster / Principal</div>
    </div>
    <div class="signature-box">
        <div class="signature-line">Guardian's Signature</div>
    </div>
</div>

@endsection

@push('css')
<style>
    td.num   { text-align: center; }
    td.pass  { background: #d4edda; }
    td.fail  { background: #f8d7da; }
    td.ab    { background: #fff3cd; color: #856404; text-align: center; }
    .info-row { display: flex; justify-content: space-between; margin-bottom: 12px; gap: 20px; }
    .info-block p { margin-bottom: 3px; }
    .summary { display: flex; gap: 12px; margin-bottom: 16px; }
    .summary-box { flex: 1; border: 1px solid #ccc; padding: 8px; text-align: center; }
    .summary-box .val { font-size: 20px; font-weight: bold; }
    .summary-box .lbl { font-size: 10px; color: #555; margin-top: 3px; }
    @media print {
        @page { margin: 10mm; }
    }
</style>
@endpush
