@extends('me::master')

@section('title', sm_t('Teacher Class Assignment'))

@push('buttons')
<button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal">
    <i class="fas fa-plus"></i> Assign Class
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
                                    <th>{{ sm_t('Class') }}</th>
                                    <th>{{ sm_t('Section') }}</th>
                                    <th>{{ sm_t('Class Teacher') }}</th>
                                    <th>{{ sm_t('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $a)
                                <tr>
                                    <td>{{ $a->id }}</td>
                                    <td>{{ $a->teacher?->full_name }}</td>
                                    <td>{{ $a->classe?->name }}</td>
                                    <td>{{ $a->section?->name ?? '—' }}</td>
                                    <td>
                                        @if($a->is_class_teacher)
                                        <span class="badge bg-success-subtle text-success">Yes</span>
                                        @else
                                        <span class="badge bg-secondary-subtle text-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('teachers.classes.destroy', $a->id) }}" class="d-inline" onsubmit="return confirm('Remove this assignment?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No assignments found</td>
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
        <form method="POST" action="{{ route('teachers.classes.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Assign Class') }}</h5>
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
                        <label class="form-label">{{ sm_t('Class') }}</label>
                        <select name="class_id" class="form-select form-select-sm" required>
                            <option value="">{{ sm_t('Select Class') }}</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Section') }}</label>
                        <select name="section_id" class="form-select form-select-sm">
                            <option value="">{{ sm_t('Select Section') }}</option>
                            @foreach($sections as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 form-check">
                        <input type="checkbox" name="is_class_teacher" class="form-check-input" id="isClassTeacher" value="1">
                        <label class="form-check-label" for="isClassTeacher">{{ sm_t('Set as Class Teacher') }}</label>
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
