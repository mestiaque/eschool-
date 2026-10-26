@extends('me::master')

@section('title', sm_t('Holidays'))

@push('buttons')
<button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#holidayModal">
    <i class="fas fa-plus"></i> {{ sm_t('Add Holiday') }}
</button>
@endpush

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
                                    <th>{{ sm_t('ID') }}</th>
                                    <th>{{ sm_t('Title') }}</th>
                                    <th>{{ sm_t('Date') }}</th>
                                    <th>{{ sm_t('Description') }}</th>
                                    <th>{{ sm_t('Status') }}</th>
                                    <th>{{ sm_t('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($holidays as $holiday)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $holiday->title }}
                                        @if(($holiday->type ?? 'holiday') === 'school_day')<span class="badge bg-info-subtle text-info ms-1">{{ sm_t('Extra school day') }}</span>@endif
                                    </td>
                                    <td>{{ sm_date(\Carbon\Carbon::parse($holiday->date)) }}</td>
                                    <td>{{ $holiday->description ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $bc = $holiday->is_active ?? true ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">
                                            {{ ($holiday->is_active ?? true) ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('holidays.destroy', $holiday->id) }}" class="d-inline" onsubmit="return confirm('Delete this holiday?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">{{ sm_t('No holidays found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 px-2">{{ $holidays->links('pagination::bootstrap-5') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Holiday Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('holidays.index') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Add Holiday') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Title') }}</label>
                        <input type="text" name="title" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Date') }}</label>
                        <input type="date" name="date" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Type') }}</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="holiday">{{ sm_t('Holiday (school closed)') }}</option>
                            <option value="school_day">{{ sm_t('Extra school day (e.g. make-up class on a weekly holiday)') }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-encodex-cancel btn-sm" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
