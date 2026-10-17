@extends('me::master')
@section('title', sm_t('Routine Titles'))
@push('buttons')
    <a href="{{ route('academic.routines') }}" class="btn btn-sm btn-encodex-list">
        <i class="fas fa-arrow-left"></i> Class Routines
    </a>
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#routineTitleModal">
        <i class="fas fa-plus"></i> Add Routine Title
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
                        <th>{{ sm_t('Title') }}</th>
                        <th>{{ sm_t('Status') }}</th>
                        <th>{{ sm_t('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routineTitles as $rt)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $rt->name }}</td>
                        <td class="text-center">
                            <span class="badge {{ $rt->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $rt->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-encodex-edit" type="button"
                                data-bs-toggle="modal" data-bs-target="#editRoutineTitleModal{{ $rt->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('academic.routine-titles.destroy', $rt->id) }}" style="display:inline;"
                                onsubmit="return confirm('Delete this routine title? Existing routines using it will keep their title text.')">
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
        @if($routineTitles->isEmpty())
        <div class="text-center py-3 text-muted">No routine titles found. Add one (e.g. v1, Summer, Winter, Ramadan, Morning) to get started.</div>
        @endif
    </div>
</div>

<!-- Add Routine Title Modal -->
<div class="modal fade" id="routineTitleModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Add Routine Title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('academic.routine-titles.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Title *</label>
                        <input type="text" name="name" class="form-control form-control-sm" required placeholder="e.g. v1, Summer, Winter, Ramadan, Morning">
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="rtIsActiveAdd" checked>
                            <label class="form-check-label" for="rtIsActiveAdd">{{ sm_t('Active') }}</label>
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

<!-- Edit Routine Title Modals -->
@foreach($routineTitles as $rt)
<div class="modal fade" id="editRoutineTitleModal{{ $rt->id }}" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Edit Routine Title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('academic.routine-titles.update', $rt->id) }}">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Title *</label>
                        <input type="text" name="name" class="form-control form-control-sm" required value="{{ $rt->name }}">
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                id="rtIsActiveEdit{{ $rt->id }}" {{ $rt->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="rtIsActiveEdit{{ $rt->id }}">Active</label>
                        </div>
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
@endsection
