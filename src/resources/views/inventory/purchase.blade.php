@extends('me::master')

@section('title', sm_t('Purchase Management'))
@push('buttons')
    <button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#purchaseModal">
        <i class="fas fa-plus"></i> New Purchase
    </button>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <p class="text-muted small mb-2">Saving a purchase adds every item's quantity to stock. Deleting it takes that stock back out.</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-encodex align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ sm_t('Invoice No') }}</th>
                            <th>{{ sm_t('Supplier') }}</th>
                            <th>{{ sm_t('Items') }}</th>
                            <th class="text-end">{{ sm_t('Total') }}</th>
                            <th class="text-end">{{ sm_t('Paid') }}</th>
                            <th class="text-end">{{ sm_t('Due') }}</th>
                            <th>{{ sm_t('Date') }}</th>
                            <th>{{ sm_t('Payment') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        @php
                            $pay = $purchase->due_amount <= 0 ? ['Paid', 'success'] : ($purchase->paid_amount > 0 ? ['Partial', 'warning'] : ['Due', 'danger']);
                        @endphp
                        <tr>
                            <td>{{ $purchase->invoice_no }}</td>
                            <td>{{ $purchase->supplier->name ?? '—' }}</td>
                            <td class="small">
                                @forelse($purchase->items as $item)
                                    <div>{{ $item->product->name ?? 'Item #' . $item->product_id }} × {{ $item->quantity }}</div>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td class="text-end">{{ sm_money($purchase->total_amount, 2) }}</td>
                            <td class="text-end">{{ sm_money($purchase->paid_amount, 2) }}</td>
                            <td class="text-end">{{ sm_money($purchase->due_amount, 2) }}</td>
                            <td>{{ sm_date($purchase->purchase_date) }}</td>
                            <td><span class="badge bg-{{ $pay[1] }}-subtle text-{{ $pay[1] }}">{{ $pay[0] }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('inventory.purchase.destroy', $purchase->id) }}" onsubmit="return confirm('Delete this purchase and remove its items from stock?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-encodex-delete btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center">No purchases found</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $purchases->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Purchase Modal -->
<div class="modal fade" id="purchaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <form method="POST" action="{{ route('inventory.purchase.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('New Purchase') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Invoice No') }} <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" class="form-control form-control-sm" value="{{ old('invoice_no') }}" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Supplier') }}</label>
                            <select name="supplier_id" class="form-select form-select-sm">
                                <option value="">{{ sm_t('Select Supplier') }}</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">{{ sm_t('Purchase Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <table class="table table-sm align-middle mb-2" id="itemsTable">
                        <thead><tr><th>{{ sm_t('Product') }}</th><th style="width:110px">{{ sm_t('Qty') }}</th><th style="width:140px">{{ sm_t('Unit price') }}</th><th style="width:120px" class="text-end">{{ sm_t('Line total') }}</th><th style="width:40px"></th></tr></thead>
                        <tbody></tbody>
                    </table>
                    <button type="button" class="btn btn-encodex-create btn-sm" id="addItem"><i class="fas fa-plus"></i> Add item</button>

                    <div class="row g-2 mt-2">
                        <div class="col-md-4 ms-auto">
                            <div class="d-flex justify-content-between small"><span>Total</span><strong id="purchaseTotal">{{ sm_money(0, 2) }}</strong></div>
                            <label class="form-label mt-2">{{ sm_t('Paid now') }}</label>
                            <input type="number" name="paid_amount" class="form-control form-control-sm" min="0" step="0.01" value="0">
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label">{{ sm_t('Note') }}</label>
                        <textarea name="note" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-encodex-cancel btn-sm" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Save & add to stock') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<template id="itemRow">
    <tr>
        <td>
            <select name="items[__i__][product_id]" class="form-select form-select-sm item-product" required>
                <option value="">{{ sm_t('Select product') }}</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }} ({{ $product->item_code }})</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="items[__i__][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required></td>
        <td><input type="number" name="items[__i__][unit_price]" class="form-control form-control-sm item-price" min="0" step="0.01" value="0" required></td>
        <td class="text-end item-total">0.00</td>
        <td><button type="button" class="btn btn-sm btn-encodex-delete item-remove"><i class="fas fa-times"></i></button></td>
    </tr>
</template>

@push('scripts')
<script>
(function () {
    const body = document.querySelector('#itemsTable tbody');
    const tpl = document.getElementById('itemRow').innerHTML;
    const currency = @json(sm_currency());
    let i = 0;
    const recalc = () => {
        let total = 0;
        body.querySelectorAll('tr').forEach(tr => {
            const line = (parseFloat(tr.querySelector('.item-qty').value) || 0) * (parseFloat(tr.querySelector('.item-price').value) || 0);
            tr.querySelector('.item-total').textContent = line.toFixed(2);
            total += line;
        });
        document.getElementById('purchaseTotal').textContent = currency + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    const addRow = () => {
        body.insertAdjacentHTML('beforeend', tpl.replaceAll('__i__', i++));
        recalc();
    };
    document.getElementById('addItem').addEventListener('click', addRow);
    body.addEventListener('input', recalc);
    body.addEventListener('change', e => {
        if (e.target.classList.contains('item-product')) {
            const price = e.target.selectedOptions[0]?.dataset.price;
            if (price) e.target.closest('tr').querySelector('.item-price').value = price;
            recalc();
        }
    });
    body.addEventListener('click', e => {
        if (e.target.closest('.item-remove') && body.children.length > 1) { e.target.closest('tr').remove(); recalc(); }
    });
    addRow();
})();
</script>
@endpush
@endsection
