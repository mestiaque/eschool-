@extends('me::master')
@section('title', sm_t('School Tours'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#tourModal">
        <i class="fas fa-plus"></i> Add Tour
    </button>
@endpush

@section('content')


@php
$statusColors = ['planning'=>'warning','approved'=>'success','ongoing'=>'info','completed'=>'secondary','cancelled'=>'danger'];
@endphp

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-striped table-encodex table-sm mb-0">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th class="text-start">{{ sm_t('Title') }}</th>
                    <th>{{ sm_t('Destination') }}</th>
                    <th>{{ sm_t('Start Date') }}</th>
                    <th>{{ sm_t('End Date') }}</th>
                    <th>{{ sm_t('Budget') }}</th>
                    <th>{{ sm_t('Expenses') }}</th>
                    <th>{{ sm_t('Students') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tours as $i => $tour)
                <tr>
                    <td class="text-center">{{ $tours->firstItem() + $i }}</td>
                    <td>
                        <a href="{{ route('academic.tours.show', $tour->id) }}" class="fw-semibold text-decoration-none">
                            {{ $tour->title }}
                        </a>
                        @if($tour->organizer)<br><small class="text-muted">{{ $tour->organizer }}</small>@endif
                    </td>
                    <td>{{ $tour->destination }}</td>
                    <td class="text-center">{{ sm_date($tour->start_date) }}</td>
                    <td class="text-center">{{ sm_date($tour->end_date) }}</td>
                    <td class="text-end">{{ sm_money($tour->budget, 0) }}</td>
                    <td class="text-end">{{ sm_money($tour->expenses_sum_amount ?? 0, 0) }}</td>
                    <td class="text-center">
                        <a href="{{ route('academic.tours.show', $tour->id) }}" class="badge bg-primary-subtle text-primary text-decoration-none">
                            {{ $tour->registrations_count }}
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $bc = $statusColors[$tour->status] ?? 'secondary' }}-subtle text-{{ $bc }}">
                            {{ ucfirst($tour->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('academic.tours.show', $tour->id) }}" class="btn btn-sm btn-encodex-show" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-sm btn-encodex-edit" title="Edit"
                            onclick="openEditModal({{ $tour->id }}, {{ json_encode($tour) }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('academic.tours.destroy', $tour->id) }}" class="d-inline"
                            onsubmit="return confirm('Delete this tour?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-3">No tours found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $tours->links('pagination::bootstrap-5') }}</div>

{{-- Add Tour Modal --}}
<div class="modal fade" id="tourModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Add School Tour') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('academic.tours.store') }}">
                @csrf
                @include('school-management::academic.tours._form')
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save Tour') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Tour Modal --}}
<div class="modal fade" id="editTourModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Edit Tour') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editTourForm">
                @csrf @method('PUT')
                @include('school-management::academic.tours._form', ['edit' => true])
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update Tour') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, tour) {
    const form = document.getElementById('editTourForm');
    form.action = '/academic/tours/' + id;

    const f = (n, v) => { const el = form.querySelector('[name="' + n + '"]'); if(el) el.value = v ?? ''; };
    f('title',         tour.title);
    f('destination',   tour.destination);
    f('description',   tour.description);
    f('start_date',    tour.start_date?.substring(0,10));
    f('end_date',      tour.end_date?.substring(0,10));
    f('budget',        tour.budget);
    f('organizer',     tour.organizer);
    f('contact_phone', tour.contact_phone);
    f('status',        tour.status);

    new bootstrap.Modal(document.getElementById('editTourModal')).show();
}
</script>
@endsection
