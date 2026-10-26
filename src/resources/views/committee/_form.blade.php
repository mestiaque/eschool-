<div class="modal-body">
    <div class="mb-2">
        <label class="form-label form-label-sm">{{ sm_t('Committee Name') }} <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control form-control-sm" required placeholder="{{ sm_t('e.g. পরিচালনা কমিটি ২০২৪') }}">
    </div>
    <div class="row g-2">
        <div class="col-md-6 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Academic Year') }}</label>
            <input type="text" name="academic_year" class="form-control form-control-sm" placeholder="{{ sm_t('e.g. 2024') }}">
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Formed Date') }}</label>
            <input type="date" name="formed_date" class="form-control form-control-sm">
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Expiry Date') }}</label>
            <input type="date" name="expiry_date" class="form-control form-control-sm">
        </div>
        <div class="col-md-6 mb-2 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="committeeActive">
                <label class="form-check-label" for="committeeActive">{{ sm_t('Active Committee') }}</label>
            </div>
        </div>
    </div>
    <div class="mb-2">
        <label class="form-label form-label-sm">{{ sm_t('Description') }}</label>
        <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
    </div>
</div>
