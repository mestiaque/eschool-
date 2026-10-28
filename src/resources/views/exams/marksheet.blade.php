@extends('me::printMaster2')
@section('title', sm_t('Mark Sheet') . ' — ' . $student->full_name)

@section('contents')
@include('school-management::exams.partials.marksheet-body')
@endsection
