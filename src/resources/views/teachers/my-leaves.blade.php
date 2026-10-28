@extends('me::master')
@section('title', sm_t('My Leaves'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#leaveModal"><i class="fas fa-plus"></i> {{ sm_t('Apply Leave') }}</button>
@endpush

@section('content')
<div class="row g-2 mb-3">
    @foreach($balances as $b)
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body py-2">
                <div class="small text-muted">{{ $b['type']->name }}</div>
                <div class="fs-5 fw-bold">
                    @if($b['left'] === null) {{ sm_t('No limit') }}
                    @else {{ $b['left'] }} <small class="fs-6 fw-normal text-muted">/ {{ $b['type']->days }}</small>
                    @endif
                </div>
                <div class="small text-muted">{{ sm_t('Used this year') }}: {{ $b['used'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
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
                @forelse($leaves as $leave)
                <tr>
                    <td>{{ $leave->leaveType?->name }}</td>
                    <td>{{ sm_date($leave->start_date) }}</td>
                    <td>{{ sm_date($leave->end_date) }}</td>
                    <td>{{ $leave->days }}</td>
                    <td><small>{{ Str::limit($leave->reason, 50) }}</small>@if($leave->note)<div class="small text-muted">{{ $leave->note }}</div>@endif</td>
                    <td>
                        @php $c = ['approved' => 'success', 'rejected' => 'danger', 'pending' => 'warning']; @endphp
                        <span class="badge bg-{{ $bc = $c[$leave->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t(ucfirst($leave->status)) }}</span>
                    </td>
                    <td>
                        @if($leave->status === 'pending')
                        <form method="POST" action="{{ route('my.leaves.cancel', $leave->id) }}" class="d-inline" onsubmit="return confirm(@json(sm_t('Cancel this leave request?'), JSON_HEX_APOS|JSON_HEX_QUOT))">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete">{{ sm_t('Cancel') }}</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-3">{{ sm_t('No leave applications') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $leaves->links('pagination::bootstrap-5') }}</div>

<div class="modal fade" id="leaveModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('my.leaves.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ sm_t('Apply Leave') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Leave Type') }} *</label>
                        <select name="leave_type_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Type') }}</option>
                            @foreach($balances as $b)
                            <option value="{{ $b['type']->id }}" {{ old('leave_type_id') == $b['type']->id ? 'selected' : '' }}>
                                {{ $b['type']->name }} ({{ $b['left'] === null ? sm_t('No limit') : sm_t('Left') . ': ' . $b['left'] }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label">{{ sm_t('From') }} *</label><input type="date" name="start_date" class="form-control form-control-sm" min="{{ now()->toDateString() }}" value="{{ old('start_date') }}" required></div>
                        <div class="col"><label class="form-label">{{ sm_t('To') }} *</label><input type="date" name="end_date" class="form-control form-control-sm" min="{{ now()->toDateString() }}" value="{{ old('end_date') }}" required></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Reason') }} *</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="3" maxlength="1000" required>{{ old('reason') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Submit') }}</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
