@extends('me::master')

@section('title', sm_t('Class Texts'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#classTextModal">
    <i class="fas fa-plus"></i> Add Class Text
</button>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="glass-search-form mb-3 row g-2">
                        <div class="col-md-3">
                            <select name="class_id" class="form-select form-select-sm">
                                <option value="">{{ sm_t('All Classes') }}</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="subject_id" class="form-select form-select-sm">
                                <option value="">{{ sm_t('All Subjects') }}</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Filter') }}</button>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-encodex table-sm table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ sm_t('ID') }}</th>
                                    <th>{{ sm_t('Title') }}</th>
                                    <th>{{ sm_t('Class') }}</th>
                                    <th>{{ sm_t('Subject') }}</th>
                                    <th>{{ sm_t('Author') }}</th>
                                    <th>{{ sm_t('Edition') }}</th>
                                    <th>{{ sm_t('Status') }}</th>
                                    <th>{{ sm_t('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($classTexts as $text)
                                    <tr>
                                        <td>{{ $text->id }}</td>
                                        <td>{{ $text->title }}</td>
                                        <td>{{ $text->class->name ?? 'N/A' }}</td>
                                        <td>{{ $text->subject->name ?? 'N/A' }}</td>
                                        <td>{{ $text->author ?? 'N/A' }}</td>
                                        <td>{{ $text->edition ?? 'N/A' }}</td>
                                        <td>
                                            @if($text->is_active)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-encodex-edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $text->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ route('academic.class-texts.destroy', $text->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-encodex-delete" onclick="return confirm('Delete this class text?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No class texts found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $classTexts->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($classTexts as $text)
<!-- Edit Modal -->
<div class="modal fade" id="editModal{{ $text->id }}" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('academic.class-texts.update', $text->id) }}">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Edit Class Text') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Title') }}</label>
                        <input type="text" name="title" class="form-control form-control-sm" value="{{ $text->title }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Class') }}</label>
                        <select name="class_id" class="form-select form-select-sm" required>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $text->class_id == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Subject') }}</label>
                        <select name="subject_id" class="form-select form-select-sm" required>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ $text->subject_id == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Author') }}</label>
                        <input type="text" name="author" class="form-control form-control-sm" value="{{ $text->author }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Edition') }}</label>
                        <input type="text" name="edition" class="form-control form-control-sm" value="{{ $text->edition }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Content') }}</label>
                        <textarea name="content" class="form-control form-control-sm" rows="2">{{ $text->content }}</textarea>
                    </div>
                    <div class="mb-2 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="active{{ $text->id }}" {{ $text->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="active{{ $text->id }}">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- Create Modal -->
<div class="modal fade" id="classTextModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('academic.class-texts.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Add Class Text') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Title') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Class') }} <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select form-select-sm" required>
                            <option value="">{{ sm_t('Select Class') }}</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Subject') }} <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select form-select-sm" required>
                            <option value="">{{ sm_t('Select Subject') }}</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Author') }}</label>
                        <input type="text" name="author" class="form-control form-control-sm" placeholder="Book author">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Edition') }}</label>
                        <input type="text" name="edition" class="form-control form-control-sm" placeholder="e.g., 1st, 2nd">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Content/Description') }}</label>
                        <textarea name="content" class="form-control form-control-sm" rows="2" placeholder="Brief description"></textarea>
                    </div>
                    <div class="mb-2 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" checked>
                        <label class="form-check-label" for="isActive">{{ sm_t('Active') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
