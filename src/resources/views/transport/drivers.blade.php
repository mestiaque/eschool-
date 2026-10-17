@extends('me::master')

@section('title', sm_t('Drivers'))

@push('buttons')
<button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#driverModal">
    <i class="fas fa-plus"></i> Add Driver
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
                                    <th>{{ sm_t('Vehicle') }}</th>
                                    <th>{{ sm_t('Driver Name') }}</th>
                                    <th>{{ sm_t('Phone') }}</th>
                                    <th>{{ sm_t('License') }}</th>
                                    <th>{{ sm_t('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($drivers ?? [] as $driver)
                                    <tr>
                                        <td>{{ $driver->id }}</td>
                                        <td>{{ $driver->vehicle_number }}</td>
                                        <td>{{ $driver->driver_name }}</td>
                                        <td>{{ $driver->driver_phone }}</td>
                                        <td>{{ $driver->driver_license }}</td>
                                        <td>
                                            <button class="btn btn-encodex-edit btn-sm" onclick="editDriver({{ $driver->id }}, '{{ addslashes($driver->driver_name) }}', '{{ addslashes($driver->driver_phone ?? '') }}', '{{ addslashes($driver->driver_license ?? '') }}')"><i class="fas fa-edit"></i></button>
                                            <form method="POST" action="{{ route('transport.drivers.destroy', $driver->id) }}" class="d-inline" onsubmit="return confirm('Remove this driver?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-encodex-delete btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No drivers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Driver Modal -->
<div class="modal fade" id="driverModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" id="driverForm" action="{{ route('transport.drivers.store') }}">
            @csrf
            <input type="hidden" name="_method" id="driverMethod" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="driverModalTitle">{{ sm_t('Add Driver') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2" id="driverVehicleField">
                        <label class="form-label">{{ sm_t('Vehicle') }}</label>
                        <select name="vehicle_id" id="driverVehicle" class="form-select form-select-sm" required>
                            <option value="">{{ sm_t('Select Vehicle') }}</option>
                            @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->vehicle_number }}{{ $v->model ? ' — ' . $v->model : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Driver Name') }}</label>
                        <input type="text" name="driver_name" id="driverName" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Phone') }}</label>
                        <input type="text" name="driver_phone" id="driverPhone" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('License Number') }}</label>
                        <input type="text" name="driver_license" id="driverLicense" class="form-control form-control-sm" required>
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
<script>
function editDriver(id, name, phone, license) {
    document.getElementById('driverModalTitle').textContent = 'Edit Driver';
    document.getElementById('driverForm').action = '/transport/drivers/' + id;
    document.getElementById('driverMethod').value = 'PUT';
    document.getElementById('driverVehicleField').style.display = 'none';
    document.getElementById('driverVehicle').required = false;
    document.getElementById('driverName').value = name;
    document.getElementById('driverPhone').value = phone;
    document.getElementById('driverLicense').value = license;
    new bootstrap.Modal(document.getElementById('driverModal')).show();
}
document.getElementById('driverModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('driverModalTitle').textContent = 'Add Driver';
    document.getElementById('driverForm').action = '{{ route('transport.drivers.store') }}';
    document.getElementById('driverMethod').value = 'POST';
    document.getElementById('driverVehicleField').style.display = '';
    document.getElementById('driverVehicle').required = true;
    document.getElementById('driverForm').reset();
});
</script>
@endsection
