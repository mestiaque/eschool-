@extends('me::master')
@section('title', sm_t('Exam Results'))

@section('content')

<div class="card shadow">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-4">
                <select name="exam_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ sm_t('Select Exam') }}</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="class_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ sm_t('All Classes') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                @if(request('exam_id') && request('class_id'))
                <form method="POST" action="{{ route('exams.results.generate') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="exam_id"  value="{{ request('exam_id') }}">
                    <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                    <button type="submit" class="btn btn-sm btn-encodex-save" onclick="return confirm('Re-generate results? This will overwrite existing results.')">
                        <i class="fas fa-sync-alt me-1"></i> {{ sm_t('Generate') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('exams.results.publish') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="exam_id"  value="{{ request('exam_id') }}">
                    <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                    <button type="submit" class="btn btn-sm btn-encodex-active" onclick="return confirm('Publish all results for this exam and class?')">
                        <i class="fas fa-bullhorn me-1"></i> {{ sm_t('Publish All') }}
                    </button>
                </form>
                @else
                <a href="{{ route('exams.marksheet.index') }}" class="btn btn-sm btn-encodex-list">
                    <i class="fas fa-file-alt me-1"></i> {{ sm_t('Marksheet') }}
                </a>
                @endif
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>{{ sm_t('Pos') }}</th>
                    <th>{{ sm_t('Student') }}</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Total Mark') }}</th>
                    <th>{{ sm_t('Obtained') }}</th>
                    <th>%</th>
                    <th>{{ sm_t('GPA') }}</th>
                    <th>{{ sm_t('Grade') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $r)
                <tr>
                    <td class="text-center fw-bold">{{ $r->position ?? '—' }}</td>
                    <td>{{ $r->student?->first_name }} {{ $r->student?->last_name }}</td>
                    <td class="small">{{ $r->classe?->name ?? '—' }}</td>
                    <td class="text-center">{{ $r->total_mark > 0 ? number_format($r->total_mark,0) : '—' }}</td>
                    <td class="text-center fw-semibold">{{ number_format($r->obtained_mark, 0) }}</td>
                    <td class="text-center">
                        @if($r->total_mark > 0)
                            {{ round(($r->obtained_mark / $r->total_mark) * 100, 1) }}%
                        @else —
                        @endif
                    </td>
                    <td class="text-center">{{ $r->gpa }}</td>
                    <td class="text-center"><span class="badge bg-success-subtle text-success">{{ $r->grade ?? '—' }}</span></td>
                    <td><span class="badge bg-{{ $bc = $r->is_published ? 'success' : 'warning' }}-subtle text-{{ $bc }}">{{ sm_t($r->is_published ? 'Published' : 'Draft') }}</span></td>
                    <td>
                        @if($r->student_id && $r->exam_id)
                        <a href="{{ route('exams.marksheet.view', [$r->student_id, $r->exam_id]) }}" target="_blank" class="btn btn-sm btn-encodex-show" title="Marksheet">
                            <i class="fas fa-file-alt"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-3">
                    @if(!request('exam_id'))
                        Select an exam to view results.
                    @else
                        No results found. Click <strong>{{ sm_t('Generate') }}</strong> after entering marks.
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $results->links('pagination::bootstrap-5') }}</div>
@endsection
