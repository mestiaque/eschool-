@extends('me::master')
@section('title', sm_t('Teacher Leaves'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#leaveModal"><i class="fas fa-plus"></i> Apply Leave</button>
@endpush

@section('content')

<div class="card">
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Teacher') }}</th>
                    <th>{{ sm_t('Leave Type') }}</th>
                    <th>{{ sm_t('From') }}</th>
                    <th>{{ sm_t('To') }}</th>
                    <th>{{ sm_t('Days') }}</th>
                    <th>{{ sm_t('Reason') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $i => $leave)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $leave->teacher?->full_name }}</td>
                    <td>{{ $leave->leaveType?->name }}</td>
                    <td>{{ sm_date($leave->start_date) }}</td>
                    <td>{{ sm_date($leave->end_date) }}</td>
                    <td>{{ $leave->days }}</td>
                    <td><small>{{ Str::limit($leave->reason, 40) }}</small></td>
                    <td>
                        @php $c=['approved'=>'success','rejected'=>'danger','pending'=>'warning']; @endphp
                        <span class="badge bg-{{ $bc = $c[$leave->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($leave->status) }}</span>
                    </td>
                    <td>
                        @if($leave->status === 'pending')
                        <form method="POST" action="{{ route('teachers.leaves.approve', $leave->id) }}" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-encodex-active">{{ sm_t('Approve') }}</button></form>
                        <form method="POST" action="{{ route('teachers.leaves.reject', $leave->id) }}" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-encodex-deactive">{{ sm_t('Reject') }}</button></form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-3">No leave applications</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-2">{{ $leaves->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<div class="modal fade" id="leaveModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('teachers.leaves.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ sm_t('Apply Leave') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Teacher *</label>
                        <select name="teacher_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Teacher') }}</option>
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Leave Type *</label>
                        <select name="leave_type_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Type') }}</option>
                            @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label">From *</label><input type="date" name="start_date" class="form-control form-control-sm" required></div>
                        <div class="col"><label class="form-label">To *</label><input type="date" name="end_date" class="form-control form-control-sm" required></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Reason') }}</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Submit') }}</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
