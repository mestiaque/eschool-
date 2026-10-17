@extends('me::master')

@section('title', sm_t('Attendance Report'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="glass-search-form mb-3 row g-2">
                        <div class="col-md-3">
                            <select name="class_id" class="form-select form-select-sm">
                                <option value="">{{ sm_t('Select Class') }}</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="month" class="form-control form-control-sm" placeholder="Month (1-12)" min="1" max="12" value="{{ request('month') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="year" class="form-control form-control-sm" placeholder="Year" value="{{ request('year', date('Y')) }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-encodex-search btn-sm w-100">{{ sm_t('Filter') }}</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-encodex table-sm table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ sm_t('Student Name') }}</th>
                                    <th>{{ sm_t('Roll') }}</th>
                                    <th>{{ sm_t('Present') }}</th>
                                    <th>{{ sm_t('Absent') }}</th>
                                    <th>{{ sm_t('Late') }}</th>
                                    <th>{{ sm_t('Excused') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                @php
                                    $counts = $student->attendances->countBy('status');
                                @endphp
                                <tr>
                                    <td>{{ $student->full_name }}</td>
                                    <td>{{ $student->roll_number ?? 'N/A' }}</td>
                                    <td>{{ $counts->get('present', 0) }}</td>
                                    <td>{{ $counts->get('absent', 0) }}</td>
                                    <td>{{ $counts->get('late', 0) }}</td>
                                    <td>{{ $counts->get('excused', 0) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ request('class_id') ? 'No records found' : 'Select a class to view attendance' }}</td>
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
