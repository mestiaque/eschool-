@extends('me::master')
@section('title', sm_t('Managing Committee'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#committeeModal">
        <i class="fas fa-plus"></i> {{ sm_t('New Committee') }}
    </button>
@endpush

@section('content')


<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th class="text-start">{{ sm_t('Committee Name') }}</th>
                    <th>{{ sm_t('Academic Year') }}</th>
                    <th>{{ sm_t('Formed') }}</th>
                    <th>{{ sm_t('Expires') }}</th>
                    <th>{{ sm_t('Members') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($committees as $i => $committee)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <a href="{{ route('committee.show', $committee->id) }}" class="fw-semibold text-decoration-none">
                            {{ $committee->name }}
                        </a>
                        @if($committee->description)<br><small class="text-muted">{{ Str::limit($committee->description, 60) }}</small>@endif
                    </td>
                    <td class="text-center">{{ $committee->academic_year ?? '—' }}</td>
                    <td class="text-center">{{ sm_date($committee->formed_date) ?? '—' }}</td>
                    <td class="text-center">{{ sm_date($committee->expiry_date) ?? '—' }}</td>
                    <td class="text-center">
                        <a href="{{ route('committee.show', $committee->id) }}" class="badge bg-primary-subtle text-primary text-decoration-none">
                            {{ $committee->members_count }}
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $bc = $committee->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">
                            {{ sm_t($committee->is_active ? 'Active' : 'Inactive') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('committee.show', $committee->id) }}" class="btn btn-sm btn-encodex-show" title="Members">
                            <i class="fas fa-users"></i>
                        </a>
                        <button class="btn btn-sm btn-encodex-edit"
                            onclick="openEditCommittee({{ $committee->id }}, {{ json_encode($committee) }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('committee.destroy', $committee->id) }}" class="d-inline"
                            onsubmit="return confirm('Delete this committee?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-3">{{ sm_t('No committees found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $committees->links('pagination::bootstrap-5') }}</div>

{{-- Add Modal --}}
<div class="modal fade" id="committeeModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('New Committee') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('committee.store') }}">
                @csrf
                @include('school-management::committee._form')
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editCommitteeModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('Edit Committee') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editCommitteeForm">
                @csrf @method('PUT')
                @include('school-management::committee._form', ['edit' => true])
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditCommittee(id, c) {
    const form = document.getElementById('editCommitteeForm');
    form.action = '/committee/' + id;
    const f = (n, v) => { const el = form.querySelector('[name="'+n+'"]'); if(el) el.value = v ?? ''; };
    f('name', c.name); f('academic_year', c.academic_year);
    f('formed_date', c.formed_date?.substring(0,10));
    f('expiry_date', c.expiry_date?.substring(0,10));
    f('description', c.description);
    const isActive = form.querySelector('[name="is_active"]');
    if (isActive) isActive.checked = !!c.is_active;
    new bootstrap.Modal(document.getElementById('editCommitteeModal')).show();
}
</script>
@endsection
