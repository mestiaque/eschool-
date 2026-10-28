{{-- One student's marksheet. Pass $student, $exam and $sheet (ResultSheet::marksheet). --}}
@use('ME\SchoolManagement\Services\GradeScale')
@php
    $result = $sheet['result'];
    $rows = collect($sheet['rows']);
    // Hide mark components nobody in this sheet used (e.g. no practical).
    $parts = collect(['written' => 'Written', 'mcq' => 'MCQ', 'practical' => 'Practical', 'ca' => 'CA'])
        ->filter(fn ($label, $col) => $rows->contains(fn ($r) => $r['mark'] && (float) $r['mark']->$col > 0));
    $statusLabel = ['pass' => 'Pass', 'fail' => 'Fail', 'absent' => 'Absent', 'missing' => '—'];
@endphp

@once
@push('css')
<style>
    .ms-page { page-break-after: always; break-after: page; }
    .ms-page:last-child { page-break-after: auto; break-after: auto; }
    .ms-info { display: flex; justify-content: space-between; gap: 20px; margin: 10px 0 12px; font-size: 13px; }
    .ms-info table { width: auto; margin: 0; }
    .ms-info td { border: none; padding: 2px 8px 2px 0; font-size: 13px; }
    .ms-info td:first-child { color: #555; white-space: nowrap; }
    .ms-marks th, .ms-marks td { text-align: center; }
    .ms-marks td.sub { text-align: left; }
    .ms-marks .fail { color: #b02a37; font-weight: bold; }
    .ms-marks .absent { color: #997404; font-weight: bold; }
    .ms-marks tfoot td { font-weight: bold; background: #f4f6f8; }
    .ms-bottom { display: flex; gap: 16px; align-items: flex-start; }
    .ms-summary { flex: 1; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .ms-box { border: 1px solid #999; padding: 8px; text-align: center; }
    .ms-box .val { font-size: 20px; font-weight: bold; }
    .ms-box .lbl { font-size: 11px; color: #555; }
    .ms-box.pass .val { color: #146c43; }
    .ms-box.fail .val { color: #b02a37; }
    .ms-scale { width: 210px; margin: 0; }
    .ms-scale th, .ms-scale td { padding: 2px 6px; font-size: 10px; text-align: center; }
    .ms-note { font-size: 11px; color: #555; margin-top: 6px; }
    .ms-sign { display: flex; justify-content: space-between; margin-top: 50px; font-size: 12px; }
    .ms-sign div { min-width: 160px; text-align: center; border-top: 1px solid #000; padding-top: 4px; }
    @media print { @page { size: A4 portrait; margin: 10mm; } }
</style>
@endpush
@endonce

<div class="ms-page">
    @include('school-management::partials.print-header', [
        'title' => sm_t('Mark Sheet'),
        'subtitle' => $exam->name . ($exam->examType ? ' — ' . $exam->examType->name : '') . ($exam->academicYear ? ' | ' . $exam->academicYear->name : ''),
    ])

    <div class="ms-info">
        <table>
            <tr><td>{{ sm_t('Student') }}:</td><td><strong>{{ $student->full_name }}</strong></td></tr>
            <tr><td>{{ sm_t('Student ID') }}:</td><td>{{ $student->student_id ?? '—' }}</td></tr>
            <tr><td>{{ sm_t('Father\'s Name') }}:</td><td>{{ $student->guardian?->father_name ?? '—' }}</td></tr>
        </table>
        <table>
            <tr><td>{{ sm_t('Class') }}:</td><td>{{ $student->classe?->name ?? '—' }}</td></tr>
            <tr><td>{{ sm_t('Section') }}:</td><td>{{ $student->section?->name ?? '—' }}</td></tr>
            <tr><td>{{ sm_t('Roll') }}:</td><td>{{ $student->roll_number ?? '—' }}</td></tr>
        </table>
    </div>

    <table class="ms-marks">
        <thead>
            <tr>
                <th>{{ sm_t('Subject') }}</th>
                <th>{{ sm_t('Full Mark') }}</th>
                <th>{{ sm_t('Pass Mark') }}</th>
                @foreach($parts as $label)<th>{{ sm_t($label) }}</th>@endforeach
                <th>{{ sm_t('Total') }}</th>
                <th>{{ sm_t('Grade') }}</th>
                <th>{{ sm_t('Grade Point') }}</th>
                <th>{{ sm_t('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            @php $m = $row['mark']; @endphp
            <tr>
                <td class="sub">{{ $row['subject']->name }}@if($row['optional']) <small>({{ sm_t('4th subject') }})</small>@endif</td>
                <td>{{ (float) $row['full'] }}</td>
                <td>{{ (float) $row['pass'] }}</td>
                @if(!$m || $m->is_absent)
                    @if($parts->isNotEmpty())<td colspan="{{ $parts->count() }}" class="{{ $row['status'] }}">{{ $m ? sm_t('Absent') : '—' }}</td>@endif
                    <td class="{{ $row['status'] }}">{{ $m ? 'AB' : '—' }}</td>
                @else
                    @foreach($parts as $col => $label)<td>{{ (float) $m->$col > 0 ? (float) $m->$col : '—' }}</td>@endforeach
                    <td class="{{ $row['status'] === 'fail' ? 'fail' : '' }}"><strong>{{ (float) $m->total_mark }}</strong></td>
                @endif
                <td class="{{ in_array($row['status'], ['fail', 'absent']) ? 'fail' : '' }}">{{ $m ? ($m->grade_name ?? GradeScale::fail()['name']) : '—' }}</td>
                <td>{{ $m ? number_format((float) $m->grade_point, 2) : '—' }}</td>
                <td class="{{ $row['status'] }}">{{ sm_t($statusLabel[$row['status']]) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="sub">{{ sm_t('Total') }}</td>
                <td>{{ (float) $sheet['possible'] }}</td>
                <td colspan="{{ 1 + $parts->count() }}"></td>
                <td>{{ (float) $sheet['obtained'] }}</td>
                <td>{{ $result?->grade ?? '—' }}</td>
                <td>{{ $result ? number_format((float) $result->gpa, 2) : '—' }}</td>
                <td class="{{ $sheet['passed'] ? '' : 'fail' }}">{{ $result ? sm_t($sheet['passed'] ? 'Pass' : 'Fail') : '—' }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="ms-bottom">
        <div class="ms-summary">
            <div class="ms-box"><div class="val">{{ $result ? number_format((float) $result->gpa, 2) : '—' }}</div><div class="lbl">{{ sm_t('GPA') }}</div></div>
            <div class="ms-box"><div class="val">{{ $result?->grade ?? '—' }}</div><div class="lbl">{{ sm_t('Grade') }}</div></div>
            <div class="ms-box {{ $result ? ($sheet['passed'] ? 'pass' : 'fail') : '' }}"><div class="val">{{ $result ? sm_t($sheet['passed'] ? 'Pass' : 'Fail') : '—' }}</div><div class="lbl">{{ sm_t('Result') }}</div></div>
            <div class="ms-box"><div class="val">{{ $sheet['possible'] > 0 ? round($sheet['obtained'] / $sheet['possible'] * 100, 1) . '%' : '—' }}</div><div class="lbl">{{ sm_t('Percentage') }}</div></div>
            <div class="ms-box"><div class="val">{{ $result?->position ?? '—' }}</div><div class="lbl">{{ sm_t('Class Position') }}</div></div>
            <div class="ms-box"><div class="val">{{ $result?->section_position ?? '—' }}</div><div class="lbl">{{ sm_t('Section Position') }}</div></div>
        </div>
        <table class="ms-scale">
            <thead><tr><th>{{ sm_t('Marks') }} (%)</th><th>{{ sm_t('Grade') }}</th><th>{{ sm_t('Point') }}</th></tr></thead>
            <tbody>
                @php $prevFrom = 100; @endphp
                @foreach(GradeScale::rows() as $i => $g)
                <tr><td>{{ (float) $g['from'] }}–{{ $i === 0 ? 100 : $prevFrom - 1 }}</td><td>{{ $g['name'] }}</td><td>{{ number_format($g['point'], 2) }}</td></tr>
                @php $prevFrom = $g['from']; @endphp
                @endforeach
            </tbody>
        </table>
    </div>
    @if($result?->note)<div class="ms-note">{{ sm_t('Note') }}: {{ $result->note }}</div>@endif

    <div class="ms-sign">
        <div>{{ sm_t('Class Teacher') }}</div>
        <div>{{ sm_t('Guardian\'s Signature') }}</div>
        <div>{{ sm_t('Head Teacher / Principal') }}</div>
    </div>
</div>
