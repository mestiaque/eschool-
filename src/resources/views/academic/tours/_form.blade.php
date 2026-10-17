<div class="modal-body">
    <div class="row">
        <div class="col-md-8 mb-2">
            <label class="form-label">{{ sm_t('Title') }} <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label">{{ sm_t('Status') }}</label>
            <select name="status" class="form-control form-control-sm">
                <option value="planning">{{ sm_t('Planning') }}</option>
                <option value="approved">{{ sm_t('Approved') }}</option>
                <option value="ongoing">{{ sm_t('Ongoing') }}</option>
                <option value="completed">{{ sm_t('Completed') }}</option>
                <option value="cancelled">{{ sm_t('Cancelled') }}</option>
            </select>
        </div>
    </div>
    <div class="mb-2">
        <label class="form-label">{{ sm_t('Destination') }} <span class="text-danger">*</span></label>
        <input type="text" name="destination" class="form-control form-control-sm" required>
    </div>
    <div class="mb-2">
        <label class="form-label">{{ sm_t('Description') }}</label>
        <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
    </div>
    <div class="row">
        <div class="col-md-6 mb-2">
            <label class="form-label">{{ sm_t('Start Date') }} <span class="text-danger">*</span></label>
            <input type="date" name="start_date" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label">{{ sm_t('End Date') }} <span class="text-danger">*</span></label>
            <input type="date" name="end_date" class="form-control form-control-sm" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-2">
            <label class="form-label">Budget ({{ sm_currency() }})</label>
            <input type="number" name="budget" class="form-control form-control-sm" step="0.01" min="0">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label">{{ sm_t('Organizer') }}</label>
            <input type="text" name="organizer" class="form-control form-control-sm">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label">{{ sm_t('Contact Phone') }}</label>
            <input type="text" name="contact_phone" class="form-control form-control-sm">
        </div>
    </div>
</div>
