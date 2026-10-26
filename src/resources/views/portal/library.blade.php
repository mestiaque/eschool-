@extends('school-management::portal.layout')
@section('title', sm_t('Library History'))

@section('content')
@if($activeIssues->count())
<div class="portal-card mb-3">
    <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="fas fa-book-open text-info me-2"></i>{{ sm_t('Currently Issued Books') }}</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-portal table-hover mb-0">
            <thead><tr><th>{{ sm_t('Book') }}</th><th>{{ sm_t('Copy ID') }}</th><th>{{ sm_t('Issued On') }}</th><th>{{ sm_t('Due Date') }}</th><th>{{ sm_t('Fine') }}</th><th>{{ sm_t('Status') }}</th></tr></thead>
            <tbody>
                @foreach($activeIssues as $issue)
                <tr>
                    <td class="fw-semibold">{{ $issue->book?->title }}</td>
                    <td class="small text-muted">{{ $issue->copy?->copy_id }}</td>
                    <td class="small">{{ sm_date($issue->issue_date) }}</td>
                    <td class="small">{{ sm_date($issue->due_date) }}</td>
                    <td class="text-end">@if($issue->fine_amount > 0)<span class="text-danger">{{ sm_money($issue->fine_amount,0) }}</span>@else —@endif</td>
                    <td>
                        @if($issue->overdue_days > 0)
                        <span class="badge bg-danger-subtle text-danger">Overdue {{ $issue->overdue_days }}d</span>
                        @else
                        <span class="badge bg-success-subtle text-success">{{ sm_t('On Time') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="portal-card">
    <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="fas fa-history text-secondary me-2"></i>{{ sm_t('Issue History') }}</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-portal table-hover mb-0">
            <thead><tr><th>{{ sm_t('Book') }}</th><th>{{ sm_t('Copy ID') }}</th><th>{{ sm_t('Issued On') }}</th><th>{{ sm_t('Returned On') }}</th><th>{{ sm_t('Fine') }}</th><th>{{ sm_t('Status') }}</th></tr></thead>
            <tbody>
                @forelse($history as $issue)
                <tr>
                    <td class="fw-semibold">{{ $issue->book?->title }}</td>
                    <td class="small text-muted">{{ $issue->copy?->copy_id }}</td>
                    <td class="small">{{ sm_date($issue->issue_date) }}</td>
                    <td class="small">{{ sm_date($issue->return_date) ?? '—' }}</td>
                    <td class="text-end small">
                        @if($issue->fine_amount > 0)
                            <span class="{{ $issue->fine_paid ? 'text-muted' : 'text-danger fw-semibold' }}">
                                {{ sm_money($issue->fine_amount,0) }}
                                @if($issue->fine_paid)<span class="badge bg-success-subtle text-success ms-1" style="font-size:10px">{{ sm_t('Paid') }}</span>@endif
                            </span>
                        @else —
                        @endif
                    </td>
                    <td>
                        @if($issue->status === 'returned')
                        <span class="badge bg-light-subtle text-dark border">{{ sm_t('Returned') }}</span>
                        @elseif($issue->status === 'lost')
                        <span class="badge bg-danger-subtle text-danger">{{ sm_t('Lost') }}</span>
                        @else
                        <span class="badge bg-success-subtle text-success">{{ sm_t('Active') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">{{ sm_t('No library history.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
