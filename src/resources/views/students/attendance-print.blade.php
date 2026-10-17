@extends('me::printMaster2')
@section('title', sm_t('Student Attendance Sheet'))

@push('css')
<style>
@media print {
    @page { size: landscape; }
}
table.attendance-print-table th,
table.attendance-print-table td {
    padding: 3px 4px;
    font-size: 10px;
    text-align: center;
}
table.attendance-print-table td.col-name {
    text-align: left;
    white-space: nowrap;
}
table.attendance-print-table td.col-absent {
    font-weight: bold;
}
</style>
@endpush

@section('contents')

@php
    $className   = optional($classes->firstWhere('id', request('class_id')))->name;
    $sectionName = request('section_id') ? optional($sections->firstWhere('id', request('section_id')))->name : null;
    $monthYear   = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');
    $subtitle    = collect([
        $className ? 'Class: ' . $className : null,
        $sectionName ? 'Section: ' . $sectionName : null,
        'Month: ' . $monthYear,
    ])->filter()->implode(' | ');
@endphp

@include('school-management::partials.print-header', [
    'title' => 'Student Attendance Sheet',
    'subtitle' => $subtitle ?: null,
])

@if($students->isEmpty())
<p class="text-center">No data selected</p>
@else
<table class="attendance-print-table">
    <thead>
        <tr>
            <th>{{ sm_t('Student Name') }}</th>
            <th>{{ sm_t('Roll') }}</th>
            @for($d = 1; $d <= $daysInMonth; $d++)
                <th>{{ $d }}</th>
            @endfor
            <th>P</th>
            <th>A</th>
            <th>L</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        @php
            $studentAtts = $attendanceMap[$student->id] ?? [];
            $pCount = 0; $aCount = 0; $lCount = 0;
            foreach ($studentAtts as $a) {
                if ($a['status'] === 'present') $pCount++;
                elseif ($a['status'] === 'absent') $aCount++;
                elseif ($a['status'] === 'late') $lCount++;
            }
            $statusLabels = ['present' => 'P', 'absent' => 'A', 'late' => 'L', 'excused' => 'E'];
        @endphp
        <tr>
            <td class="col-name">{{ $student->first_name }} {{ $student->last_name }}</td>
            <td>{{ $student->roll_number ?? '-' }}</td>
            @for($d = 1; $d <= $daysInMonth; $d++)
                @php
                    $dayOfWeek = (int) date('w', mktime(0, 0, 0, $month, $d, $year));
                    $isWeekend = in_array($dayOfWeek, $weekendDays);
                    $isHoliday = isset($holidays[$d]);
                    $status    = $studentAtts[$d]['status'] ?? null;
                    $label     = $status ? ($statusLabels[$status] ?? '') : (($isWeekend || $isHoliday) ? '—' : '');
                @endphp
                <td class="{{ $status === 'absent' ? 'col-absent' : '' }}">{{ $label }}</td>
            @endfor
            <td>{{ $pCount }}</td>
            <td>{{ $aCount }}</td>
            <td>{{ $lCount }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@endsection
