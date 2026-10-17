@extends('me::master')
@section('title', $committee->name)
@push('buttons')
    <a href="{{ route('committee.index') }}" class="btn btn-sm btn-encodex-list">
        <i class="fas fa-arrow-left"></i> All Committees
    </a>
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#addMemberModal">
        <i class="fas fa-user-plus"></i> Add Member
    </button>
    <a href="{{ route('committee.show.print', $committee->id) }}" target="_blank" class="btn btn-sm btn-encodex-print no-print">
        <i class="fas fa-print"></i> Print
    </a>
@endpush

@section('content')


{{-- Committee Info --}}
<div class="card shadow mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h5 class="mb-1">{{ $committee->name }}</h5>
                <div class="text-muted small">
                    @if($committee->academic_year)<span class="me-3"><i class="fas fa-calendar me-1"></i>{{ $committee->academic_year }}</span>@endif
                    @if($committee->formed_date)<span class="me-3"><i class="fas fa-calendar-plus me-1"></i>Formed: {{ sm_date($committee->formed_date) }}</span>@endif
                    @if($committee->expiry_date)<span><i class="fas fa-calendar-minus me-1"></i>Expires: {{ sm_date($committee->expiry_date) }}</span>@endif
                </div>
                @if($committee->description)<p class="mb-0 mt-1 small">{{ $committee->description }}</p>@endif
            </div>
            <div class="col-md-5 text-end">
                <span class="badge bg-{{ $bc = $committee->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }} me-2">
                    {{ $committee->is_active ? 'Active' : 'Inactive' }}
                </span>
                <strong>{{ $committee->members->count() }}</strong> Members
            </div>
        </div>
    </div>
</div>

{{-- Members Table --}}
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0"><i class="fas fa-users me-1"></i> Committee Members</h6>
        <small class="text-muted">Drag rows to reorder</small>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-sm table-encodex mb-0" id="membersTable">
            <thead class="text-center">
                <tr>
                    <th style="width:40px"><i class="fas fa-grip-vertical text-muted"></i></th>
                    <th style="width:40px">#</th>
                    <th>{{ sm_t('Photo') }}</th>
                    <th class="text-start">{{ sm_t('Name') }}</th>
                    <th>Role / পদবি</th>
                    <th>{{ sm_t('Profession') }}</th>
                    <th>{{ sm_t('Phone') }}</th>
                    <th class="text-start">{{ sm_t('Address') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody id="sortableMembers">
                @forelse($committee->members as $i => $member)
                <tr data-id="{{ $member->id }}">
                    <td class="text-center drag-handle" style="cursor:grab"><i class="fas fa-grip-vertical text-muted"></i></td>
                    <td class="text-center serial-cell">{{ $i + 1 }}</td>
                    <td class="text-center" style="width:48px">
                        @if($member->photo)
                            <img src="{{ Storage::url($member->photo) }}" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                        @else
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white" style="width:36px;height:36px;font-size:13px">
                                {{ strtoupper(substr($member->name,0,1)) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $member->name }}</strong>
                        @if($member->email)<br><small class="text-muted">{{ $member->email }}</small>@endif
                    </td>
                    <td class="text-center">
                        @php $roleLabel = $roles[$member->role] ?? $member->role; @endphp
                        @php $roleBadge = in_array($member->role, ['chairperson','vice_chairperson','secretary']) ? 'primary' : 'info'; @endphp
                        <span class="badge bg-{{ $bc = $roleBadge }}-subtle text-{{ $bc }}">
                            {{ $roleLabel }}
                        </span>
                    </td>
                    <td class="text-center">{{ $member->profession ?? '—' }}</td>
                    <td class="text-center">{{ $member->phone ?? '—' }}</td>
                    <td>{{ $member->address ? Str::limit($member->address, 40) : '—' }}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-encodex-edit"
                            onclick="openEditMember({{ $member->id }}, {{ json_encode($member) }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST"
                            action="{{ route('committee.members.destroy', [$committee->id, $member->id]) }}"
                            class="d-inline" onsubmit="return confirm('Remove this member?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-3">No members added yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Member Modal --}}
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('Add Member') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('committee.members.store', $committee->id) }}" enctype="multipart/form-data">
                @csrf
                @include('school-management::committee._member_form')
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Add Member') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Member Modal --}}
<div class="modal fade" id="editMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('Edit Member') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editMemberForm" enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('school-management::committee._member_form', ['edit' => true])
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function openEditMember(id, m) {
    const form = document.getElementById('editMemberForm');
    form.action = '{{ route("committee.members.update", [$committee->id, "__ID__"]) }}'.replace('__ID__', id);
    const f = (n, v) => { const el = form.querySelector('[name="'+n+'"]'); if(el) el.value = v ?? ''; };
    f('name', m.name); f('role', m.role); f('serial', m.serial);
    f('phone', m.phone); f('email', m.email); f('address', m.address);
    f('profession', m.profession); f('notes', m.notes);
    new bootstrap.Modal(document.getElementById('editMemberModal')).show();
}

// Drag to reorder
const tbody = document.getElementById('sortableMembers');
if (tbody) {
    Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function () {
            const ids = [...tbody.querySelectorAll('tr[data-id]')].map(r => r.dataset.id);
            // Update serial numbers visually
            tbody.querySelectorAll('.serial-cell').forEach((cell, i) => cell.textContent = i + 1);
            // Send to server
            fetch('{{ route("committee.reorder", $committee->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ order: ids })
            });
        }
    });
}
</script>

<style>
@media print {
    .no-print, .btn, form { display: none !important; }
    .drag-handle, th:first-child, th:last-child, td:first-child, td:last-child { display: none !important; }
}
</style>
@endsection
