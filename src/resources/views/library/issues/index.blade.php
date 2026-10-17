@extends('me::master')
@section('title', sm_t('Issue / Return Books'))
@push('buttons')
    <a href="{{ route('library.books') }}" class="btn btn-sm btn-encodex-list"><i class="fas fa-book me-1"></i>Books</a>
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#issueModal">
        <i class="fas fa-plus"></i> Issue Book
    </button>
@endpush

@section('content')

<div class="card shadow">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Book title / ID / Copy ID / Member" value="{{ request('search') }}"></div>
            <div class="col-md-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Status') }}</option>
                    <option value="issued"   {{ request('status')=='issued'  ?'selected':'' }}>{{ sm_t('Issued') }}</option>
                    <option value="returned" {{ request('status')=='returned'?'selected':'' }}>{{ sm_t('Returned') }}</option>
                    <option value="lost"     {{ request('status')=='lost'    ?'selected':'' }}>{{ sm_t('Lost') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="member_type" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Members') }}</option>
                    <option value="student" {{ request('member_type')=='student'?'selected':'' }}>{{ sm_t('Student') }}</option>
                    <option value="teacher" {{ request('member_type')=='teacher'?'selected':'' }}>{{ sm_t('Teacher') }}</option>
                    <option value="staff"   {{ request('member_type')=='staff'  ?'selected':'' }}>{{ sm_t('Staff') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-encodex-search me-1"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('library.issues') }}" class="btn btn-sm btn-encodex-cancel">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Copy ID') }}</th>
                    <th>{{ sm_t('Book') }}</th>
                    <th>{{ sm_t('Member') }}</th>
                    <th>{{ sm_t('Type') }}</th>
                    <th>{{ sm_t('Issue Date') }}</th>
                    <th>{{ sm_t('Due Date') }}</th>
                    <th>{{ sm_t('Return Date') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Fine') }}</th>
                    <th>{{ sm_t('Fine Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($issues as $i => $issue)
                @php $overdue = $issue->overdue_days; @endphp
                <tr class="{{ $issue->is_lost ? 'table-danger' : ($overdue > 0 ? 'table-warning' : '') }}">
                    <td class="text-center small">{{ $issues->firstItem() + $i }}</td>
                    <td class="fw-semibold small">{{ optional($issue->copy)->copy_id ?? '—' }}</td>
                    <td class="small">
                        <a href="{{ route('library.books.copies', $issue->book_id) }}" class="text-decoration-none">{{ optional($issue->book)->title }}</a>
                        <br><small class="text-muted">{{ optional($issue->book)->book_id }}</small>
                    </td>
                    <td class="small">{{ $issue->member_name }}</td>
                    <td><span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($issue->member_type) }}</span></td>
                    <td class="small">{{ sm_date($issue->issue_date) }}</td>
                    <td class="small {{ $overdue > 0 ? 'text-danger fw-bold' : '' }}">{{ sm_date($issue->due_date) }}</td>
                    <td class="small">{{ $issue->return_date ? sm_date($issue->return_date) : '—' }}</td>
                    <td>
                        @if($issue->is_lost)
                            <span class="badge bg-danger-subtle text-danger">Lost</span>
                        @elseif($issue->status==='returned')
                            <span class="badge bg-success-subtle text-success">Returned</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning">Issued</span>
                            @if($overdue > 0)<br><span class="badge bg-danger-subtle text-danger mt-1">{{ $overdue }}d overdue</span>@endif
                        @endif
                    </td>
                    <td class="small text-end">
                        @if($issue->fine_amount > 0)
                            <span class="fw-bold">{{ sm_money($issue->fine_amount,2) }}</span>
                            @if($issue->fine_paid > 0)<br><small class="text-success">Paid: {{ sm_money($issue->fine_paid,2) }}</small>@endif
                            @if($issue->fine_balance > 0)<br><small class="text-danger">Due: {{ sm_money($issue->fine_balance,2) }}</small>@endif
                        @elseif($issue->status==='issued' && $issue->calculated_fine > 0)
                            <span class="text-warning">≈{{ sm_money($issue->calculated_fine,2) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($issue->fine_status==='paid')
                            <span class="badge bg-success-subtle text-success">Paid</span>
                        @elseif($issue->fine_status==='waived')
                            <span class="badge bg-info-subtle text-info">Waived</span>
                        @elseif($issue->fine_status==='pending')
                            <span class="badge bg-danger-subtle text-danger">Pending</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center" style="min-width:100px">
                        @if($issue->status==='issued')
                            <button class="btn btn-sm btn-encodex-edit mb-1" onclick="openReturnModal({{ $issue->id }}, '{{ optional($issue->copy)->copy_id }}', '{{ addslashes($issue->member_name) }}', {{ number_format($issue->calculated_fine,2,'.','') }})">
                                <i class="fas fa-undo-alt"></i> Return
                            </button>
                        @endif
                        @if($issue->fine_status==='pending' && $issue->fine_balance > 0)
                            <button class="btn btn-sm btn-encodex-payment mb-1" onclick="openPayFineModal({{ $issue->id }}, {{ number_format($issue->fine_balance,2,'.','') }}, '{{ addslashes($issue->member_name) }}')">
                                <i class="fas fa-coins"></i> Pay
                            </button>
                            <form method="POST" action="{{ route('library.issues.waive-fine', $issue->id) }}" class="d-inline" onsubmit="return confirm('Waive fine for {{ addslashes($issue->member_name) }}?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-encodex-clear mb-1"><i class="fas fa-eraser"></i> Waive</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center text-muted py-4">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $issues->links('pagination::bootstrap-5') }}</div>

{{-- Issue Modal --}}
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title"><i class="fas fa-hand-holding-book me-1"></i>Issue Book</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('library.issues.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Book') }} <span class="text-danger">*</span></label>
                            <select name="book_id" id="issueBookSelect" class="form-control form-control-sm" required onchange="loadCopies(this.value)">
                                <option value="">— Select Book —</option>
                                @foreach($books as $b)
                                    <option value="{{ $b->id }}" {{ request('book_id')==$b->id?'selected':'' }}>{{ $b->book_id }} — {{ $b->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Available Copy') }} <span class="text-danger">*</span></label>
                            <select name="book_copy_id" id="issueCopySelect" class="form-control form-control-sm" required>
                                <option value="">— Select Book First —</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Member Type') }} <span class="text-danger">*</span></label>
                            <select name="member_type" id="memberTypeSelect" class="form-control form-control-sm" required onchange="updateMemberList()">
                                <option value="student">{{ sm_t('Student') }}</option>
                                <option value="teacher">{{ sm_t('Teacher') }}</option>
                                <option value="staff">{{ sm_t('Staff') }}</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Member') }} <span class="text-danger">*</span></label>
                            <select name="member_id" id="memberSelect" class="form-control form-control-sm" required></select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Issue Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Due Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="issueDueDate" class="form-control form-control-sm" required value="{{ date('Y-m-d', strtotime('+'.$settings->max_issue_days.' days')) }}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Note') }}</label>
                            <input type="text" name="note" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Issue Book') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Return Modal --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title"><i class="fas fa-undo-alt me-1"></i>Return Book</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" id="returnForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3" id="returnInfo"></div>
                    <div id="fineAlert" class="alert alert-warning py-2 small mb-3 d-none"></div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_lost" id="isLostCheck" value="1">
                            <label class="form-check-label text-danger fw-semibold" for="isLostCheck">Mark as Lost (fine = book price)</label>
                        </div>
                    </div>
                    <div class="mb-2"><label class="form-label form-label-sm">{{ sm_t('Return Note') }}</label><textarea name="return_note" class="form-control form-control-sm" rows="2"></textarea></div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Confirm Return') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Pay Fine Modal --}}
<div class="modal fade" id="payFineModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title"><i class="fas fa-coins me-1"></i>Pay Fine</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" id="payFineForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small mb-3" id="payFineInfo"></div>
                    <div class="mb-3">
                        <label class="form-label form-label-sm">Amount to Pay ({{ sm_currency() }}) <span class="text-danger">*</span></label>
                        <input type="number" name="fine_paid" id="payFineAmount" class="form-control form-control-sm" required min="0.01" step="0.01">
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Record Payment') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Member data for JS
const studentsData = @json($students->map(fn($s)=>['id'=>$s->id,'name'=>$s->first_name.' '.$s->last_name]));
const teachersData = @json($teachers->map(fn($t)=>['id'=>$t->id,'name'=>$t->first_name.' '.$t->last_name]));
const staffData    = @json($staffAll->map(fn($s)=>['id'=>$s->id,'name'=>$s->first_name.' '.$s->last_name]));

function updateMemberList() {
    const type = document.getElementById('memberTypeSelect').value;
    const sel  = document.getElementById('memberSelect');
    const data = type==='teacher' ? teachersData : type==='staff' ? staffData : studentsData;
    sel.innerHTML = data.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
}

async function loadCopies(bookId) {
    const sel = document.getElementById('issueCopySelect');
    sel.innerHTML = '<option>{{ sm_t('Loading...') }}</option>';
    if (!bookId) { sel.innerHTML = '<option value="">— Select Book First —</option>'; return; }
    const res  = await fetch(`{{ url('book-copies') }}/${bookId}`);
    const data = await res.json();
    if (data.length === 0) {
        sel.innerHTML = '<option value="">{{ sm_t('No available copies') }}</option>';
    } else {
        sel.innerHTML = data.map(c => `<option value="${c.id}">${c.copy_id}</option>`).join('');
    }
}

function openReturnModal(issueId, copyId, memberName, fine) {
    document.getElementById('returnForm').action = '/library/issues/' + issueId + '/return';
    document.getElementById('returnInfo').textContent = copyId + ' — ' + memberName;
    const fineAlert = document.getElementById('fineAlert');
    if (fine > 0) {
        fineAlert.classList.remove('d-none');
        fineAlert.textContent = 'Estimated overdue fine: {{ sm_currency() }}' + fine.toFixed(2);
    } else {
        fineAlert.classList.add('d-none');
    }
    document.getElementById('isLostCheck').checked = false;
    new bootstrap.Modal(document.getElementById('returnModal')).show();
}

function openPayFineModal(issueId, balance, memberName) {
    document.getElementById('payFineForm').action = '/library/issues/' + issueId + '/pay-fine';
    document.getElementById('payFineInfo').textContent = memberName + ' — Balance: {{ sm_currency() }}' + parseFloat(balance).toFixed(2);
    document.getElementById('payFineAmount').value = balance;
    new bootstrap.Modal(document.getElementById('payFineModal')).show();
}

// Init member list on load
updateMemberList();

// Pre-select book if URL has book_id
@if(request('book_id'))
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('issueBookSelect');
    if(sel) loadCopies(sel.value);
    @if(request('copy_id'))
    // pre-select copy after load — slight delay for async
    setTimeout(() => {
        const cs = document.getElementById('issueCopySelect');
        [...cs.options].forEach(o => { if (o.value == '{{ request('copy_id') }}') o.selected = true; });
    }, 600);
    @endif
    new bootstrap.Modal(document.getElementById('issueModal')).show();
});
@endif
</script>
@endsection
