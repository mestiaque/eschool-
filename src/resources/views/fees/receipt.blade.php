@extends('me::printMaster2')
@section('title', ($payment ? sm_t('Money Receipt') : sm_t('Invoice')) . ' — ' . $collection->invoice_no)

@php
    $student = $collection->student;
    $paidNow = $payment ? (float) $payment->amount : null;
    $paidTotal = $payment ? $before + $paidNow : (float) $payments->sum('amount');
    $due = max(0, $payable - $paidTotal);
    $copies = $payment ? [sm_t('Student Copy'), sm_t('Office Copy')] : [null];
    $period = trim(($collection->monthLabel() ?? '') . ' ' . ($collection->year ?? ''));
@endphp

@push('css')
<style>
    .rc-copy { border: 1px solid #333; padding: 10px 14px; margin-bottom: 10px; page-break-inside: avoid; }
    .rc-copy + .rc-copy { border-top: 1px dashed #333; }
    .rc-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .rc-top .rc-label { border: 1.5px solid #000; padding: 2px 10px; font-weight: bold; font-size: 13px; }
    .rc-top .rc-copyname { font-size: 11px; color: #555; }
    .rc-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 2px 20px; font-size: 12px; margin: 8px 0; }
    .rc-meta div span { color: #555; }
    table.rc-lines { margin-bottom: 6px; }
    table.rc-lines td, table.rc-lines th { padding: 4px 8px; font-size: 12px; }
    table.rc-lines td.amt, table.rc-lines th.amt { text-align: right; white-space: nowrap; }
    table.rc-lines tr.now td { font-weight: bold; background: #eef6ee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    table.rc-lines tr.due td { font-weight: bold; }
    .rc-words { font-size: 12px; margin-bottom: 6px; }
    .rc-foot { display: flex; justify-content: space-between; align-items: flex-end; font-size: 11px; margin-top: 26px; }
    .rc-foot .sig { min-width: 150px; text-align: center; border-top: 1px solid #000; padding-top: 3px; }
    .rc-stamp { font-size: 22px; font-weight: bold; letter-spacing: 3px; border: 3px solid; padding: 0 10px; transform: rotate(-8deg); opacity: .6; }
    .rc-stamp.paid { color: #146c43; }
    .rc-stamp.due { color: #b02a37; }
    @media print { @page { size: A4 portrait; margin: 8mm; } .print-header { margin-bottom: 4px; } }
</style>
@endpush

@section('contents')
@foreach($copies as $copyName)
<div class="rc-copy">
    @include('school-management::partials.print-header', ['title' => $payment ? sm_t('Money Receipt') : sm_t('Invoice')])

    <div class="rc-top">
        <div>
            @if($payment)
            <div class="rc-label">{{ sm_t('Receipt No') }}: MR-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
            @else
            <div class="rc-label">{{ sm_t('Invoice No') }}: {{ $collection->invoice_no }}</div>
            @endif
        </div>
        @if($copyName)<div class="rc-copyname">{{ $copyName }}</div>@endif
    </div>

    <div class="rc-meta">
        <div><span>{{ sm_t('Student') }}:</span> <strong>{{ $student?->full_name ?? '—' }}</strong></div>
        <div><span>{{ sm_t('Date') }}:</span> {{ sm_date($payment?->date ?? now()) }}</div>
        <div><span>{{ sm_t('Student ID') }}:</span> {{ $student?->student_id ?? '—' }}</div>
        <div><span>{{ sm_t('Invoice No') }}:</span> {{ $collection->invoice_no }}</div>
        <div><span>{{ sm_t('Class') }}:</span> {{ $student?->classe?->name ?? '—' }}@if($student?->section) / {{ $student->section->name }}@endif @if($student?->roll_number) &nbsp;|&nbsp; {{ sm_t('Roll') }}: {{ $student->roll_number }}@endif</div>
        <div><span>{{ sm_t('Fee Type') }}:</span> {{ $collection->feeType?->name ?? '—' }}@if($period) ({{ $period }})@endif</div>
    </div>

    <table class="rc-lines">
        <tbody>
            <tr><td>{{ sm_t('Fee Amount') }}</td><td class="amt">{{ sm_money($collection->amount, 2) }}</td></tr>
            @if((float) $collection->fine > 0)<tr><td>{{ sm_t('Fine') }}</td><td class="amt">+ {{ sm_money($collection->fine, 2) }}</td></tr>@endif
            @if((float) $collection->discount > 0)<tr><td>{{ sm_t('Discount') }}</td><td class="amt">− {{ sm_money($collection->discount, 2) }}</td></tr>@endif
            <tr><td><strong>{{ sm_t('Total Payable') }}</strong></td><td class="amt"><strong>{{ sm_money($payable, 2) }}</strong></td></tr>
            @if($payment)
                @if($before > 0)<tr><td>{{ sm_t('Paid Before') }}</td><td class="amt">{{ sm_money($before, 2) }}</td></tr>@endif
                <tr class="now"><td>{{ sm_t('Paid Now') }}@if($payment->payment_method) ({{ sm_t(ucfirst($payment->payment_method)) }}@if($payment->transaction_id), {{ $payment->transaction_id }}@endif)@endif</td><td class="amt">{{ sm_money($paidNow, 2) }}</td></tr>
            @else
                @foreach($payments as $p)
                <tr><td>{{ sm_t('Paid') }} — {{ sm_date($p->date) }}@if($p->payment_method) ({{ sm_t(ucfirst($p->payment_method)) }})@endif · MR-{{ str_pad($p->id, 6, '0', STR_PAD_LEFT) }}</td><td class="amt">{{ sm_money($p->amount, 2) }}</td></tr>
                @endforeach
            @endif
            <tr class="due"><td>{{ sm_t('Due') }}</td><td class="amt">{{ sm_money($due, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="rc-words"><strong>{{ sm_t('In words') }}:</strong> {{ sm_amount_words($payment ? $paidNow : $paidTotal) }}</div>

    <div class="rc-foot">
        <div>
            @if($payment && ($name = $collectors[$payment->collected_by] ?? null)){{ sm_t('Collected by') }}: {{ $name }}@endif
        </div>
        <div class="rc-stamp {{ $due > 0 ? 'due' : 'paid' }}">{{ $due > 0 ? sm_t('DUE') : sm_t('PAID') }}</div>
        <div class="sig">{{ sm_t('Authorised Signature') }}</div>
    </div>
</div>
@endforeach
@endsection
