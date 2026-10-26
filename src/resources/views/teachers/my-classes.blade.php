@extends('me::master')
@section('title', sm_t('My Classes'))

@section('content')

@if(!$teacherId)
<div class="alert alert-warning">Your login account isn't linked to a teacher profile yet. Ask an admin to link your account from the Teachers page.</div>
@else
<div class="card shadow">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-0">{{ sm_t('Date') }}</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
            </div>
            <div class="col-md">
                <span class="text-muted small">{{ $dayName }}, {{ sm_date(\Carbon\Carbon::parse($date)) }}</span>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-encodex mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ sm_t('Time') }}</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Section') }}</th>
                    <th>{{ sm_t('Subject') }}</th>
                    <th>{{ sm_t('Room') }}</th>
                    <th>{{ sm_t('Class Test') }}</th>
                    <th>{{ sm_t('Assignment') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periods as $period)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }}&ndash;{{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}</td>
                    <td>{{ $period->class->name ?? '—' }}</td>
                    <td>{{ $period->section->name ?? '—' }}</td>
                    <td>{{ $period->subject->name ?? '—' }}</td>
                    <td>{{ $period->room->name ?? '—' }}</td>
                    <td class="text-center">
                        @if($period->has_class_test)
                        <span class="badge bg-warning-subtle text-warning">{{ sm_t('Scheduled') }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($period->has_assignment_due)
                        <span class="badge bg-info-subtle text-info">{{ sm_t('Due Today') }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('my.attendance', ['classId' => $period->class_id, 'section_id' => $period->section_id, 'date' => $date]) }}" class="btn btn-sm btn-encodex-show">
                            <i class="fas fa-user-check"></i> {{ sm_t('Take Attendance') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-3">{{ sm_t('No classes scheduled for this day.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
