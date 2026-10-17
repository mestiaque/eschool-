@extends('school-management::portal.layout')
@section('title', sm_t('Notices'))

@section('content')
<div class="portal-card">
    <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="fas fa-bell text-warning me-2"></i>Notices &amp; Announcements</h6>
    </div>

    @forelse($notices as $notice)
    <div class="p-3 border-bottom">
        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
            <div class="fw-bold">{{ $notice->title }}</div>
            <div class="d-flex gap-2 align-items-center flex-shrink-0">
                @if($notice->for === 'all')
                <span class="badge bg-secondary-subtle text-secondary" style="font-size:10px">All</span>
                @elseif($notice->for === 'students')
                <span class="badge bg-primary-subtle text-primary" style="font-size:10px">Students</span>
                @elseif($notice->for === 'class')
                <span class="badge bg-info-subtle text-info" style="font-size:10px">{{ $portalStudent->classe?->name }}</span>
                @endif
                <span class="text-muted small">{{ sm_date($notice->created_at) }}</span>
            </div>
        </div>
        @if($notice->content)
        <div class="mt-2 text-muted small" style="line-height:1.7">{{ $notice->content }}</div>
        @endif
        @if($notice->attachment)
        <a href="{{ asset('storage/'.$notice->attachment) }}" class="btn btn-sm btn-outline-secondary mt-2" target="_blank">
            <i class="fas fa-paperclip me-1"></i>Attachment
        </a>
        @endif
    </div>
    @empty
    <div class="p-4 text-center text-muted">
        <i class="fas fa-bell-slash fa-2x mb-2 opacity-25"></i>
        <div>No notices available.</div>
    </div>
    @endforelse

    @if($notices instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="p-2">{{ $notices->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
