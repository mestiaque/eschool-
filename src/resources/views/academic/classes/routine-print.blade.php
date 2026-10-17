@extends('me::printMaster2')
@section('title', 'Class Routine - ' . $class->name)

@push('css')
<style>
    .print-header { border-bottom: none; margin-bottom: 0; }
    .routine-doc-title {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 4px;
        text-decoration: underline;
        margin: 6px 0 4px;
    }
    .routine-doc-subtitle {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 12px;
    }
    .section-title {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        margin: 10px 0 4px;
        border-bottom: 1px solid #333;
        padding-bottom: 2px;
    }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td {
        border: 2px solid #000;
        padding: 7px 4px;
        text-align: center;
        font-size: 11px;
        vertical-align: middle;
    }
    th { background: #fff !important; font-weight: bold; }
    td.day-cell { font-weight: bold; width: 70px; }
    td.spcl-cell, th.spcl-head { width: 55px; }
    .period-num { font-size: 12px; font-weight: bold; }
    .period-time { font-size: 9px; font-weight: normal; }
    .sub-name { font-weight: bold; font-size: 11px; }
    .tch-name { font-size: 9px; }
    .sig-row {
        display: flex;
        justify-content: flex-end;
        margin-top: 36px;
    }
    .sig-box {
        text-align: center;
        min-width: 160px;
        border-top: 1px solid #000;
        padding-top: 5px;
        font-size: 11px;
    }
    @media print {
        @page { size: A4 landscape; margin: 8mm 10mm; }
        .container { padding: 0; }
    }
</style>
@endpush

@section('contents')
@php
    $schoolName  = \ME\SchoolManagement\Models\SchoolSetting::where('key','school_name')->value('value') ?? config('app.name','School');
    $academicYear = \ME\SchoolManagement\Models\AcademicYear::where('is_current', true)->first();
    $ordinals = ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th'];

    $classLevelRoutines = $routines->whereNull('section_id');
    $allPeriods = $routines->pluck('start_time')->unique()->sort()->values();

    // sections to render: each section OR one block for class-level if no sections
    $renderSections = $class->sections->isNotEmpty() ? $class->sections : collect([null]);
@endphp

{{-- School header --}}
<div class="print-header">
    <div class="company-info">
        <div class="company-name">{{ $schoolName }}</div>
        <div class="print-time">Printed: {{ now()->format('d M Y, h:i A') }}</div>
    </div>
</div>

<div class="routine-doc-title">Class Routine{{ $selectedTitle ? ' — ' . $selectedTitle : '' }}</div>
<div class="routine-doc-subtitle">
    Class: <strong>{{ $class->name }}</strong>
    @if($academicYear) &nbsp;—&nbsp; Session: {{ $academicYear->name }} @endif
</div>

@foreach($renderSections as $section)
@php
    if ($section) {
        $secSpecific = $routines->where('section_id', $section->id);
        $secRoutines = $secSpecific->merge($classLevelRoutines);
        $sectionLabel = $section->name . ' Section';
    } else {
        $secRoutines = $classLevelRoutines->isNotEmpty() ? $classLevelRoutines : $routines;
        $secSpecific = $secRoutines;
        $sectionLabel = null;
    }
    $periods = $secRoutines->pluck('start_time')->unique()->sort()->values();
    if ($periods->isEmpty()) $periods = $allPeriods;
@endphp

@if($sectionLabel)
<div class="section-title">{{ $sectionLabel }}</div>
@endif

<table>
    <thead>
        <tr>
            <th style="width:72px;">{{ sm_t('DAYS') }}</th>
            <th class="spcl-head">{{ sm_t('SP. CL.') }}</th>
            @foreach($periods as $i => $time)
            <th>
                <div class="period-num">{{ $ordinals[$i] ?? ($i+1).'th' }} Period</div>
                <div class="period-time">{{ \Carbon\Carbon::parse($time)->format('g:i A') }}</div>
            </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($days as $day)
        @php $dayRoutines = $secRoutines->where('day', $day); @endphp
        <tr>
            <td class="day-cell">{{ $day }}</td>
            <td class="spcl-cell">&nbsp;</td>
            @foreach($periods as $time)
            @php $r = $dayRoutines->where('start_time', $time)->first(); @endphp
            <td>
                @if($r && $r->subject)
                    <div class="sub-name">{{ $r->subject->name }}</div>
                    @if($r->teacher)
                    <div class="tch-name">{{ $r->teacher->first_name ?? '' }} {{ $r->teacher->last_name ?? '' }}</div>
                    @endif
                @endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

@if(!$loop->last)<div style="page-break-after:always;"></div>@endif
@endforeach

<div class="sig-row">
    <div class="sig-box">Head Teacher / Principal</div>
</div>

@endsection
