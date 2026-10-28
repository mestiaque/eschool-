@extends('me::printMaster2')
@section('title', sm_t('Admit Card') . ' — ' . $exam->name)

@push('css')
<style>
    .ac-card { border: 2px solid #1a237e; padding: 10px 14px; margin-bottom: 8mm; min-height: 128mm; display: flex; flex-direction: column; page-break-inside: avoid; break-inside: avoid; }
    .ac-card:nth-child(2n) { page-break-after: always; break-after: page; }
    .ac-card:last-child { page-break-after: auto; break-after: auto; }
    .ac-head { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1a237e; padding-bottom: 4px; }
    .ac-school { font-size: 20px; font-weight: bold; color: #1a237e; }
    .ac-title { display: inline-block; background: #1a237e; color: #fff; padding: 2px 14px; font-weight: bold; letter-spacing: 1px; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .ac-exam { font-size: 12px; margin-top: 2px; }
    .ac-body { display: flex; gap: 14px; margin-top: 8px; }
    .ac-info { flex: 1; font-size: 12px; }
    .ac-info td { border: none; padding: 2px 6px 2px 0; font-size: 12px; text-align: left; }
    .ac-info td:first-child { color: #555; width: 90px; }
    .ac-photo { width: 25mm; height: 30mm; border: 1px solid #999; object-fit: cover; display: flex; align-items: center; justify-content: center; font-size: 28px; color: #1a237e; font-weight: bold; }
    table.ac-routine { margin: 6px 0 0; }
    table.ac-routine th, table.ac-routine td { padding: 2px 6px; font-size: 10.5px; text-align: center; }
    table.ac-routine td.sub { text-align: left; }
    .ac-rules { font-size: 9.5px; color: #444; margin-top: 4px; }
    .ac-sign { margin-top: auto; padding-top: 26px; display: flex; justify-content: space-between; font-size: 11px; }
    .ac-sign div { min-width: 130px; text-align: center; border-top: 1px solid #000; padding-top: 2px; }
    @media print { @page { size: A4 portrait; margin: 8mm; } }
</style>
@endpush

@section('contents')
@php $schoolName = sm_setting('school_name') ?: config('app.name', 'School'); @endphp
@forelse($students as $student)
<div class="ac-card">
    <div class="ac-head">
        <div>
            <div class="ac-school">{{ $schoolName }}</div>
            <div class="ac-exam">{{ $exam->name }}{{ $exam->academicYear ? ' — ' . $exam->academicYear->name : '' }}</div>
        </div>
        <div class="ac-title">{{ sm_t('Admit Card') }}</div>
    </div>
    <div class="ac-body">
        <table class="ac-info">
            <tr><td>{{ sm_t('Student') }}:</td><td><strong>{{ $student->full_name }}</strong></td></tr>
            <tr><td>{{ sm_t('Student ID') }}:</td><td>{{ $student->student_id ?? '—' }}</td></tr>
            <tr><td>{{ sm_t('Class') }}:</td><td>{{ $student->classe?->name }}@if($student->section) / {{ $student->section->name }}@endif</td></tr>
            <tr><td>{{ sm_t('Roll') }}:</td><td><strong>{{ $student->roll_number ?? '—' }}</strong></td></tr>
        </table>
        @if($student->photo)
        <img src="{{ asset('storage/' . $student->photo) }}" class="ac-photo" alt="">
        @else
        <div class="ac-photo">{{ mb_substr($student->first_name, 0, 1) }}</div>
        @endif
    </div>
    @if($schedules->isNotEmpty())
    <table class="ac-routine">
        <thead><tr><th>{{ sm_t('Date') }}</th><th>{{ sm_t('Day') }}</th><th>{{ sm_t('Subject') }}</th><th>{{ sm_t('Time') }}</th></tr></thead>
        <tbody>
            @foreach($schedules as $sc)
            <tr>
                <td>{{ sm_date($sc->date) }}</td>
                <td>{{ sm_t($sc->date?->format('l') ?? '') }}</td>
                <td class="sub">{{ $sc->subject?->name }}</td>
                <td>{{ sm_time($sc->start_time) }} – {{ sm_time($sc->end_time) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    <div class="ac-rules">{{ sm_t('Bring this card to every exam. Be in the hall 15 minutes before the start. Electronic devices are not allowed.') }}</div>
    <div class="ac-sign">
        <div>{{ sm_t('Class Teacher') }}</div>
        <div>{{ sm_t('Head Teacher / Principal') }}</div>
    </div>
</div>
@empty
@include('school-management::partials.print-header', ['title' => sm_t('Admit Card')])
<p style="text-align:center">{{ sm_t('No students found.') }}</p>
@endforelse
@endsection
