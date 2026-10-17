@extends('me::master')
@section('title', sm_t('Student Marksheet'))

@section('content')
<div class="card shadow mx-auto" style="max-width:600px">
    <div class="card-header py-2 fw-semibold"><i class="fas fa-file-alt me-1"></i> Find Student Marksheet</div>
    <div class="card-body">
        <form method="GET" class="glass-search-form row g-3">
            <div class="col-12">
                <label class="form-label form-label-sm">{{ sm_t('Exam') }} <span class="text-danger">*</span></label>
                <select name="exam_id" id="msExamId" class="form-control form-control-sm" required>
                    <option value="">{{ sm_t('Select Exam') }}</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label form-label-sm">{{ sm_t('Class') }} <span class="text-danger">*</span></label>
                <select name="class_id" id="msClassId" class="form-control form-control-sm" required onchange="this.form.submit()">
                    <option value="">{{ sm_t('Select Class') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($students->isNotEmpty())
            <div class="col-12">
                <label class="form-label form-label-sm">{{ sm_t('Student') }} <span class="text-danger">*</span></label>
                <select name="student_id" id="msStudentId" class="form-control form-control-sm" required>
                    <option value="">{{ sm_t('Select Student') }}</option>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                        {{ $student->first_name }} {{ $student->last_name }}
                        @if($student->roll_number) (Roll: {{ $student->roll_number }}) @endif
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" name="preview" value="1" class="btn btn-sm btn-encodex-search"><i class="fas fa-search me-1"></i> Preview</button>
            </div>
            @elseif(request('class_id'))
            <div class="col-12"><div class="alert alert-warning small py-2">No students found for this class.</div></div>
            @endif
        </form>

        @if(request('exam_id') && request('student_id') && request('preview'))
        <div class="mt-3 pt-3 border-top text-center">
            <a href="{{ route('exams.marksheet.view', [request('student_id'), request('exam_id')]) }}" target="_blank" class="btn btn-sm btn-encodex-show">
                <i class="fas fa-external-link-alt me-1"></i> Open Marksheet (Print)
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
