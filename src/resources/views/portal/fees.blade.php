@extends('school-management::portal.layout')
@section('title', sm_t('My Fees'))

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2 bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
            <div class="fw-bold fs-4 text-success">{{ sm_money($totalPaid, 0) }}</div>
            <div class="text-muted small">{{ sm_t('Total Paid') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2 bg-danger bg-opacity-10 text-danger"><i class="fas fa-clock"></i></div>
            <div class="fw-bold fs-4 text-danger">{{ sm_money($totalDue, 0) }}</div>
            <div class="text-muted small">{{ sm_t('Total Due') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2 bg-info bg-opacity-10 text-info"><i class="fas fa-tag"></i></div>
            <div class="fw-bold fs-4 text-info">{{ sm_money($totalWaived, 0) }}</div>
            <div class="text-muted small">{{ sm_t('Waived / Discount') }}</div>
        </div>
    </div>
</div>

<div class="portal-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>{{ sm_t('Fee Records') }}</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-portal table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ sm_t('Month / Period') }}</th>
                    <th>{{ sm_t('Fee Type') }}</th>
                    <th class="text-end">{{ sm_t('Amount') }}</th>
                    <th class="text-end">{{ sm_t('Paid') }}</th>
                    <th class="text-end">{{ sm_t('Due') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Paid On') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fees as $fee)
                <tr>
                    <td class="small">{{ $fee->month_number ? $fee->monthLabel('F Y') : ($fee->academicYear?->year ?? '—') }}</td>
                    <td>{{ $fee->feeType?->name ?? '—' }}</td>
                    <td class="text-end">{{ sm_money($fee->amount, 0) }}</td>
                    <td class="text-end text-success fw-semibold">{{ sm_money($fee->paid_amount ?? 0, 0) }}</td>
                    <td class="text-end text-danger fw-semibold">{{ sm_money(max(0, $fee->amount - ($fee->paid_amount ?? 0)), 0) }}</td>
                    <td>
                        @php $due = max(0, $fee->amount - ($fee->paid_amount ?? 0)); @endphp
                        @if($due <= 0)
                        <span class="badge badge-status-paid">{{ sm_t('Paid') }}</span>
                        @elseif(($fee->paid_amount ?? 0) > 0)
                        <span class="badge badge-status-partial">{{ sm_t('Partial') }}</span>
                        @else
                        <span class="badge badge-status-unpaid">{{ sm_t('Unpaid') }}</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $fee->status === 'paid' ? sm_date($fee->updated_at) : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">{{ sm_t('No fee records found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($discounts->count())
<div class="portal-card mt-3 p-3">
    <h6 class="fw-bold mb-2"><i class="fas fa-tag text-success me-2"></i>{{ sm_t('Active Waivers / Discounts') }}</h6>
    @foreach($discounts as $d)
    <div class="d-flex justify-content-between align-items-center py-1 border-bottom small">
        <div>
            <span class="fw-semibold">{{ $d->feeType?->name ?? 'All Fees' }}</span>
            @if($d->discount_type)<span class="text-muted ms-2">– {{ sm_t(ucfirst($d->discount_type)) }}</span>@endif
        </div>
        <span class="text-success fw-bold">{{ sm_money($d->discount_amount, 0) }}</span>
    </div>
    @endforeach
</div>
@endif
@endsection
