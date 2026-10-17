@extends('me::master')
@section('title', sm_t('Library Books'))
@push('buttons')
    <button class="btn btn-sm btn-encodex" data-bs-toggle="modal" data-bs-target="#settingsModal">
        <i class="fas fa-cog"></i> Fine Settings
    </button>
    <a href="{{ route('library.availability') }}" class="btn btn-sm btn-encodex-list">
        <i class="fas fa-th"></i> Availability
    </a>
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#bookModal">
        <i class="fas fa-plus"></i> Add Book
    </button>
@endpush

@section('content')

<div class="alert alert-light border py-2 mb-3 d-flex gap-4 flex-wrap small align-items-center">
    <span><i class="fas fa-coins me-1 text-warning"></i>Fine: <strong>@if($settings->fine_type==='per_day') {{ sm_currency() }}{{ $settings->fine_per_day }}/day @else Fixed {{ sm_currency() }}{{ $settings->fixed_fine }} @endif</strong></span>
    <span><i class="fas fa-calendar me-1 text-info"></i>Max loan: <strong>{{ $settings->max_issue_days }} days</strong></span>
    <span><i class="fas fa-book me-1 text-success"></i>Max/member: <strong>{{ $settings->max_books_per_member }}</strong></span>
    <a href="{{ route('library.issues') }}" class="ms-auto small"><i class="fas fa-hand-holding me-1"></i>Issue / Return →</a>
</div>

