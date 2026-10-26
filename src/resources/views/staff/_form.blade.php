<div class="modal-body">
    <div class="row g-2">
        <div class="col-md-3 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Staff ID') }}</label>
            <input type="text" name="staff_id" class="form-control form-control-sm" placeholder="{{ sm_t('Auto if blank') }}">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('First Name') }} <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-5 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Last Name') }}</label>
            <input type="text" name="last_name" class="form-control form-control-sm">
        </div>

        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Designation') }} <span class="text-danger">*</span></label>
            <select name="designation" class="form-control form-control-sm" required>
                <option value="">{{ sm_t('Select') }}</option>
                @foreach(\ME\SchoolManagement\Models\Staff::designations() as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Gender') }}</label>
            <select name="gender" class="form-control form-control-sm">
                <option value="">{{ sm_t('Select') }}</option>
                <option value="male">{{ sm_t('Male') }}</option>
                <option value="female">{{ sm_t('Female') }}</option>
                <option value="other">{{ sm_t('Other') }}</option>
            </select>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Date of Birth') }}</label>
            <input type="date" name="date_of_birth" class="form-control form-control-sm">
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
            <label class="form-label form-label-sm">{{ sm_t('NID') }}</label>
            <input type="text" name="nid" class="form-control form-control-sm">
        </div>

        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Joining Date') }}</label>
            <input type="date" name="joining_date" class="form-control form-control-sm">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Leaving Date') }}</label>
            <input type="date" name="leaving_date" class="form-control form-control-sm">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">Monthly Salary ({{ sm_currency() }})</label>
            <input type="number" name="salary" class="form-control form-control-sm" min="0" step="0.01" value="0">
        </div>

        <div class="col-md-6 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Present Address') }}</label>
            <textarea name="present_address" class="form-control form-control-sm" rows="2"></textarea>
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Permanent Address') }}</label>
            <textarea name="permanent_address" class="form-control form-control-sm" rows="2"></textarea>
        </div>

        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Bank Name') }}</label>
            <input type="text" name="bank_name" class="form-control form-control-sm">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Bank Account No') }}</label>
            <input type="text" name="bank_account_no" class="form-control form-control-sm">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Religion') }}</label>
            <select name="religion" class="form-control form-control-sm">
                <option value="">{{ sm_t('Select') }}</option>
                <option value="Islam">{{ sm_t('Islam') }}</option>
                <option value="Hindu">{{ sm_t('Hindu') }}</option>
                <option value="Christian">{{ sm_t('Christian') }}</option>
                <option value="Buddhist">{{ sm_t('Buddhist') }}</option>
                <option value="Other">{{ sm_t('Other') }}</option>
            </select>
        </div>

        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Emergency Contact Name') }}</label>
            <input type="text" name="emergency_contact_name" class="form-control form-control-sm">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Emergency Phone') }}</label>
            <input type="text" name="emergency_contact_phone" class="form-control form-control-sm">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Relationship') }}</label>
            <input type="text" name="relationship" class="form-control form-control-sm" placeholder="{{ sm_t('e.g. Spouse') }}">
        </div>

        <div class="col-md-8 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Notes') }}</label>
            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
        </div>
        <div class="col-md-2 mb-2">
            <label class="form-label form-label-sm">{{ sm_t('Photo') }}</label>
            <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
        </div>
        <div class="col-md-2 mb-2 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="staffActive">
                <label class="form-check-label" for="staffActive">{{ sm_t('Active') }}</label>
            </div>
        </div>
    </div>
</div>
