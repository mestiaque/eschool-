@extends('me::master')
@section('title', sm_t('Teacher Profile'))

@push('buttons')
<a href="{{ route('teachers.index') }}" class="btn btn-sm btn-encodex-list"><i class="fas fa-arrow-left"></i> Back</a>
@endpush

@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                @if($teacher->photo)
                <img src="{{ asset('storage/'.$teacher->photo) }}" class="rounded-circle mb-2" width="100" height="100" style="object-fit:cover">
                @else
                <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-2" style="width:100px;height:100px">
                    <span class="text-white fs-2">{{ substr($teacher->first_name,0,1) }}</span>
                </div>
                @endif
                <h5 class="mb-0">{{ $teacher->full_name }}</h5>
                <p class="text-muted small">{{ $teacher->teacher_id }}</p>
                <span class="badge bg-{{ $bc = $teacher->is_active ? 'success' : 'danger' }}-subtle text-{{ $bc }}">{{ $teacher->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Designation</span><strong>{{ $teacher->designation ?? '-' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Department</span><strong>{{ $teacher->department ?? '-' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Gender</span><strong>{{ $teacher->gender }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">DOB</span><strong>{{ sm_date($teacher->date_of_birth) }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Blood</span><strong>{{ $teacher->blood_group ?? '-' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Phone</span><strong>{{ $teacher->phone }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Email</span><strong>{{ $teacher->email ?? '-' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Join Date</span><strong>{{ sm_date($teacher->joining_date) }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Basic Salary</span><strong>{{ sm_money($teacher->salary ?? 0,2) }}</strong></li>
            </ul>
            <div class="card-footer">
                <button class="btn btn-sm btn-encodex-edit w-100" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fas fa-edit"></i> Edit</button>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ sm_t('Login Account') }}</h6></div>
            <div class="card-body">
                @if($linkedUser)
                <p class="mb-2">
                    <strong>{{ $linkedUser->name }}</strong><br>
                    <span class="text-muted small">{{ $linkedUser->email }}</span>
                </p>
                <p class="small text-muted">This teacher can sign in and use "My Classes" to take attendance for their assigned classes.</p>
                <form method="POST" action="{{ route('teachers.unlink-user', $teacher->id) }}" onsubmit="return confirm('Unlink this login account from the teacher?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-encodex-cancel w-100"><i class="fas fa-unlink"></i> Unlink Login</button>
                </form>
                @else
                <p class="small text-muted">No login account linked yet — this teacher can't sign in to take attendance for their classes.</p>
                <form method="POST" action="{{ route('teachers.link-user', $teacher->id) }}">
                    @csrf @method('PUT')
                    <div class="mb-2">
                        <select name="user_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select an existing user account') }}</option>
                            @foreach($availableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-encodex-save w-100"><i class="fas fa-link"></i> Link Login</button>
                </form>
                <p class="small text-muted mt-2 mb-0">Don't have a user account for this teacher yet? Create one first from Users &amp; Roles &gt; All Users, then come back here to link it.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ sm_t('Teaching Subjects') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-encodex table-sm mb-0">
                    <thead class="table-light"><tr><th>{{ sm_t('Class') }}</th><th>{{ sm_t('Subject') }}</th></tr></thead>
                    <tbody>
                        @forelse($teacher->teacherSubjects ?? [] as $ts)
                        <tr><td>{{ $ts->classe?->name }}</td><td>{{ $ts->subject?->name }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-2">No subjects assigned</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ sm_t('Recent Attendance') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-encodex table-sm mb-0">
                    <thead class="table-light"><tr><th>{{ sm_t('Date') }}</th><th>{{ sm_t('In Time') }}</th><th>{{ sm_t('Out Time') }}</th><th>{{ sm_t('Status') }}</th></tr></thead>
                    <tbody>
                        @forelse($teacher->attendances?->sortByDesc('date')->take(10) ?? [] as $att)
                        <tr>
                            <td>{{ sm_date($att->date) }}</td>
                            <td>{{ $att->in_time ?? '-' }}</td>
                            <td>{{ $att->out_time ?? '-' }}</td>
                            <td><span class="badge bg-{{ $bc = ['present'=>'success','absent'=>'danger','late'=>'warning','excused'=>'info'][$att->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($att->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-2">No attendance records</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ sm_t('Address') }}</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><small class="text-muted">Present</small><p>{{ $teacher->present_address ?? '-' }}</p></div>
                    <div class="col-md-6"><small class="text-muted">Permanent</small><p>{{ $teacher->permanent_address ?? '-' }}</p></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <form method="POST" action="{{ route('teachers.update', $teacher->id) }}">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ sm_t('Edit Teacher') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">{{ sm_t('First Name') }}</label><input type="text" name="first_name" class="form-control form-control-sm" value="{{ $teacher->first_name }}" required></div>
                        <div class="col-md-4"><label class="form-label">{{ sm_t('Last Name') }}</label><input type="text" name="last_name" class="form-control form-control-sm" value="{{ $teacher->last_name }}" required></div>
                        <div class="col-md-4"><label class="form-label">{{ sm_t('Phone') }}</label><input type="text" name="phone" class="form-control form-control-sm" value="{{ $teacher->phone }}" required></div>
                        <div class="col-md-4"><label class="form-label">{{ sm_t('Email') }}</label><input type="email" name="email" class="form-control form-control-sm" value="{{ $teacher->email }}"></div>
                        <div class="col-md-4"><label class="form-label">{{ sm_t('Designation') }}</label><input type="text" name="designation" class="form-control form-control-sm" value="{{ $teacher->designation }}"></div>
                        <div class="col-md-4"><label class="form-label">{{ sm_t('Basic Salary') }}</label><input type="number" name="salary" class="form-control form-control-sm" value="{{ $teacher->salary }}" step="0.01"></div>
                        <div class="col-md-6"><label class="form-label">{{ sm_t('Present Address') }}</label><textarea name="present_address" class="form-control form-control-sm" rows="2">{{ $teacher->present_address }}</textarea></div>
                        <div class="col-md-6"><label class="form-label">{{ sm_t('Permanent Address') }}</label><textarea name="permanent_address" class="form-control form-control-sm" rows="2">{{ $teacher->permanent_address }}</textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
