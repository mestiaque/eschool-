@extends('me::master')
@section('title', sm_t('My Assignments'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#assignmentModal" onclick="resetAssignmentForm()">
        <i class="fas fa-plus"></i> {{ sm_t('Add Assignment') }}
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
                    <th>{{ sm_t('Assigned') }}</th>
                    <th>{{ sm_t('Due Date') }}</th>
                    <th>{{ sm_t('Marks') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                @php $statusColors = ['active'=>'success','closed'=>'secondary','cancelled'=>'danger']; @endphp
                <tr>
                    <td class="text-center">{{ $assignments->firstItem() + $loop->index }}</td>
                    <td>{{ $assignment->title }}</td>
                    <td>{{ $assignment->classe->name ?? '-' }}</td>
                    <td>{{ $assignment->subject->name ?? '-' }}</td>
                    <td class="text-center">{{ sm_date($assignment->assigned_date) }}</td>
                    <td class="text-center">
                        {{ sm_date($assignment->due_date) }}
                        @if($assignment->due_date && $assignment->due_date->isPast() && $assignment->status === 'active')
                        <span class="badge bg-danger-subtle text-danger ms-1">{{ sm_t('Overdue') }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $assignment->total_marks }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $bc = $statusColors[$assignment->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t(ucfirst($assignment->status)) }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('my.assignments.submissions', $assignment->id) }}" class="btn btn-sm btn-encodex-show" title="View Submissions">
                            <i class="fas fa-list-check"></i> {{ sm_t('Submissions') }}
                        </a>
                        <button class="btn btn-sm btn-encodex-edit"
                            onclick="editAssignment({{ $assignment->id }}, '{{ addslashes($assignment->title) }}', {{ $assignment->class_id }}, {{ $assignment->subject_id }}, '{{ $assignment->assigned_date?->format('Y-m-d') }}', '{{ $assignment->due_date?->format('Y-m-d') }}', {{ $assignment->total_marks }}, '{{ addslashes($assignment->description ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('my.assignments.destroy', $assignment->id) }}" class="d-inline" onsubmit="return confirm('Delete this assignment?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">{{ sm_t('No assignments found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $assignments->links('pagination::bootstrap-5') }}</div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignmentModalTitle">{{ sm_t('Add Assignment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="assignmentForm" action="{{ route('my.assignments.store') }}">
                @csrf
                <input type="hidden" name="_method" id="assignmentMethod" value="POST">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-12 mb-2">
                            <label class="form-label">{{ sm_t('Title') }} *</label>
                            <input type="text" name="title" id="as_title" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Class') }} *</label>
                            <select name="class_id" id="as_class_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Class') }}</option>
                                @foreach($myClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Subject') }} *</label>
                            <select name="subject_id" id="as_subject_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Subject') }}</option>
                                @foreach($mySubjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Assigned Date') }} *</label>
                            <input type="date" name="assigned_date" id="as_assigned_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Due Date') }} *</label>
                            <input type="date" name="due_date" id="as_due_date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Total Marks') }} *</label>
                            <input type="number" name="total_marks" id="as_total_marks" class="form-control form-control-sm" value="100" required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label">{{ sm_t('Description') }}</label>
                            <textarea name="description" id="as_description" class="form-control form-control-sm" rows="3"></textarea>
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
function resetAssignmentForm() {
    document.getElementById('assignmentModalTitle').textContent = 'Add Assignment';
    document.getElementById('assignmentForm').action = '{{ route('my.assignments.store') }}';
    document.getElementById('assignmentMethod').value = 'POST';
    ['as_title','as_class_id','as_subject_id','as_due_date','as_description'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('as_assigned_date').value = '{{ date('Y-m-d') }}';
    document.getElementById('as_total_marks').value = 100;
}
function editAssignment(id, title, class_id, subject_id, assigned_date, due_date, total_marks, description) {
    document.getElementById('assignmentModalTitle').textContent = 'Edit Assignment';
    document.getElementById('assignmentForm').action = '{{ url('my/assignments') }}/' + id;
    document.getElementById('assignmentMethod').value = 'PUT';
    document.getElementById('as_title').value = title;
    document.getElementById('as_class_id').value = class_id;
    document.getElementById('as_subject_id').value = subject_id;
    document.getElementById('as_assigned_date').value = assigned_date;
    document.getElementById('as_due_date').value = due_date;
    document.getElementById('as_total_marks').value = total_marks;
    document.getElementById('as_description').value = description;
    new bootstrap.Modal(document.getElementById('assignmentModal')).show();
}
</script>
@endif
@endsection
