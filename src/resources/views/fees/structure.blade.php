@extends('me::master')
@section('title', sm_t('Fee Structure'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#structureModal">
        <i class="fas fa-plus"></i> Add Structure
    </button>
@endpush

@section('content')

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Fee Type') }}</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Academic Year') }}</th>
                    <th class="text-end">{{ sm_t('Amount') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($structures as $i => $s)
                <tr>
                    <td class="text-muted small">{{ $i+1 }}</td>
                    <td class="fw-semibold">{{ $s->feeType?->name ?? '—' }}</td>
                    <td>{{ $s->class?->name ?? '—' }}</td>
                    <td>{{ $s->academicYear?->name ?? '—' }}</td>
                    <td class="text-end fw-semibold">{{ sm_money($s->amount, 2) }}</td>
                    <td class="text-center">
                        <form method="POST" action="{{ route('fees.structure.destroy', $s->id) }}" class="d-inline" onsubmit="return confirm('Delete this fee structure?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No fee structures defined. Add one to enable monthly fee generation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Structure Modal --}}
<div class="modal fade" id="structureModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('fees.structure.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title"><i class="fas fa-sitemap me-2"></i>Add Fee Structure</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Fee Type') }} <span class="text-danger">*</span></label>
                            <select name="fee_type_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach($feeTypes as $ft)
                                <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Class') }} <span class="text-danger">*</span></label>
                            <select name="class_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Academic Year') }} <span class="text-danger">*</span></label>
                            <select name="academic_year_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_current ? 'selected':'' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Amount') }} <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">{{ sm_currency() }}</span>
                                <input type="number" name="amount" class="form-control form-control-sm" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
