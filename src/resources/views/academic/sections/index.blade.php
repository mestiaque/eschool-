@extends('me::master')
@section('title', sm_t('Sections'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#sectionModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> {{ sm_t('Add Section') }}
    </button>
@endpush
@section('content')

<div class="card shadow">
    <div class="card-body">
        <form method="GET" class="glass-search-form mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="@lang('Enter Name')" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="class_id" class="form-control form-control-sm">
                        <option value="">@lang('All Classes')</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
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
                    <a href="{{ route('academic.sections') }}" class="btn btn-sm btn-encodex-clear">
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
                        <th>{{ sm_t('Class') }}</th>
                        <th>{{ sm_t('Name') }}</th>
                        <th>{{ sm_t('Room') }}</th>
                        <th>{{ sm_t('Teacher') }}</th>
                        <th>{{ sm_t('Status') }}</th>
                        <th>{{ sm_t('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sections as $key => $section)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>{{ $section->class->name ?? 'N/A' }}</td>
                        <td>{{ $section->name }}</td>
                        <td>{{ $section->room_number }}</td>
                        <td>{{ $section->teacher_id }}</td>
                        <td class="text-center">
                            @if($section->is_active)
                            <span class="badge bg-success-subtle text-success">{{ sm_t('Active') }}</span>
                            @else
                            <span class="badge bg-danger-subtle text-danger">{{ sm_t('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-encodex-edit" type="button" data-bs-toggle="modal" data-bs-target="#editModal{{ $section->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('academic.sections.destroy', $section->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure?')">
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
        @if($sections->isEmpty())
        <div class="text-center py-3 text-muted">
            {{ sm_t('No sections found') }}
        </div>
        @endif
        <div class="mt-2">{{ $sections->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="sectionModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span id="modalTitle">{{ sm_t('Add Section') }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="sectionForm" action="{{ route('academic.sections.store') }}">
                @csrf
                <input type="hidden" id="sectionId" name="section_id">
                <input type="hidden" id="methodField" name="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Class') }} *</label>
                        <select id="classField" name="class_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Class') }}</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Section Name') }} *</label>
                        <input type="text" id="nameField" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Room Number') }}</label>
                        <input type="text" id="roomField" name="room_number" class="form-control form-control-sm">
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


@foreach ($sections as $section)
    <div class="modal fade" id="editModal{{ $section->id }}" tabindex="-1">
        <div class="modal-dialog glass-card">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modalTitle">{{ sm_t('Edit Section') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="sectionForm" action="{{ route('academic.sections.update', $section->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="sectionId" name="section_id">
                    <input type="hidden" id="methodField" name="_method" value="PUT">
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Class') }} *</label>
                            <select id="classField" name="class_id" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Class') }}</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $section->class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Section Name') }} *</label>
                            <input type="text" id="nameField" name="name" class="form-control form-control-sm" value="{{ $section->name ?? '' }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Room Number') }}</label>
                            <input type="text" id="roomField" name="room_number" class="form-control form-control-sm" value="{{ $section->room_number ?? '' }}">
                        </div>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isActiveField" name="is_active" value="1" {{ isset($section) && $section->is_active ? 'checked' : '' }}>
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
