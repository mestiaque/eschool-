@extends('me::master')
@section('title', 'Class Routine - ' . $class->name)
@push('buttons')
    <a href="{{ route('academic.classes') }}" class="btn btn-sm btn-encodex-list">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    <a href="{{ route('academic.classes.routine.print', array_filter(['id' => $class->id, 'title' => $selectedTitle])) }}" target="_blank" class="btn btn-sm btn-encodex-print no-print">
        <i class="fas fa-print"></i> Print
    </a>
@endpush

@section('content')

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
    }
    .card-body {
        padding: 0 !important;
    }
    body {
        font-size: 12px;
    }
    table {
        font-size: 11px;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #000 !important;
    }
}
</style>

<div class="card shadow">
    <div class="card-header bg-primary text-white no-print">
        <h5 class="mb-0">Class Routine: {{ $class->name }}{{ $selectedTitle ? ' — ' . $selectedTitle : '' }}</h5>
    </div>
    @if($titles->isNotEmpty())
    <div class="card-body py-2 border-bottom no-print">
        <form method="GET" class="glass-search-form d-flex align-items-center gap-2">
            <label class="form-label form-label-sm mb-0">{{ sm_t('Routine Version:') }}</label>
            <select name="title" class="form-control form-control-sm w-auto" onchange="this.form.submit()">
                @foreach($titles as $t)
                <option value="{{ $t }}" {{ $selectedTitle == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </form>
    </div>
    @endif
    <div class="card-body">
        @php
            $defaultPeriodSlots = collect([
                '08:45:00',
                '09:30:00',
                '10:10:00',
                '10:30:00',
                '11:10:00',
                '11:50:00',
            ])->mapWithKeys(function ($time) {
                return [$time => collect()];
            });

            $globalPeriods = $routines->groupBy('start_time')->sortKeys();
            if ($globalPeriods->isEmpty()) {
                $globalPeriods = $defaultPeriodSlots;
            }
        @endphp

        @php
            $classLevelRoutines = $routines->whereNull('section_id');
            $ordinals = ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th'];
            // Sections to show: actual sections OR one block for class-level only
            $renderSections = $class->sections->isNotEmpty() ? $class->sections : collect([null]);
        @endphp

        @foreach($renderSections as $section)
        @php
            if ($section) {
                $sectionSpecificRoutines = $routines->where('section_id', $section->id);
                $sectionRoutines = $sectionSpecificRoutines->merge($classLevelRoutines);
                $sectionLabel = $section->name . ' Section';
            } else {
                $sectionRoutines = $classLevelRoutines->isNotEmpty() ? $classLevelRoutines : $routines;
                $sectionSpecificRoutines = $sectionRoutines;
                $sectionLabel = null;
            }
            $periods = $sectionRoutines->groupBy('start_time')->sortKeys();
            if ($periods->isEmpty()) $periods = $globalPeriods;
        @endphp
        <div class="mb-4">
            @if($sectionLabel)
            <h6 class="bg-light p-2 border rounded">{{ $sectionLabel }}</h6>
            @endif
            <div class="table-responsive">
                <table class="table table-encodex table-bordered table-sm text-center">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 80px;">{{ sm_t('Day') }}</th>
                            @foreach($periods as $time => $period)
                            <th>
                                {{ $ordinals[$loop->index] ?? ($loop->index+1).'th' }} Period<br>
                                <small class="fw-normal">{{ \Carbon\Carbon::parse($time)->format('g:i A') }}</small>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                        @php
                            $daySectionSpecific = $sectionSpecificRoutines->where('day', $day);
                            $dayClassLevel = $classLevelRoutines->where('day', $day);
                        @endphp
                        <tr>
                            <td class="table-light fw-bold">{{ $day }}</td>
                            @foreach($periods as $time => $period)
                            @php
                                $routine = $daySectionSpecific->where('start_time', $time)->first()
                                        ?? $dayClassLevel->where('start_time', $time)->first();
                            @endphp
                            <td>
                                @if($routine && $routine->subject)
                                <div class="fw-semibold small">{{ $routine->subject->name }}</div>
                                @if($routine->teacher)
                                <small class="text-info d-block">{{ $routine->teacher->first_name }} {{ $routine->teacher->last_name }}</small>
                                @endif
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
