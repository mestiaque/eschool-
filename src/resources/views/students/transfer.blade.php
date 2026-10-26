@extends('me::master')

@section('title', sm_t('Student Transfer'))

@push('buttons')
<button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#transferModal">
    <i class="fas fa-plus"></i> {{ sm_t('New Transfer') }}
</button>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-encodex table-sm table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ sm_t('ID') }}</th>
                                    <th>{{ sm_t('Student') }}</th>
                                    <th>{{ sm_t('From School') }}</th>
                                    <th>{{ sm_t('To School') }}</th>
                                    <th>{{ sm_t('Transfer Date') }}</th>
                                    <th>{{ sm_t('Status') }}</th>
                                    <th>{{ sm_t('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $transfer)
                                    <tr>
                                        <td>{{ $transfer->id }}</td>
                                        <td>{{ $transfer->student->first_name ?? 'N/A' }} {{ $transfer->student->last_name ?? '' }}</td>
                                        <td>{{ $transfer->from_school }}</td>
                                        <td>{{ $transfer->to_school }}</td>
                                        <td>{{ sm_date($transfer->transfer_date) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $bc = ['approved' => 'success', 'rejected' => 'danger'][$transfer->status] ?? 'warning' }}-subtle text-{{ $bc }}">
                                                {{ sm_t(ucfirst($transfer->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($transfer->status === 'pending')
                                            <form method="POST" action="{{ route('students.transfer.approve', $transfer->id) }}" class="d-inline" onsubmit="return confirm('Approve transfer? The student will be marked inactive.')">
                                                @csrf
                                                <button class="btn btn-encodex-save btn-sm">{{ sm_t('Approve') }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('students.transfer.reject', $transfer->id) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-encodex-cancel btn-sm">{{ sm_t('Reject') }}</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ sm_t('No transfers found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $transfers->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('students.transfer.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('New Transfer') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Student') }} <span class="text-danger">*</span></label>
                        @include('school-management::partials.student-select', ['name' => 'student_id', 'required' => true, 'placeholder' => 'Select Student'])
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('From School') }}</label>
                        <input type="text" name="from_school" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('To School') }} <span class="text-danger">*</span></label>
                        <input type="text" name="to_school" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Transfer Date') }}</label>
                        <input type="date" name="transfer_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Reason') }}</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-encodex-cancel btn-sm" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Submit') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
