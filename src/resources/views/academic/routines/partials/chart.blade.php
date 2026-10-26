{{-- One printable timetable: time slots across, days down. Pass $chart (class, section, routines), $title, and optionally $onlyDay / $filterNote. --}}
@use('ME\SchoolManagement\Services\RoutineGrid')
@php
    $schoolName = sm_setting('school_name') ?: config('app.name', 'School');
    $session = \ME\SchoolManagement\Models\AcademicYear::where('is_current', true)->value('name');
    $slots = RoutineGrid::slots($chart['routines']);
    $days = RoutineGrid::days($chart['routines'], $onlyDay ?? null);
@endphp

@once
@push('css')
<style>
    .routine-chart { page-break-after: always; break-after: page; margin-bottom: 40px; padding-bottom: 30px; border-bottom: 2px dashed #ccc; }
    .routine-chart:last-child { border-bottom: none; }
    .routine-chart:last-child { page-break-after: auto; break-after: auto; }
    .rc-head { text-align: center; margin-bottom: 10px; }
    .rc-school { font-size: 24px; font-weight: bold; color: #0047ab; }
    .rc-title { display: inline-block; font-size: 16px; font-weight: bold; border: 2px solid #000; border-radius: 4px; padding: 2px 18px; margin: 6px 0 4px; }
    .rc-meta { font-size: 13px; }
    .rc-meta strong { font-size: 15px; }
    .rc-filter { font-size: 11px; color: #666; }
    table.rc-table { table-layout: fixed; width: 100%; border-collapse: collapse; margin-bottom: 0; }
    table.rc-table th, table.rc-table td { border: 1.5px solid #000; padding: 6px 4px; text-align: center; vertical-align: middle; font-size: 12px; }
    table.rc-table th { background: #e9ecef !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .rc-table .rc-corner { width: 90px; font-size: 11px; }
    .rc-table .rc-day { font-weight: bold; background: #f4f6f8 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .rc-table .rc-break { width: 34px; padding: 2px; background: #f4f6f8 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .rc-table .rc-break span { writing-mode: vertical-rl; transform: rotate(180deg); font-size: 11px; font-weight: bold; letter-spacing: 1px; }
    .rc-table .rc-off { font-weight: bold; color: #888; letter-spacing: 2px; }
    .rc-num { font-size: 13px; font-weight: bold; }
    .rc-time { font-size: 10px; font-weight: normal; white-space: nowrap; }
    .rc-sub { font-weight: bold; font-size: 13px; }
    .rc-tch { font-size: 11px; }
    .rc-room { font-size: 10px; color: #555; }
    .rc-sec { font-size: 10px; color: #555; }
    .rc-item + .rc-item { border-top: 1px dashed #999; margin-top: 3px; padding-top: 3px; }
    .rc-clash { outline: 2px solid #dc3545; outline-offset: -3px; }
    .rc-sign { display: flex; justify-content: space-between; margin-top: 50px; font-size: 12px; }
    .rc-sign div { min-width: 170px; text-align: center; border-top: 1px solid #000; padding-top: 4px; }
    .rc-printed { font-size: 10px; color: #888; text-align: right; margin-top: 6px; }
    @media print {
        @page { size: A4 landscape; margin: 8mm 10mm; }
        .routine-chart { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        table.rc-table td { height: 15mm; }
    }
</style>
@endpush
@endonce

<div class="routine-chart">
    <div class="rc-head">
        <div class="rc-school">{{ $schoolName }}</div>
        <div class="rc-title">{{ sm_t('Class Routine') }}@if($title) — {{ $title }}@endif</div>
        <div class="rc-meta">
            {{ sm_t('Class') }}: <strong>{{ $chart['class']->name ?? '' }}</strong>
            @if($chart['section']) &nbsp;|&nbsp; {{ sm_t('Section') }}: <strong>{{ $chart['section']->name }}</strong>@endif
            @if($session) &nbsp;|&nbsp; {{ sm_t('Session') }}: {{ $session }}@endif
        </div>
        @if(!empty($filterNote))<div class="rc-filter">{{ $filterNote }}</div>@endif
    </div>

    <table class="rc-table">
        <thead>
            <tr>
                <th class="rc-corner">{{ sm_t('Day') }} / {{ sm_t('Time') }}</th>
                @foreach($slots as $slot)
                    @if($slot['type'] === 'break')
                    <th class="rc-break"><span class="rc-time">{{ sm_time($slot['start']) }} – {{ sm_time($slot['end']) }}</span></th>
                    @else
                    <th>
                        <div class="rc-num">{{ sm_t('Period') }} {{ $slot['number'] }}</div>
                        <div class="rc-time">{{ sm_time($slot['start']) }} – {{ sm_time($slot['end']) }}</div>
                    </th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($days as $day => $closed)
            <tr>
                <td class="rc-day">{{ sm_t($day) }}</td>
                @if($closed)
                    <td colspan="{{ max(count($slots), 1) }}" class="rc-off">{{ sm_t('Weekly holiday') }}</td>
                @else
                    @forelse($slots as $slot)
                        @if($slot['type'] === 'break')
                        <td class="rc-break"><span>{{ sm_t('Break') }}</span></td>
                        @else
                        @php $items = RoutineGrid::cell($chart['routines'], $day, $slot); @endphp
                        <td class="{{ $items->count() > 1 ? 'rc-clash' : '' }}">
                            @foreach($items as $r)
                            <div class="rc-item">
                                <div class="rc-sub">{{ $r->subject->name ?? '—' }}</div>
                                @if($r->teacher)<div class="rc-tch">{{ $r->teacher->full_name }}</div>@endif
                                @if($r->room)<div class="rc-room">{{ $r->room->name }}</div>@endif
                                @if($items->count() > 1 && $r->section)<div class="rc-sec">{{ sm_t('Section') }}: {{ $r->section->name }}</div>@endif
                            </div>
                            @endforeach
                        </td>
                        @endif
                    @empty
                        <td></td>
                    @endforelse
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="rc-sign">
        <div>{{ sm_t('Class Teacher') }}</div>
        <div>{{ sm_t('Head Teacher / Principal') }}</div>
    </div>
    <div class="rc-printed">{{ sm_t('Printed') }}: {{ sm_date(now(), true) }}</div>
</div>
