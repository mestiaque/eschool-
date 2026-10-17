@extends('me::master')
@section('title', sm_t('Student Early Exit'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#earlyExitModal"><i class="fas fa-plus"></i> Add Early Exit</button>
@endpush

@section('content')

<div class="card">
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Student') }}</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Date') }}</th>
                    <th>{{ sm_t('Time') }}</th>
                    <th>{{ sm_t('Picked Up By') }}</th>
                    <th>{{ sm_t('Reason') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exits as $i => $exit)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $exit->student?->full_name }}</td>
                    <td>{{ $exit->student?->classe?->name }}{{ $exit->student?->section ? ' / ' . $exit->student->section->name : '' }}</td>
                    <td>{{ sm_date($exit->exit_date) }}</td>
                    <td>{{ \Carbon\Carbon::parse($exit->exit_time)->format('h:i A') }}</td>
                    <td>{{ $exit->picked_up_by ?? '—' }}</td>
                    <td><small>{{ Str::limit($exit->reason, 40) }}</small></td>
                    <td>
                        @php $c=['approved'=>'success','rejected'=>'danger','pending'=>'warning']; @endphp
                        <span class="badge bg-{{ $bc = $c[$exit->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($exit->status) }}</span>
                    </td>
                    <td>
                        @if($exit->status === 'pending')
                        <form method="POST" action="{{ route('students.early-exits.approve', $exit->id) }}" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-encodex-active">{{ sm_t('Approve') }}</button></form>
                        <form method="POST" action="{{ route('students.early-exits.reject', $exit->id) }}" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-encodex-deactive">{{ sm_t('Reject') }}</button></form>
                        @endif
                        <form method="POST" action="{{ route('students.early-exits.destroy', $exit->id) }}" class="d-inline" onsubmit="return confirm('Delete this record?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button></form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-3">No early exit records</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-2">{{ $exits->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<div class="modal fade" id="earlyExitModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('students.early-exits.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ sm_t('Add Early Exit') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Student *</label>
                        @include('school-management::partials.student-select', ['name' => 'student_id', 'required' => true, 'placeholder' => 'Select Student'])
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label">Date *</label><input type="date" name="exit_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required></div>
                        <div class="col"><label class="form-label">Time *</label><input type="time" name="exit_time" class="form-control form-control-sm" required></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Picked Up By') }}</label>
                        <input type="text" name="picked_up_by" class="form-control form-control-sm" placeholder="Guardian / relative name">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Reason *</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Submit') }}</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
