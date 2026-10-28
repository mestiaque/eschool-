@extends('me::master')
@section('title', sm_t('Leave Types'))

@push('buttons')
<a href="{{ route('teachers.leaves') }}" class="btn btn-sm btn-encodex-list"><i class="fas fa-calendar-minus"></i> {{ sm_t('Teacher Leaves') }}</a>
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#typeModal" onclick="editType(null)"><i class="fas fa-plus"></i> {{ sm_t('Add Leave Type') }}</button>
@endpush

@section('content')
<div class="alert alert-info small py-2">
    {{ sm_t('Days per year is counted in school days (weekly and declared holidays are not counted). 0 means no limit.') }}
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ sm_t('Name') }}</th>
                    <th>{{ sm_t('For') }}</th>
                    <th>{{ sm_t('Days per year') }}</th>
                    <th>{{ sm_t('Used') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $type)
                @php $n = ($used['teacher'][$type->id] ?? 0) + ($used['student'][$type->id] ?? 0); @endphp
                <tr>
                    <td class="fw-semibold">{{ $type->name }}</td>
                    <td>{{ sm_t(\ME\SchoolManagement\Models\LeaveType::APPLIES_TO[$type->applies_to] ?? $type->applies_to) }}</td>
                    <td>{{ $type->days > 0 ? $type->days : sm_t('No limit') }}</td>
                    <td>{{ $n }}</td>
                    <td><span class="badge bg-{{ $bc = $type->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t($type->is_active ? 'Active' : 'Inactive') }}</span></td>
                    <td>
                        <button class="btn btn-sm btn-encodex-edit" onclick="editType({{ json_encode($type->only(['id', 'name', 'days', 'applies_to', 'is_active'])) }})"><i class="fas fa-edit"></i></button>
                        @if($n === 0)
                        <form method="POST" action="{{ route('teachers.leave-types.destroy', $type->id) }}" class="d-inline" onsubmit="return confirm(@json(sm_t('Delete this leave type?'), JSON_HEX_APOS|JSON_HEX_QUOT))">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">{{ sm_t('No leave types yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="typeModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" id="typeForm" action="{{ route('teachers.leave-types.store') }}">
            @csrf
            <span id="typeMethod"></span>
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="typeTitle">{{ sm_t('Add Leave Type') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Name') }} *</label>
                        <input type="text" name="name" id="typeName" class="form-control form-control-sm" maxlength="255" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('For') }} *</label>
                        <select name="applies_to" id="typeFor" class="form-control form-control-sm" required>
                            @foreach(\ME\SchoolManagement\Models\LeaveType::APPLIES_TO as $key => $label)
                            <option value="{{ $key }}">{{ sm_t($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Days per year') }} *</label>
                        <input type="number" name="days" id="typeDays" class="form-control form-control-sm" min="0" max="365" value="0" required>
                        <small class="text-muted">{{ sm_t('0 means no limit.') }}</small>
                    </div>
                    <div class="form-check" id="typeActiveWrap" style="display:none">
                        <input type="checkbox" name="is_active" value="1" id="typeActive" class="form-check-input">
                        <label for="typeActive" class="form-check-label">{{ sm_t('Active') }}</label>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button></div>
            </div>
        </form>
    </div>
</div>
<script>
function editType(t) {
    const f = document.getElementById('typeForm');
    document.getElementById('typeTitle').textContent = t ? @json(sm_t('Edit Leave Type')) : @json(sm_t('Add Leave Type'));
    f.action = t ? @json(route('teachers.leave-types.update', '__ID__')).replace('__ID__', t.id) : @json(route('teachers.leave-types.store'));
    document.getElementById('typeMethod').innerHTML = t ? '<input type="hidden" name="_method" value="PUT">' : '';
    document.getElementById('typeName').value = t ? t.name : '';
    document.getElementById('typeDays').value = t ? t.days : 0;
    document.getElementById('typeFor').value = t ? t.applies_to : 'both';
    document.getElementById('typeActive').checked = t ? !!t.is_active : true;
    document.getElementById('typeActiveWrap').style.display = t ? '' : 'none';
    if (t) bootstrap.Modal.getOrCreateInstance(document.getElementById('typeModal')).show();
}
</script>
@endsection
