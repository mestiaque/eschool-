<div class="row g-2">
    <div class="col-12 mb-2">
        <label class="form-label form-label-sm">{{ sm_t('Route Name') }} <span class="text-danger">*</span></label>
        <input type="text" name="route_name" class="form-control form-control-sm" required placeholder="e.g. Dhaka - Mirpur Route">
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label form-label-sm">{{ sm_t('Start Point') }} <span class="text-danger">*</span></label>
        <input type="text" name="start_point" class="form-control form-control-sm" required placeholder="Starting location">
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label form-label-sm">{{ sm_t('End Point') }} <span class="text-danger">*</span></label>
        <input type="text" name="end_point" class="form-control form-control-sm" required placeholder="Destination">
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label form-label-sm">{{ sm_t('Distance (km)') }}</label>
        <input type="number" name="distance" class="form-control form-control-sm" step="0.01" min="0">
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label form-label-sm">Fee ({{ sm_currency() }}) <span class="text-danger">*</span></label>
        <input type="number" name="fee" class="form-control form-control-sm" step="0.01" min="0" required>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="routeActive">
            <label class="form-check-label" for="routeActive">{{ sm_t('Active') }}</label>
        </div>
    </div>
</div>
