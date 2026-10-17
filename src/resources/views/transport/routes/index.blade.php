@extends('me::master')
@section('title', sm_t('Transport Routes'))
@push('buttons')
    <a href="{{ route('transport.schedule') }}" class="btn btn-sm btn-encodex-show">
        <i class="fas fa-clock"></i> Schedule Chart
    </a>
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#routeModal">
        <i class="fas fa-plus"></i> Add Route
    </button>
@endpush

@section('content')


@php
$directions = \ME\SchoolManagement\Models\RouteSchedule::directions();
$allDays    = \ME\SchoolManagement\Models\RouteSchedule::allDays();
$dirColors  = ['morning'=>'warning','afternoon'=>'info','evening'=>'success','night'=>'dark'];
@endphp

@forelse($routes as $route)
<div class="card shadow mb-3">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <div>
            <h6 class="mb-0 d-inline">
                <i class="fas fa-route me-1 text-primary"></i>{{ $route->route_name }}
            </h6>
            <span class="badge bg-{{ $bc = $route->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }} ms-2">{{ $route->is_active ? 'Active' : 'Inactive' }}</span>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="text-muted small">
                <i class="fas fa-map-marker-alt me-1"></i>{{ $route->start_point }}
                <i class="fas fa-arrow-right mx-1"></i>{{ $route->end_point }}
                @if($route->distance) &nbsp;({{ $route->distance }} km)@endif
            </span>
            <span class="badge bg-primary-subtle text-primary">{{ sm_money($route->fee, 0) }}</span>
            <button class="btn btn-sm btn-encodex-edit"
                onclick="openEditRoute({{ $route->id }}, {{ json_encode($route) }})">
                <i class="fas fa-edit"></i>
            </button>
            <form method="POST" action="{{ route('transport.routes.update', $route->id) }}" class="d-none" id="delRoute{{ $route->id }}">
                @csrf @method('DELETE')
            </form>
            <button type="button" class="btn btn-sm btn-encodex-delete"
                onclick="if(confirm('Delete route?')) document.getElementById('delRoute{{ $route->id }}').action='{{ route('transport.routes.destroy', $route->id) }}', document.getElementById('delRoute{{ $route->id }}').submit()">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="row g-0">
            {{-- Schedule List --}}
            <div class="col-md-8 border-end">
                <div class="p-2 border-bottom d-flex justify-content-between align-items-center bg-light">
                    <small class="fw-semibold text-muted"><i class="fas fa-clock me-1"></i> Schedules ({{ $route->schedules->count() }})</small>
                    <button class="btn btn-sm btn-encodex-create"
                        data-bs-toggle="collapse" data-bs-target="#addSched{{ $route->id }}">
                        <i class="fas fa-plus"></i> Add Schedule
                    </button>
                </div>

                @if($route->schedules->isNotEmpty())
                <table class="table table-sm table-bordered table-encodex mb-0">
                    <thead class="text-center" style="font-size:12px">
                        <tr>
                            <th>{{ sm_t('Direction') }}</th>
                            <th>{{ sm_t('Departure') }}</th>
                            <th>{{ sm_t('Arrival') }}</th>
                            <th>{{ sm_t('Vehicle / Bus') }}</th>
                            <th>{{ sm_t('Days') }}</th>
                            <th>{{ sm_t('Notes') }}</th>
                            <th>{{ sm_t('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($route->schedules as $sched)
                        <tr class="text-center" style="font-size:12px">
                            <td>
                                <span class="badge bg-{{ $bc = $dirColors[$sched->direction] ?? 'secondary' }}-subtle text-{{ $bc }}">
                                    {{ $directions[$sched->direction] ?? $sched->direction }}
                                </span>
                            </td>
                            <td><strong class="text-primary">{{ \Carbon\Carbon::parse($sched->departure_time)->format('h:i A') }}</strong></td>
                            <td>{{ $sched->arrival_time ? \Carbon\Carbon::parse($sched->arrival_time)->format('h:i A') : '—' }}</td>
                            <td>
                                @if($sched->vehicle)
                                    <span class="badge bg-light-subtle text-dark border">{{ $sched->vehicle->vehicle_number }}</span>
                                    @if($sched->vehicle->driver_name)
                                    <br><small class="text-muted">{{ $sched->vehicle->driver_name }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if(empty($sched->days))
                                    <span class="text-muted small">Every day</span>
                                @else
                                    @foreach($sched->days as $d)
                                    <span class="badge bg-light-subtle text-dark border" style="font-size:10px">{{ $allDays[$d] ?? $d }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td class="text-start small text-muted">{{ $sched->notes ?? '—' }}</td>
                            <td>
                                <button class="btn btn-sm btn-encodex-edit"
                                    onclick="openEditSched({{ $sched->id }}, {{ $sched->route_id }}, {{ json_encode($sched) }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('transport.schedules.destroy', $sched->id) }}"
                                    class="d-inline" onsubmit="return confirm('Delete schedule?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center text-muted py-3 small">No schedules yet. Click "Add Schedule" to add.</div>
                @endif

                {{-- Inline Add Schedule Form --}}
                <div class="collapse" id="addSched{{ $route->id }}">
                    <div class="p-3 bg-light border-top">
                        <form method="POST" action="{{ route('transport.schedules.store') }}">
                            @csrf
                            <input type="hidden" name="route_id" value="{{ $route->id }}">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">Direction *</label>
                                    <select name="direction" class="form-control form-control-sm" required>
                                        @foreach($directions as $dk => $dv)
                                        <option value="{{ $dk }}">{{ $dv }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">Departure *</label>
                                    <input type="time" name="departure_time" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">{{ sm_t('Arrival') }}</label>
                                    <input type="time" name="arrival_time" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">{{ sm_t('Vehicle / Bus') }}</label>
                                    <select name="vehicle_id" class="form-control form-control-sm">
                                        <option value="">— Select —</option>
                                        @foreach($vehicles as $v)
                                        <option value="{{ $v->id }}">{{ $v->vehicle_number }}{{ $v->driver_name ? ' ('.$v->driver_name.')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">{{ sm_t('Notes') }}</label>
                                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-sm btn-encodex-save w-100">
                                        <i class="fas fa-save me-1"></i> Save
                                    </button>
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm">Days (blank = every day)</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        @foreach($allDays as $dk => $dv)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="days[]" value="{{ $dk }}" id="day_{{ $route->id }}_{{ $dk }}">
                                            <label class="form-check-label small" for="day_{{ $route->id }}_{{ $dk }}">{{ $dv }}</label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Stops --}}
            <div class="col-md-4">
                <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <small class="fw-semibold text-muted"><i class="fas fa-map-pin me-1"></i> Stops ({{ $route->stops->count() }})</small>
                </div>
                @if($route->stops->isNotEmpty())
                <div class="p-2">
                    @foreach($route->stops->sortBy('arrival_time') as $stop)
                    <div class="d-flex align-items-center justify-content-between border-bottom py-1" style="font-size:12px">
                        <span><i class="fas fa-circle text-primary me-1" style="font-size:8px"></i>{{ $stop->stop_name }}</span>
                        <div class="text-end text-muted">
                            @if($stop->arrival_time)<span>{{ \Carbon\Carbon::parse($stop->arrival_time)->format('h:i A') }}</span>@endif
                            @if($stop->extra_fee) <span class="badge bg-light-subtle text-dark border ms-1">+{{ sm_currency() }}{{ $stop->extra_fee }}</span>@endif
                            <form method="POST" action="{{ route('transport.stops.destroy', $stop->id) }}" class="d-inline" onsubmit="return confirm('Remove stop?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-encodex-delete ms-1"><i class="fas fa-times"></i></button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-2 small">No stops</div>
                @endif
                {{-- Add Stop --}}
                <div class="p-2 border-top">
                    <form method="POST" action="{{ route('transport.stops.store') }}" class="d-flex gap-1">
                        @csrf
                        <input type="hidden" name="route_id" value="{{ $route->id }}">
                        <input type="text" name="stop_name" class="form-control form-control-sm" placeholder="Stop name" required style="font-size:12px">
                        <input type="time" name="arrival_time" class="form-control form-control-sm" style="font-size:12px;width:110px">
                        <button type="submit" class="btn btn-sm btn-encodex-save flex-shrink-0"><i class="fas fa-plus"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="alert alert-info">No routes found. Add a route to get started.</div>
@endforelse
<div class="mt-2">{{ $routes->links('pagination::bootstrap-5') }}</div>

{{-- Add Route Modal --}}
<div class="modal fade" id="routeModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('Add Route') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('transport.routes.store') }}">
                @csrf
                <div class="modal-body">
                    @include('school-management::transport.routes._form')
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save Route') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Route Modal --}}
<div class="modal fade" id="editRouteModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('Edit Route') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editRouteForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    @include('school-management::transport.routes._form')
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Schedule Modal --}}
<div class="modal fade" id="editSchedModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">{{ sm_t('Edit Schedule') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editSchedForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">Direction *</label>
                            <select name="direction" id="es_direction" class="form-control form-control-sm" required>
                                @foreach($directions as $dk => $dv)
                                <option value="{{ $dk }}">{{ $dv }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Vehicle / Bus') }}</label>
                            <select name="vehicle_id" id="es_vehicle" class="form-control form-control-sm">
                                <option value="">— Select —</option>
                                @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">{{ $v->vehicle_number }}{{ $v->driver_name ? ' ('.$v->driver_name.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">Departure *</label>
                            <input type="time" name="departure_time" id="es_dep" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Arrival') }}</label>
                            <input type="time" name="arrival_time" id="es_arr" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Days') }}</label>
                            <div class="d-flex gap-2 flex-wrap" id="es_days_wrap">
                                @foreach($allDays as $dk => $dv)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input es-day" type="checkbox" name="days[]" value="{{ $dk }}" id="es_day_{{ $dk }}">
                                    <label class="form-check-label small" for="es_day_{{ $dk }}">{{ $dv }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Notes') }}</label>
                            <input type="text" name="notes" id="es_notes" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditRoute(id, r) {
    const form = document.getElementById('editRouteForm');
    form.action = '/transport/routes/' + id;
    const f = (n, v) => { const el = form.querySelector('[name="'+n+'"]'); if(el) el.value = v ?? ''; };
    f('route_name', r.route_name); f('start_point', r.start_point); f('end_point', r.end_point);
    f('distance', r.distance); f('fee', r.fee);
    const act = form.querySelector('[name="is_active"]');
    if (act) act.checked = !!r.is_active;
    new bootstrap.Modal(document.getElementById('editRouteModal')).show();
}

function openEditSched(id, routeId, s) {
    const form = document.getElementById('editSchedForm');
    form.action = '/transport/schedules/' + id;
    document.getElementById('es_direction').value = s.direction ?? 'morning';
    document.getElementById('es_vehicle').value   = s.vehicle_id ?? '';
    document.getElementById('es_dep').value       = s.departure_time ?? '';
    document.getElementById('es_arr').value       = s.arrival_time ?? '';
    document.getElementById('es_notes').value     = s.notes ?? '';
    // days checkboxes
    const days = s.days ?? [];
    document.querySelectorAll('.es-day').forEach(cb => {
        cb.checked = Array.isArray(days) && days.includes(cb.value);
    });
    new bootstrap.Modal(document.getElementById('editSchedModal')).show();
}
</script>
@endsection
