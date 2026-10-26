@extends('me::master')
@section('title', sm_t('Vehicles'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#vehicleModal">
    <i class="fas fa-plus"></i> {{ sm_t('Add Vehicle') }}
</button>
@endpush

@section('content')
<div class="card">
    <div class="card-body p-0">
        <table class="table table-encodex table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ sm_t('Number') }}</th>
                    <th>{{ sm_t('Model') }}</th>
                    <th>{{ sm_t('Type') }}</th>
                    <th>{{ sm_t('Capacity') }}</th>
                    <th>{{ sm_t('Driver') }}</th>
                    <th>{{ sm_t('Phone') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vehicles as $vehicle)
                <tr>
                    <td>{{ $vehicle->vehicle_number }}</td>
                    <td>{{ $vehicle->model ?? '-' }}</td>
                    <td>{{ sm_t(ucfirst($vehicle->type)) }}</td>
                    <td>{{ $vehicle->capacity }}</td>
                    <td>{{ $vehicle->driver_name ?? '-' }}</td>
                    <td>{{ $vehicle->driver_phone ?? '-' }}</td>
                    <td>
                        @if($vehicle->is_active)
                        <span class="badge bg-success-subtle text-success">{{ sm_t('Active') }}</span>
                        @else
                        <span class="badge bg-danger-subtle text-danger">{{ sm_t('Inactive') }}</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-encodex-edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $vehicles->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Add Vehicle') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('transport.vehicles') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Vehicle Number') }} *</label>
                        <input type="text" name="vehicle_number" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Model') }}</label>
                        <input type="text" name="model" class="form-control form-control-sm">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Type') }} *</label>
                            <select name="type" class="form-control form-control-sm" required>
                                <option value="bus">{{ sm_t('Bus') }}</option>
                                <option value="van">{{ sm_t('Van') }}</option>
                                <option value="car">{{ sm_t('Car') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Capacity') }} *</label>
                            <input type="number" name="capacity" class="form-control form-control-sm" min="1" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Driver Name') }}</label>
                        <input type="text" name="driver_name" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Driver Phone') }}</label>
                        <input type="text" name="driver_phone" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Driver License') }}</label>
                        <input type="text" name="driver_license" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
