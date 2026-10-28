@extends('me::printMaster2')
@section('title', sm_t('Mark Sheet') . ' — ' . $student->full_name)

@section('contents')
<div class="no-print" style="margin-bottom:8px">
    <a href="{{ route('portal.results') }}" style="text-decoration:none;color:#0047ab;font-size:13px">← {{ sm_t('Back') }}</a>
</div>
@include('school-management::exams.partials.marksheet-body')
@endsection
