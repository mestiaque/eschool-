@extends('me::printMaster2')
@section('title', sm_t('Pay Slip') . ' — ' . $payment->payee_name)

@push('css')
<style>
    .ps-copy { border: 1px solid #333; padding: 10px 14px; margin-bottom: 10px; page-break-inside: avoid; }
    .ps-copy + .ps-copy { border-top: 1px dashed #333; }
    .ps-top { display: flex; justify-content: space-between; font-size: 11px; color: #555; }
    .ps-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 2px 20px; font-size: 12px; margin: 8px 0; }
    .ps-meta span { color: #555; }
    table.ps-lines td { padding: 4px 8px; font-size: 12px; }
    table.ps-lines td.amt { text-align: right; white-space: nowrap; }
    table.ps-lines tr.net td { font-weight: bold; background: #eef6ee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .ps-words { font-size: 12px; margin: 4px 0; }
    .ps-sign { display: flex; justify-content: space-between; margin-top: 30px; font-size: 11px; }
    .ps-sign div { min-width: 150px; text-align: center; border-top: 1px solid #000; padding-top: 3px; }
    @media print { @page { size: A4 portrait; margin: 8mm; } }
</style>
@endpush

@section('contents')
@foreach([sm_t('Employee Copy'), sm_t('Office Copy')] as $copy)
<div class="ps-copy">
    @include('school-management::partials.print-header', ['title' => sm_t('Pay Slip') . ' — ' . sm_t($payment->month_name) . ' ' . $payment->year])
    <div class="ps-top"><div>{{ sm_t('Slip No') }}: PS-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div><div>{{ $copy }}</div></div>

    <div class="ps-meta">
        <div><span>{{ sm_t('Name') }}:</span> <strong>{{ $payment->payee_name }}</strong></div>
        <div><span>{{ sm_t('Payment Date') }}:</span> {{ sm_date($payment->payment_date) }}</div>
        <div><span>{{ sm_t('Designation') }}:</span> {{ $person?->designation ?? sm_t(ucfirst($payment->staff_type)) }}</div>
        <div><span>{{ sm_t('Payment Method') }}:</span> {{ $payment->payment_method ? sm_t(ucfirst($payment->payment_method)) : sm_t('Cash') }}@if($payment->transaction_id) ({{ $payment->transaction_id }})@endif</div>
        @if($person?->phone)<div><span>{{ sm_t('Phone') }}:</span> {{ $person->phone }}</div>@endif
        @if($person?->bank_name ?? null)<div><span>{{ sm_t('Bank') }}:</span> {{ $person->bank_name }}</div>@endif
    </div>

    <table class="ps-lines">
        <tbody>
            <tr><td>{{ sm_t('Basic Salary') }}</td><td class="amt">{{ sm_money($payment->basic_salary, 2) }}</td></tr>
            <tr><td>{{ sm_t('Allowance') }}</td><td class="amt">+ {{ sm_money($payment->allowance, 2) }}</td></tr>
            <tr><td><strong>{{ sm_t('Gross Salary') }}</strong></td><td class="amt"><strong>{{ sm_money($payment->gross_salary, 2) }}</strong></td></tr>
            <tr><td>{{ sm_t('Deduction') }}</td><td class="amt">− {{ sm_money($payment->deduction, 2) }}</td></tr>
            <tr class="net"><td>{{ sm_t('Net Salary') }}</td><td class="amt">{{ sm_money($payment->net_salary, 2) }}</td></tr>
        </tbody>
    </table>
    <div class="ps-words"><strong>{{ sm_t('In words') }}:</strong> {{ sm_amount_words($payment->net_salary) }}</div>
    @if($payment->note)<div class="ps-words"><strong>{{ sm_t('Note') }}:</strong> {{ $payment->note }}</div>@endif

    <div class="ps-sign">
        <div>{{ sm_t('Receiver\'s Signature') }}</div>
        <div>{{ sm_t('Accountant') }}</div>
        <div>{{ sm_t('Head Teacher / Principal') }}</div>
    </div>
</div>
@endforeach
@endsection
