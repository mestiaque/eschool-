@extends('me::master')

@section('title', sm_t('Inventory Products'))

@push('buttons')
<button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#productModal">
    <i class="fas fa-plus"></i> Add Product
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
                                    <th>{{ sm_t('Item Code') }}</th>
                                    <th>{{ sm_t('Name') }}</th>
                                    <th>{{ sm_t('Category') }}</th>
                                    <th>{{ sm_t('Unit') }}</th>
                                    <th>{{ sm_t('Price') }}</th>
                                    <th>{{ sm_t('Stock') }}</th>
                                    <th>{{ sm_t('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td>{{ $product->id }}</td>
                                        <td>{{ $product->item_code }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                                        <td>{{ $product->unit }}</td>
                                        <td>{{ $product->price }}</td>
                                        <td>{{ $product->quantity }}</td>
                                        <td>
                                            @if($product->is_active)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No products found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('inventory.products') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Add Product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Item Code') }} <span class="text-danger">*</span></label>
                        <input type="text" name="item_code" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Category') }}</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">{{ sm_t('Select Category') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Unit') }}</label>
                        <input type="text" name="unit" class="form-control form-control-sm" placeholder="pcs, kg, etc">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Price') }}</label>
                        <input type="number" name="price" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Quantity') }}</label>
                        <input type="number" name="quantity" class="form-control form-control-sm">
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
