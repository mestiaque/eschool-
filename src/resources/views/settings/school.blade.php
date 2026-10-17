@extends('me::master')

@section('title', sm_t('School Settings'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.school.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('School Name') }}</label>
                            <input type="text" name="school_name" class="form-control form-control-sm" value="{{ old('school_name', $settings['school_name'] ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('Address') }}</label>
                            <textarea name="school_address" class="form-control form-control-sm" rows="2">{{ old('school_address', $settings['school_address'] ?? '') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ sm_t('Phone') }}</label>
                                <input type="text" name="school_phone" class="form-control form-control-sm" value="{{ old('school_phone', $settings['school_phone'] ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ sm_t('Email') }}</label>
                                <input type="email" name="school_email" class="form-control form-control-sm" value="{{ old('school_email', $settings['school_email'] ?? '') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('EIIN') }}</label>
                            <input type="text" name="school_eiin" class="form-control form-control-sm" value="{{ old('school_eiin', $settings['school_eiin'] ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('Code') }}</label>
                            <input type="text" name="school_code" class="form-control form-control-sm" value="{{ old('school_code', $settings['school_code'] ?? '') }}">
                        </div>
                        <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Save Changes') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