<div class="card shadow">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-5"><input type="text" name="search" class="form-control form-control-sm" placeholder="Title / Author / Book ID / ISBN" value="{{ request('search') }}"></div>
            <div class="col-md-3">
                <select name="type" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Types') }}</option>
                    @foreach(['text'=>'Text Book','reference'=>'Reference','novel'=>'Novel','magazine'=>'Magazine','other'=>'Other'] as $k=>$v)
                    <option value="{{ $k }}" {{ request('type')==$k?'selected':'' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-encodex-search me-1"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('library.books') }}" class="btn btn-sm btn-encodex-cancel">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead class="text-center">
                <tr>
                    <th>{{ sm_t('Book ID') }}</th>
                    <th class="text-start">{{ sm_t('Title') }}</th>
                    <th>{{ sm_t('Author') }}</th>
                    <th>{{ sm_t('Type') }}</th>
                    <th>{{ sm_t('Total') }}</th>
                    <th><span class="text-success">Available</span></th>
                    <th><span class="text-warning">Issued</span></th>
                    <th><span class="text-danger">Lost</span></th>
                    <th>{{ sm_t('Rack') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($books as $book)
                <tr>
                    <td class="text-center fw-semibold text-primary">{{ $book->book_id }}</td>
                    <td>
                        <a href="{{ route('library.books.copies', $book->id) }}" class="text-decoration-none fw-semibold">{{ $book->title }}</a>
                        @if($book->isbn)<br><small class="text-muted">ISBN: {{ $book->isbn }}</small>@endif
                    </td>
                    <td class="small">{{ $book->author ?? '—' }}</td>
                    <td class="text-center"><span class="badge bg-info-subtle text-info">{{ ucfirst($book->type ?? 'text') }}</span></td>
                    <td class="text-center fw-bold">{{ $book->copies_count }}</td>
                    <td class="text-center"><span class="badge bg-success-subtle text-success">{{ $book->available_count }}</span></td>
                    <td class="text-center"><span class="badge bg-warning-subtle text-warning">{{ $book->issued_count }}</span></td>
                    <td class="text-center"><span class="badge bg-{{ $bc = $book->lost_count > 0 ? 'danger' : 'secondary' }}-subtle text-{{ $bc }}">{{ $book->lost_count }}</span></td>
                    <td class="text-center small">{{ $book->rack_no ?? '—' }}</td>
                    <td class="text-center">
                        <a href="{{ route('library.books.copies', $book->id) }}" class="btn btn-sm btn-encodex-show" title="Copies"><i class="fas fa-copy"></i></a>
                        <button class="btn btn-sm btn-encodex-edit" onclick="openEditBook({{ $book->id }}, {{ json_encode($book) }})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-encodex-create" title="Add Copies" onclick="openPurchaseModal({{ $book->id }}, '{{ $book->book_id }}', '{{ addslashes($book->title) }}')"><i class="fas fa-cart-plus"></i></button>
                        <form method="POST" action="{{ route('library.books.destroy', $book->id) }}" class="d-inline" onsubmit="return confirm('Delete this book?')">
                            @csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No books found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $books->links('pagination::bootstrap-5') }}</div>

{{-- Add Book Modal --}}
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title">{{ sm_t('Add New Book') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('library.books.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-8 mb-2"><label class="form-label form-label-sm">{{ sm_t('Title') }} <span class="text-danger">*</span></label><input type="text" name="title" class="form-control form-control-sm" required></div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">{{ sm_t('ISBN') }}</label><input type="text" name="isbn" class="form-control form-control-sm"></div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">{{ sm_t('Author') }}</label><input type="text" name="author" class="form-control form-control-sm"></div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">{{ sm_t('Publisher') }}</label><input type="text" name="publisher" class="form-control form-control-sm"></div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">{{ sm_t('Edition') }}</label><input type="text" name="edition" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-2"><label class="form-label form-label-sm">{{ sm_t('Type') }}</label>
                            <select name="type" class="form-control form-control-sm">
                                <option value="text">{{ sm_t('Text Book') }}</option><option value="reference">{{ sm_t('Reference') }}</option><option value="novel">{{ sm_t('Novel') }}</option><option value="magazine">{{ sm_t('Magazine') }}</option><option value="other">{{ sm_t('Other') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2"><label class="form-label form-label-sm">{{ sm_t('Language') }}</label>
                            <select name="language" class="form-control form-control-sm"><option>{{ sm_t('Bangla') }}</option><option>{{ sm_t('English') }}</option><option>{{ sm_t('Arabic') }}</option><option>{{ sm_t('Other') }}</option></select>
                        </div>
                        <div class="col-md-3 mb-2"><label class="form-label form-label-sm">{{ sm_t('Rack No') }}</label><input type="text" name="rack_no" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-2"><label class="form-label form-label-sm">Price/Copy ({{ sm_currency() }})</label><input type="number" name="price" class="form-control form-control-sm" step="0.01" min="0"></div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('No. of Copies') }} <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control form-control-sm" required min="1" value="1">
                            <small class="text-muted">BK-0001-01, -02… auto</small>
                        </div>
                        <div class="col-md-3 mb-2"><label class="form-label form-label-sm">{{ sm_t('Vendor') }}</label><input type="text" name="vendor" class="form-control form-control-sm"></div>
                        <div class="col-md-3 mb-2"><label class="form-label form-label-sm">{{ sm_t('Invoice No') }}</label><input type="text" name="invoice_no" class="form-control form-control-sm"></div>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save & Generate Copies') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Book Modal --}}
<div class="modal fade" id="editBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title">{{ sm_t('Edit Book') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" id="editBookForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-8 mb-2"><label class="form-label form-label-sm">Title *</label><input type="text" name="title" class="form-control form-control-sm" required></div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">{{ sm_t('ISBN') }}</label><input type="text" name="isbn" class="form-control form-control-sm"></div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">{{ sm_t('Author') }}</label><input type="text" name="author" class="form-control form-control-sm"></div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">{{ sm_t('Publisher') }}</label><input type="text" name="publisher" class="form-control form-control-sm"></div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">{{ sm_t('Edition') }}</label><input type="text" name="edition" class="form-control form-control-sm"></div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">{{ sm_t('Type') }}</label>
                            <select name="type" class="form-control form-control-sm"><option value="text">{{ sm_t('Text Book') }}</option><option value="reference">{{ sm_t('Reference') }}</option><option value="novel">{{ sm_t('Novel') }}</option><option value="magazine">{{ sm_t('Magazine') }}</option><option value="other">{{ sm_t('Other') }}</option></select>
                        </div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">{{ sm_t('Rack No') }}</label><input type="text" name="rack_no" class="form-control form-control-sm"></div>
                        <div class="col-md-4 mb-2"><label class="form-label form-label-sm">Price ({{ sm_currency() }})</label><input type="number" name="price" class="form-control form-control-sm" step="0.01"></div>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Purchase Modal --}}
<div class="modal fade" id="purchaseModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title"><i class="fas fa-cart-plus me-1"></i> Add Purchase / New Copies</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('library.purchase.store') }}">
                @csrf
                <input type="hidden" name="book_id" id="purchaseBookId">
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3" id="purchaseBookTitle"></div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-2"><label class="form-label form-label-sm">New Copies *</label><input type="number" name="copies_count" class="form-control form-control-sm" required min="1" value="1"></div>
                        <div class="col-md-6 mb-2"><label class="form-label form-label-sm">Purchase Date *</label><input type="date" name="purchase_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}"></div>
                        <div class="col-md-6 mb-2"><label class="form-label form-label-sm">Unit Price ({{ sm_currency() }})</label><input type="number" name="unit_price" class="form-control form-control-sm" step="0.01" min="0"></div>
                        <div class="col-md-6 mb-2"><label class="form-label form-label-sm">{{ sm_t('Vendor') }}</label><input type="text" name="vendor" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2"><label class="form-label form-label-sm">{{ sm_t('Invoice No') }}</label><input type="text" name="invoice_no" class="form-control form-control-sm"></div>
                        <div class="col-12 mb-2"><label class="form-label form-label-sm">{{ sm_t('Notes') }}</label><textarea name="notes" class="form-control form-control-sm" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save & Generate Copies') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Settings Modal --}}
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title"><i class="fas fa-cog me-1"></i> Library Fine Settings</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('library.settings.update') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label form-label-sm">{{ sm_t('Fine Type') }}</label>
                        <select name="fine_type" class="form-control form-control-sm" id="fineTypeSelect" onchange="toggleFineFields()">
                            <option value="per_day" {{ $settings->fine_type==='per_day'?'selected':'' }}>Per Day (দৈনিক)</option>
                            <option value="fixed"   {{ $settings->fine_type==='fixed'?'selected':'' }}>Fixed (নির্দিষ্ট পরিমাণ)</option>
                        </select>
                    </div>
                    <div id="perDayWrap" class="mb-3" style="{{ $settings->fine_type==='fixed'?'display:none':'' }}">
                        <label class="form-label form-label-sm">Fine per Day ({{ sm_currency() }})</label>
                        <input type="number" name="fine_per_day" class="form-control form-control-sm" step="0.01" min="0" value="{{ $settings->fine_per_day }}">
                    </div>
                    <div id="fixedWrap" class="mb-3" style="{{ $settings->fine_type==='per_day'?'display:none':'' }}">
                        <label class="form-label form-label-sm">Fixed Fine Amount ({{ sm_currency() }})</label>
                        <input type="number" name="fixed_fine" class="form-control form-control-sm" step="0.01" min="0" value="{{ $settings->fixed_fine }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-sm">{{ sm_t('Default Loan Period (days)') }}</label>
                        <input type="number" name="max_issue_days" class="form-control form-control-sm" min="1" value="{{ $settings->max_issue_days }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-sm">{{ sm_t('Max Books per Member') }}</label>
                        <input type="number" name="max_books_per_member" class="form-control form-control-sm" min="1" value="{{ $settings->max_books_per_member }}">
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save Settings') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditBook(id, b) {
    const form = document.getElementById('editBookForm');
    form.action = '/library/books/' + id;
    const f = (n, v) => { const el = form.querySelector('[name="'+n+'"]'); if(el) el.value = v ?? ''; };
    f('title',b.title); f('isbn',b.isbn); f('author',b.author); f('publisher',b.publisher);
    f('edition',b.edition); f('type',b.type); f('rack_no',b.rack_no); f('price',b.price);
    new bootstrap.Modal(document.getElementById('editBookModal')).show();
}
function openPurchaseModal(id, bookId, title) {
    document.getElementById('purchaseBookId').value = id;
    document.getElementById('purchaseBookTitle').innerHTML = '<strong>' + bookId + '</strong> — ' + title;
    new bootstrap.Modal(document.getElementById('purchaseModal')).show();
}
function toggleFineFields() {
    const v = document.getElementById('fineTypeSelect').value;
    document.getElementById('perDayWrap').style.display = v==='per_day' ? '' : 'none';
    document.getElementById('fixedWrap').style.display  = v==='fixed'   ? '' : 'none';
}
</script>
@endsection
