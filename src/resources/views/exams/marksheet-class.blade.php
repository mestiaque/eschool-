@extends('me::printMaster2')
@section('title', sm_t('Mark Sheet') . ' — ' . $exam->name)

@section('contents')
@forelse($sheets as $sheet)
    @include('school-management::exams.partials.marksheet-body', ['student' => $sheet['student'], 'sheet' => $sheet])
@empty
    @include('school-management::partials.print-header', ['title' => sm_t('Mark Sheet')])
    <p style="text-align:center">{{ sm_t('No results found. Generate results first.') }}</p>
@endforelse
@endsection
