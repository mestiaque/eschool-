@extends('me::master')

@section('title', sm_t('General Settings'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.general.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('Currency') }}</label>
                            <select name="currency" class="form-select form-select-sm">
                                <option value="BDT" {{ ($settings['currency'] ?? '') == 'BDT' ? 'selected' : '' }}>{{ sm_t('BDT (Taka)') }}</option>
                                <option value="USD" {{ ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>{{ sm_t('USD (Dollar)') }}</option>
                                <option value="INR" {{ ($settings['currency'] ?? '') == 'INR' ? 'selected' : '' }}>{{ sm_t('INR (Rupee)') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('Timezone') }}</label>
                            <select name="timezone" class="form-select form-select-sm" data-control="select2">
                                @foreach(timezone_identifiers_list() as $tz)
                                <option value="{{ $tz }}" {{ ($settings['timezone'] ?? 'Asia/Dhaka') == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('Date Format') }}</label>
                            <select name="date_format" class="form-select form-select-sm">
                                <option value="d M Y" {{ ($settings['date_format'] ?? 'd M Y') == 'd M Y' ? 'selected' : '' }}>17 Oct 2026 style ({{ date('d M Y') }})</option>
                                <option value="d/m/Y" {{ ($settings['date_format'] ?? 'd M Y') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY ({{ date('d/m/Y') }})</option>
                                <option value="d-m-Y" {{ ($settings['date_format'] ?? 'd M Y') == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY ({{ date('d-m-Y') }})</option>
                                <option value="Y-m-d" {{ ($settings['date_format'] ?? 'd M Y') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD ({{ date('Y-m-d') }})</option>
                                <option value="m-d-Y" {{ ($settings['date_format'] ?? 'd M Y') == 'm-d-Y' ? 'selected' : '' }}>MM-DD-YYYY ({{ date('m-d-Y') }})</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ sm_t('Salary expense category') }}</label>
                            <select name="salary_expense_category_id" class="form-select form-select-sm">
                                <option value="">Automatic (বেতন ও ভাতা / Salaries)</option>
                                @foreach($expenseCategories as $cat)
                                <option value="{{ $cat->id }}" {{ ($settings['salary_expense_category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Every salary payment is posted to Expenses under this category, so reports count it once.</div>
                        </div>
                        <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Save Changes') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
