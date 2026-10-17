@extends('me::master')
@section('title', sm_t('Dashboard'))

@section('content')
@if(!$teacherId)
<div class="alert alert-warning">Your login account isn't linked to a teacher profile yet. Ask an admin to link your account from the Teachers page.</div>
@else

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="fas fa-calendar-day fa-lg text-primary"></i></div>
                <div><div class="fs-4 fw-bold">{{ $todaysPeriods->count() }}</div><div class="text-muted small">Today's Classes</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-layer-group fa-lg text-info"></i></div>
                <div><div class="fs-4 fw-bold">{{ $totalClasses }}</div><div class="text-muted small">My Classes</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-user-graduate fa-lg text-success"></i></div>
                <div><div class="fs-4 fw-bold">{{ $totalStudents }}</div><div class="text-muted small">Total Students</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-pencil-alt fa-lg text-warning"></i></div>
                <div><div class="fs-4 fw-bold">{{ $pendingTests }}</div><div class="text-muted small">Pending Class Tests</div></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Today's Schedule --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="fas fa-calendar-day text-primary me-2"></i>Today's Classes</h6>
                <a href="{{ route('my.classes') }}" class="btn btn-sm btn-encodex-show ms-auto">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover table-encodex mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ sm_t('Time') }}</th>
                            <th>{{ sm_t('Class') }}</th>
                            <th>{{ sm_t('Subject') }}</th>
                            <th>{{ sm_t('Room') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todaysPeriods as $period)
                        <tr>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }}</td>
                            <td>{{ $period->class->name ?? '—' }}{{ $period->section ? ' / ' . $period->section->name : '' }}</td>
                            <td>{{ $period->subject->name ?? '—' }}</td>
                            <td>{{ $period->room->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-mug-hot fa-lg d-block mb-2 opacity-50"></i>
                                No classes scheduled for today.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Notices --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="fas fa-bullhorn text-danger me-2"></i>Recent Notices</h6>
                <a href="{{ route('my.notices') }}" class="btn btn-sm btn-encodex-show ms-auto">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($recentNotices as $notice)
                <div class="d-flex gap-2 p-3 border-bottom" style="border-left: 3px solid var(--bs-info, #0dcaf0);">
                    <i class="fas fa-circle-info text-info mt-1"></i>
                    <div>
                        <div class="fw-semibold small">
                            {{ $notice->title }}
                            @if($notice->created_at && $notice->created_at->gt(now()->subHours(24)))
                            <span class="badge bg-danger-subtle text-danger ms-1">New</span>
                            @endif
                        </div>
                        <div class="text-muted small"><i class="fas fa-clock me-1"></i>{{ sm_date($notice->publish_date) }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-lg d-block mb-2 opacity-50"></i>
                    No notices available.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Quick Links --}}
<div class="row g-3 mt-1">
    <div class="col-6 col-md-3">
        <a href="{{ route('my.schedule') }}" class="btn btn-encodex w-100 py-3"><i class="fas fa-calendar-alt me-1"></i> My Schedule</a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('my.class-tests') }}" class="btn btn-encodex w-100 py-3"><i class="fas fa-pencil-alt me-1"></i> Class Tests ({{ $pendingTests }})</a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('my.assignments') }}" class="btn btn-encodex w-100 py-3"><i class="fas fa-tasks me-1"></i> Assignments ({{ $activeAssignments }})</a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('my.notices') }}" class="btn btn-encodex w-100 py-3"><i class="fas fa-bullhorn me-1"></i> Notice Board</a>
    </div>
</div>
@endif
@endsection
