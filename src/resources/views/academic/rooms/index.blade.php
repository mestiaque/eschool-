@extends('me::master')
@section('title', sm_t('Rooms'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#roomModal">
        <i class="fas fa-plus"></i> Add Room
    </button>
@endpush

@section('content')
<div class="card shadow">
    <div class="card-body">
        <div class="responsive">
            <table class="table table-bordered table-hover table-striped table-encodex table-sm">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ sm_t('Room Name') }}</th>
                        <th>{{ sm_t('Building') }}</th>
                        <th>{{ sm_t('Capacity') }}</th>
                        <th>{{ sm_t('Schedules') }}</th>
                        <th>{{ sm_t('Status') }}</th>
                        <th>{{ sm_t('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rooms as $room)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->building ?? '-' }}</td>
                        <td>{{ $room->capacity ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('academic.rooms.schedule', $room->id) }}" class="badge bg-info-subtle text-info">
                                {{ $room->routines_count }} schedules
                            </a>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $room->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $room->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-encodex-edit" type="button"
                                data-bs-toggle="modal" data-bs-target="#editRoomModal{{ $room->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('academic.rooms.destroy', $room->id) }}" style="display:inline;"
                                onsubmit="return confirm('Delete this room?')">
                                @csrf @method('DELETE')
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
        @if($rooms->isEmpty())
        <div class="text-center py-3 text-muted">No rooms found. Add a room to get started.</div>
        @endif
    </div>
</div>

<!-- Add Room Modal -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Add Room') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('academic.rooms.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Room Name *</label>
                        <input type="text" name="name" class="form-control form-control-sm" required placeholder="e.g. Room 101">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Building') }}</label>
                        <input type="text" name="building" class="form-control form-control-sm" placeholder="e.g. Main Building">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Capacity') }}</label>
                        <input type="number" name="capacity" class="form-control form-control-sm" min="1" placeholder="e.g. 40">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveAdd" checked>
                            <label class="form-check-label" for="isActiveAdd">{{ sm_t('Active') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save Room') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Room Modals -->
@foreach($rooms as $room)
<div class="modal fade" id="editRoomModal{{ $room->id }}" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Edit Room') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('academic.rooms.update', $room->id) }}">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Room Name *</label>
                        <input type="text" name="name" class="form-control form-control-sm" required value="{{ $room->name }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Building') }}</label>
                        <input type="text" name="building" class="form-control form-control-sm" value="{{ $room->building }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Capacity') }}</label>
                        <input type="number" name="capacity" class="form-control form-control-sm" min="1" value="{{ $room->capacity }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2">{{ $room->description }}</textarea>
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                id="isActiveEdit{{ $room->id }}" {{ $room->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActiveEdit{{ $room->id }}">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update Room') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
