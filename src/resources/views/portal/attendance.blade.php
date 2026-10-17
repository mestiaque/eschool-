@extends('school-management::portal.layout')
@section('title', sm_t('Attendance'))

@section('content')
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2 bg-primary bg-opacity-10 text-primary"><i class="fas fa-calendar-alt"></i></div>
            <div class="fw-bold fs-4">{{ $totalDays }}</div>
            <div class="text-muted small">School Days</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2 bg-success bg-opacity-10 text-success"><i class="fas fa-calendar-check"></i></div>
            <div class="fw-bold fs-4 text-success">{{ $presentDays }}</div>
            <div class="text-muted small">Present</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2 bg-danger bg-opacity-10 text-danger"><i class="fas fa-calendar-times"></i></div>
            <div class="fw-bold fs-4 text-danger">{{ $absentDays }}</div>
            <div class="text-muted small">Absent</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2 bg-warning bg-opacity-10 text-warning"><i class="fas fa-percentage"></i></div>
            <div class="fw-bold fs-4 text-{{ $attendancePct >= 75 ? 'success' : 'danger' }}">{{ $attendancePct }}%</div>
            <div class="text-muted small">Attendance</div>
        </div>
    </div>
</div>

@if($attendancePct < 75)
<div class="alert alert-warning border-warning py-2 small mb-3">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Your attendance is below <strong>75%</strong>. Please contact your class teacher.
</div>
@endif

<div class="portal-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Monthly Breakdown</h6>
        <form method="GET" class="d-flex gap-2">
            <select name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                @foreach($availableYears as $yr)
                <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-portal mb-0">
            <thead><tr><th>{{ sm_t('Month') }}</th><th class="text-center">{{ sm_t('School Days') }}</th><th class="text-center">{{ sm_t('Present') }}</th><th class="text-center">{{ sm_t('Absent') }}</th><th class="text-center">{{ sm_t('Late') }}</th><th class="text-center">%</th></tr></thead>
            <tbody>
                @forelse($monthly as $row)
                <tr>
                    <td class="fw-semibold">{{ date('F', mktime(0,0,0,(int)$row->month,1,$selectedYear)) }}</td>
                    <td class="text-center">{{ $row->total }}</td>
                    <td class="text-center text-success fw-semibold">{{ $row->present }}</td>
                    <td class="text-center text-danger">{{ $row->absent }}</td>
                    <td class="text-center text-warning">{{ $row->late ?? 0 }}</td>
                    <td class="text-center">
                        @php $pct = $row->total > 0 ? round(($row->present/$row->total)*100) : 0; @endphp
                        <span class="badge bg-{{ $bc = $pct >= 75 ? 'success' : 'danger' }}-subtle text-{{ $bc }}">{{ $pct }}%</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No attendance records for {{ $selectedYear }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
