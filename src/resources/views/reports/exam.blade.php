@extends('me::master')

@section('title', sm_t('Exam Report'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="glass-search-form mb-3 row g-2">
                        <div class="col-md-3">
                            <select name="exam_id" class="form-select form-select-sm">
                                <option value="">{{ sm_t('Select Exam') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="class_id" class="form-select form-select-sm">
                                <option value="">{{ sm_t('Select Class') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-encodex-search btn-sm w-100">{{ sm_t('Filter') }}</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-encodex table-sm table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ sm_t('Student') }}</th>
                                    <th>{{ sm_t('Class') }}</th>
                                    <th>{{ sm_t('Total Mark') }}</th>
                                    <th>{{ sm_t('Obtained') }}</th>
                                    <th>{{ sm_t('GPA') }}</th>
                                    <th>{{ sm_t('Grade') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($results ?? [] as $result)
                                    <tr>
                                        <td>{{ $result->student->first_name ?? '' }} {{ $result->student->last_name ?? '' }}</td>
                                        <td>{{ $result->class->name ?? 'N/A' }}</td>
                                        <td>{{ $result->total_mark }}</td>
                                        <td>{{ $result->obtained_mark }}</td>
                                        <td>{{ $result->gpa }}</td>
                                        <td>{{ $result->grade }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ sm_t('No results found') }}</td>
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
@endsection
