@extends('me::master')
@section('title', sm_t('Subject Groups'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#groupModal">
        <i class="fas fa-plus"></i> {{ sm_t('Add Group') }}
    </button>
@endpush
@section('content')


<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-striped table-encodex table-sm mb-0">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Group Name') }}</th>
                    <th>{{ sm_t('Description') }}</th>
                    <th>{{ sm_t('Subjects') }}</th>
                    <th>{{ sm_t('Students') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $i => $group)
                <tr>
                    <td class="text-center">{{ $groups->firstItem() + $i }}</td>
                    <td><strong>{{ $group->name }}</strong></td>
                    <td>{{ $group->description ?? '—' }}</td>
                    <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $group->subjects_count }}</span></td>
                    <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $group->students_count }}</span></td>
                    <td class="text-center">
                        <span class="badge bg-{{ $bc = $group->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">
                            {{ sm_t($group->is_active ? 'Active' : 'Inactive') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-encodex-edit"
                            onclick="editGroup({{ $group->id }}, '{{ addslashes($group->name) }}', '{{ addslashes($group->description ?? '') }}', {{ $group->is_active ? 1 : 0 }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('academic.subject-groups.destroy', $group->id) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('Delete this group? Students and subjects using it will be unlinked.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">{{ sm_t('No subject groups found. Add one to get started.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $groups->links('pagination::bootstrap-5') }}</div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="groupModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalTitle">{{ sm_t('Add Subject Group') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="groupForm" action="{{ route('academic.subject-groups.store') }}">
                @csrf
                <input type="hidden" name="_method" id="groupMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Group Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="groupName" class="form-control form-control-sm"
                               placeholder="{{ sm_t('e.g. Science, Arts, Commerce') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea name="description" id="groupDesc" class="form-control form-control-sm" rows="2"
                                  placeholder="{{ sm_t('Optional description') }}"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="groupActive" value="1" checked>
                        <label class="form-check-label">{{ sm_t('Active') }}</label>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editGroup(id, name, desc, isActive) {
    document.getElementById('groupModalTitle').textContent = 'Edit Subject Group';
    document.getElementById('groupForm').action = '/academic/subject-groups/' + id;
    document.getElementById('groupMethod').value = 'PUT';
    document.getElementById('groupName').value = name;
    document.getElementById('groupDesc').value = desc;
    document.getElementById('groupActive').checked = isActive == 1;
    new bootstrap.Modal(document.getElementById('groupModal')).show();
}
</script>
@endsection
