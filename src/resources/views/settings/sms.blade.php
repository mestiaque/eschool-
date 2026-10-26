@extends('me::master')

@section('title', sm_t('Notifications'))

@section('content')
<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-semibold mb-1">{{ sm_t('Automatic messages') }}</h6>
                    <p class="text-muted small">Choose which messages are sent to guardians automatically. SMS goes to the guardian's phone
                        (or father's / mother's), e-mail to the guardian e-mail when one is saved.</p>
                    <form method="POST" action="{{ route('settings.sms.update') }}">
                        @csrf
                        @foreach($events as $key => $label)
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="{{ $key }}" value="1" id="{{ $key }}" {{ !empty($settings[$key]) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
                        </div>
                        @endforeach
                        <p class="text-muted small mt-3 mb-2">Fee due reminders and notice broadcasts are sent on demand from the
                            Fee Collection and Notices pages.</p>
                        <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-semibold mb-1">{{ sm_t('Gateway & mail server') }}</h6>
                    <p class="text-muted small">SMS gateway and SMTP credentials are managed (encrypted, with test buttons and delivery logs) in the theme configuration:</p>
                    <div class="d-grid gap-2">
                        @if(Route::has('sms-config.edit'))<a href="{{ route('sms-config.edit') }}" class="btn btn-encodex-edit btn-sm text-start"><i class="fas fa-sms me-2"></i>{{ sm_t('SMS configuration') }}</a>@endif
                        @if(Route::has('mail-config.edit'))<a href="{{ route('mail-config.edit') }}" class="btn btn-encodex-edit btn-sm text-start"><i class="fas fa-envelope me-2"></i>{{ sm_t('Mail configuration') }}</a>@endif
                        @if(Route::has('sms-log.index'))<a href="{{ route('sms-log.index') }}" class="btn btn-encodex-list btn-sm text-start"><i class="fas fa-list me-2"></i>{{ sm_t('SMS log & balance') }}</a>@endif
                        @if(Route::has('mail-log.index'))<a href="{{ route('mail-log.index') }}" class="btn btn-encodex-list btn-sm text-start"><i class="fas fa-inbox me-2"></i>{{ sm_t('Mail log') }}</a>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
