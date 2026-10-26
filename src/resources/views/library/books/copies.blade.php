@extends('me::master')
@section('title', 'Copies — '.$book->title)
@push('buttons')
    <a href="{{ route('library.books') }}" class="btn btn-sm btn-encodex-cancel"><i class="fas fa-arrow-left"></i> {{ sm_t('Books') }}</a>
    <a href="{{ route('library.issues') }}" class="btn btn-sm btn-encodex-list"><i class="fas fa-hand-holding me-1"></i>{{ sm_t('Issue / Return') }}</a>
@endpush

@section('content')

<div class="row g-3 mb-3">
    <div class="col-md-7">
        <div class="glass-card p-3">
            <div class="d-flex align-items-start gap-3">
                <div class="text-primary fs-3"><i class="fas fa-book"></i></div>
                <div>
                    <h5 class="mb-1">{{ $book->title }}</h5>
                    <div class="text-muted small">
                        @if($book->author)<span class="me-3"><i class="fas fa-user-pen me-1"></i>{{ $book->author }}</span>@endif
                        @if($book->publisher)<span class="me-3"><i class="fas fa-building me-1"></i>{{ $book->publisher }}</span>@endif
                        @if($book->isbn)<span class="me-3">ISBN: {{ $book->isbn }}</span>@endif
                    </div>
                    <div class="mt-1">
                        <span class="badge bg-info-subtle text-info me-1">{{ sm_t(ucfirst($book->type ?? 'text')) }}</span>
                        @if($book->rack_no)<span class="badge bg-secondary-subtle text-secondary me-1">Rack: {{ $book->rack_no }}</span>@endif
                        @if($book->price)<span class="badge bg-light-subtle text-dark border me-1">{{ sm_money($book->price,2) }}</span>@endif
                    </div>
                </div>
                <div class="ms-auto text-end">
                    <div class="fs-5 fw-bold text-primary">{{ $book->book_id }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="row g-2 h-100">
            <div class="col-6">
                <div class="glass-card p-3 text-center h-100">
                    <div class="fs-4 fw-bold text-success">{{ $book->copies->where('status','available')->count() }}</div>
                    <div class="small text-muted">{{ sm_t('Available') }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="glass-card p-3 text-center h-100">
                    <div class="fs-4 fw-bold text-warning">{{ $book->copies->where('status','issued')->count() }}</div>
                    <div class="small text-muted">{{ sm_t('Issued') }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="glass-card p-3 text-center h-100">
                    <div class="fs-4 fw-bold text-danger">{{ $book->copies->where('status','lost')->count() }}</div>
                    <div class="small text-muted">{{ sm_t('Lost') }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="glass-card p-3 text-center h-100">
                    <div class="fs-4 fw-bold">{{ $book->copies->count() }}</div>
                    <div class="small text-muted">{{ sm_t('Total') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <span class="fw-semibold">{{ sm_t('All Copies') }}</span>
        <small class="text-muted">{{ $book->copies->count() }} copies</small>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>{{ sm_t('Copy ID') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Current Holder') }}</th>
                    <th>{{ sm_t('Issue Date') }}</th>
                    <th>{{ sm_t('Due Date') }}</th>
                    <th>{{ sm_t('Overdue') }}</th>
                    <th>{{ sm_t('Fine') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($book->copies as $copy)
                @php
                    $colors = \ME\SchoolManagement\Models\BookCopy::statusColors();
                    $issue  = $copy->activeIssue;
                @endphp
                <tr>
                    <td class="fw-semibold">{{ $copy->copy_id }}</td>
                    <td><span class="badge bg-{{ $bc = $colors[$copy->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t(ucfirst($copy->status)) }}</span></td>
                    <td>
                        @if($issue)
                            {{ $issue->member_name }}
                            <span class="badge bg-light-subtle text-dark border ms-1 small">{{ sm_t(ucfirst($issue->member_type)) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="small">{{ $issue ? sm_date($issue->issue_date) : '—' }}</td>
                    <td class="small">
                        @if($issue)
                            <span class="{{ $issue->overdue_days > 0 ? 'text-danger fw-bold' : '' }}">{{ sm_date($issue->due_date) }}</span>
                        @else —
                        @endif
                    </td>
                    <td class="text-center">
                        @if($issue && $issue->overdue_days > 0)
                            <span class="badge bg-danger-subtle text-danger">{{ $issue->overdue_days }} days</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($issue && $issue->calculated_fine > 0)
                            <span class="badge bg-warning-subtle text-warning">{{ sm_money($issue->calculated_fine,2) }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($copy->status === 'available')
                            <a href="{{ route('library.issues') }}?book_id={{ $book->id }}&copy_id={{ $copy->id }}" class="btn btn-sm btn-encodex-create">{{ sm_t('Issue') }}</a>
                        @elseif($copy->status === 'issued' && $issue)
                            <button class="btn btn-sm btn-encodex-edit" onclick="openReturnModal({{ $issue->id }}, '{{ $copy->copy_id }}', '{{ $issue->member_name }}')">{{ sm_t('Return') }}</button>
                        @elseif($copy->status === 'lost')
                            <span class="badge bg-danger-subtle text-danger">{{ sm_t('Lost') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow">
    <div class="card-header py-2"><span class="fw-semibold"><i class="fas fa-history me-1"></i>{{ sm_t('Issue History') }}</span></div>
    <div class="card-body p-0">
        <table class="table table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>{{ sm_t('Copy') }}</th>
                    <th>{{ sm_t('Member') }}</th>
                    <th>{{ sm_t('Type') }}</th>
                    <th>{{ sm_t('Issue') }}</th>
                    <th>{{ sm_t('Due') }}</th>
                    <th>{{ sm_t('Return') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Fine') }}</th>
                    <th>{{ sm_t('Paid') }}</th>
                    <th>{{ sm_t('Fine Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($issues as $issue)
                <tr class="{{ $issue->is_lost ? 'table-danger' : ($issue->status==='issued' && $issue->overdue_days > 0 ? 'table-warning' : '') }}">
                    <td class="small fw-semibold">{{ optional($issue->copy)->copy_id }}</td>
                    <td class="small">{{ $issue->member_name }}</td>
                    <td><span class="badge bg-secondary-subtle text-secondary">{{ sm_t(ucfirst($issue->member_type)) }}</span></td>
                    <td class="small">{{ sm_date($issue->issue_date) }}</td>
                    <td class="small">{{ sm_date($issue->due_date) }}</td>
                    <td class="small">{{ $issue->return_date ? sm_date($issue->return_date) : '—' }}</td>
                    <td>
                        @if($issue->is_lost)
                            <span class="badge bg-danger-subtle text-danger">{{ sm_t('Lost') }}</span>
                        @elseif($issue->status==='returned')
                            <span class="badge bg-success-subtle text-success">{{ sm_t('Returned') }}</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning">{{ sm_t('Issued') }}</span>
                        @endif
                    </td>
                    <td class="small">{{ $issue->fine_amount > 0 ? sm_money($issue->fine_amount,2) : '—' }}</td>
                    <td class="small">{{ $issue->fine_paid > 0 ? sm_money($issue->fine_paid,2) : '—' }}</td>
                    <td>
                        @if($issue->fine_status==='paid')
                            <span class="badge bg-success-subtle text-success">{{ sm_t('Paid') }}</span>
                        @elseif($issue->fine_status==='waived')
                            <span class="badge bg-info-subtle text-info">{{ sm_t('Waived') }}</span>
                        @elseif($issue->fine_status==='pending')
                            <span class="badge bg-danger-subtle text-danger">{{ sm_t('Pending') }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-3">{{ sm_t('No history found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Return Modal --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title">{{ sm_t('Return Book') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" id="returnForm">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted mb-2"><strong id="returnInfo"></strong></p>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_lost" id="isLostCheck" value="1">
                            <label class="form-check-label text-danger" for="isLostCheck"><strong>{{ sm_t('Mark as Lost') }}</strong></label>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label form-label-sm">{{ sm_t('Return Note') }}</label><textarea name="return_note" class="form-control form-control-sm" rows="2"></textarea></div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Confirm Return') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReturnModal(issueId, copyId, memberName) {
    document.getElementById('returnForm').action = '/library/issues/' + issueId + '/return';
    document.getElementById('returnInfo').textContent = copyId + ' — ' + memberName;
    new bootstrap.Modal(document.getElementById('returnModal')).show();
}
</script>
@endsection
