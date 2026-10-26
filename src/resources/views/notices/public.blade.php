@extends('me::master')
@section('title', sm_t('Public Notices'))

@push('buttons')
<a href="{{ route('notices.index') }}" class="btn btn-sm btn-encodex-list"><i class="fas fa-arrow-left"></i> {{ sm_t('All Notices') }}</a>
@endpush

@section('content')
@foreach($notices as $notice)
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="card-title mb-0">{{ $notice->title }}</h6>
            <div>
                @if($notice->notice_type)
                <span class="badge bg-info-subtle text-info">{{ $notice->notice_type }}</span>
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
<div class="alert alert-info">{{ sm_t('No public notices available.') }}</div>
@endforeach

@if(method_exists($notices, 'links'))
{{ $notices->links('pagination::bootstrap-5') }}
@endif
@endsection
