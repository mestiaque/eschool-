@extends('me::master')

@section('title', sm_t('Teacher Subject Assignment'))

@push('buttons')
<button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal">
    <i class="fas fa-plus"></i> Assign Subject
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
                                    <th>{{ sm_t('Teacher') }}</th>
                                    <th>{{ sm_t('Subject') }}</th>
                                    <th>{{ sm_t('Class') }}</th>
                                    <th>{{ sm_t('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teachers->flatMap(fn($t) => $t->subjects->map(fn($ts) => [$t, $ts])) as [$teacher, $ts])
                                <tr>
                                    <td>{{ $ts->id }}</td>
                                    <td>{{ $teacher->full_name }}</td>
                                    <td>{{ $ts->subject?->name }}</td>
                                    <td>{{ $ts->classe?->name }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('teachers.subjects.destroy', $ts->id) }}" class="d-inline" onsubmit="return confirm('Unassign this subject?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No assignments found</td>
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

<!-- Assignment Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('teachers.subjects.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Assign Subject') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Teacher') }}</label>
                        <select name="teacher_id" class="form-select form-select-sm" required>
                            <option value="">{{ sm_t('Select Teacher') }}</option>
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Subject') }}</label>
                        <select name="subject_id" class="form-select form-select-sm" required>
                            <option value="">{{ sm_t('Select Subject') }}</option>
                            @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->class?->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Class') }}</label>
                        <select name="class_id" class="form-select form-select-sm" required>
                            <option value="">{{ sm_t('Select Class') }}</option>
                            @foreach($subjects->pluck('class')->unique('id')->filter() as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-encodex-cancel btn-sm" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-encodex-save btn-sm">{{ sm_t('Assign') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
