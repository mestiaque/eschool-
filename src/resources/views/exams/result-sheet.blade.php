@extends('me::printMaster2')
@section('title', sm_t('Result Sheet') . ' — ' . $class->name)

@use('ME\SchoolManagement\Services\GradeScale')

@push('css')
<style>
    .rs-table th, .rs-table td { text-align: center; padding: 4px 3px; font-size: 11px; vertical-align: middle; }
    .rs-table td.name { text-align: left; white-space: nowrap; }
    .rs-table th.sub { font-size: 10px; max-width: 70px; }
    .rs-table .mk { font-weight: bold; }
    .rs-table .gr { font-size: 9px; color: #555; }
    .rs-table .fail, .rs-table .fail .gr { color: #b02a37; }
    .rs-table .opt { font-weight: normal; font-size: 9px; }
    .rs-summary { display: flex; gap: 10px; flex-wrap: wrap; margin: 6px 0 10px; font-size: 12px; }
    .rs-summary div { border: 1px solid #999; padding: 4px 10px; }
    .rs-sign { display: flex; justify-content: space-between; margin-top: 45px; font-size: 12px; }
    .rs-sign div { min-width: 160px; text-align: center; border-top: 1px solid #000; padding-top: 4px; }
    @media print { @page { size: A4 landscape; margin: 8mm; } }
</style>
@endpush

@section('contents')
@include('school-management::partials.print-header', [
    'title' => sm_t('Result Sheet'),
    'subtitle' => $exam->name . ($exam->academicYear ? ' | ' . $exam->academicYear->name : '')
        . ' | ' . sm_t('Class') . ': ' . $class->name . ($section ? ' | ' . sm_t('Section') . ': ' . $section->name : ''),
])

@php $s = $sheet['summary']; @endphp
<div class="rs-summary">
    <div>{{ sm_t('Appeared') }}: <strong>{{ $s['appeared'] }}</strong></div>
    <div>{{ sm_t('Passed') }}: <strong>{{ $s['passed'] }}</strong></div>
    <div>{{ sm_t('Failed') }}: <strong>{{ $s['failed'] }}</strong></div>
    <div>{{ sm_t('Pass Rate') }}: <strong>{{ $s['rate'] }}%</strong></div>
    @foreach($s['grades'] as $grade => $n)
    <div>{{ $grade }}: <strong>{{ $n }}</strong></div>
    @endforeach
</div>

<table class="rs-table">
    <thead>
        <tr>
            <th>{{ sm_t('Roll') }}</th>
            <th>{{ sm_t('Student') }}</th>
            @unless($section)<th>{{ sm_t('Section') }}</th>@endunless
            @foreach($sheet['subjects'] as $subject)
            <th class="sub">{{ $subject->name }}@if($subject->type === 'optional')<div class="opt">({{ sm_t('4th subject') }})</div>@endif</th>
            @endforeach
            <th>{{ sm_t('Total') }}</th>
            <th>{{ sm_t('GPA') }}</th>
            <th>{{ sm_t('Grade') }}</th>
            <th>{{ sm_t('Class Position') }}</th>
            <th>{{ sm_t('Section Position') }}</th>
            <th>{{ sm_t('Result') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sheet['students'] as $student)
        @php
            $marks = $sheet['marks'][$student->id] ?? collect();
            $r = $sheet['results'][$student->id] ?? null;
            $failed = !$r || GradeScale::isFail($r->grade);
        @endphp
        <tr>
            <td>{{ $student->roll_number ?? '—' }}</td>
            <td class="name">{{ $student->full_name }}</td>
            @unless($section)<td>{{ $student->section?->name ?? '—' }}</td>@endunless
            @foreach($sheet['subjects'] as $subject)
                @php $m = $marks[$subject->id] ?? null; @endphp
                @if(!$m)
                <td>—</td>
                @elseif($m->is_absent)
                <td class="fail"><span class="mk">AB</span></td>
                @else
                <td class="{{ (float) $m->grade_point > 0 ? '' : 'fail' }}">
                    <div class="mk">{{ (float) $m->total_mark }}</div>
                    <div class="gr">{{ $m->grade_name }}</div>
                </td>
                @endif
            @endforeach
            <td><strong>{{ $r ? (float) $r->obtained_mark : '—' }}</strong></td>
            <td><strong>{{ $r ? number_format((float) $r->gpa, 2) : '—' }}</strong></td>
            <td class="{{ $failed ? 'fail' : '' }}"><strong>{{ $r?->grade ?? '—' }}</strong></td>
            <td>{{ $r?->position ?? '—' }}</td>
            <td>{{ $r?->section_position ?? '—' }}</td>
            <td class="{{ $failed ? 'fail' : '' }}">{{ $r ? sm_t($failed ? 'Fail' : 'Pass') : sm_t('Not generated') }}</td>
        </tr>
        @empty
        <tr><td colspan="{{ 8 + $sheet['subjects']->count() + ($section ? 0 : 1) }}">{{ sm_t('No results found. Generate results first.') }}</td></tr>
        @endforelse
    </tbody>
</table>

<div class="rs-sign">
    <div>{{ sm_t('Class Teacher') }}</div>
    <div>{{ sm_t('Exam Controller') }}</div>
    <div>{{ sm_t('Head Teacher / Principal') }}</div>
</div>
@endsection
