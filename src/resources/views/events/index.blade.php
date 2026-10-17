@extends('me::master')
@section('title', sm_t('Events'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#eventModal">
    <i class="fas fa-plus"></i> Add Event
</button>
@endpush

@section('content')
<div class="card">
    <div class="card-body p-0">
        <table class="table table-encodex table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ sm_t('Title') }}</th>
                    <th>{{ sm_t('Start Date') }}</th>
                    <th>{{ sm_t('End Date') }}</th>
                    <th>{{ sm_t('Location') }}</th>
                    <th>{{ sm_t('For') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                <tr>
                    <td>{{ $event->title }}</td>
                    <td>{{ $event->start_date?->format('Y-m-d') }}</td>
                    <td>{{ $event->end_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $event->location ?? '-' }}</td>
                    <td>{{ ucfirst($event->for) }}</td>
                    <td>
                        @if($event->is_active)
                        <span class="badge bg-success-subtle text-success">Active</span>
                        @else
                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-encodex-edit" data-bs-toggle="modal" data-bs-target="#editEventModal{{ $event->id }}"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="{{ route('events.destroy', $event->id) }}" class="d-inline" onsubmit="return confirm('Delete this event?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $events->links('pagination::bootstrap-5') }}</div>

@foreach($events as $event)
<!-- Edit Modal -->
<div class="modal fade" id="editEventModal{{ $event->id }}" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Edit Event') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('events.update', $event->id) }}">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control form-control-sm" value="{{ $event->title }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2">{{ $event->description }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $event->start_date?->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('End Date') }}</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $event->end_date?->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Location') }}</label>
                        <input type="text" name="location" class="form-control form-control-sm" value="{{ $event->location }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('For') }}</label>
                        <select name="for" class="form-control form-control-sm">
                            <option value="all" {{ $event->for == 'all' ? 'selected' : '' }}>All</option>
                            <option value="student" {{ $event->for == 'student' ? 'selected' : '' }}>Students</option>
                            <option value="teacher" {{ $event->for == 'teacher' ? 'selected' : '' }}>Teachers</option>
                        </select>
                    </div>
                    <div class="mb-2 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="activeEvent{{ $event->id }}" value="1" {{ $event->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="activeEvent{{ $event->id }}">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Create Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Add Event') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('events.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('End Date') }}</label>
                            <input type="date" name="end_date" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Location') }}</label>
                        <input type="text" name="location" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('For') }}</label>
                        <select name="for" class="form-control form-control-sm">
                            <option value="all">{{ sm_t('All') }}</option>
                            <option value="student">{{ sm_t('Students') }}</option>
                            <option value="teacher">{{ sm_t('Teachers') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
