@extends('me::master')

@section('title', sm_t('Academic Session'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.session.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('Current Academic Year') }}</label>
                            <select name="current_year_id" class="form-select form-select-sm" required>
                                <option value="">{{ sm_t('Select Year') }}</option>
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('Session Start Month') }}</label>
                            <select name="session_start_month" class="form-select form-select-sm">
                                @foreach([1 => 'January', 7 => 'July'] as $m => $label)
                                <option value="{{ $m }}" {{ ($settings['session_start_month'] ?? 1) == $m ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Save Changes') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
