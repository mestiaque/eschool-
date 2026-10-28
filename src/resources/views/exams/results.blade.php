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
                <select name="class_id" class="form-control form-control-sm" onchange="this.form.section_id.value='';this.form.submit()">
                    <option value="">{{ sm_t('All Classes') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="section_id" class="form-control form-control-sm" onchange="this.form.submit()" {{ $sections->isEmpty() ? 'disabled' : '' }}>
                    <option value="">{{ sm_t('All Sections') }}</option>
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        {{-- Separate forms: a form nested inside the filter form would be dropped by the browser. --}}
        <div class="d-flex flex-wrap gap-2 mt-2">
            @if(request('exam_id') && request('class_id'))
            @php $sheetQuery = array_filter(request()->only('exam_id', 'class_id', 'section_id')); @endphp
            <form method="POST" action="{{ route('exams.results.generate') }}" class="d-inline"
                  onsubmit="return confirm(@json(sm_t('Re-generate results? This will overwrite existing results.'), JSON_HEX_APOS|JSON_HEX_QUOT))">
                @csrf
                <input type="hidden" name="exam_id"  value="{{ request('exam_id') }}">
                <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                <button type="submit" class="btn btn-sm btn-encodex-save"><i class="fas fa-sync-alt me-1"></i> {{ sm_t('Generate') }}</button>
            </form>
            <form method="POST" action="{{ route('exams.results.publish') }}" class="d-inline"
                  onsubmit="return confirm(@json(sm_t('Publish all results for this exam and class?'), JSON_HEX_APOS|JSON_HEX_QUOT))">
                @csrf
                <input type="hidden" name="exam_id"  value="{{ request('exam_id') }}">
                <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                <button type="submit" class="btn btn-sm btn-encodex-active"><i class="fas fa-bullhorn me-1"></i> {{ sm_t('Publish All') }}</button>
            </form>
            <a href="{{ route('exams.results.print', $sheetQuery) }}" target="_blank" class="btn btn-sm btn-encodex-print">
                <i class="fas fa-print me-1"></i> {{ sm_t('Result Sheet') }}
            </a>
            <a href="{{ route('exams.marksheet.class', $sheetQuery) }}" target="_blank" class="btn btn-sm btn-encodex-print">
                <i class="fas fa-copy me-1"></i> {{ sm_t('All Marksheets') }}
            </a>
            @else
            <span class="small text-muted align-self-center">{{ sm_t('Select an exam and a class to generate, publish or print results.') }}</span>
            <a href="{{ route('exams.marksheet.index') }}" class="btn btn-sm btn-encodex-list">
                <i class="fas fa-file-alt me-1"></i> {{ sm_t('Marksheet') }}
            </a>
            @endif
        </div>
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
                    <td class="text-center fw-bold">{{ $r->position ?? '—' }}@if($r->section_position)<div class="small text-muted fw-normal">{{ sm_t('Section') }}: {{ $r->section_position }}</div>@endif</td>
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
                    <td class="text-center">{{ number_format((float) $r->gpa, 2) }}</td>
                    <td class="text-center"><span class="badge bg-{{ $gc = \ME\SchoolManagement\Services\GradeScale::isFail($r->grade) ? 'danger' : 'success' }}-subtle text-{{ $gc }}">{{ $r->grade ?? '—' }}</span>@if($r->note)<div class="small text-muted">{{ $r->note }}</div>@endif</td>
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
                    {{ sm_t(request('exam_id') ? 'No results found. Generate results after entering marks.' : 'Select an exam to view results.') }}
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $results->links('pagination::bootstrap-5') }}</div>
@endsection
