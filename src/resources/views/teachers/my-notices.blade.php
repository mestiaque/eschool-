@extends('me::master')
@section('title', sm_t('Notice Board'))

@section('content')
@forelse($notices as $notice)
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="card-title mb-0">
                {{ $notice->title }}
                @if($notice->created_at && $notice->created_at->gt(now()->subHours(24)))
                <span class="badge bg-danger-subtle text-danger ms-1">{{ sm_t('New') }}</span>
                @endif
            </h6>
            <div>
                @if($notice->type)
                <span class="badge bg-info-subtle text-info">{{ sm_t(ucfirst($notice->type)) }}</span>
                @endif
                <small class="text-muted ms-2"><i class="fas fa-calendar"></i> {{ sm_date($notice->publish_date) }}</small>
            </div>
        </div>
        <p class="card-text text-muted">{{ $notice->content }}</p>
        @if($notice->file)
        <a href="{{ asset('storage/'.$notice->file) }}" class="btn btn-sm btn-encodex-show" target="_blank">
            <i class="fas fa-paperclip"></i> {{ sm_t('Attachment') }}
        </a>
        @endif
    </div>
</div>
@empty
<div class="alert alert-info">{{ sm_t('No notices available.') }}</div>
@endforelse

{{ $notices->links('pagination::bootstrap-5') }}
@endsection
