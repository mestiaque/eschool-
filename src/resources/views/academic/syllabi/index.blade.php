@extends('me::master')
@section('title', sm_t('Syllabus'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#syllabusModal">
        <i class="fas fa-plus"></i> Add Syllabus
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
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="academic_year_id" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Years') }}</option>
                    @foreach($academicYears as $y)
                    <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md">
                <button type="submit" class="btn btn-sm btn-encodex-search"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('academic.syllabi') }}" class="btn btn-sm btn-encodex-clear"><i class="fas fa-eraser"></i> Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-striped table-encodex table-sm mb-0">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Subject') }}</th>
                    <th>{{ sm_t('Academic Year') }}</th>
                    <th>{{ sm_t('Title') }}</th>
                    <th>{{ sm_t('File') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($syllabi as $s)
                <tr>
                    <td>{{ $syllabi->firstItem() + $loop->index }}</td>
                    <td>{{ $s->classe?->name ?? '-' }}</td>
                    <td>{{ $s->subject?->name ?? '-' }}</td>
                    <td>{{ $s->academicYear?->name ?? '-' }}</td>
                    <td>{{ $s->title }}</td>
                    <td class="text-center">
                        @if($s->file)
                            <a href="{{ route('academic.syllabi.download', $s->id) }}" class="btn btn-sm btn-encodex" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $bc = $s->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">
                            {{ $s->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{-- Preview --}}
                        <button class="btn btn-sm btn-encodex-show" type="button"
                            data-bs-toggle="modal" data-bs-target="#previewModal{{ $s->id }}" title="Preview">
                            <i class="fas fa-eye"></i>
                        </button>
                        {{-- Edit --}}
                        <button class="btn btn-sm btn-encodex-edit" type="button"
                            data-bs-toggle="modal" data-bs-target="#editSyllabusModal{{ $s->id }}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        {{-- Delete --}}
                        <form method="POST" action="{{ route('academic.syllabi.destroy', $s->id) }}" class="d-inline"
                            onsubmit="return confirm('Delete this syllabus?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-3">No syllabus records found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $syllabi->links('pagination::bootstrap-5') }}</div>
</div>

{{-- Add Syllabus Modal --}}
<div class="modal fade" id="syllabusModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('academic.syllabi.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Add Syllabus') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Class *</label>
                        <select name="class_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Class') }}</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Subject *</label>
                        <select name="subject_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Subject') }}</option>
                            @foreach($subjects as $sub)<option value="{{ $sub->id }}">{{ $sub->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Academic Year *</label>
                        <select name="academic_year_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Year') }}</option>
                            @foreach($academicYears as $y)<option value="{{ $y->id }}">{{ $y->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea name="description" class="form-control form-control-sm" rows="4"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('File') }} <small class="text-muted">(PDF, DOC, DOCX — max 10MB)</small></label>
                        <input type="file" name="file" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addIsActive" checked>
                            <label class="form-check-label" for="addIsActive">{{ sm_t('Active') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save Syllabus') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Preview + Edit Modals --}}
@foreach($syllabi as $s)

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal{{ $s->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-book me-2"></i>{{ $s->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Class</small>
                        <strong>{{ $s->classe?->name ?? '—' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Subject</small>
                        <strong>{{ $s->subject?->name ?? '—' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Academic Year</small>
                        <strong>{{ $s->academicYear?->name ?? '—' }}</strong>
                    </div>
                </div>
                @if($s->description)
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Description / Content</small>
                    <div class="border rounded p-3 bg-light" style="white-space: pre-wrap;">{{ $s->description }}</div>
                </div>
                @else
                <p class="text-muted fst-italic">No description added.</p>
                @endif
                @if($s->file)
                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('academic.syllabi.download', $s->id) }}" class="btn btn-sm btn-encodex">
                        <i class="fas fa-download me-1"></i> Download File
                    </a>
                    @if(str_ends_with(strtolower($s->file), '.pdf'))
                    <a href="{{ asset('storage/' . $s->file) }}" target="_blank" class="btn btn-sm btn-encodex-show">
                        <i class="fas fa-external-link-alt me-1"></i> Open PDF
                    </a>
                    @endif
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Close') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editSyllabusModal{{ $s->id }}" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('academic.syllabi.update', $s->id) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Edit Syllabus') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Class *</label>
                        <select name="class_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Class') }}</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $s->class_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Subject *</label>
                        <select name="subject_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Subject') }}</option>
                            @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ $s->subject_id == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Academic Year *</label>
                        <select name="academic_year_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Year') }}</option>
                            @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ $s->academic_year_id == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control form-control-sm" required value="{{ $s->title }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea name="description" class="form-control form-control-sm" rows="4">{{ $s->description }}</textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">
                            {{ sm_t('File') }} <small class="text-muted">(PDF, DOC, DOCX — max 10MB)</small>
                        </label>
                        @if($s->file)
                        <div class="mb-1 d-flex align-items-center gap-2">
                            <span class="badge bg-info-subtle text-info"><i class="fas fa-file me-1"></i>{{ basename($s->file) }}</span>
                            <a href="{{ route('academic.syllabi.download', $s->id) }}" class="small text-decoration-none">
                                <i class="fas fa-download"></i> Download current
                            </a>
                        </div>
                        @endif
                        <input type="file" name="file" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
                        @if($s->file)
                        <small class="text-muted">Leave empty to keep existing file</small>
                        @endif
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                id="editIsActive{{ $s->id }}" {{ $s->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="editIsActive{{ $s->id }}">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update Syllabus') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endforeach

@endsection
