@extends('me::master')
@section('title', $tour->title)
@push('buttons')
    <a href="{{ route('academic.tours') }}" class="btn btn-sm btn-encodex-list no-print">
        <i class="fas fa-arrow-left"></i> All Tours
    </a>
    @if($tour->registrations->isNotEmpty())
    <a href="{{ route('academic.tours.badges', $tour->id) }}" class="btn btn-sm btn-encodex-print no-print" target="_blank">
        <i class="fas fa-id-card"></i> Print All Badges
    </a>
    @endif
@endpush

@section('content')


@php
$totalExpense   = $tour->expenses->sum('amount');
$totalCollected = $tour->registrations->sum('paid_amount');
$totalFee       = $tour->registrations->sum('fee');
@endphp

{{-- Tour Header --}}
<div class="card shadow mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-1">{{ $tour->title }}</h5>
                <div class="text-muted small">
                    <i class="fas fa-map-marker-alt me-1"></i>{{ $tour->destination }}
                    &nbsp;|&nbsp;
                    <i class="fas fa-calendar me-1"></i>{{ sm_date($tour->start_date) }} – {{ sm_date($tour->end_date) }}
                    @if($tour->organizer) &nbsp;|&nbsp; <i class="fas fa-user me-1"></i>{{ $tour->organizer }}@endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="row text-center g-2">
                    <div class="col-3">
                        <div class="border rounded p-2">
                            <div class="h5 mb-0 text-primary">{{ $tour->registrations->count() }}</div>
                            <small class="text-muted">Participants</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="border rounded p-2">
                            <div class="h5 mb-0 text-success">{{ sm_money($tour->budget, 0) }}</div>
                            <small class="text-muted">Budget</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="border rounded p-2">
                            <div class="h5 mb-0 text-danger">{{ sm_money($totalExpense, 0) }}</div>
                            <small class="text-muted">Expenses</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="border rounded p-2">
                            <div class="h5 mb-0 text-info">{{ sm_money($totalCollected, 0) }}</div>
                            <small class="text-muted">Collected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3 no-print" id="tourTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#registrations">
            <i class="fas fa-users me-1"></i> Participants
            <span class="badge bg-primary-subtle text-primary ms-1">{{ $tour->registrations->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#addParticipants">
            <i class="fas fa-user-plus me-1"></i> Add Participants
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#feeSetup">
            <i class="fas fa-tags me-1"></i> Fee Setup
            <span class="badge bg-warning-subtle text-warning ms-1">{{ $tour->feeCategories->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#expenses">
            <i class="fas fa-money-bill me-1"></i> Expenses
            <span class="badge bg-danger-subtle text-danger ms-1">{{ $tour->expenses->count() }}</span>
        </a>
    </li>
</ul>

<div class="tab-content">

    {{-- ===== PARTICIPANTS TAB ===== --}}
    <div class="tab-pane fade show active" id="registrations">

        {{-- Filter Bar --}}
        <div class="card shadow mb-2">
            <div class="card-body py-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label form-label-sm mb-0">{{ sm_t('Type') }}</label>
                        <select id="filterType" class="form-control form-control-sm">
                            <option value="">{{ sm_t('All Types') }}</option>
                            <option value="student">{{ sm_t('Student') }}</option>
                            <option value="teacher">{{ sm_t('Teacher') }}</option>
                            <option value="staff">{{ sm_t('Staff') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm mb-0">{{ sm_t('Class / Designation') }}</label>
                        <select id="filterClass" class="form-control form-control-sm">
                            <option value="">{{ sm_t('All') }}</option>
                            @foreach($classes as $cls)
                            <option value="{{ $cls->name }}">{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm mb-0">{{ sm_t('Payment') }}</label>
                        <select id="filterPayment" class="form-control form-control-sm">
                            <option value="">{{ sm_t('All') }}</option>
                            <option value="unpaid">{{ sm_t('Unpaid') }}</option>
                            <option value="partial">{{ sm_t('Partial') }}</option>
                            <option value="paid">{{ sm_t('Paid') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm mb-0">{{ sm_t('Approved') }}</label>
                        <select id="filterApproved" class="form-control form-control-sm">
                            <option value="">{{ sm_t('All') }}</option>
                            <option value="yes">{{ sm_t('Yes') }}</option>
                            <option value="no">{{ sm_t('No') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2 align-items-end">
                        <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Search name...">
                        <button type="button" class="btn btn-sm btn-encodex-clear" onclick="resetFilters()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-1">
                    <small class="text-muted">Showing <span id="filterCount">{{ $tour->registrations->count() }}</span> of {{ $tour->registrations->count() }} participants</small>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body p-0">
                <table class="table table-bordered table-hover table-sm table-encodex mb-0" id="participantsTable">
                    <thead class="text-center">
                        <tr>
                            <th>#</th>
                            <th>{{ sm_t('Type') }}</th>
                            <th class="text-start">{{ sm_t('Name') }}</th>
                            <th>{{ sm_t('Class / Role') }}</th>
                            <th>{{ sm_t('Fee Category') }}</th>
                            <th>Fee ({{ sm_currency() }})</th>
                            <th>Paid ({{ sm_currency() }})</th>
                            <th>{{ sm_t('Payment') }}</th>
                            <th>{{ sm_t('Approved') }}</th>
                            <th>{{ sm_t('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tour->registrations as $i => $reg)
                        @php
                            $name = match($reg->participant_type) {
                                'teacher' => optional($reg->teacher)->first_name . ' ' . optional($reg->teacher)->last_name,
                                'staff'   => optional($reg->staff)->first_name . ' ' . optional($reg->staff)->last_name,
                                default   => optional($reg->student)->first_name . ' ' . optional($reg->student)->last_name,
                            };
                            $classRole = match($reg->participant_type) {
                                'teacher' => optional($reg->teacher)->designation ?? 'Teacher',
                                'staff'   => $staffDesignations[optional($reg->staff)->designation ?? ''] ?? optional($reg->staff)->designation ?? 'Staff',
                                default   => optional($reg->student?->classe)->name ?? '—',
                            };
                            $typeBadge = ['student'=>'primary','teacher'=>'success','staff'=>'warning'][$reg->participant_type] ?? 'secondary';
                        @endphp
                        <tr class="participant-row"
                            data-type="{{ $reg->participant_type }}"
                            data-name="{{ strtolower($name) }}"
                            data-class="{{ strtolower($classRole) }}"
                            data-payment="{{ $reg->payment_status }}"
                            data-approved="{{ $reg->is_approved ? 'yes' : 'no' }}">
                            <td class="text-center row-num">{{ $i + 1 }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $bc = $typeBadge }}-subtle text-{{ $bc }}">{{ ucfirst($reg->participant_type) }}</span>
                            </td>
                            <td>{{ $name }}</td>
                            <td class="text-center small">{{ $classRole }}</td>
                            <td class="text-center small text-muted">
                                {{ optional($reg->feeCategory)->category_label ?? '—' }}
                            </td>
                            <td class="text-end">{{ number_format($reg->fee, 0) }}</td>
                            <td class="text-end">{{ number_format($reg->paid_amount, 0) }}</td>
                            <td class="text-center">
                                @php $pc = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success'][$reg->payment_status] ?? 'secondary' @endphp
                                <span class="badge bg-{{ $bc = $pc }}-subtle text-{{ $bc }}">{{ ucfirst($reg->payment_status) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $bc = $reg->is_approved ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">
                                    {{ $reg->is_approved ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($reg->participant_type === 'student' && $reg->student_id)
                                <a href="{{ route('academic.tours.badge', [$tour->id, $reg->id]) }}" target="_blank"
                                    class="btn btn-sm btn-encodex-show" title="Badge">
                                    <i class="fas fa-id-card"></i>
                                </a>
                                @endif
                                <button class="btn btn-sm btn-encodex-edit" title="Edit Payment"
                                    onclick="openPaymentModal({{ $reg->id }}, {{ json_encode($reg) }})">
                                    <i class="fas fa-money-bill"></i>
                                </button>
                                <form method="POST"
                                    action="{{ route('academic.tours.registrations.destroy', [$tour->id, $reg->id]) }}"
                                    class="d-inline" onsubmit="return confirm('Remove this participant?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-encodex-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center text-muted py-3">No participants registered yet</td></tr>
                        @endforelse
                    </tbody>
                    @if($tour->registrations->isNotEmpty())
                    <tfoot class="table-light fw-bold text-end">
                        <tr>
                            <td colspan="5" class="text-center">Total</td>
                            <td>{{ sm_money($totalFee, 0) }}</td>
                            <td>{{ sm_money($totalCollected, 0) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ===== ADD PARTICIPANTS TAB ===== --}}
    <div class="tab-pane fade" id="addParticipants">
        <div class="card shadow">
            <div class="card-header">
                <ul class="nav nav-pills" id="participantTypeTabs">
                    <li class="nav-item">
                        <a class="nav-link active py-1 px-3" data-bs-toggle="pill" href="#addStudents">
                            <i class="fas fa-user-graduate me-1"></i> Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3" data-bs-toggle="pill" href="#addTeachers">
                            <i class="fas fa-chalkboard-teacher me-1"></i> Teachers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3" data-bs-toggle="pill" href="#addStaffTab">
                            <i class="fas fa-people-carry me-1"></i> Staff
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">

                    {{-- Students --}}
                    <div class="tab-pane fade show active" id="addStudents">
                        @php
                            $allStudents = \ME\SchoolManagement\Models\Student::where('is_active',true)
                                ->with(['classe','section'])
                                ->orderBy('class_id')->orderBy('roll_number')->get();
                        @endphp
                        <form method="POST" action="{{ route('academic.tours.registrations.store', $tour->id) }}">
                            @csrf
                            <input type="hidden" name="participant_type" value="student">
                            <div class="row g-2 mb-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm">{{ sm_t('Filter by Class') }}</label>
                                    <select id="stuClassFilter" class="form-control form-control-sm">
                                        <option value="">{{ sm_t('All Classes') }}</option>
                                        @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm">{{ sm_t('Fee Category (Master)') }}</label>
                                    <select name="tour_fee_category_id" id="stuFeeCategory" class="form-control form-control-sm">
                                        <option value="">— Select Category —</option>
                                        @foreach($tour->feeCategories as $cat)
                                        <option value="{{ $cat->id }}" data-fee="{{ $cat->fee }}">{{ $cat->category_label }} — {{ sm_money($cat->fee,0) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">Fee ({{ sm_currency() }}) <small class="text-muted">editable</small></label>
                                    <input type="number" name="fee" id="stuFee" class="form-control form-control-sm" value="0" min="0" step="0.01">
                                </div>
                                <div class="col-md-4 d-flex gap-2 align-items-end">
                                    <button type="button" class="btn btn-sm btn-encodex" onclick="selectAll('stuCheck', true)">{{ sm_t('All') }}</button>
                                    <button type="button" class="btn btn-sm btn-encodex-clear" onclick="selectAll('stuCheck', false)">{{ sm_t('None') }}</button>
                                    <button type="submit" class="btn btn-sm btn-encodex-save ms-auto" onclick="return confirm('Register selected?')">
                                        <i class="fas fa-user-plus me-1"></i> Register
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                                <table class="table table-bordered table-sm table-encodex mb-0">
                                    <thead class="text-center sticky-top bg-white">
                                        <tr>
                                            <th><input type="checkbox" onchange="selectAll('stuCheck', this.checked)"></th>
                                            <th>#</th>
                                            <th class="text-start">{{ sm_t('Name') }}</th>
                                            <th>{{ sm_t('Class') }}</th>
                                            <th>{{ sm_t('Section') }}</th>
                                            <th>{{ sm_t('Roll') }}</th>
                                            <th>{{ sm_t('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allStudents as $i => $student)
                                        @php $alreadyReg = in_array($student->id, $registeredStudentIds); @endphp
                                        <tr class="stu-row" data-class="{{ $student->class_id }}">
                                            <td class="text-center">
                                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                                    class="stuCheck" {{ $alreadyReg ? 'disabled' : '' }}>
                                            </td>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                            <td class="text-center">{{ $student->classe?->name ?? '—' }}</td>
                                            <td class="text-center">{{ $student->section?->name ?? '—' }}</td>
                                            <td class="text-center">{{ $student->roll_number ?? '—' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $bc = $alreadyReg ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">
                                                    {{ $alreadyReg ? 'Registered' : 'Not Registered' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>

                    {{-- Teachers --}}
                    <div class="tab-pane fade" id="addTeachers">
                        @php $allTeachers = \ME\SchoolManagement\Models\Teacher::where('is_active',true)->orderBy('first_name')->get(); @endphp
                        <form method="POST" action="{{ route('academic.tours.registrations.store', $tour->id) }}">
                            @csrf
                            <input type="hidden" name="participant_type" value="teacher">
                            <div class="row g-2 mb-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm">{{ sm_t('Fee Category (Master)') }}</label>
                                    <select name="tour_fee_category_id" id="tchFeeCategory" class="form-control form-control-sm">
                                        <option value="">— Select Category —</option>
                                        @foreach($tour->feeCategories as $cat)
                                        <option value="{{ $cat->id }}" data-fee="{{ $cat->fee }}">{{ $cat->category_label }} — {{ sm_money($cat->fee,0) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">Fee ({{ sm_currency() }})</label>
                                    <input type="number" name="fee" id="tchFee" class="form-control form-control-sm" value="0" min="0" step="0.01">
                                </div>
                                <div class="col-md-7 d-flex gap-2 align-items-end">
                                    <button type="button" class="btn btn-sm btn-encodex" onclick="selectAll('tchCheck', true)">{{ sm_t('All') }}</button>
                                    <button type="button" class="btn btn-sm btn-encodex-clear" onclick="selectAll('tchCheck', false)">{{ sm_t('None') }}</button>
                                    <button type="submit" class="btn btn-sm btn-encodex-save ms-auto" onclick="return confirm('Register selected?')">
                                        <i class="fas fa-user-plus me-1"></i> Register
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                                <table class="table table-bordered table-sm table-encodex mb-0">
                                    <thead class="text-center sticky-top bg-white">
                                        <tr>
                                            <th><input type="checkbox" onchange="selectAll('tchCheck', this.checked)"></th>
                                            <th>#</th>
                                            <th class="text-start">{{ sm_t('Name') }}</th>
                                            <th>{{ sm_t('Designation') }}</th>
                                            <th>{{ sm_t('Phone') }}</th>
                                            <th>{{ sm_t('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allTeachers as $i => $teacher)
                                        @php $alreadyReg = in_array($teacher->id, $registeredTeacherIds); @endphp
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="teacher_ids[]" value="{{ $teacher->id }}"
                                                    class="tchCheck" {{ $alreadyReg ? 'disabled' : '' }}>
                                            </td>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td>{{ $teacher->first_name }} {{ $teacher->last_name }}</td>
                                            <td class="text-center">{{ $teacher->designation ?? '—' }}</td>
                                            <td class="text-center">{{ $teacher->phone ?? '—' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $bc = $alreadyReg ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">
                                                    {{ $alreadyReg ? 'Registered' : 'Not Registered' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>

                    {{-- Staff --}}
                    <div class="tab-pane fade" id="addStaffTab">
                        @php $allStaff = \ME\SchoolManagement\Models\Staff::where('is_active',true)->orderBy('designation')->orderBy('first_name')->get(); @endphp
                        <form method="POST" action="{{ route('academic.tours.registrations.store', $tour->id) }}">
                            @csrf
                            <input type="hidden" name="participant_type" value="staff">
                            <div class="row g-2 mb-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm">{{ sm_t('Fee Category (Master)') }}</label>
                                    <select name="tour_fee_category_id" id="stfFeeCategory" class="form-control form-control-sm">
                                        <option value="">— Select Category —</option>
                                        @foreach($tour->feeCategories as $cat)
                                        <option value="{{ $cat->id }}" data-fee="{{ $cat->fee }}">{{ $cat->category_label }} — {{ sm_money($cat->fee,0) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">Fee ({{ sm_currency() }})</label>
                                    <input type="number" name="fee" id="stfFee" class="form-control form-control-sm" value="0" min="0" step="0.01">
                                </div>
                                <div class="col-md-7 d-flex gap-2 align-items-end">
                                    <button type="button" class="btn btn-sm btn-encodex" onclick="selectAll('stfCheck', true)">{{ sm_t('All') }}</button>
                                    <button type="button" class="btn btn-sm btn-encodex-clear" onclick="selectAll('stfCheck', false)">{{ sm_t('None') }}</button>
                                    <button type="submit" class="btn btn-sm btn-encodex-save ms-auto" onclick="return confirm('Register selected?')">
                                        <i class="fas fa-user-plus me-1"></i> Register
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                                <table class="table table-bordered table-sm table-encodex mb-0">
                                    <thead class="text-center sticky-top bg-white">
                                        <tr>
                                            <th><input type="checkbox" onchange="selectAll('stfCheck', this.checked)"></th>
                                            <th>#</th>
                                            <th class="text-start">{{ sm_t('Name') }}</th>
                                            <th>{{ sm_t('Designation') }}</th>
                                            <th>{{ sm_t('Phone') }}</th>
                                            <th>{{ sm_t('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allStaff as $i => $stf)
                                        @php $alreadyReg = in_array($stf->id, $registeredStaffIds); @endphp
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="staff_ids[]" value="{{ $stf->id }}"
                                                    class="stfCheck" {{ $alreadyReg ? 'disabled' : '' }}>
                                            </td>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td>{{ $stf->first_name }} {{ $stf->last_name }}</td>
                                            <td class="text-center">{{ $staffDesignations[$stf->designation] ?? $stf->designation }}</td>
                                            <td class="text-center">{{ $stf->phone ?? '—' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $bc = $alreadyReg ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">
                                                    {{ $alreadyReg ? 'Registered' : 'Not Registered' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ===== FEE SETUP TAB ===== --}}
    <div class="tab-pane fade" id="feeSetup">
        <div class="row g-3">
            <div class="col-md-7">
                <div class="card shadow">
                    <div class="card-header"><h6 class="mb-0"><i class="fas fa-tags me-1"></i> Fee Categories for this Tour</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm table-encodex mb-0">
                            <thead class="text-center">
                                <tr>
                                    <th>#</th>
                                    <th class="text-start">{{ sm_t('Category Label') }}</th>
                                    <th>{{ sm_t('Type') }}</th>
                                    <th>{{ sm_t('Class') }}</th>
                                    <th class="text-end">Fee ({{ sm_currency() }})</th>
                                    <th>{{ sm_t('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tour->feeCategories as $i => $cat)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td><strong>{{ $cat->category_label }}</strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info">{{ ucfirst(str_replace('_',' ',$cat->category_type)) }}</span>
                                    </td>
                                    <td class="text-center">{{ optional($cat->classe)->name ?? '—' }}</td>
                                    <td class="text-end fw-bold">{{ sm_money($cat->fee, 0) }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-encodex-edit"
                                            onclick="openEditFee({{ $cat->id }}, {{ json_encode($cat) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST"
                                            action="{{ route('academic.tours.fee.destroy', [$tour->id, $cat->id]) }}"
                                            class="d-inline" onsubmit="return confirm('Delete fee category?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">No fee categories defined. Add categories first before registering participants.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow mb-3">
                    <div class="card-header"><h6 class="mb-0"><i class="fas fa-plus me-1"></i> Add Fee Category</h6></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('academic.tours.fee.store', $tour->id) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label form-label-sm">{{ sm_t('Category Label') }} <span class="text-danger">*</span></label>
                                <input type="text" name="category_label" class="form-control form-control-sm" required
                                    placeholder="e.g. Class 6, Senior Teacher, Staff, Management">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">{{ sm_t('Category Type') }}</label>
                                <select name="category_type" class="form-control form-control-sm" id="feeCatType">
                                    <option value="student_class">{{ sm_t('Student (Class-wise)') }}</option>
                                    <option value="teacher">{{ sm_t('Teacher') }}</option>
                                    <option value="staff">{{ sm_t('Staff') }}</option>
                                    <option value="management">{{ sm_t('Management') }}</option>
                                    <option value="custom">{{ sm_t('Custom') }}</option>
                                </select>
                            </div>
                            <div class="mb-2" id="classSelectWrap">
                                <label class="form-label form-label-sm">{{ sm_t('Specific Class') }}</label>
                                <select name="class_id" class="form-control form-control-sm">
                                    <option value="">{{ sm_t('All / Not class-specific') }}</option>
                                    @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Fee Amount ({{ sm_currency() }}) <span class="text-danger">*</span></label>
                                <input type="number" name="fee" class="form-control form-control-sm" required min="0" step="0.01">
                            </div>
                            <button type="submit" class="btn btn-sm btn-encodex-save w-100">
                                <i class="fas fa-save me-1"></i> Save Category
                            </button>
                        </form>
                    </div>
                </div>
                <div class="alert alert-info small p-2 mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Master fees এখানে set করুন (Teacher {{ sm_currency() }}X, Class 6 {{ sm_currency() }}Y, Staff {{ sm_currency() }}Z)। Participant add করার সময় category select করলে fee auto-fill হবে — কিন্তু প্রতিটির জন্য আলাদা edit করা যাবে।
                </div>
            </div>
        </div>
    </div>

    {{-- ===== EXPENSES TAB ===== --}}
    <div class="tab-pane fade" id="expenses">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-list me-1"></i> Expense List</h6>
                        <strong class="text-danger">Total: {{ sm_money($totalExpense, 0) }}</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm table-encodex mb-0">
                            <thead class="text-center">
                                <tr>
                                    <th>#</th>
                                    <th>{{ sm_t('Category') }}</th>
                                    <th class="text-start">{{ sm_t('Description') }}</th>
                                    <th>{{ sm_t('Date') }}</th>
                                    <th>{{ sm_t('Paid By') }}</th>
                                    <th class="text-end">Amount ({{ sm_currency() }})</th>
                                    <th>{{ sm_t('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tour->expenses as $i => $exp)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info">{{ $expenseCategories[$exp->category] ?? $exp->category }}</span>
                                    </td>
                                    <td>{{ $exp->description }}</td>
                                    <td class="text-center">{{ sm_date($exp->expense_date) }}</td>
                                    <td>{{ $exp->paid_by ?? '—' }}</td>
                                    <td class="text-end fw-bold">{{ number_format($exp->amount, 2) }}</td>
                                    <td class="text-center">
                                        <form method="POST"
                                            action="{{ route('academic.tours.expenses.destroy', [$tour->id, $exp->id]) }}"
                                            class="d-inline" onsubmit="return confirm('Delete expense?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-encodex-delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">No expenses recorded</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                @if($expenseByCategory->isNotEmpty())
                <div class="card shadow mb-3">
                    <div class="card-header"><h6 class="mb-0">{{ sm_t('Expense Summary') }}</h6></div>
                    <div class="card-body p-2">
                        @foreach($expenseByCategory as $cat => $sum)
                        <div class="d-flex justify-content-between border-bottom py-1">
                            <span class="small">{{ $expenseCategories[$cat] ?? $cat }}</span>
                            <strong class="small">{{ sm_money($sum, 0) }}</strong>
                        </div>
                        @endforeach
                        <div class="d-flex justify-content-between pt-2">
                            <strong>Total</strong>
                            <strong class="text-danger">{{ sm_money($totalExpense, 0) }}</strong>
                        </div>
                    </div>
                </div>
                @endif
                <div class="card shadow">
                    <div class="card-header"><h6 class="mb-0"><i class="fas fa-plus me-1"></i> Add Expense</h6></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('academic.tours.expenses.store', $tour->id) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Category *</label>
                                <select name="category" class="form-control form-control-sm" required>
                                    @foreach($expenseCategories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Description *</label>
                                <input type="text" name="description" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Amount ({{ sm_currency() }}) *</label>
                                <input type="number" name="amount" class="form-control form-control-sm" required min="0" step="0.01">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Date *</label>
                                <input type="date" name="expense_date" class="form-control form-control-sm" required value="{{ $tour->start_date->format('Y-m-d') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">{{ sm_t('Paid By') }}</label>
                                <input type="text" name="paid_by" class="form-control form-control-sm">
                            </div>
                            <button type="submit" class="btn btn-sm btn-encodex-save w-100">
                                <i class="fas fa-save me-1"></i> Save Expense
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Payment Edit Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('Update Payment') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="paymentForm">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label form-label-sm">{{ sm_t('Fee Category') }}</label>
                        <select name="tour_fee_category_id" class="form-control form-control-sm" id="payFeeCategory">
                            <option value="">— No category —</option>
                            @foreach($tour->feeCategories as $cat)
                            <option value="{{ $cat->id }}" data-fee="{{ $cat->fee }}">{{ $cat->category_label }} — {{ sm_money($cat->fee,0) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">Fee ({{ sm_currency() }})</label>
                        <input type="number" name="fee" id="payFee" class="form-control form-control-sm" min="0" step="0.01">
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">Paid Amount ({{ sm_currency() }})</label>
                        <input type="number" name="paid_amount" id="payPaid" class="form-control form-control-sm" min="0" step="0.01">
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">{{ sm_t('Payment Status') }}</label>
                        <select name="payment_status" id="payStatus" class="form-control form-control-sm">
                            <option value="unpaid">{{ sm_t('Unpaid') }}</option>
                            <option value="partial">{{ sm_t('Partial') }}</option>
                            <option value="paid">{{ sm_t('Paid') }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">{{ sm_t('Approved') }}</label>
                        <select name="is_approved" class="form-control form-control-sm" id="payApproved">
                            <option value="0">{{ sm_t('No') }}</option>
                            <option value="1">{{ sm_t('Yes') }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">{{ sm_t('Notes') }}</label>
                        <textarea name="notes" id="payNotes" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Fee Category Modal --}}
<div class="modal fade" id="editFeeModal" tabindex="-1">
    <div class="modal-dialog modal-sm glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('Edit Fee Category') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editFeeForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label form-label-sm">Category Label *</label>
                        <input type="text" name="category_label" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">{{ sm_t('Type') }}</label>
                        <select name="category_type" class="form-control form-control-sm">
                            <option value="student_class">{{ sm_t('Student (Class-wise)') }}</option>
                            <option value="teacher">{{ sm_t('Teacher') }}</option>
                            <option value="staff">{{ sm_t('Staff') }}</option>
                            <option value="management">{{ sm_t('Management') }}</option>
                            <option value="custom">{{ sm_t('Custom') }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">Fee ({{ sm_currency() }}) *</label>
                        <input type="number" name="fee" class="form-control form-control-sm" required min="0" step="0.01">
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">{{ sm_t('Serial / Order') }}</label>
                        <input type="number" name="serial" class="form-control form-control-sm" min="1">
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Participants filter ────────────────────────────────────────────────
function applyParticipantFilter() {
    const type     = document.getElementById('filterType').value.toLowerCase();
    const cls      = document.getElementById('filterClass').value.toLowerCase();
    const payment  = document.getElementById('filterPayment').value.toLowerCase();
    const approved = document.getElementById('filterApproved').value.toLowerCase();
    const search   = document.getElementById('filterSearch').value.toLowerCase().trim();

    const rows = document.querySelectorAll('.participant-row');
    let visible = 0;
    rows.forEach((row, idx) => {
        const matchType     = !type     || row.dataset.type     === type;
        const matchClass    = !cls      || row.dataset.class.includes(cls);
        const matchPayment  = !payment  || row.dataset.payment  === payment;
        const matchApproved = !approved || row.dataset.approved === approved;
        const matchSearch   = !search   || row.dataset.name.includes(search);

        const show = matchType && matchClass && matchPayment && matchApproved && matchSearch;
        row.style.display = show ? '' : 'none';
        if (show) {
            visible++;
            // re-number visible rows
            const numCell = row.querySelector('.row-num');
            if (numCell) numCell.textContent = visible;
        }
    });

    const countEl = document.getElementById('filterCount');
    if (countEl) countEl.textContent = visible;
}

function resetFilters() {
    ['filterType','filterClass','filterPayment','filterApproved'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('filterSearch').value = '';
    applyParticipantFilter();
}

['filterType','filterClass','filterPayment','filterApproved'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', applyParticipantFilter);
});
document.getElementById('filterSearch')?.addEventListener('input', applyParticipantFilter);

// ── Fee category → auto-fill fee
function bindFeeCategory(selectId, feeInputId) {
    const sel = document.getElementById(selectId);
    const feeInput = document.getElementById(feeInputId);
    if (!sel || !feeInput) return;
    sel.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.dataset.fee) feeInput.value = opt.dataset.fee;
    });
}
bindFeeCategory('stuFeeCategory', 'stuFee');
bindFeeCategory('tchFeeCategory', 'tchFee');
bindFeeCategory('stfFeeCategory', 'stfFee');
bindFeeCategory('payFeeCategory', 'payFee');

// Checkbox select all helper
function selectAll(className, checked) {
    document.querySelectorAll('.' + className + ':not([disabled])').forEach(cb => cb.checked = checked);
}

// Class filter for students
document.getElementById('stuClassFilter')?.addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('.stu-row').forEach(row => {
        row.style.display = (!val || row.dataset.class == val) ? '' : 'none';
    });
});

// Payment modal
function openPaymentModal(regId, reg) {
    const base = '{{ route("academic.tours.registrations.update", [$tour->id, "__ID__"]) }}';
    document.getElementById('paymentForm').action = base.replace('__ID__', regId);
    document.getElementById('payFee').value         = reg.fee;
    document.getElementById('payPaid').value        = reg.paid_amount;
    document.getElementById('payStatus').value      = reg.payment_status;
    document.getElementById('payApproved').value    = reg.is_approved ? '1' : '0';
    document.getElementById('payNotes').value       = reg.notes ?? '';
    document.getElementById('payFeeCategory').value = reg.tour_fee_category_id ?? '';
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

// Fee category edit modal
function openEditFee(id, cat) {
    const base = '{{ route("academic.tours.fee.update", [$tour->id, "__ID__"]) }}';
    document.getElementById('editFeeForm').action = base.replace('__ID__', id);
    const f = (n, v) => { const el = document.getElementById('editFeeForm').querySelector('[name="'+n+'"]'); if(el) el.value = v ?? ''; };
    f('category_label', cat.category_label);
    f('category_type',  cat.category_type);
    f('fee',            cat.fee);
    f('serial',         cat.serial);
    new bootstrap.Modal(document.getElementById('editFeeModal')).show();
}

// Hide class select when type is not student_class
document.getElementById('feeCatType')?.addEventListener('change', function() {
    const wrap = document.getElementById('classSelectWrap');
    if (wrap) wrap.style.display = this.value === 'student_class' ? '' : 'none';
});
</script>
@endsection
