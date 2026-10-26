@extends('school-management::portal.layout')
@section('title', sm_t('My Results'))

@section('content')
<div class="portal-card">
    <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-primary"></i>{{ sm_t('Exam Results') }}</h6>
    </div>

    @forelse($results as $result)
    <div class="p-3 border-bottom">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <div class="fw-bold">{{ $result->exam?->name }}</div>
                <div class="text-muted small">{{ $result->exam?->academicYear?->year ?? '' }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2">{{ $result->grade ?? '—' }}</span>
                <a href="{{ route('portal.marksheet', $result->exam_id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-alt me-1"></i>{{ sm_t('Marksheet') }}
                </a>
            </div>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-6 col-md-3">
                <div class="bg-light rounded p-2 text-center small">
                    <div class="text-muted">{{ sm_t('Obtained') }}</div>
                    <div class="fw-bold fs-6">{{ $result->obtained_mark }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-light rounded p-2 text-center small">
                    <div class="text-muted">{{ sm_t('Total') }}</div>
                    <div class="fw-bold fs-6">{{ $result->total_mark }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-light rounded p-2 text-center small">
                    <div class="text-muted">{{ sm_t('GPA') }}</div>
                    <div class="fw-bold fs-6">{{ $result->gpa }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-light rounded p-2 text-center small">
                    <div class="text-muted">{{ sm_t('Position') }}</div>
                    <div class="fw-bold fs-6">{{ $result->position ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="p-4 text-center text-muted">
        <i class="fas fa-chart-bar fa-2x mb-2 opacity-25"></i>
        <div>{{ sm_t('No published results yet.') }}</div>
    </div>
    @endforelse
</div>
@endsection
