@extends('me::master')
@section('title', sm_t('Transport Assignments'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#assignModal"><i class="fas fa-plus"></i> {{ sm_t('Assign Vehicle') }}</button>
@endpush

@section('content')

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-3">
                <select name="route_id" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Routes') }}</option>
                    @foreach($routes as $route)
                    <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>{{ $route->route_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Filter') }}</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>{{ sm_t('Vehicle') }}</th><th>{{ sm_t('Driver') }}</th><th>{{ sm_t('Route') }}</th><th>{{ sm_t('Status') }}</th><th>{{ sm_t('Action') }}</th></tr>
            </thead>
            <tbody>
                @forelse($assignments as $i => $a)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $a->vehicle?->vehicle_number }}</td>
                    <td>{{ $a->vehicle?->driver_name ?? '-' }}</td>
                    <td>{{ $a->route?->route_name }}</td>
                    <td><span class="badge bg-{{ $bc = $a->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t($a->is_active ? 'Active' : 'Inactive') }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('transport.assignments.destroy', $a->id) }}" class="d-inline" onsubmit="return confirm('Remove?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">{{ sm_t('No transport assignments') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $assignments->links('pagination::bootstrap-5') }}</div>

<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('transport.assignments.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ sm_t('Assign Vehicle to Route') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Vehicle') }} *</label>
                        <select name="vehicle_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Vehicle') }}</option>
                            @foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->vehicle_number }}{{ $v->driver_name ? ' - '.$v->driver_name : '' }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Route') }} *</label>
                        <select name="route_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Route') }}</option>
                            @foreach($routes as $r)<option value="{{ $r->id }}">{{ $r->route_name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Assign') }}</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
