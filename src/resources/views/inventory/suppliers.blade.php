@extends('me::master')

@section('title', sm_t('Supplier Management'))

@push('buttons')
<button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#supplierModal">
    <i class="fas fa-plus"></i> {{ sm_t('Add Supplier') }}
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
                                    <th>{{ sm_t('Name') }}</th>
                                    <th>{{ sm_t('Company') }}</th>
                                    <th>{{ sm_t('Email') }}</th>
                                    <th>{{ sm_t('Phone') }}</th>
                                    <th>{{ sm_t('Balance') }}</th>
                                    <th>{{ sm_t('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $supplier)
                                    <tr>
                                        <td>{{ $supplier->id }}</td>
                                        <td>{{ $supplier->name }}</td>
                                        <td>{{ $supplier->company_name }}</td>
                                        <td>{{ $supplier->email }}</td>
                                        <td>{{ $supplier->phone }}</td>
                                        <td>{{ $supplier->balance }}</td>
                                        <td>
                                            @if($supplier->is_active)
                                                <span class="badge bg-success-subtle text-success">{{ sm_t('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">{{ sm_t('Inactive') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ sm_t('No suppliers found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $suppliers->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('inventory.suppliers') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Add Supplier') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Company Name') }}</label>
                        <input type="text" name="company_name" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Email') }}</label>
                        <input type="email" name="email" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Phone') }}</label>
                        <input type="text" name="phone" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Address') }}</label>
                        <textarea name="address" class="form-control form-control-sm" rows="2"></textarea>
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
