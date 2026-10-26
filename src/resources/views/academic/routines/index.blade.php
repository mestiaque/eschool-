@extends('me::master')
@section('title', sm_t('Class Routines'))
@push('buttons')
    <a href="{{ route('academic.routines.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-encodex-print no-print">
        <i class="fas fa-print"></i> Print
    </a>
    <a href="{{ route('academic.rooms') }}" class="btn btn-sm btn-encodex-list no-print">
        <i class="fas fa-door-open"></i> Manage Rooms
    </a>
    <a href="{{ route('academic.routine-titles') }}" class="btn btn-sm btn-encodex-list no-print">
        <i class="fas fa-tags"></i> Manage Versions
    </a>
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#routineModal">
        <i class="fas fa-plus"></i> Add Routine
    </button>
@endpush

@section('content')

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>

<script>
function loadRoutineOptions(classId, sectionField, subjectField, selectedSectionId, selectedSubjectId) {
    sectionField.innerHTML = '<option value="">{{ sm_t('Select Section') }}</option>';
    subjectField.innerHTML = '<option value="">{{ sm_t('Select Subject') }}</option>';

    if (!classId) return;

    fetch(`{{ url('sections') }}/${classId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(section => {
                const option = document.createElement('option');
                option.value = section.id;
                option.textContent = section.name;
                if (selectedSectionId && String(section.id) === String(selectedSectionId)) {
                    option.selected = true;
                }
                sectionField.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching sections:', error));

    fetch(`{{ url('subjects/class') }}/${classId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name;
                if (selectedSubjectId && String(subject.id) === String(selectedSubjectId)) {
                    option.selected = true;
                }
                subjectField.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching subjects:', error));
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle Add Routine Modal - Class change
    const classField = document.getElementById('classField');
    const sectionField = document.getElementById('sectionField');
    const subjectField = document.getElementById('subjectField');

    if (classField) {
        classField.addEventListener('change', function() {
            loadRoutineOptions(this.value, sectionField, subjectField);
        });
    }

    // Handle Edit Routine Modals - pre-fill Section/Subject when opened, and on Class change
    document.querySelectorAll('[id^="editRoutineModal"]').forEach(modal => {
        const editClassField = modal.querySelector('select[name="class_id"]');
        const editSectionField = modal.querySelector('select[name="section_id"]');
        const editSubjectField = modal.querySelector('select[name="subject_id"]');

        if (!editClassField) return;

        modal.addEventListener('show.bs.modal', function() {
            loadRoutineOptions(editClassField.value, editSectionField, editSubjectField, modal.dataset.sectionId, modal.dataset.subjectId);
        });

        editClassField.addEventListener('change', function() {
            loadRoutineOptions(this.value, editSectionField, editSubjectField);
        });
    });
});
</script>

<div class="card shadow">
    <div class="card-body">
        <form method="GET" class="glass-search-form mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="class_id" class="form-control form-control-sm">
                        <option value="">@lang('All Classes')</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="day" class="form-control form-control-sm">
                        <option value="">@lang('All Days')</option>
                        <option value="Saturday" {{ request('day') == 'Saturday' ? 'selected' : '' }}>{{ sm_t('Saturday') }}</option>
                        <option value="Sunday" {{ request('day') == 'Sunday' ? 'selected' : '' }}>{{ sm_t('Sunday') }}</option>
                        <option value="Monday" {{ request('day') == 'Monday' ? 'selected' : '' }}>{{ sm_t('Monday') }}</option>
                        <option value="Tuesday" {{ request('day') == 'Tuesday' ? 'selected' : '' }}>{{ sm_t('Tuesday') }}</option>
                        <option value="Wednesday" {{ request('day') == 'Wednesday' ? 'selected' : '' }}>{{ sm_t('Wednesday') }}</option>
                        <option value="Thursday" {{ request('day') == 'Thursday' ? 'selected' : '' }}>{{ sm_t('Thursday') }}</option>
                        <option value="Friday" {{ request('day') == 'Friday' ? 'selected' : '' }}>{{ sm_t('Friday') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="room_id" class="form-control form-control-sm">
                        <option value="">@lang('All Rooms')</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="title" class="form-control form-control-sm">
                        <option value="">@lang('All Titles')</option>
                        @foreach($titles as $t)
                        <option value="{{ $t }}" {{ request('title') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md">
                    <button type="submit" class="btn btn-sm btn-encodex-search">
                        <i class="fas fa-search"></i> @lang('Search')
                    </button>
                    <a href="{{ route('academic.routines') }}" class="btn btn-sm btn-encodex-clear">
                        <i class="fas fa-eraser"></i> @lang('Reset')
                    </a>
                    @if(request('room_id'))
                    <a href="{{ route('academic.rooms.schedule', request('room_id')) }}" class="btn btn-sm btn-encodex-show">
                        <i class="fas fa-calendar-alt"></i> Room Schedule
                    </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="responsive">
            <table class="table table-bordered table-hover table-striped table-encodex table-sm">
                <thead class="text-center">
                    <tr>
                        <th>{{ sm_t('Title') }}</th>
                        <th>{{ sm_t('Class') }}</th>
                        <th>{{ sm_t('Section') }}</th>
                        <th>{{ sm_t('Subject') }}</th>
                        <th>{{ sm_t('Teacher') }}</th>
                        <th>{{ sm_t('Day') }}</th>
                        <th>{{ sm_t('Start Time') }}</th>
                        <th>{{ sm_t('End Time') }}</th>
                        <th>{{ sm_t('Room') }}</th>
                        <th>{{ sm_t('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routines as $routine)
                    <tr>
                        <td>{{ $routine->title ?? '—' }}</td>
                        <td>{{ $routine->class->name ?? '' }}</td>
                        <td>{{ $routine->section->name ?? '-' }}</td>
                        <td>{{ $routine->subject->name ?? '' }}</td>
                        <td>{{ $routine->teacher?->full_name ?? '-' }}</td>
                        <td>{{ $routine->day }}</td>
                        <td>{{ sm_time($routine->start_time) }}</td>
                        <td>{{ sm_time($routine->end_time) }}</td>
                        <td>
                            @if($routine->room)
                                <a href="{{ route('academic.rooms.schedule', $routine->room_id) }}" class="text-decoration-none">
                                    {{ $routine->room->name }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-encodex-edit" type="button" data-bs-toggle="modal" data-bs-target="#editRoutineModal{{ $routine->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('academic.routines.destroy', $routine->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-encodex-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($routines->isEmpty())
        <div class="text-center py-3 text-muted">
            No routines found
        </div>
        @endif
        <div class="mt-2">{{ $routines->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="routineModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span id="modalTitle">Add Class Routine</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="routineForm" action="{{ route('academic.routines') }}">
                @csrf
                <input type="hidden" id="routineId" name="routine_id">
                <input type="hidden" id="methodField" name="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Routine Title') }} <small class="text-muted">(leave as Default, or pick a version — manage the list via "Manage Versions")</small></label>
                        <select name="title" class="form-control form-control-sm">
                            <option value="">{{ sm_t('Default') }}</option>
                            @foreach($titles as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Class *</label>
                        <select id="classField" name="class_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Class') }}</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Section') }}</label>
                        <select id="sectionField" name="section_id" class="form-control form-control-sm">
                            <option value="">{{ sm_t('Select Section') }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Subject *</label>
                        <select id="subjectField" name="subject_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Subject') }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Teacher') }}</label>
                        <select name="teacher_id" class="form-control form-control-sm">
                            <option value="">{{ sm_t('Select Teacher') }}</option>
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Day *</label>
                        <select id="dayField" name="day" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Day') }}</option>
                            <option value="Saturday">{{ sm_t('Saturday') }}</option>
                            <option value="Sunday">{{ sm_t('Sunday') }}</option>
                            <option value="Monday">{{ sm_t('Monday') }}</option>
                            <option value="Tuesday">{{ sm_t('Tuesday') }}</option>
                            <option value="Wednesday">{{ sm_t('Wednesday') }}</option>
                            <option value="Thursday">{{ sm_t('Thursday') }}</option>
                            <option value="Friday">{{ sm_t('Friday') }}</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Start Time *</label>
                            <input type="time" id="startTimeField" name="start_time" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">End Time *</label>
                            <input type="time" id="endTimeField" name="end_time" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Room') }}</label>
                        <select id="roomField" name="room_id" class="form-control form-control-sm">
                            <option value="">{{ sm_t('Select Room') }}</option>
                            @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save"> {{ sm_t('Save Data') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($routines as $routine)
    <div class="modal fade" id="editRoutineModal{{ $routine->id }}" tabindex="-1" data-section-id="{{ $routine->section_id }}" data-subject-id="{{ $routine->subject_id }}">
        <div class="modal-dialog glass-card">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modalTitle">Edit Class Routine</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="routineForm" action="{{ route('academic.routines.update', $routine->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="routineId" name="routine_id">
                    <input type="hidden" id="methodField" name="_method" value="PUT">
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Routine Title') }} <small class="text-muted">(leave as Default, or pick a version — manage the list via "Manage Versions")</small></label>
                            <select name="title" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Default') }}</option>
                                @if($routine->title && !$titles->contains($routine->title))
                                <option value="{{ $routine->title }}" selected>{{ $routine->title }} (inactive)</option>
                                @endif
                                @foreach($titles as $t)
                                <option value="{{ $t }}" {{ $routine->title === $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Class *</label>
                            <select name="class_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Class') }}</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $routine->class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Section') }}</label>
                            <select name="section_id" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Select Section') }}</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Subject *</label>
                            <select name="subject_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Subject') }}</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Teacher') }}</label>
                            <select name="teacher_id" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Select Teacher') }}</option>
                                @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ $routine->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Day *</label>
                            <select id="dayField" name="day" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Day') }}</option>
                                <option value="Saturday" {{ $routine->day == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                                <option value="Sunday" {{ $routine->day == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                                <option value="Monday" {{ $routine->day == 'Monday' ? 'selected' : '' }}>Monday</option>
                                <option value="Tuesday" {{ $routine->day == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                                <option value="Wednesday" {{ $routine->day == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                                <option value="Thursday" {{ $routine->day == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                                <option value="Friday" {{ $routine->day == 'Friday' ? 'selected' : '' }}>Friday</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Start Time *</label>
                                <input type="time" id="startTimeField" name="start_time" class="form-control form-control-sm" value="{{ $routine->start_time }}" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">End Time *</label>
                                <input type="time" id="endTimeField" name="end_time" class="form-control form-control-sm" value="{{ $routine->end_time }}" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Room') }}</label>
                            <select name="room_id" class="form-control form-control-sm">
                                <option value="">{{ sm_t('Select Room') }}</option>
                                @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ $routine->room_id == $room->id ? 'selected' : '' }}>
                                    {{ $room->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                        <button type="submit" class="btn btn-sm btn-encodex-save"> {{ sm_t('Update Data') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@endsection


@push('css')
<style>
    input::-webkit-calendar-picker-indicator {
        filter: invert(0.5); /* রঙ পরিবর্তনের জন্য ইনভার্ট বা স্যাপিয়া ট্রাই করুন */
    }
</style>
@endpush
