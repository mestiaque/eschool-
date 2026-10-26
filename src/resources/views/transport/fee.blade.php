@extends('me::master')

@section('title', sm_t('Transport Fee'))

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('transport.fee.generate') }}" class="row g-2 align-items-end"
                  onsubmit="return confirm(@json(sm_t('Create this month\'s transport invoices for every student on a route?'), JSON_HEX_APOS | JSON_HEX_QUOT))">
                @csrf
                <div class="col-md-3">
                    <label class="form-label form-label-sm">{{ sm_t('Month') }}</label>
                    <select name="month" class="form-select form-select-sm" required>
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm">{{ sm_t('Due Date') }}</label>
                    <input type="date" name="due_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-6">
                    <button class="btn btn-encodex-create btn-sm"><i class="fas fa-file-invoice"></i> {{ sm_t('Generate transport fees') }}</button>
                    <div class="form-text">{{ sm_t('Each student on a route is billed the route fee plus their stop\'s extra fee. Assign routes on the student\'s profile.') }}</div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-encodex table-sm table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ sm_t('Route') }}</th>
                                    <th>{{ sm_t('Start Point') }}</th>
                                    <th>{{ sm_t('End Point') }}</th>
                                    <th>{{ sm_t('Distance') }}</th>
                                    <th>{{ sm_t('Fee') }}</th>
                                    <th>{{ sm_t('Students') }}</th>
                                    <th>{{ sm_t('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($routes ?? [] as $route)
                                    <tr>
                                        <td>{{ $route->route_name }}</td>
                                        <td>{{ $route->start_point }}</td>
                                        <td>{{ $route->end_point }}</td>
                                        <td>{{ $route->distance }} km</td>
                                        <td>{{ sm_money($route->fee, 2) }}</td>
                                        <td>{{ $route->students_count }}</td>
                                        <td>
                                            <button class="btn btn-encodex-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editFee{{ $route->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ sm_t('No routes found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($routes ?? [] as $route)
<!-- Edit Fee Modal -->
<div class="modal fade" id="editFee{{ $route->id }}" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('transport.fee.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Edit Transport Fee') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="route_id" value="{{ $route->id }}">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Transport Fee') }}</label>
                        <input type="number" name="fee" class="form-control form-control-sm" value="{{ $route->fee }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-encodex-cancel btn-sm" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Update') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
