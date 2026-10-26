@extends('me::printMaster2')
@section('title', sm_t('Class Routine') . ' - ' . $class->name)

@section('contents')
@forelse($charts as $chart)
    @include('school-management::academic.routines.partials.chart', ['chart' => $chart, 'title' => $selectedTitle])
@empty
    @include('school-management::partials.print-header', ['title' => sm_t('Class Routine') . ' — ' . $class->name])
    <p style="text-align:center">{{ sm_t('No routines found') }}</p>
@endforelse
@endsection
