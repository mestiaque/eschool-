@extends('me::master')
@section('title', sm_t('Academic Years'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#yearModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> {{ sm_t('Add Year') }}
    </button>
@endpush
@section('content')


<div class="card shadow">
    <div class="card-body ">
        <form method="GET" class="glass-search-form mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search"
                        class="form-control form-control-sm"
                        placeholder="@lang('Enter Name')"
                        value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">@lang('All Status')</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>@lang('Active')</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>@lang('Inactive')</option>
                    </select>
                </div>

                <div class="col-md">
                    <button type="submit" class="btn btn-sm btn-encodex-search">
                        <i class="fas fa-search"></i> @lang('Search')
                    </button>

                    <a href="{{ route('academic.years') }}"
                    class="btn btn-sm btn-encodex-clear">
                        <i class="fas fa-eraser"></i> @lang('Reset')
                    </a>
                </div>
            </div>
        </form>
        <div class="responsive">
            <table class="table table-bordered table-hover table-striped table-encodex table-sm">
                <thead class="text-center">
                    <tr>
                        <th>{{ sm_t('Name') }}</th>
                        <th>{{ sm_t('Start Date') }}</th>
                        <th>{{ sm_t('End Date') }}</th>
                        <th>{{ sm_t('Status') }}</th>
                        <th>{{ sm_t('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($years as $year)
                    <tr class="{{ $year->is_current ? 'table-success' : '' }}">
                        <td>{{ $year->name }}</td>
                        <td>{{ sm_date(\Carbon\Carbon::parse($year->start_date)) }}</td>
                        <td>{{ sm_date(\Carbon\Carbon::parse($year->end_date)) }}</td>
                        <td class="text-center">
                            @if($year->is_active)
                            <span class="badge bg-success-subtle text-success">{{ sm_t('Active') }}</span>
                            @else
                            <span class="badge bg-danger-subtle text-danger">{{ sm_t('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-encodex-edit" type="button" data-bs-toggle="modal" data-bs-target="#editYearModal{{ $year->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('academic.years.destroy', $year->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-encodex-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($years->isEmpty())
        <div class="text-center py-3 text-muted">
            {{ sm_t('No academic years found') }}
        </div>
        @endif
        <div class="mt-2">{{ $years->links('pagination::bootstrap-5') }}</div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="yearModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span id="modalTitle">{{ sm_t('Add Academic Year') }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="yearForm" action="{{ route('academic.years') }}">
                @csrf
                <input type="hidden" id="yearId" name="year_id">
                <input type="hidden" id="methodField" name="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Name') }} *</label>
                        <input type="text" id="nameField" name="name" class="form-control form-control-sm" placeholder="{{ sm_t('e.g. 2026') }}" required>
                        <small class="text-muted">{{ sm_t('Leave blank to auto-generate') }}</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Start Date') }} *</label>
                        <input type="date" id="startDateField" name="start_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('End Date') }} *</label>
                        <input type="date" id="endDateField" name="end_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="isCurrentField" name="is_current" value="1">
                            <label class="form-check-label">{{ sm_t('Set as Current') }}</label>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="isActiveField" name="is_active" value="1" checked>
                            <label class="form-check-label">{{ sm_t('Active') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save"> {{ sm_t('Save Data') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>


@foreach ($years as $year)
    <div class="modal fade" id="editYearModal{{ $year->id }}" tabindex="-1">
        <div class="modal-dialog glass-card">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modalTitle">{{ sm_t('Add Academic Year') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="yearForm" action="{{ route('academic.years.update', $year->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="yearId" name="year_id">
                    <input type="hidden" id="methodField" name="_method" value="PUT">
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Name') }} *</label>
                            <input type="text" id="nameField" name="name" class="form-control form-control-sm" value="{{ $year->name ?? '' }}" placeholder="{{ sm_t('e.g. 2026') }}" required>
                            <small class="text-muted">{{ sm_t('Leave blank to auto-generate') }}</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('Start Date') }} *</label>

                            <input type="date" id="startDateField" name="start_date" class="form-control form-control-sm" value="{{ isset($year->start_date) ? $year->start_date->format('Y-m-d') : '' }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ sm_t('End Date') }} *</label>
                            <input type="date" id="endDateField" name="end_date" class="form-control form-control-sm" value="{{ isset($year->end_date) ? $year->end_date->format('Y-m-d') : '' }}" required>
                        </div>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isCurrentField" name="is_current" value="1" {{ isset($year) && $year->is_current ? 'checked' : '' }}>
                                <label class="form-check-label">{{ sm_t('Set as Current') }}</label>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isActiveField" name="is_active" value="1" {{ isset($year) && $year->is_active ? 'checked' : '' }}>
                                <label class="form-check-label">{{ sm_t('Active') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                        <button type="submit" class="btn btn-sm btn-encodex-save"> {{ sm_t('Update Data') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach


@endsection
