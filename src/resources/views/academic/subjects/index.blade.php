@extends('me::master')
@section('title', sm_t('Subjects'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#subjectModal">
        <i class="fas fa-plus"></i> {{ sm_t('Add Subject') }}
    </button>
@endpush
@section('content')

    <div class="card shadow">
        <div class="card-body">
            <form method="GET" class="glass-search-form mb-3">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ sm_t('Enter Name or Code') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="class_id" class="form-control form-control-sm">
                            <option value="">{{ sm_t('All Classes') }}</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control form-control-sm">
                            <option value="">{{ sm_t('All Status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ sm_t('Active') }}</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ sm_t('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md">
                        <button type="submit" class="btn btn-sm btn-encodex-search">
                            <i class="fas fa-search"></i> {{ sm_t('Search') }}
                        </button>
                        <a href="{{ route('academic.subjects') }}" class="btn btn-sm btn-encodex-clear">
                            <i class="fas fa-eraser"></i> {{ sm_t('Reset') }}
                        </a>
                    </div>
                </div>
            </form>

            <div class="responsive">
                <table class="table table-bordered table-hover table-striped table-encodex table-sm">
                    <thead class="text-center">
                        <tr>
                            <th>{{ sm_t('Name') }}</th>
                            <th>{{ sm_t('Code') }}</th>
                            <th>{{ sm_t('Class') }}</th>
                            <th>{{ sm_t('Type') }}</th>
                            <th>{{ sm_t('Group') }}</th>
                            <th>{{ sm_t('Pass Mark') }}</th>
                            <th>{{ sm_t('Full Mark') }}</th>
                            <th>{{ sm_t('Status') }}</th>
                            <th>{{ sm_t('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                        @php
                            $typeColors = ['compulsory' => 'primary', 'group' => 'warning', 'optional' => 'info'];
                        @endphp
                        <tr>
                            <td>{{ $subject->name }}</td>
                            <td>{{ $subject->code ?? '-' }}</td>
                            <td>{{ $subject->class->name ?? '' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $bc = $typeColors[$subject->type] ?? 'secondary' }}-subtle text-{{ $bc }}">
                                    {{ sm_t(ucfirst($subject->type)) }}
                                </span>
                            </td>
                            <td class="text-center">{{ $subject->subjectGroup?->name ?? '—' }}</td>
                            <td class="text-center">{{ $subject->pass_mark }}</td>
                            <td class="text-center">{{ $subject->full_mark }}</td>
                            <td class="text-center">
                                @if($subject->is_active)
                                <span class="badge bg-success-subtle text-success">{{ sm_t('Active') }}</span>
                                @else
                                <span class="badge bg-danger-subtle text-danger">{{ sm_t('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-encodex-edit" data-bs-toggle="modal" data-bs-target="#editSubjectModal{{ $subject->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('academic.subjects.destroy', $subject->id) }}" method="POST" class="d-inline" style="display:inline;">
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
            @if($subjects->isEmpty())
            <div class="text-center py-3 text-muted">
                {{ sm_t('No subjects found') }}
            </div>
            @endif
            <div class="mt-2">{{ $subjects->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="subjectModal" tabindex="-1">
        <div class="modal-dialog glass-card">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modalTitle">{{ sm_t('Add Subject') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="subjectForm" action="{{ route('academic.subjects.store') }}">
                    @csrf
                    <input type="hidden" id="methodField" name="_method" value="POST">
                    <div class="modal-body">
                        <div class="row g-2 mb-2">
                            <div class="col-md-8">
                                <label class="form-label">{{ sm_t('Subject Name') }} *</label>
                                <input type="text" id="nameField" name="name" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ sm_t('Subject Code') }}</label>
                                <input type="text" id="codeField" name="code" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label">{{ sm_t('Class') }} *</label>
                                <select id="classField" name="class_id" class="form-control form-control-sm" required>
                                    <option value="">{{ sm_t('Select Class') }}</option>
                                    @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ sm_t('Type') }} *</label>
                                <select id="typeField" name="type" class="form-control form-control-sm" onchange="toggleGroupField('groupRow', this.value)">
                                    <option value="compulsory">Compulsory — সবার জন্য</option>
                                    <option value="group">Group Subject — নির্দিষ্ট Group এর জন্য</option>
                                    <option value="optional">Optional / 4th Subject — student নিজে বেছে নেবে</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-2" id="groupRow" style="display:none">
                            <label class="form-label">{{ sm_t('Group') }}</label>
                            <select id="groupField" name="group_id" class="form-control form-control-sm">
                                <option value="">— খালি রাখলে সব Group এর student নিতে পারবে —</option>
                                @foreach($subjectGroups as $sg)
                                <option value="{{ $sg->id }}">{{ $sg->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Group Subject: group বাধ্যতামূলক। Optional: group বেছে দিলে শুধু সেই group এর student নিতে পারবে; খালি রাখলে সব group।
                            </small>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label">{{ sm_t('Pass Mark') }} *</label>
                                <input type="number" id="passMarkField" name="pass_mark" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ sm_t('Full Mark') }} *</label>
                                <input type="number" id="fullMarkField" name="full_mark" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="isActiveField" name="is_active" value="1" checked>
                            <label class="form-check-label">{{ sm_t('Active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                        <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save Data') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    @foreach($subjects as $subject)
    <div class="modal fade" id="editSubjectModal{{ $subject->id }}" tabindex="-1">
        <div class="modal-dialog glass-card">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modalTitle">{{ sm_t('Edit Subject') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('academic.subjects.update', $subject->id) }}">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="row g-2 mb-2">
                            <div class="col-md-8">
                                <label class="form-label">{{ sm_t('Subject Name') }} *</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $subject->name }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ sm_t('Subject Code') }}</label>
                                <input type="text" name="code" class="form-control form-control-sm" value="{{ $subject->code }}">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label">{{ sm_t('Class') }} *</label>
                                <select name="class_id" class="form-control form-control-sm" required>
                                    <option value="">{{ sm_t('Select Class') }}</option>
                                    @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ $subject->class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ sm_t('Type') }} *</label>
                                <select name="type" class="form-control form-control-sm edit-type-select"
                                        data-target="editGroupRow{{ $subject->id }}"
                                        onchange="toggleGroupField('editGroupRow{{ $subject->id }}', this.value)">
                                    @foreach(['compulsory' => 'Compulsory — সবার জন্য', 'group' => 'Group Subject — নির্দিষ্ট Group', 'optional' => 'Optional / 4th Subject — student বেছে নেবে'] as $val => $label)
                                    <option value="{{ $val }}" {{ $subject->type === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2" id="editGroupRow{{ $subject->id }}"
                             style="{{ in_array($subject->type, ['group','optional']) ? '' : 'display:none' }}">
                            <label class="form-label">{{ sm_t('Group') }}</label>
                            <select name="group_id" class="form-control form-control-sm">
                                <option value="">— খালি রাখলে সব Group এর student নিতে পারবে —</option>
                                @foreach($subjectGroups as $sg)
                                <option value="{{ $sg->id }}" {{ $subject->group_id == $sg->id ? 'selected' : '' }}>{{ $sg->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Optional: group দিলে শুধু সেই group; খালি রাখলে সব group।</small>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label">{{ sm_t('Pass Mark') }} *</label>
                                <input type="number" name="pass_mark" class="form-control form-control-sm" value="{{ $subject->pass_mark }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ sm_t('Full Mark') }} *</label>
                                <input type="number" name="full_mark" class="form-control form-control-sm" value="{{ $subject->full_mark }}" required>
                            </div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $subject->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">{{ sm_t('Active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                        <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update Data') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

<script>
function toggleGroupField(rowId, type) {
    const row = document.getElementById(rowId);
    if (row) row.style.display = (type === 'group' || type === 'optional') ? '' : 'none';
}
</script>
@endsection
