@extends('me::master')
@section('title', sm_t('Classes'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#classModal">
        <i class="fas fa-plus"></i> Add Class
    </button>
@endpush
@section('content')

<div class="card shadow">
    <div class="card-body">
        <form method="GET" class="glass-search-form mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="@lang('Enter Class Name')" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">@lang('All Status')</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>@lang('Active')</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>@lang('Inactive')</option>
                    </select>
                </div>
                <div class="col-md">
                    <button type="submit" class="btn btn-sm btn-encodex-search">
                        <i class="fas fa-search"></i> @lang('Search')
                    </button>
                    <a href="{{ route('academic.classes') }}" class="btn btn-sm btn-encodex-clear">
                        <i class="fas fa-eraser"></i> @lang('Reset')
                    </a>
                </div>
            </div>
        </form>

        <div class="responsive">
            <table class="table table-bordered table-hover table-striped table-encodex table-sm">
                <thead class="text-center">
                    <tr>
                        <th>{{ sm_t('SL') }}</th>
                        <th>{{ sm_t('Name') }}</th>
                        <th>{{ sm_t('Numeric Name') }}</th>
                        <th>{{ sm_t('Sections') }}</th>
                        <th>{{ sm_t('Status') }}</th>
                        <th>{{ sm_t('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $key => $class)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>{{ $class->name }}</td>
                        <td class="text-center">{{ $class->numeric_name }}</td>
                        <td class="text-center">{{ $class->sections->count() }}</td>
                        <td class="text-center">
                            @if($class->is_active)
                            <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('academic.classes.routine', $class->id) }}" class="btn btn-sm btn-encodex-show" title="View Routine">
                                <i class="fas fa-calendar-alt"></i>
                            </a>
                            <button class="btn btn-sm btn-encodex-edit" data-bs-toggle="modal" data-bs-target="#editClassModal{{ $class->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('academic.classes.destroy', $class->id) }}" class="d-inline" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-encodex-delete" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($classes->isEmpty())
        <div class="text-center py-3 text-muted">
            No classes found
        </div>
        @endif
        <div class="mt-2">{{ $classes->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<!-- Class Modal -->
<div class="modal fade" id="classModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span id="modalTitle">Add Class</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="classForm" action="{{ route('academic.classes') }}">
                @csrf
                <input type="hidden" id="classId" name="class_id">
                <input type="hidden" id="methodField" name="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Class Name *</label>
                        <input type="text" id="nameField" name="name" class="form-control form-control-sm" placeholder="e.g. Class One" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Numeric Name *</label>
                        <input type="number" id="numericField" name="numeric_name" class="form-control form-control-sm" placeholder="e.g. 1" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea id="descField" name="description" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="isActiveField" name="is_active" value="1" checked>
                            <label class="form-check-label">{{ sm_t('Active') }}</label>
                        </div>
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

<!-- Edit Class Modals -->
@foreach($classes as $class)
<div class="modal fade" id="editClassModal{{ $class->id }}" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span id="modalTitle">Edit Class</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="classForm" action="{{ route('academic.classes.update', $class->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" id="classId" name="class_id">
                <input type="hidden" id="methodField" name="_method" value="PUT">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Class Name *</label>
                        <input type="text" id="nameField" name="name" class="form-control form-control-sm" value="{{ $class->name }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Numeric Name *</label>
                        <input type="number" id="numericField" name="numeric_name" class="form-control form-control-sm" value="{{ $class->numeric_name }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea id="descField" name="description" class="form-control form-control-sm" rows="2">{{ $class->description }}</textarea>
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="isActiveField" name="is_active" value="1" {{ $class->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">{{ sm_t('Active') }}</label>
                        </div>
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
