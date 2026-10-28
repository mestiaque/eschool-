{{-- After a payment: link to its money receipt. --}}
@if(session('receipt_url'))
<div class="alert alert-success small py-2 d-flex align-items-center gap-2 no-print">
    <i class="fas fa-check-circle"></i> {{ sm_t('Payment received.') }}
    <a href="{{ session('receipt_url') }}" target="_blank" class="btn btn-sm btn-encodex-print ms-auto"><i class="fas fa-receipt"></i> {{ sm_t('Print Money Receipt') }}</a>
</div>
@endif
