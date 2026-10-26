@extends('me::master')
@section('title', sm_t('Class Tests'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#classTestModal">
        <i class="fas fa-plus"></i> {{ sm_t('Add Class Test') }}
    </button>
@endpush

@section('content')


<div class="card shadow">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-3">
                <select name="class_id" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Classes') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="subject_id" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Subjects') }}</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Status') }}</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>{{ sm_t('Scheduled') }}</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>{{ sm_t('Ongoing') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ sm_t('Completed') }}</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ sm_t('Cancelled') }}</option>
                </select>
            </div>
            <div class="col-md">
                <button type="submit" class="btn btn-sm btn-encodex-search"><i class="fas fa-search"></i> {{ sm_t('Filter') }}</button>
                <a href="{{ route('academic.class-tests') }}" class="btn btn-sm btn-encodex-clear"><i class="fas fa-eraser"></i> {{ sm_t('Reset') }}</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-striped table-encodex table-sm mb-0">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Title') }}</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Subject') }}</th>
                    <th>{{ sm_t('Test Date') }}</th>
                    <th>{{ sm_t('Total') }}</th>
                    <th>{{ sm_t('Pass') }}</th>
                    <th>{{ sm_t('Duration') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classTests as $test)
                @php $statusColors = ['scheduled'=>'primary','ongoing'=>'warning','completed'=>'success','cancelled'=>'danger']; @endphp
                <tr>
                    <td class="text-center">{{ $classTests->firstItem() + $loop->index }}</td>
                    <td>{{ $test->title }}</td>
                    <td>{{ $test->classe->name ?? '-' }}</td>
                    <td>{{ $test->subject->name ?? '-' }}</td>
                    <td class="text-center">{{ sm_date($test->test_date) }}</td>
                    <td class="text-center">{{ $test->total_marks }}</td>
                    <td class="text-center">{{ $test->pass_marks }}</td>
                    <td class="text-center">{{ $test->duration ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $bc = $statusColors[$test->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t(ucfirst($test->status)) }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('academic.class-tests.marks', $test->id) }}" class="btn btn-sm btn-encodex-show" title="Enter Marks">
                            <i class="fas fa-marker"></i>
                        </a>
                        <button class="btn btn-sm btn-encodex-edit"
                            onclick="editTest({{ $test->id }}, '{{ addslashes($test->title) }}', {{ $test->class_id }}, {{ $test->subject_id }}, '{{ $test->test_date?->format('Y-m-d') }}', {{ $test->total_marks }}, {{ $test->pass_marks }}, '{{ $test->duration }}', '{{ $test->status }}', '{{ addslashes($test->description ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('academic.class-tests.destroy', $test->id) }}" class="d-inline" onsubmit="return confirm('Delete this test?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">{{ sm_t('No class tests found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $classTests->links('pagination::bootstrap-5') }}</div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="classTestModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classTestModalTitle">{{ sm_t('Add Class Test') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="classTestForm" action="{{ route('academic.class-tests.store') }}">
                @csrf
                <input type="hidden" name="_method" id="classTestMethod" value="POST">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-12 mb-2">
                            <label class="form-label">{{ sm_t('Title') }} *</label>
                            <input type="text" name="title" id="ct_title" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Class') }} *</label>
                            <select name="class_id" id="ct_class_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Class') }}</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Subject') }} *</label>
                            <select name="subject_id" id="ct_subject_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Subject') }}</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Test Date') }} *</label>
                            <input type="date" name="test_date" id="ct_test_date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Total Marks') }} *</label>
                            <input type="number" name="total_marks" id="ct_total_marks" class="form-control form-control-sm" value="100" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Pass Marks') }} *</label>
                            <input type="number" name="pass_marks" id="ct_pass_marks" class="form-control form-control-sm" value="33" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Duration') }}</label>
                            <input type="text" name="duration" id="ct_duration" class="form-control form-control-sm" placeholder="{{ sm_t('e.g. 30 min') }}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Status') }}</label>
                            <select name="status" id="ct_status" class="form-control form-control-sm">
                                <option value="scheduled">{{ sm_t('Scheduled') }}</option>
                                <option value="ongoing">{{ sm_t('Ongoing') }}</option>
                                <option value="completed">{{ sm_t('Completed') }}</option>
                                <option value="cancelled">{{ sm_t('Cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label">{{ sm_t('Description') }}</label>
                            <textarea name="description" id="ct_description" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTest(id, title, class_id, subject_id, test_date, total_marks, pass_marks, duration, status, description) {
    document.getElementById('classTestModalTitle').textContent = 'Edit Class Test';
    document.getElementById('classTestForm').action = '/academic/class-tests/' + id;
    document.getElementById('classTestMethod').value = 'PUT';
    document.getElementById('ct_title').value = title;
    document.getElementById('ct_class_id').value = class_id;
    document.getElementById('ct_subject_id').value = subject_id;
    document.getElementById('ct_test_date').value = test_date;
    document.getElementById('ct_total_marks').value = total_marks;
    document.getElementById('ct_pass_marks').value = pass_marks;
    document.getElementById('ct_duration').value = duration;
    document.getElementById('ct_status').value = status;
    document.getElementById('ct_description').value = description;
    new bootstrap.Modal(document.getElementById('classTestModal')).show();
}
</script>
@endsection
