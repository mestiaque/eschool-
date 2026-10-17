<div class="modal-body">
    <div class="row g-2">
        <div class="col-md-5 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Full Name') }} <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">Role / পদবি <span class="text-danger">*</span></label>
            <select name="role" class="form-control form-control-sm" required>
                <option value="">{{ sm_t('Select Role') }}</option>
                @foreach(\ME\SchoolManagement\Models\Committee::roles() as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Serial / Order') }}</label>
            <input type="number" name="serial" class="form-control form-control-sm" min="1" placeholder="Auto">
        </div>

        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Phone') }}</label>
            <input type="text" name="phone" class="form-control form-control-sm">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Email') }}</label>
            <input type="email" name="email" class="form-control form-control-sm">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Profession') }}</label>
            <input type="text" name="profession" class="form-control form-control-sm" placeholder="e.g. Teacher, Businessman">
        </div>

        <div class="col-md-8 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Address') }}</label>
            <textarea name="address" class="form-control form-control-sm" rows="2" placeholder="Full address"></textarea>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Photo') }}</label>
            <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
        </div>

        <div class="col-12 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Notes') }}</label>
            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
        </div>
    </div>
</div>
