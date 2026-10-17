@extends('me::master')
@section('title', sm_t('My Schedule'))

@section('content')
@if(!$teacherId)
<div class="alert alert-warning">Your login account isn't linked to a teacher profile yet. Ask an admin to link your account from the Teachers page.</div>
@else
<div class="row g-3">
    @foreach($days as $day)
    <div class="col-md-6 col-xl-4">
        <div class="card shadow h-100">
            <div class="card-header py-2"><strong>{{ $day }}</strong></div>
            <div class="card-body p-0">
                @forelse($scheduleByDay[$day] as $routine)
                <div class="px-3 py-2 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">{{ $routine->subject->name ?? '—' }}</div>
                            <div class="small text-muted">
                                {{ $routine->class->name ?? '—' }}
                                @if($routine->section)
                                &bull; {{ $routine->section->name }}
                                @endif
                            </div>
                        </div>
                        <div class="text-end small">
                            <div>{{ \Carbon\Carbon::parse($routine->start_time)->format('h:i A') }}&ndash;{{ \Carbon\Carbon::parse($routine->end_time)->format('h:i A') }}</div>
                            @if($routine->room)
                            <div class="text-muted">{{ $routine->room->name }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-3 py-3 text-center text-muted small">No classes</div>
                @endforelse
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
