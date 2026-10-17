@extends('me::master')

@section('title', sm_t('Stock Management'))

@push('buttons')
<button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#stockModal">
    <i class="fas fa-plus"></i> Add Stock
</button>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-encodex table-sm table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ sm_t('ID') }}</th>
                                    <th>{{ sm_t('Product') }}</th>
                                    <th>{{ sm_t('Type') }}</th>
                                    <th>{{ sm_t('Quantity') }}</th>
                                    <th>{{ sm_t('Date') }}</th>
                                    <th>{{ sm_t('Note') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stocks as $stock)
                                    <tr>
                                        <td>{{ $stock->id }}</td>
                                        <td>{{ $stock->product->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($stock->type == 'in')
                                                <span class="badge bg-success-subtle text-success">In</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Out</span>
                                            @endif
                                        </td>
                                        <td>{{ $stock->quantity }}</td>
                                        <td>{{ $stock->date }}</td>
                                        <td>{{ $stock->note }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No stock records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $stocks->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('inventory.stock') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Add Stock') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Product') }} <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select form-select-sm" required>
                            <option value="">{{ sm_t('Select Product') }}</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->item_code }}) — in stock: {{ $product->quantity ?? 0 }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Type') }}</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="in">{{ sm_t('Stock In') }}</option>
                            <option value="out">{{ sm_t('Stock Out') }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Quantity') }} <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control form-control-sm" min="1" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Note') }}</label>
                        <textarea name="note" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-encodex-cancel btn-sm" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
