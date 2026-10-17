@extends('me::master')
@section('title', sm_t('Students'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#admitModal">
        <i class="fas fa-plus"></i> Admit Student
    </button>
@endpush

@section('content')

<div class="card shadow">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-2">
                <select name="class_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ sm_t('All Classes') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="section_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ sm_t('All Sections') }}</option>
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ sm_t('All Status') }}</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ sm_t('Active') }}</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ sm_t('Inactive') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name / Student ID / Phone / Roll" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-encodex-search me-1"><i class="fas fa-search"></i></button>
                <a href="{{ route('students.index') }}" class="btn btn-sm btn-encodex-cancel">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>{{ sm_t('Student ID') }}</th>
                    <th>{{ sm_t('Name') }}</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Section') }}</th>
                    <th>{{ sm_t('Roll') }}</th>
                    <th>{{ sm_t('Phone') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td class="fw-semibold text-primary small">{{ $student->student_id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($student->photo)
                            <img src="{{ asset('storage/'.$student->photo) }}" class="rounded-circle" style="width:28px;height:28px;object-fit:cover">
                            @endif
                            {{ $student->first_name }} {{ $student->last_name }}
                        </div>
                    </td>
                    <td>{{ $student->classe?->name ?? '—' }}</td>
                    <td>{{ $student->section?->name ?? '—' }}</td>
                    <td class="text-center">{{ $student->roll_number ?? '—' }}</td>
                    <td class="small">{{ $student->phone ?? '—' }}</td>
                    <td><span class="badge bg-{{ $bc = $student->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">{{ $student->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-center">
                        <a href="{{ route('students.show', $student->id) }}" class="btn btn-sm btn-encodex-show" title="Profile"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('students.idcard.print', $student->id) }}" class="btn btn-sm btn-encodex-print" title="ID Card" target="_blank"><i class="fas fa-id-card"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $students->links('pagination::bootstrap-5') }}</div>

{{-- Admit Student Modal --}}
<div class="modal fade" id="admitModal" tabindex="-1">
    <div class="modal-dialog modal-xl glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-user-plus me-2"></i>Student Admission</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    {{-- Personal Info --}}
                    <h6 class="text-primary fw-bold mb-2 border-bottom pb-1"><i class="fas fa-user me-1"></i>Personal Information</h6>
                    <div class="row g-2">
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Student ID') }}</label><input type="text" name="student_id" class="form-control form-control-sm" placeholder="Auto-generated if blank"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('First Name') }} <span class="text-danger">*</span></label><input type="text" name="first_name" class="form-control form-control-sm" required></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Last Name') }} <span class="text-danger">*</span></label><input type="text" name="last_name" class="form-control form-control-sm" required></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Gender') }} <span class="text-danger">*</span></label>
                            <select name="gender" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select') }}</option>
                                <option value="male">{{ sm_t('Male') }}</option>
                                <option value="female">{{ sm_t('Female') }}</option>
                                <option value="other">{{ sm_t('Other') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Date of Birth') }} <span class="text-danger">*</span></label><input type="date" name="date_of_birth" class="form-control form-control-sm" required></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Blood Group') }}</label>
                            <select name="blood_group" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                                <option value="{{ $bg }}">{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Religion') }}</label>
                            <select name="religion" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach(['Islam','Hinduism','Christianity','Buddhism','Other'] as $rel)
                                <option value="{{ $rel }}">{{ $rel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Phone') }}</label><input type="text" name="phone" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Email') }}</label><input type="email" name="email" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Photo') }}</label><input type="file" name="photo" class="form-control form-control-sm" accept="image/*"></div>
                        <div class="col-md-6 mb-1"><label class="form-label form-label-sm">{{ sm_t('Present Address') }}</label><input type="text" name="present_address" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-1"><label class="form-label form-label-sm">{{ sm_t('Permanent Address') }}</label><input type="text" name="permanent_address" class="form-control form-control-sm"></div>
                    </div>

                    {{-- Academic Info --}}
                    <h6 class="text-primary fw-bold mb-2 border-bottom pb-1 mt-3"><i class="fas fa-school me-1"></i>Academic Information</h6>
                    <div class="row g-2">
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Class') }} <span class="text-danger">*</span></label>
                            <select name="class_id" id="admitClassId" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Section') }}</label>
                            <select name="section_id" id="admitSectionId" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach($sections as $sec)
                                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Roll No.') }}</label><input type="text" name="roll_number" class="form-control form-control-sm"></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Admission Date') }}</label><input type="date" name="admission_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}"></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Academic Year') }}</label>
                            <select name="academic_year_id" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach($academicYears as $yr)
                                <option value="{{ $yr->id }}" {{ $yr->is_current ? 'selected' : '' }}>{{ $yr->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Group') }}</label>
                            <select name="subject_group_id" id="addGroupSelect" class="form-control form-control-sm" onchange="loadOptionalSubjects('addOptSubject', this.value, document.getElementById('admitClassId').value)">
                                <option value="">{{ sm_t('None') }}</option>
                                @foreach($subjectGroups as $sg)
                                <option value="{{ $sg->id }}">{{ $sg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Optional Subject (4th)') }}</label>
                            <select name="optional_subject_id" id="addOptSubject" class="form-control form-control-sm">
                                <option value="">— None —</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Registration No.') }}</label><input type="text" name="registration_number" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Previous School') }}</label><input type="text" name="previous_school" class="form-control form-control-sm"></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Previous Class') }}</label><input type="text" name="previous_class" class="form-control form-control-sm"></div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Portal PIN') }}</label>
                            <input type="text" name="portal_pin" class="form-control form-control-sm" placeholder="Auto if blank" maxlength="10">
                        </div>
                    </div>

                    {{-- Guardian --}}
                    <h6 class="text-primary fw-bold mb-2 border-bottom pb-1 mt-3"><i class="fas fa-users me-1"></i>Guardian Information</h6>
                    <div class="row g-2">
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Father\'s Name') }}</label><input type="text" name="father_name" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Father\'s Phone') }}</label><input type="text" name="father_phone" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Mother\'s Name') }}</label><input type="text" name="mother_name" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Mother\'s Phone') }}</label><input type="text" name="mother_phone" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Guardian Name (if diff.)') }}</label><input type="text" name="guardian_name" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Guardian Phone') }}</label><input type="text" name="guardian_phone" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Father\'s Occupation') }}</label><input type="text" name="father_occupation" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Guardian Address') }}</label><input type="text" name="guardian_address" class="form-control form-control-sm"></div>
                    </div>

                    {{-- Fee Waiver --}}
                    <h6 class="text-primary fw-bold mb-2 border-bottom pb-1 mt-3"><i class="fas fa-tag me-1"></i>Fee Waiver / Discount <small class="text-muted fw-normal">(optional)</small></h6>
                    <div class="row g-2">
                        <div class="col-md-3 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Fee Type') }}</label>
                            <select name="waiver_fee_type_id" class="form-control form-control-sm">
                                <option value="">{{ sm_t('All Fees') }}</option>
                                @foreach($feeTypes as $ft)
                                <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Discount Type') }}</label>
                            <select name="waiver_type" class="form-control form-control-sm">
                                <option value="fixed">Fixed Amount ({{ sm_currency() }})</option>
                                <option value="percent">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label form-label-sm">Amount / %</label>
                            <input type="number" name="waiver_amount" class="form-control form-control-sm" min="0" step="0.01" placeholder="0">
                        </div>
                    </div>

                    {{-- Documents --}}
                    <h6 class="text-primary fw-bold mb-2 border-bottom pb-1 mt-3"><i class="fas fa-paperclip me-1"></i>Documents <small class="text-muted fw-normal">(optional)</small></h6>
                    <div id="docRows">
                        <div class="row g-2 mb-1 doc-row">
                            <div class="col-md-3"><select name="document_type[]" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Doc Type') }}</option>
                                @foreach(['birth_certificate'=>'Birth Certificate','nid'=>'NID','photo_id'=>'Photo ID','tc'=>'Transfer Certificate','marksheet'=>'Marksheet','other'=>'Other'] as $k=>$v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select></div>
                            <div class="col-md-4"><input type="file" name="documents[]" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png"></div>
                            <div class="col-md-4"><input type="text" name="document_description[]" class="form-control form-control-sm" placeholder="Description (optional)"></div>
                            <div class="col-md-1"><button type="button" class="btn btn-sm btn-encodex-delete remove-doc" title="Remove"><i class="fas fa-times"></i></button></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-encodex-create mt-1" id="addDocRow"><i class="fas fa-plus me-1"></i>Add Document</button>

                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save"><i class="fas fa-save me-1"></i>Admit Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('addDocRow').addEventListener('click', function() {
    const tpl = document.querySelector('.doc-row').cloneNode(true);
    tpl.querySelectorAll('input').forEach(i => i.value = '');
    tpl.querySelector('select').selectedIndex = 0;
    document.getElementById('docRows').appendChild(tpl);
});
document.getElementById('docRows').addEventListener('click', function(e) {
    if (e.target.closest('.remove-doc')) {
        const rows = document.querySelectorAll('.doc-row');
        if (rows.length > 1) e.target.closest('.doc-row').remove();
    }
});
</script>
@endpush
@push('scripts')
<script>
function loadOptionalSubjects(selectId, groupId, classId) {
    const sel = document.getElementById(selectId);
    sel.innerHTML = '<option value="">— None —</option>';
    if (!groupId || !classId) return;
    fetch(`{{ url('optional-subjects') }}?group_id=${groupId}&class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name;
                sel.appendChild(opt);
            });
        });
}
</script>
@endpush
@push('js')
<script>
if (new URLSearchParams(window.location.search).get('admit') === '1') {
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('admitModal')).show();
    });
}
</script>
@endpush
@endsection
