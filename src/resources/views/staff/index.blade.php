@extends('me::master')
@section('title', sm_t('Non-Teaching Staff'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#staffModal">
        <i class="fas fa-plus"></i> Add Staff
    </button>
@endpush

@section('content')


<div class="card shadow">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name / ID / Phone" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="designation" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Designations') }}</option>
                    @foreach($designations as $key => $label)
                    <option value="{{ $key }}" {{ request('designation') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_active" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Status') }}</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ sm_t('Active') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ sm_t('Inactive') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-encodex-search me-1"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('staff.index') }}" class="btn btn-sm btn-encodex-cancel">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Photo') }}</th>
                    <th class="text-start">{{ sm_t('Name') }}</th>
                    <th>{{ sm_t('Staff ID') }}</th>
                    <th>{{ sm_t('Designation') }}</th>
                    <th>{{ sm_t('Phone') }}</th>
                    <th>{{ sm_t('Joining') }}</th>
                    <th>Salary ({{ sm_currency() }})</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staffList as $i => $staff)
                <tr>
                    <td class="text-center">{{ $staffList->firstItem() + $i }}</td>
                    <td class="text-center" style="width:48px">
                        @if($staff->photo)
                            <img src="{{ Storage::url($staff->photo) }}" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                        @else
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center text-white" style="width:36px;height:36px;font-size:14px">
                                {{ strtoupper(substr($staff->first_name,0,1)) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $staff->first_name }} {{ $staff->last_name }}</strong>
                        @if($staff->email)<br><small class="text-muted">{{ $staff->email }}</small>@endif
                    </td>
                    <td class="text-center">{{ $staff->staff_id ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge bg-info-subtle text-info">{{ $designations[$staff->designation] ?? $staff->designation }}</span>
                    </td>
                    <td class="text-center">{{ $staff->phone ?? '—' }}</td>
                    <td class="text-center">{{ sm_date($staff->joining_date) ?? '—' }}</td>
                    <td class="text-end">{{ number_format($staff->salary, 0) }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $bc = $staff->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">
                            {{ $staff->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-encodex-edit"
                            onclick="openEditStaff({{ $staff->id }}, {{ json_encode($staff) }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('staff.destroy', $staff->id) }}" class="d-inline"
                            onsubmit="return confirm('Delete this staff member?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-3">No staff members found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $staffList->links('pagination::bootstrap-5') }}</div>

{{-- Add Staff Modal --}}
<div class="modal fade" id="staffModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('Add Staff Member') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('staff.store') }}" enctype="multipart/form-data">
                @csrf
                @include('school-management::staff._form')
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Staff Modal --}}
<div class="modal fade" id="editStaffModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('Edit Staff Member') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editStaffForm" enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('school-management::staff._form', ['edit' => true])
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditStaff(id, s) {
    const form = document.getElementById('editStaffForm');
    form.action = '/staff/' + id;
    const f = (n, v) => { const el = form.querySelector('[name="' + n + '"]'); if(el) el.value = v ?? ''; };
    f('staff_id', s.staff_id); f('first_name', s.first_name); f('last_name', s.last_name);
    f('gender', s.gender); f('date_of_birth', s.date_of_birth?.substring(0,10));
    f('religion', s.religion); f('blood_group', s.blood_group);
    f('phone', s.phone); f('email', s.email); f('nid', s.nid);
    f('present_address', s.present_address); f('permanent_address', s.permanent_address);
    f('designation', s.designation); f('joining_date', s.joining_date?.substring(0,10));
    f('leaving_date', s.leaving_date?.substring(0,10));
    f('salary', s.salary); f('bank_name', s.bank_name); f('bank_account_no', s.bank_account_no);
    f('emergency_contact_name', s.emergency_contact_name);
    f('emergency_contact_phone', s.emergency_contact_phone);
    f('relationship', s.relationship); f('notes', s.notes);
    const isActive = form.querySelector('[name="is_active"]');
    if (isActive) isActive.checked = !!s.is_active;
    new bootstrap.Modal(document.getElementById('editStaffModal')).show();
}
</script>
@endsection
