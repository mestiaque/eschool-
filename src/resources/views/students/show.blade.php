@extends('me::master')
@section('title', sm_t('Student Profile'))
@push('buttons')
    <a href="{{ route('students.index') }}" class="btn btn-sm btn-encodex-list"><i class="fas fa-arrow-left"></i> Students</a>
    <a href="{{ route('students.idcard.print', $student->id) }}" class="btn btn-sm btn-encodex-print" target="_blank"><i class="fas fa-id-card"></i> ID Card</a>
    <button class="btn btn-sm btn-encodex-edit" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fas fa-edit"></i> Edit</button>
@endpush

@section('content')

<div class="row g-3">
    {{-- Left: Profile Card --}}
    <div class="col-md-3">
        <div class="card shadow text-center">
            <div class="card-body pb-2">
                @if($student->photo)
                <img src="{{ asset('storage/'.$student->photo) }}" class="rounded-circle mb-2 border border-3 border-primary" style="width:90px;height:90px;object-fit:cover">
                @else
                <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-2 border border-3 border-primary" style="width:90px;height:90px">
                    <span class="text-white fs-2 fw-bold">{{ substr($student->first_name,0,1) }}</span>
                </div>
                @endif
                <div class="fw-bold fs-6">{{ $student->first_name }} {{ $student->last_name }}</div>
                <div class="text-primary small fw-semibold">{{ $student->student_id }}</div>
                <span class="badge bg-{{ $bc = $student->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }} mt-1">{{ $student->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <ul class="list-group list-group-flush text-start small">
                <li class="list-group-item d-flex justify-content-between py-1"><span class="text-muted">Class</span><strong>{{ $student->classe?->name ?? '—' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between py-1"><span class="text-muted">Section</span><strong>{{ $student->section?->name ?? '—' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between py-1"><span class="text-muted">Roll</span><strong>{{ $student->roll_number ?? '—' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between py-1"><span class="text-muted">Gender</span><strong>{{ ucfirst($student->gender ?? '—') }}</strong></li>
                <li class="list-group-item d-flex justify-content-between py-1"><span class="text-muted">DOB</span><strong>{{ sm_date($student->date_of_birth) ?? '—' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between py-1"><span class="text-muted">Blood</span><strong>{{ $student->blood_group ?? '—' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between py-1"><span class="text-muted">Phone</span><strong>{{ $student->phone ?? '—' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between py-1"><span class="text-muted">Admitted</span><strong>{{ sm_date($student->admission_date) ?? '—' }}</strong></li>
            </ul>

            {{-- Portal PIN --}}
            <div class="card-body pt-2 pb-2 border-top">
                <div class="small text-muted mb-1"><i class="fas fa-lock me-1"></i>Portal PIN: <strong>{{ $student->portal_pin ? '••••' : 'Not set' }}</strong></div>
                <form method="POST" action="{{ route('students.portal-pin', $student->id) }}">
                    @csrf
                    <div class="d-flex gap-1">
                        <input type="text" name="portal_pin" class="form-control form-control-sm" placeholder="Blank = random" maxlength="10" inputmode="numeric" pattern="\d{4,10}">
                        <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Reset') }}</button>
                    </div>
                    <div class="form-check small mt-1">
                        <input class="form-check-input" type="checkbox" name="send_sms" value="1" id="pinSms">
                        <label class="form-check-label" for="pinSms">{{ sm_t('SMS the new PIN to the guardian') }}</label>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: Tabs --}}
    <div class="col-md-9">
        <ul class="nav nav-tabs nav-tabs-bordered mb-3" id="profileTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabInfo">{{ sm_t('Info') }}</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDocs"><i class="fas fa-paperclip me-1"></i>Documents <span class="badge bg-secondary-subtle text-secondary">{{ $student->documents->count() }}</span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabFees"><i class="fas fa-money-bill me-1"></i>Fees <span class="badge bg-secondary-subtle text-secondary">{{ $student->feeCollections->count() }}</span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabResults"><i class="fas fa-chart-bar me-1"></i>Results</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabLibrary"><i class="fas fa-book me-1"></i>Library</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabAttendance"><i class="fas fa-calendar me-1"></i>Attendance</button></li>
        </ul>

        <div class="tab-content">
            {{-- Info Tab --}}
            <div class="tab-pane fade show active" id="tabInfo">
                <div class="card shadow mb-3">
                    <div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fas fa-user me-2"></i>Personal Details</h6></div>
                    <div class="card-body">
                        <div class="row g-2 small">
                            <div class="col-md-4"><span class="text-muted">Religion</span><div class="fw-semibold">{{ $student->religion ?? '—' }}</div></div>
                            <div class="col-md-4"><span class="text-muted">Nationality</span><div class="fw-semibold">{{ $student->nationality ?? '—' }}</div></div>
                            <div class="col-md-4"><span class="text-muted">Email</span><div class="fw-semibold">{{ $student->email ?? '—' }}</div></div>
                            <div class="col-md-4"><span class="text-muted">Birth Cert. No.</span><div class="fw-semibold">{{ $student->birth_certificate_no ?? '—' }}</div></div>
                            <div class="col-md-4"><span class="text-muted">Reg. No.</span><div class="fw-semibold">{{ $student->registration_number ?? '—' }}</div></div>
                            <div class="col-md-4"><span class="text-muted">Previous School</span><div class="fw-semibold">{{ $student->previous_school ?? '—' }}</div></div>
                            <div class="col-md-4"><span class="text-muted">Group</span><div class="fw-semibold">{{ $student->subjectGroup?->name ?? '—' }}</div></div>
                            <div class="col-md-4"><span class="text-muted">Optional Subject</span><div class="fw-semibold">{{ $student->optionalSubject?->name ?? '—' }}</div></div>
                            <div class="col-md-4"><span class="text-muted">Academic Year</span><div class="fw-semibold">{{ $student->academicYear?->name ?? '—' }}</div></div>
                            <div class="col-md-6"><span class="text-muted">Present Address</span><div>{{ $student->present_address ?? '—' }}</div></div>
                            <div class="col-md-6"><span class="text-muted">Permanent Address</span><div>{{ $student->permanent_address ?? '—' }}</div></div>
                        </div>
                    </div>
                </div>

                @if($student->guardian)
                <div class="card shadow mb-3">
                    <div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i>Guardian Information</h6></div>
                    <div class="card-body">
                        <div class="row g-2 small">
                            <div class="col-md-4"><span class="text-muted">Father</span><div class="fw-semibold">{{ $student->guardian->father_name ?? '—' }}</div><div class="text-muted">{{ $student->guardian->father_phone ?? '' }}</div></div>
                            <div class="col-md-4"><span class="text-muted">Mother</span><div class="fw-semibold">{{ $student->guardian->mother_name ?? '—' }}</div><div class="text-muted">{{ $student->guardian->mother_phone ?? '' }}</div></div>
                            <div class="col-md-4"><span class="text-muted">Guardian</span><div class="fw-semibold">{{ $student->guardian->guardian_name ?? '—' }}</div><div class="text-muted">{{ $student->guardian->guardian_phone ?? '' }}</div></div>
                            @if($student->guardian->father_occupation)<div class="col-md-4"><span class="text-muted">Father's Occ.</span><div>{{ $student->guardian->father_occupation }}</div></div>@endif
                            @if($student->guardian->guardian_address)<div class="col-md-8"><span class="text-muted">Guardian Address</span><div>{{ $student->guardian->guardian_address }}</div></div>@endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Discounts / Waivers --}}
                @if($student->discounts->count())
                <div class="card shadow">
                    <div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fas fa-tag me-2 text-success"></i>Fee Waivers / Discounts</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-encodex mb-0">
                            <thead><tr><th>{{ sm_t('Fee Type') }}</th><th>{{ sm_t('Type') }}</th><th class="text-end">{{ sm_t('Amount') }}</th><th>{{ sm_t('Status') }}</th></tr></thead>
                            <tbody>
                                @foreach($student->discounts as $d)
                                <tr>
                                    <td>{{ $d->feeType?->name ?? 'All Fees' }}</td>
                                    <td>{{ ucfirst($d->discount_type ?? 'fixed') }}</td>
                                    <td class="text-end fw-semibold">{{ sm_money($d->discount_amount, 2) }}</td>
                                    <td><span class="badge bg-{{ $bc = $d->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">{{ $d->is_active ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            {{-- Documents Tab --}}
            <div class="tab-pane fade" id="tabDocs">
                <div class="card shadow mb-3">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-paperclip me-2"></i>Upload Document</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('students.documents.store', $student->id) }}" enctype="multipart/form-data" class="row g-2">
                            @csrf
                            <div class="col-md-3">
                                <select name="type" class="form-control form-control-sm" required>
                                    <option value="">{{ sm_t('Type') }}</option>
                                    @foreach($docTypes as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4"><input type="file" name="file" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png"></div>
                            <div class="col-md-3"><input type="text" name="description" class="form-control form-control-sm" placeholder="Description (optional)"></div>
                            <div class="col-md-2"><button type="submit" class="btn btn-sm btn-encodex-create w-100"><i class="fas fa-upload me-1"></i>Upload</button></div>
                        </form>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-body p-0">
                        <table class="table table-sm table-encodex mb-0">
                            <thead><tr><th>{{ sm_t('Type') }}</th><th>{{ sm_t('Description') }}</th><th>{{ sm_t('File') }}</th><th>{{ sm_t('Verified') }}</th><th>{{ sm_t('Uploaded') }}</th><th>{{ sm_t('Action') }}</th></tr></thead>
                            <tbody>
                                @forelse($student->documents as $doc)
                                <tr>
                                    <td>{{ $docTypes[$doc->type] ?? $doc->type }}</td>
                                    <td class="small">{{ $doc->description ?? '—' }}</td>
                                    <td>
                                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-encodex-show">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                    <td>
                                        @if($doc->is_verified)
                                        <span class="badge bg-success-subtle text-success">Verified</span>
                                        @else
                                        <form method="POST" action="{{ route('students.documents.verify', [$student->id, $doc->id]) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-encodex-active">{{ sm_t('Verify') }}</button>
                                        </form>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ sm_date($doc->created_at) }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('students.documents.destroy', [$student->id, $doc->id]) }}" class="d-inline" onsubmit="return confirm('Delete this document?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">No documents uploaded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Fees Tab --}}
            <div class="tab-pane fade" id="tabFees">
                @php
                    $totalPaid = $student->feeCollections->sum('paid_amount');
                    $totalDue  = $student->feeCollections->sum('due_amount');
                @endphp
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><div class="stat-card text-center" style="background:#fff;border-radius:8px;padding:.75rem;border-left:3px solid #28a745;box-shadow:0 1px 4px rgba(0,0,0,.08)">
                        <div class="small text-muted">Total Paid</div><div class="fw-bold text-success">{{ sm_money($totalPaid,2) }}</div>
                    </div></div>
                    <div class="col-md-4"><div class="stat-card text-center" style="background:#fff;border-radius:8px;padding:.75rem;border-left:3px solid #dc3545;box-shadow:0 1px 4px rgba(0,0,0,.08)">
                        <div class="small text-muted">Total Due</div><div class="fw-bold text-danger">{{ sm_money($totalDue,2) }}</div>
                    </div></div>
                    <div class="col-md-4">
                        <a href="{{ route('fees.collection.create', ['student_id' => $student->id]) }}" class="btn btn-sm btn-encodex-create w-100"><i class="fas fa-plus me-1"></i>Add Fee Collection</a>
                    </div>
                </div>
                <div class="card shadow">
                    <div class="card-body p-0">
                        <table class="table table-sm table-encodex mb-0">
                            <thead><tr><th>{{ sm_t('Invoice') }}</th><th>{{ sm_t('Fee Type') }}</th><th>{{ sm_t('Month/Year') }}</th><th class="text-end">{{ sm_t('Amount') }}</th><th class="text-end">{{ sm_t('Paid') }}</th><th class="text-end">{{ sm_t('Due') }}</th><th>{{ sm_t('Status') }}</th></tr></thead>
                            <tbody>
                                @forelse($student->feeCollections as $fee)
                                <tr>
                                    <td class="small text-muted">{{ $fee->invoice_no ?? '—' }}</td>
                                    <td>{{ $fee->feeType?->name ?? '—' }}</td>
                                    <td class="small">{{ $fee->monthLabel('M Y') ?? '—' }}</td>
                                    <td class="text-end">{{ sm_money($fee->amount,2) }}</td>
                                    <td class="text-end text-success">{{ sm_money($fee->paid_amount,2) }}</td>
                                    <td class="text-end text-danger">{{ sm_money($fee->due_amount,2) }}</td>
                                    <td><span class="badge bg-{{ $bc = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger'][$fee->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($fee->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">No fee records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Results Tab --}}
            <div class="tab-pane fade" id="tabResults">
                <div class="card shadow">
                    <div class="card-body p-0">
                        <table class="table table-sm table-encodex mb-0">
                            <thead><tr><th>{{ sm_t('Exam') }}</th><th class="text-center">{{ sm_t('Obtained') }}</th><th class="text-center">{{ sm_t('Total') }}</th><th class="text-center">%</th><th class="text-center">{{ sm_t('GPA') }}</th><th class="text-center">{{ sm_t('Grade') }}</th><th class="text-center">{{ sm_t('Position') }}</th><th>{{ sm_t('Status') }}</th><th>{{ sm_t('Action') }}</th></tr></thead>
                            <tbody>
                                @forelse($student->results as $res)
                                <tr>
                                    <td class="fw-semibold">{{ $res->exam?->name ?? '—' }}</td>
                                    <td class="text-center">{{ $res->obtained_mark }}</td>
                                    <td class="text-center">{{ $res->total_mark }}</td>
                                    <td class="text-center">@if($res->total_mark > 0){{ round(($res->obtained_mark/$res->total_mark)*100,1) }}%@else—@endif</td>
                                    <td class="text-center">{{ $res->gpa }}</td>
                                    <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $res->grade ?? '—' }}</span></td>
                                    <td class="text-center">{{ $res->position ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $bc = $res->is_published ? 'success' : 'warning' }}-subtle text-{{ $bc }}">{{ $res->is_published ? 'Published' : 'Draft' }}</span></td>
                                    <td><a href="{{ route('exams.marksheet.view', [$student->id, $res->exam_id]) }}" target="_blank" class="btn btn-sm btn-encodex-show"><i class="fas fa-file-alt"></i></a></td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-center text-muted py-3">No exam results.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Library Tab --}}
            <div class="tab-pane fade" id="tabLibrary">
                <div class="card shadow">
                    <div class="card-body p-0">
                        <table class="table table-sm table-encodex mb-0">
                            <thead><tr><th>{{ sm_t('Book') }}</th><th>{{ sm_t('Copy') }}</th><th>{{ sm_t('Issued On') }}</th><th>{{ sm_t('Due Date') }}</th><th>{{ sm_t('Returned') }}</th><th>{{ sm_t('Fine') }}</th><th>{{ sm_t('Status') }}</th></tr></thead>
                            <tbody>
                                @forelse($libraryIssues as $issue)
                                <tr>
                                    <td class="fw-semibold">{{ $issue->book?->title ?? '—' }}</td>
                                    <td class="small text-muted">{{ $issue->copy?->copy_id ?? '—' }}</td>
                                    <td class="small">{{ sm_date($issue->issue_date) }}</td>
                                    <td class="small">{{ sm_date($issue->due_date) }}</td>
                                    <td class="small">{{ sm_date($issue->return_date) ?? '—' }}</td>
                                    <td class="small">@if($issue->fine_amount > 0)<span class="text-danger">{{ sm_money($issue->fine_amount,2) }}</span>@else—@endif</td>
                                    <td>
                                        @if($issue->status === 'issued')
                                        <span class="badge bg-{{ $bc = $issue->overdue_days > 0 ? 'danger' : 'success' }}-subtle text-{{ $bc }}">{{ $issue->overdue_days > 0 ? 'Overdue' : 'Issued' }}</span>
                                        @elseif($issue->status === 'returned')
                                        <span class="badge bg-light-subtle text-dark border">Returned</span>
                                        @else
                                        <span class="badge bg-danger-subtle text-danger">{{ ucfirst($issue->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">No library records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Attendance Tab --}}
            <div class="tab-pane fade" id="tabAttendance">
                @php
                    $attTotal   = $student->attendances->count();
                    $attPresent = $student->attendances->where('status','present')->count();
                    $attAbsent  = $student->attendances->where('status','absent')->count();
                    $attLate    = $student->attendances->where('status','late')->count();
                    $attPct     = $attTotal > 0 ? round(($attPresent/$attTotal)*100) : 0;
                @endphp
                <div class="row g-2 mb-3">
                    <div class="col-3"><div style="background:#fff;border-radius:8px;padding:.6rem;text-align:center;border-left:3px solid #1565c0;box-shadow:0 1px 4px rgba(0,0,0,.08)"><div class="small text-muted">Total</div><div class="fw-bold">{{ $attTotal }}</div></div></div>
                    <div class="col-3"><div style="background:#fff;border-radius:8px;padding:.6rem;text-align:center;border-left:3px solid #28a745;box-shadow:0 1px 4px rgba(0,0,0,.08)"><div class="small text-muted">Present</div><div class="fw-bold text-success">{{ $attPresent }}</div></div></div>
                    <div class="col-3"><div style="background:#fff;border-radius:8px;padding:.6rem;text-align:center;border-left:3px solid #dc3545;box-shadow:0 1px 4px rgba(0,0,0,.08)"><div class="small text-muted">Absent</div><div class="fw-bold text-danger">{{ $attAbsent }}</div></div></div>
                    <div class="col-3"><div style="background:#fff;border-radius:8px;padding:.6rem;text-align:center;border-left:3px solid #ffc107;box-shadow:0 1px 4px rgba(0,0,0,.08)"><div class="small text-muted">Attendance</div><div class="fw-bold text-{{ $attPct >= 75 ? 'success' : 'danger' }}">{{ $attPct }}%</div></div></div>
                </div>
                <div class="card shadow">
                    <div class="card-body p-0">
                        <table class="table table-sm table-encodex mb-0">
                            <thead><tr><th>{{ sm_t('Date') }}</th><th>{{ sm_t('Status') }}</th><th>{{ sm_t('Note') }}</th></tr></thead>
                            <tbody>
                                @forelse($student->attendances->sortByDesc('date')->take(30) as $att)
                                <tr>
                                    <td>{{ sm_date($att->date) }}</td>
                                    <td><span class="badge bg-{{ $bc = ['present'=>'success','absent'=>'danger','late'=>'warning','excused'=>'info'][$att->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($att->status) }}</span></td>
                                    <td class="small text-muted">{{ $att->note ?? '—' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No attendance records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Student</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('students.update', $student->id) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body">

                    <h6 class="text-primary fw-bold mb-2 border-bottom pb-1"><i class="fas fa-user me-1"></i>Personal Information</h6>
                    <div class="row g-2">
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('First Name') }} <span class="text-danger">*</span></label><input type="text" name="first_name" class="form-control form-control-sm" value="{{ $student->first_name }}" required></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Last Name') }} <span class="text-danger">*</span></label><input type="text" name="last_name" class="form-control form-control-sm" value="{{ $student->last_name }}" required></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Gender') }} <span class="text-danger">*</span></label>
                            <select name="gender" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $gk => $gv)
                                <option value="{{ $gk }}" {{ $student->gender === $gk ? 'selected' : '' }}>{{ $gv }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Date of Birth') }}</label><input type="date" name="date_of_birth" class="form-control form-control-sm" value="{{ $student->date_of_birth?->format('Y-m-d') }}"></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Blood Group') }}</label>
                            <select name="blood_group" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                                <option value="{{ $bg }}" {{ $student->blood_group === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Religion') }}</label>
                            <select name="religion" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach(['Islam','Hinduism','Christianity','Buddhism','Other'] as $rel)
                                <option value="{{ $rel }}" {{ $student->religion === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Phone') }}</label><input type="text" name="phone" class="form-control form-control-sm" value="{{ $student->phone }}"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Email') }}</label><input type="email" name="email" class="form-control form-control-sm" value="{{ $student->email }}"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Photo') }}</label><input type="file" name="photo" class="form-control form-control-sm" accept="image/*"></div>
                        <div class="col-md-6 mb-1"><label class="form-label form-label-sm">{{ sm_t('Present Address') }}</label><input type="text" name="present_address" class="form-control form-control-sm" value="{{ $student->present_address }}"></div>
                        <div class="col-md-6 mb-1"><label class="form-label form-label-sm">{{ sm_t('Permanent Address') }}</label><input type="text" name="permanent_address" class="form-control form-control-sm" value="{{ $student->permanent_address }}"></div>
                    </div>

                    <h6 class="text-primary fw-bold mb-2 border-bottom pb-1 mt-3"><i class="fas fa-school me-1"></i>Academic Information</h6>
                    <div class="row g-2">
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Class') }} <span class="text-danger">*</span></label>
                            <select name="class_id" id="editClassId" class="form-control form-control-sm" required>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $student->class_id === $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Section') }}</label>
                            <select name="section_id" class="form-control form-control-sm">
                                <option value="">{{ sm_t('None') }}</option>
                                @foreach($sections as $sec)
                                <option value="{{ $sec->id }}" {{ $student->section_id === $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Roll No.') }}</label><input type="text" name="roll_number" class="form-control form-control-sm" value="{{ $student->roll_number }}"></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Group') }}</label>
                            <select name="subject_group_id" id="editGroupSelect" class="form-control form-control-sm"
                                    onchange="loadOptionalSubjects('editOptSubject', this.value, document.getElementById('editClassId').value, {{ $student->optional_subject_id ?? 'null' }})">
                                <option value="">{{ sm_t('None') }}</option>
                                @foreach($subjectGroups as $sg)
                                <option value="{{ $sg->id }}" {{ $student->subject_group_id == $sg->id ? 'selected' : '' }}>{{ $sg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Optional Subject (4th)') }}</label>
                            <select name="optional_subject_id" id="editOptSubject" class="form-control form-control-sm">
                                <option value="">— None —</option>
                                @if($student->optionalSubject)
                                <option value="{{ $student->optional_subject_id }}" selected>{{ $student->optionalSubject->name }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Admission Date') }}</label><input type="date" name="admission_date" class="form-control form-control-sm" value="{{ $student->admission_date?->format('Y-m-d') }}"></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Academic Year') }}</label>
                            <select name="academic_year_id" class="form-control form-control-sm">
                                <option value="">{{ sm_t('None') }}</option>
                                @foreach($academicYears as $yr)
                                <option value="{{ $yr->id }}" {{ $student->academic_year_id === $yr->id ? 'selected' : '' }}>{{ $yr->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Registration No.') }}</label><input type="text" name="registration_number" class="form-control form-control-sm" value="{{ $student->registration_number }}"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Previous School') }}</label><input type="text" name="previous_school" class="form-control form-control-sm" value="{{ $student->previous_school }}"></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Previous Class') }}</label><input type="text" name="previous_class" class="form-control form-control-sm" value="{{ $student->previous_class }}"></div>
                        <div class="col-md-2 mb-1"><label class="form-label form-label-sm">{{ sm_t('Status') }}</label>
                            <select name="is_active" class="form-control form-control-sm">
                                <option value="1" {{ $student->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$student->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="text-primary fw-bold mb-2 border-bottom pb-1 mt-3"><i class="fas fa-users me-1"></i>Guardian Information</h6>
                    <div class="row g-2">
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Father\'s Name') }}</label><input type="text" name="father_name" class="form-control form-control-sm" value="{{ $student->guardian?->father_name }}"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Father\'s Phone') }}</label><input type="text" name="father_phone" class="form-control form-control-sm" value="{{ $student->guardian?->father_phone }}"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Mother\'s Name') }}</label><input type="text" name="mother_name" class="form-control form-control-sm" value="{{ $student->guardian?->mother_name }}"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Mother\'s Phone') }}</label><input type="text" name="mother_phone" class="form-control form-control-sm" value="{{ $student->guardian?->mother_phone }}"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Guardian Name') }}</label><input type="text" name="guardian_name" class="form-control form-control-sm" value="{{ $student->guardian?->guardian_name }}"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Guardian Phone') }} <span class="text-danger">*</span></label><input type="text" name="guardian_phone" class="form-control form-control-sm" value="{{ $student->guardian?->guardian_phone }}" required></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Father\'s Occupation') }}</label><input type="text" name="father_occupation" class="form-control form-control-sm" value="{{ $student->guardian?->father_occupation }}"></div>
                        <div class="col-md-3 mb-1"><label class="form-label form-label-sm">{{ sm_t('Guardian Address') }}</label><input type="text" name="guardian_address" class="form-control form-control-sm" value="{{ $student->guardian?->guardian_address }}"></div>
                    </div>

                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save"><i class="fas fa-save me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function loadOptionalSubjects(selectId, groupId, classId, selectedId) {
    const sel = document.getElementById(selectId);
    if (!groupId || !classId) { sel.innerHTML = '<option value="">— None —</option>'; return; }
    fetch(`{{ url('/optional-subjects') }}?group_id=${groupId}&class_id=${classId}`)
        .then(r => r.json())
        .then(subjects => {
            sel.innerHTML = '<option value="">— None —</option>';
            subjects.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id; opt.textContent = s.name;
                if (selectedId && s.id == selectedId) opt.selected = true;
                sel.appendChild(opt);
            });
        });
}
// Pre-load optional subjects for the edit form on page load
(function() {
    const groupSel = document.getElementById('editGroupSelect');
    const classSel = document.getElementById('editClassId');
    if (groupSel && groupSel.value && classSel && classSel.value) {
        loadOptionalSubjects('editOptSubject', groupSel.value, classSel.value, {{ $student->optional_subject_id ?? 'null' }});
    }
})();
</script>
@endsection
