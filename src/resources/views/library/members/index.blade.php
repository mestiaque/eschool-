@extends('me::master')
@section('title', sm_t('Library Members'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#memberModal"><i class="fas fa-plus"></i> {{ sm_t('Add Member') }}</button>
@endpush

@section('content')

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-3">
                <select name="member_type" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Types') }}</option>
                    <option value="student" {{ request('member_type') == 'student' ? 'selected' : '' }}>{{ sm_t('Student') }}</option>
                    <option value="teacher" {{ request('member_type') == 'teacher' ? 'selected' : '' }}>{{ sm_t('Teacher') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_active" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Status') }}</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ sm_t('Active') }}</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ sm_t('Inactive') }}</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Filter') }}</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>{{ sm_t('Type') }}</th><th>{{ sm_t('Member') }}</th><th>{{ sm_t('Membership Date') }}</th><th>{{ sm_t('Expire Date') }}</th><th>{{ sm_t('Status') }}</th><th>{{ sm_t('Action') }}</th></tr>
            </thead>
            <tbody>
                @forelse($members as $i => $m)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ sm_t(ucfirst($m->member_type)) }}</td>
                    <td>
                        @if($m->member_type === 'student')
                        {{ $m->student?->full_name }}
                        @else
                        {{ $m->teacher?->full_name }}
                        @endif
                    </td>
                    <td>{{ sm_date($m->membership_date) }}</td>
                    <td>{{ sm_date($m->expire_date) }}</td>
                    <td><span class="badge bg-{{ $bc = $m->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t($m->is_active ? 'Active' : 'Expired') }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('library.members.destroy', $m->id) }}" class="d-inline" onsubmit="return confirm('Remove member?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-3">{{ sm_t('No library members') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $members->links('pagination::bootstrap-5') }}</div>
</div>

<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('library.members.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ sm_t('Add Library Member') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Member Type') }} *</label>
                        <select name="member_type" id="memberType" class="form-control form-control-sm" required onchange="toggleMember(this.value)">
                            <option value="student">{{ sm_t('Student') }}</option>
                            <option value="teacher">{{ sm_t('Teacher') }}</option>
                        </select>
                    </div>
                    <div class="mb-2" id="studentDiv">
                        <label class="form-label">{{ sm_t('Student') }} *</label>
                        @include('school-management::partials.student-select', ['name' => 'student_id', 'placeholder' => 'Select Student'])
                    </div>
                    <div class="mb-2" id="teacherDiv" style="display:none">
                        <label class="form-label">{{ sm_t('Teacher') }} *</label>
                        <select name="teacher_id" class="form-control form-control-sm">
                            <option value="">{{ sm_t('Select Teacher') }}</option>
                            @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->full_name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label">{{ sm_t('Membership Date') }}</label><input type="date" name="membership_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}"></div>
                        <div class="col"><label class="form-label">{{ sm_t('Expire Date') }}</label><input type="date" name="expire_date" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+1 year')) }}"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Add Member') }}</button></div>
            </div>
        </form>
    </div>
</div>
<script>
function toggleMember(type) {
    document.getElementById('studentDiv').style.display = type === 'student' ? 'block' : 'none';
    document.getElementById('teacherDiv').style.display = type === 'teacher' ? 'block' : 'none';
}
</script>
@endsection
