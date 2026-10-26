@extends('me::master')
@section('title', sm_t('Book Availability Chart'))
@push('buttons')
    <a href="{{ route('library.books') }}" class="btn btn-sm btn-encodex-cancel"><i class="fas fa-arrow-left"></i> {{ sm_t('Books') }}</a>
    <a href="{{ route('library.issues') }}" class="btn btn-sm btn-encodex-list"><i class="fas fa-hand-holding me-1"></i>{{ sm_t('Issue / Return') }}</a>
@endpush

@section('content')
<form method="GET" class="glass-search-form row g-2 align-items-end mb-3">
    <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="{{ sm_t('Book title or ID') }}" value="{{ request('search') }}"></div>
    <div class="col-md-2">
        <select name="type" class="form-control form-control-sm">
            <option value="">{{ sm_t('All Types') }}</option>
            @foreach(['text'=>'Text Book','reference'=>'Reference','novel'=>'Novel','magazine'=>'Magazine','other'=>'Other'] as $k=>$v)
            <option value="{{ $k }}" {{ request('type')==$k?'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="availability" class="form-control form-control-sm">
            <option value="">{{ sm_t('Any Availability') }}</option>
            <option value="available"   {{ request('availability')=='available'  ?'selected':'' }}>{{ sm_t('Has Available') }}</option>
            <option value="unavailable" {{ request('availability')=='unavailable'?'selected':'' }}>{{ sm_t('All Issued') }}</option>
        </select>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-sm btn-encodex-search me-1"><i class="fas fa-search"></i> {{ sm_t('Filter') }}</button>
        <a href="{{ route('library.availability') }}" class="btn btn-sm btn-encodex-cancel">{{ sm_t('Reset') }}</a>
    </div>
</form>

<div class="mb-3 d-flex gap-3 small">
    <span class="d-flex align-items-center gap-1"><span style="width:14px;height:14px;background:#28a745;border-radius:3px;display:inline-block"></span> {{ sm_t('Available') }}</span>
    <span class="d-flex align-items-center gap-1"><span style="width:14px;height:14px;background:#ffc107;border-radius:3px;display:inline-block"></span> {{ sm_t('Issued') }}</span>
    <span class="d-flex align-items-center gap-1"><span style="width:14px;height:14px;background:#dc3545;border-radius:3px;display:inline-block"></span> {{ sm_t('Lost') }}</span>
    <span class="d-flex align-items-center gap-1"><span style="width:14px;height:14px;background:#6c757d;border-radius:3px;display:inline-block"></span> {{ sm_t('Damaged') }}</span>
</div>

@foreach($books as $book)
<div class="card shadow mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold text-primary me-2">{{ $book->book_id }}</span>
            <a href="{{ route('library.books.copies', $book->id) }}" class="fw-semibold text-decoration-none">{{ $book->title }}</a>
            @if($book->author)<span class="text-muted small ms-2">by {{ $book->author }}</span>@endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success">{{ $book->copies->where('status','available')->count() }} available</span>
            <span class="badge bg-warning-subtle text-warning">{{ $book->copies->where('status','issued')->count() }} issued</span>
            @if($book->copies->where('status','lost')->count() > 0)
            <span class="badge bg-danger-subtle text-danger">{{ $book->copies->where('status','lost')->count() }} lost</span>
            @endif
            <span class="badge bg-light-subtle text-dark border">{{ $book->copies->count() }} total</span>
        </div>
    </div>
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2">
            @foreach($book->copies->sortBy('copy_number') as $copy)
            @php
                $color = match($copy->status) {
                    'available' => '#28a745',
                    'issued'    => '#ffc107',
                    'lost'      => '#dc3545',
                    default     => '#6c757d',
                };
                $txtColor = $copy->status === 'issued' ? '#000' : '#fff';
                $issue = $copy->activeIssue;
                $tip = $copy->copy_id;
                if ($issue) {
                    $tip .= ' → ' . $issue->member_name;
                    if ($issue->overdue_days > 0) $tip .= ' (overdue ' . $issue->overdue_days . 'd)';
                    else $tip .= ' (due ' . $issue->due_date->format('d M') . ')';
                }
            @endphp
            <div title="{{ $tip }}" data-bs-toggle="tooltip" data-bs-placement="top"
                style="background:{{ $color }};color:{{ $txtColor }};border-radius:6px;padding:4px 8px;font-size:12px;font-weight:600;cursor:default;min-width:90px;text-align:center;line-height:1.4">
                {{ $copy->copy_id }}
                @if($issue)
                <div style="font-size:10px;font-weight:400;opacity:.9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:80px">
                    {{ Str::limit($issue->member_name, 12) }}
                </div>
                @if($issue->overdue_days > 0)
                <div style="font-size:10px;font-weight:700;color:#fff;background:rgba(0,0,0,.25);border-radius:3px;margin-top:2px">
                    +{{ $issue->overdue_days }}d
                </div>
                @endif
                @endif
            </div>
            @endforeach

            @if($book->copies->isEmpty())
            <span class="text-muted small">{{ sm_t('No copies added yet.') }}</span>
            @endif
        </div>
    </div>
</div>
@endforeach

<div class="mt-2">{{ $books->links('pagination::bootstrap-5') }}</div>

<script>
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
</script>
@endsection
