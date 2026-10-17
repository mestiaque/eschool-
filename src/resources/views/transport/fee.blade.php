@extends('me::master')

@section('title', sm_t('Transport Fee'))

@section('content')
<div class="container-fluid">
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
                                        <td>{{ $route->fee }}</td>
                                        <td>
                                            <button class="btn btn-encodex-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editFee{{ $route->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No routes found</td>
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
