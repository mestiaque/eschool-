@extends('me::master')
@section('title', sm_t('Teachers'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#teacherModal" onclick="resetTeacherForm()">
    <i class="fas fa-plus"></i> {{ sm_t('Add Teacher') }}
</button>
@endpush

@section('content')

<div class="card">
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ sm_t('ID') }}</th>
                    <th>{{ sm_t('Name') }}</th>
                    <th>{{ sm_t('Designation') }}</th>
                    <th>{{ sm_t('Phone') }}</th>
                    <th>{{ sm_t('Email') }}</th>
                    <th>{{ sm_t('Joining Date') }}</th>
                    <th>{{ sm_t('Salary') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teachers as $teacher)
                <tr>
                    <td>{{ $teacher->teacher_id }}</td>
                    <td>{{ $teacher->first_name }} {{ $teacher->last_name }}</td>
                    <td>{{ $teacher->designation ?? '-' }}</td>
                    <td>{{ $teacher->phone }}</td>
                    <td>{{ $teacher->email }}</td>
                    <td>{{ $teacher->joining_date }}</td>
                    <td>{{ sm_money($teacher->salary, 2) }}</td>
                    <td>
                        @if($teacher->is_active)
                        <span class="badge bg-success-subtle text-success">{{ sm_t('Active') }}</span>
                        @else
                        <span class="badge bg-danger-subtle text-danger">{{ sm_t('Inactive') }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('teachers.show', $teacher->id) }}" class="btn btn-sm btn-encodex-show"><i class="fas fa-eye"></i></a>
                        <button class="btn btn-sm btn-encodex-edit" onclick='editTeacher(@json($teacher))'><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $teachers->links('pagination::bootstrap-5') }}</div>

<!-- Teacher Modal -->
<div class="modal fade" id="teacherModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teacherModalTitle">{{ sm_t('Add Teacher') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="teacherForm" action="{{ route('teachers.store') }}">
                @csrf
                <span id="teacherMethodField"></span>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('First Name') }} *</label>
                            <input type="text" id="tFirstName" name="first_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Last Name') }} *</label>
                            <input type="text" id="tLastName" name="last_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Gender') }} *</label>
                            <select id="tGender" name="gender" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select') }}</option>
                                <option value="Male">{{ sm_t('Male') }}</option>
                                <option value="Female">{{ sm_t('Female') }}</option>
                                <option value="Other">{{ sm_t('Other') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Date of Birth') }} *</label>
                            <input type="date" id="tDob" name="date_of_birth" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Religion') }} *</label>
                            <select id="tReligion" name="religion" class="form-control form-control-sm" required>
                                <option value="Islam">{{ sm_t('Islam') }}</option>
                                <option value="Hinduism">{{ sm_t('Hinduism') }}</option>
                                <option value="Christianity">{{ sm_t('Christianity') }}</option>
                                <option value="Buddhism">{{ sm_t('Buddhism') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Phone') }} *</label>
                            <input type="text" id="tPhone" name="phone" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Email') }} *</label>
                            <input type="email" id="tEmail" name="email" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('NID') }}</label>
                            <input type="text" id="tNid" name="nid" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Present Address') }} *</label>
                            <textarea id="tPresentAddress" name="present_address" class="form-control form-control-sm" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Permanent Address') }}</label>
                            <textarea id="tPermanentAddress" name="permanent_address" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Joining Date') }} *</label>
                            <input type="date" id="tJoiningDate" name="joining_date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Designation') }}</label>
                            <input type="text" id="tDesignation" name="designation" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Salary') }}</label>
                            <input type="number" id="tSalary" name="salary" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Qualification') }}</label>
                            <input type="text" id="tQualification" name="qualification" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Experience') }}</label>
                            <input type="text" id="tExperience" name="experience" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetTeacherForm() {
    document.getElementById('teacherModalTitle').textContent = 'Add Teacher';
    document.getElementById('teacherForm').action = '{{ route('teachers.store') }}';
    document.getElementById('teacherMethodField').innerHTML = '';
    ['tFirstName','tLastName','tGender','tDob','tPhone','tEmail','tNid','tPresentAddress',
     'tPermanentAddress','tJoiningDate','tDesignation','tSalary','tQualification','tExperience']
        .forEach(id => document.getElementById(id).value = '');
    document.getElementById('tReligion').value = 'Islam';
}
function editTeacher(t) {
    document.getElementById('teacherModalTitle').textContent = 'Edit Teacher';
    document.getElementById('teacherForm').action = '{{ url('teachers') }}/' + t.id;
    document.getElementById('teacherMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('tFirstName').value = t.first_name ?? '';
    document.getElementById('tLastName').value = t.last_name ?? '';
    document.getElementById('tGender').value = t.gender ?? '';
    document.getElementById('tDob').value = t.date_of_birth ? t.date_of_birth.substring(0,10) : '';
    document.getElementById('tReligion').value = t.religion ?? 'Islam';
    document.getElementById('tPhone').value = t.phone ?? '';
    document.getElementById('tEmail').value = t.email ?? '';
    document.getElementById('tNid').value = t.nid ?? '';
    document.getElementById('tPresentAddress').value = t.present_address ?? '';
    document.getElementById('tPermanentAddress').value = t.permanent_address ?? '';
    document.getElementById('tJoiningDate').value = t.joining_date ? t.joining_date.substring(0,10) : '';
    document.getElementById('tDesignation').value = t.designation ?? '';
    document.getElementById('tSalary').value = t.salary ?? '';
    document.getElementById('tQualification').value = t.qualification ?? '';
    document.getElementById('tExperience').value = t.experience ?? '';
    new bootstrap.Modal(document.getElementById('teacherModal')).show();
}
</script>
@endsection
