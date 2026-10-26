{{-- Transport route + pick-up stop for a student (stops filtered by the chosen route). --}}
@php
    $currentRoute = old('transport_route_id', $student->transport_route_id ?? null);
    $currentStop  = old('route_stop_id', $student->route_stop_id ?? null);
    $uid = 'tr' . uniqid();
@endphp
<div class="col-md-3 mb-1">
    <label class="form-label form-label-sm">{{ sm_t('Transport Route') }}</label>
    <select name="transport_route_id" id="{{ $uid }}Route" class="form-select form-select-sm">
        <option value="">{{ sm_t('No transport') }}</option>
        @foreach($transportRoutes as $r)
        <option value="{{ $r->id }}" {{ (string) $currentRoute === (string) $r->id ? 'selected' : '' }}>{{ $r->route_name }} ({{ sm_money($r->fee) }})</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 mb-1">
    <label class="form-label form-label-sm">{{ sm_t('Pick-up Stop') }}</label>
    <select name="route_stop_id" id="{{ $uid }}Stop" class="form-select form-select-sm" data-current="{{ $currentStop }}">
        <option value="">—</option>
    </select>
</div>
@push('scripts')
<script>
(function () {
    const stops = @json($transportRoutes->mapWithKeys(fn ($r) => [$r->id => $r->stops->map(fn ($s) => ['id' => $s->id, 'name' => $s->stop_name, 'extra' => (float) $s->extra_fee])]));
    const route = document.getElementById(@json($uid . 'Route')), stop = document.getElementById(@json($uid . 'Stop'));
    const money = @json(sm_currency());
    function fill() {
        const list = stops[route.value] || [];
        stop.innerHTML = '<option value="">—</option>' + list.map(s =>
            `<option value="${s.id}" ${String(s.id) === stop.dataset.current ? 'selected' : ''}>${s.name}${s.extra ? ' (+' + money + s.extra + ')' : ''}</option>`).join('');
    }
    route.addEventListener('change', () => { stop.dataset.current = ''; fill(); });
    fill();
})();
</script>
@endpush
