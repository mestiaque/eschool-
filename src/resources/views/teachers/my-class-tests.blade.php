@extends('me::master')
@section('title', sm_t('My Class Tests'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#classTestModal" onclick="resetTestForm()">
        <i class="fas fa-plus"></i> Add Class Test
    </button>
@endpush

@section('content')

@if(!$teacherId)
<div class="alert alert-warning">Your login account isn't linked to a teacher profile yet. Ask an admin to link your account from the Teachers page.</div>
@else
@php
    $myClasses  = $teachingPairs->pluck('classe')->filter()->unique('id');
    $mySubjects = $teachingPairs->pluck('subject')->filter()->unique('id');
@endphp

<div class="card shadow">
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
                    <td class="text-center">
                        <span class="badge bg-{{ $bc = $statusColors[$test->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($test->status) }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('my.class-tests.marks', $test->id) }}" class="btn btn-sm btn-encodex-show" title="Enter Marks">
                            <i class="fas fa-marker"></i>
                        </a>
                        <button class="btn btn-sm btn-encodex-edit"
                            onclick="editTest({{ $test->id }}, '{{ addslashes($test->title) }}', {{ $test->class_id }}, {{ $test->subject_id }}, '{{ $test->test_date?->format('Y-m-d') }}', {{ $test->total_marks }}, {{ $test->pass_marks }}, '{{ $test->duration }}', '{{ addslashes($test->description ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('my.class-tests.destroy', $test->id) }}" class="d-inline" onsubmit="return confirm('Delete this test?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No class tests found</td></tr>
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
            <form method="POST" id="classTestForm" action="{{ route('my.class-tests.store') }}">
                @csrf
                <input type="hidden" name="_method" id="classTestMethod" value="POST">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" id="ct_title" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Class *</label>
                            <select name="class_id" id="ct_class_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Class') }}</option>
                                @foreach($myClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Subject *</label>
                            <select name="subject_id" id="ct_subject_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Subject') }}</option>
                                @foreach($mySubjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Test Date *</label>
                            <input type="date" name="test_date" id="ct_test_date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Total Marks *</label>
                            <input type="number" name="total_marks" id="ct_total_marks" class="form-control form-control-sm" value="100" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Pass Marks *</label>
                            <input type="number" name="pass_marks" id="ct_pass_marks" class="form-control form-control-sm" value="33" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Duration') }}</label>
                            <input type="text" name="duration" id="ct_duration" class="form-control form-control-sm" placeholder="e.g. 30 min">
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
function resetTestForm() {
    document.getElementById('classTestModalTitle').textContent = 'Add Class Test';
    document.getElementById('classTestForm').action = '{{ route('my.class-tests.store') }}';
    document.getElementById('classTestMethod').value = 'POST';
    ['ct_title','ct_class_id','ct_subject_id','ct_test_date','ct_duration','ct_description'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('ct_total_marks').value = 100;
    document.getElementById('ct_pass_marks').value = 33;
}
function editTest(id, title, class_id, subject_id, test_date, total_marks, pass_marks, duration, description) {
    document.getElementById('classTestModalTitle').textContent = 'Edit Class Test';
    document.getElementById('classTestForm').action = '{{ url('my/class-tests') }}/' + id;
    document.getElementById('classTestMethod').value = 'PUT';
    document.getElementById('ct_title').value = title;
    document.getElementById('ct_class_id').value = class_id;
    document.getElementById('ct_subject_id').value = subject_id;
    document.getElementById('ct_test_date').value = test_date;
    document.getElementById('ct_total_marks').value = total_marks;
    document.getElementById('ct_pass_marks').value = pass_marks;
    document.getElementById('ct_duration').value = duration;
    document.getElementById('ct_description').value = description;
    new bootstrap.Modal(document.getElementById('classTestModal')).show();
}
</script>
@endif
@endsection
