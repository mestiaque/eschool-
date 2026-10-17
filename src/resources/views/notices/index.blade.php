@extends('me::master')
@section('title', sm_t('Notices'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#noticeModal">
    <i class="fas fa-plus"></i> Add Notice
</button>
@endpush

@section('content')
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-encodex mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ sm_t('Title') }}</th>
                    <th>{{ sm_t('Type') }}</th>
                    <th>{{ sm_t('Publish Date') }}</th>
                    <th>{{ sm_t('End Date') }}</th>
                    <th>{{ sm_t('For') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notices as $notice)
                <tr>
                    <td>{{ $notice->title }}</td>
                    <td>{{ ucfirst($notice->type) }}</td>
                    <td>{{ $notice->publish_date?->format('Y-m-d') }}</td>
                    <td>{{ $notice->end_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ ucfirst($notice->for) }}</td>
                    <td>
                        @if($notice->is_active)
                        <span class="badge bg-success-subtle text-success">Active</span>
                        @else
                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-encodex-edit" data-bs-toggle="modal" data-bs-target="#editNoticeModal{{ $notice->id }}"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="{{ route('notices.destroy', $notice->id) }}" class="d-inline" onsubmit="return confirm('Delete this notice?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $notices->links('pagination::bootstrap-5') }}</div>

@foreach($notices as $notice)
<!-- Edit Modal -->
<div class="modal fade" id="editNoticeModal{{ $notice->id }}" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Edit Notice') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('notices.update', $notice->id) }}">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control form-control-sm" value="{{ $notice->title }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Content *</label>
                        <textarea name="content" class="form-control form-control-sm" rows="3" required>{{ $notice->content }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Publish Date *</label>
                            <input type="date" name="publish_date" class="form-control form-control-sm" value="{{ $notice->publish_date?->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('End Date') }}</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $notice->end_date?->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Type') }}</label>
                            <select name="type" class="form-control form-control-sm">
                                <option value="notice" {{ $notice->type == 'notice' ? 'selected' : '' }}>Notice</option>
                                <option value="announcement" {{ $notice->type == 'announcement' ? 'selected' : '' }}>Announcement</option>
                                <option value="news" {{ $notice->type == 'news' ? 'selected' : '' }}>News</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('For') }}</label>
                            <select name="for" class="form-control form-control-sm">
                                <option value="all" {{ $notice->for == 'all' ? 'selected' : '' }}>All</option>
                                <option value="student" {{ $notice->for == 'student' ? 'selected' : '' }}>Students</option>
                                <option value="teacher" {{ $notice->for == 'teacher' ? 'selected' : '' }}>Teachers</option>
                                <option value="guardian" {{ $notice->for == 'guardian' ? 'selected' : '' }}>Guardians</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="active{{ $notice->id }}" value="1" {{ $notice->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="active{{ $notice->id }}">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Create Modal -->
<div class="modal fade" id="noticeModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ sm_t('Add Notice') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('notices.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Content *</label>
                        <textarea name="content" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Publish Date *</label>
                            <input type="date" name="publish_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('End Date') }}</label>
                            <input type="date" name="end_date" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('Type') }}</label>
                            <select name="type" class="form-control form-control-sm">
                                <option value="notice">{{ sm_t('Notice') }}</option>
                                <option value="announcement">{{ sm_t('Announcement') }}</option>
                                <option value="news">{{ sm_t('News') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">{{ sm_t('For') }}</label>
                            <select name="for" class="form-control form-control-sm">
                                <option value="all">{{ sm_t('All') }}</option>
                                <option value="student">{{ sm_t('Students') }}</option>
                                <option value="teacher">{{ sm_t('Teachers') }}</option>
                                <option value="guardian">{{ sm_t('Guardians') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="border rounded p-2 mt-1">
                        <div class="small fw-semibold mb-1">Also send now</div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="send_sms" value="1" id="noticeSms">
                            <label class="form-check-label small" for="noticeSms">{{ sm_t('SMS') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="send_email" value="1" id="noticeMail">
                            <label class="form-check-label small" for="noticeMail">{{ sm_t('E-mail') }}</label>
                        </div>
                        <div class="form-text">Students / guardians get it on the guardian's phone; teachers on their own. Delivery shows in the SMS and Mail logs.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Publish') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
