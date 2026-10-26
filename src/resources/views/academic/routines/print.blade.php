@extends('me::printMaster2')
@section('title', sm_t('Class Routines'))

@section('contents')
@php
    $filterNote = collect([
        request('day') ? sm_t('Day') . ': ' . sm_t(request('day')) : null,
        request('room_id') ? sm_t('Room') . ': ' . optional($rooms->firstWhere('id', request('room_id')))->name : null,
        request('teacher_id') === 'none' ? sm_t('No teacher assigned')
            : (request('teacher_id') ? sm_t('Teacher') . ': ' . optional($teachers->firstWhere('id', request('teacher_id')))->full_name : null),
    ])->filter()->implode(' | ');
@endphp

@forelse($charts as $title => $titleCharts)
    @foreach($titleCharts as $chart)
        @include('school-management::academic.routines.partials.chart', ['chart' => $chart, 'title' => $title, 'onlyDay' => request('day'), 'filterNote' => $filterNote])
    @endforeach
@empty
    @include('school-management::partials.print-header', ['title' => sm_t('Class Routine')])
    <p style="text-align:center">{{ sm_t('No routines found') }}</p>
@endforelse
@endsection
