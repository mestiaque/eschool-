@extends('me::master')

@section('title', sm_t('Student Documents'))

@push('buttons')
<button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#documentModal">
    <i class="fas fa-plus"></i> {{ sm_t('Upload Document') }}
</button>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="glass-search-form mb-3 row g-2">
                        <div class="col-md-4">
                            @include('school-management::partials.student-select', ['name' => 'student_id', 'placeholder' => 'All Students'])
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-encodex-search btn-sm w-100">{{ sm_t('Filter') }}</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-encodex table-sm table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ sm_t('ID') }}</th>
                                    <th>{{ sm_t('Student') }}</th>
                                    <th>{{ sm_t('Title') }}</th>
                                    <th>{{ sm_t('Type') }}</th>
                                    <th>{{ sm_t('File') }}</th>
                                    <th>{{ sm_t('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $doc)
                                    <tr>
                                        <td>{{ $doc->id }}</td>
                                        <td>{{ $doc->student->first_name ?? 'N/A' }} {{ $doc->student->last_name ?? '' }}</td>
                                        <td>{{ $doc->title }}</td>
                                        <td>{{ $doc->type }}</td>
                                        <td>
                                            @if($doc->file)
                                                <a href="{{ asset('storage/'.$doc->file) }}" target="_blank" class="btn btn-encodex-show btn-sm">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-encodex-delete btn-sm" onclick="return confirm('Delete?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ sm_t('No documents found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $documents->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Modal -->
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('students.index') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Upload Document') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Student') }} <span class="text-danger">*</span></label>
                        @include('school-management::partials.student-select', ['name' => 'student_id', 'required' => true, 'selected' => null, 'placeholder' => 'Select Student'])
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Title') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Type') }}</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="photo">{{ sm_t('Photo') }}</option>
                            <option value="certificate">{{ sm_t('Certificate') }}</option>
                            <option value="id_card">{{ sm_t('ID Card') }}</option>
                            <option value="other">{{ sm_t('Other') }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('File') }}</label>
                        <input type="file" name="file" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-encodex-cancel btn-sm" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Upload') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
