@extends('school-management::portal.layout')
@section('title', sm_t('My Dashboard'))

@section('content')
<div class="row g-3 mb-3">
    {{-- Student Info Card --}}
    <div class="col-md-4">
        <div class="portal-card p-3 h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                @if($portalStudent->photo)
                <img src="{{ asset('storage/'.$portalStudent->photo) }}" class="rounded-circle" style="width:64px;height:64px;object-fit:cover">
                @else
                <div class="student-avatar bg-primary text-white"><i class="fas fa-user-graduate"></i></div>
                @endif
                <div>
                    <div class="fw-bold fs-6">{{ $portalStudent->first_name }} {{ $portalStudent->last_name }}</div>
                    <div class="text-muted small">{{ $portalStudent->student_id }}</div>
                    <span class="badge bg-primary-subtle text-primary mt-1">{{ $portalStudent->classe?->name ?? '—' }}
                        @if($portalStudent->section) – {{ $portalStudent->section->name }}@endif
                    </span>
                </div>
            </div>
            <table class="table table-sm table-borderless mb-0 small">
                <tr><td class="text-muted pe-2">Roll No.</td><td class="fw-semibold">{{ $portalStudent->roll_number ?? '—' }}</td></tr>
                <tr><td class="text-muted pe-2">Date of Birth</td><td>{{ $portalStudent->date_of_birth ? date('d M Y', strtotime($portalStudent->date_of_birth)) : '—' }}</td></tr>
                <tr><td class="text-muted pe-2">Gender</td><td>{{ ucfirst($portalStudent->gender ?? '—') }}</td></tr>
                <tr><td class="text-muted pe-2">Blood Group</td><td>{{ $portalStudent->blood_group ?? '—' }}</td></tr>
                <tr><td class="text-muted pe-2">Guardian</td><td>{{ $portalStudent->guardian?->name ?? '—' }}</td></tr>
                <tr><td class="text-muted pe-2">Phone</td><td>{{ $portalStudent->phone ?? $portalStudent->guardian?->phone ?? '—' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="col-md-8">
        <div class="row g-2 h-100">
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2 bg-danger bg-opacity-10 text-danger"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="fw-bold fs-5 text-danger">{{ sm_money($dueFees, 0) }}</div>
                    <div class="text-muted small">Due Fees</div>
                    <a href="{{ route('portal.fees') }}" class="stretched-link"></a>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2 bg-success bg-opacity-10 text-success"><i class="fas fa-chart-bar"></i></div>
                    <div class="fw-bold fs-5 text-success">{{ $latestResult?->grade ?? '—' }}</div>
                    <div class="text-muted small">Latest Grade</div>
                    <a href="{{ route('portal.results') }}" class="stretched-link"></a>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2 bg-info bg-opacity-10 text-info"><i class="fas fa-book"></i></div>
                    <div class="fw-bold fs-5 text-info">{{ $activeIssues }}</div>
                    <div class="text-muted small">Books Issued</div>
                    <a href="{{ route('portal.library') }}" class="stretched-link"></a>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2 bg-warning bg-opacity-10 text-warning"><i class="fas fa-calendar-check"></i></div>
                    <div class="fw-bold fs-5 text-warning">{{ $attendancePct }}%</div>
                    <div class="text-muted small">Attendance</div>
                    <a href="{{ route('portal.attendance') }}" class="stretched-link"></a>
                </div>
            </div>

            {{-- Latest notices --}}
            <div class="col-12">
                <div class="portal-card p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-bell text-warning me-2"></i>Recent Notices</h6>
                        <a href="{{ route('portal.notices') }}" class="small text-primary">View all</a>
                    </div>
                    @forelse($recentNotices as $notice)
                    <div class="border-start border-warning border-3 ps-2 mb-2">
                        <div class="fw-semibold small">{{ $notice->title }}</div>
                        <div class="text-muted" style="font-size:11px">{{ sm_date($notice->created_at) }}</div>
                    </div>
                    @empty
                    <div class="text-muted small">No recent notices.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Latest Result Summary --}}
@if($latestResult)
<div class="portal-card p-3 mb-3">
    <h6 class="fw-bold mb-2"><i class="fas fa-trophy text-warning me-2"></i>Latest Exam Result</h6>
    <div class="row g-2 align-items-center">
        <div class="col-auto">
            <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2">{{ $latestResult->grade }}</span>
        </div>
        <div class="col">
            <div class="fw-semibold">{{ $latestResult->exam?->name }}</div>
            <div class="small text-muted">
                Obtained: <strong>{{ $latestResult->obtained_mark }}</strong> /
                {{ $latestResult->total_mark }} &nbsp;|&nbsp;
                GPA: <strong>{{ $latestResult->gpa }}</strong> &nbsp;|&nbsp;
                Position: <strong>{{ $latestResult->position ?? '—' }}</strong>
            </div>
        </div>
        <div class="col-auto">
            <a href="{{ route('portal.marksheet', $latestResult->exam_id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-file-alt me-1"></i>Marksheet
            </a>
        </div>
    </div>
</div>
@endif

{{-- Active Library Issues --}}
@if($activeIssues > 0)
<div class="portal-card p-3">
    <h6 class="fw-bold mb-2"><i class="fas fa-book-open text-info me-2"></i>Books Currently Issued</h6>
    <div class="table-responsive">
        <table class="table table-sm table-portal mb-0">
            <thead><tr><th>{{ sm_t('Book') }}</th><th>{{ sm_t('Copy') }}</th><th>{{ sm_t('Issued On') }}</th><th>{{ sm_t('Due Date') }}</th><th>{{ sm_t('Status') }}</th></tr></thead>
            <tbody>
                @foreach($currentBooks as $issue)
                <tr>
                    <td class="fw-semibold">{{ $issue->book?->title }}</td>
                    <td class="small text-muted">{{ $issue->copy?->copy_id }}</td>
                    <td class="small">{{ sm_date($issue->issue_date) }}</td>
                    <td class="small">{{ sm_date($issue->due_date) }}</td>
                    <td>
                        @if($issue->overdue_days > 0)
                        <span class="badge bg-danger-subtle text-danger">Overdue {{ $issue->overdue_days }}d</span>
                        @else
                        <span class="badge bg-success-subtle text-success">On Time</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
