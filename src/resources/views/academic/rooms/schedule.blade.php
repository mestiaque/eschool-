@extends('me::master')
@section('title', $room->name . ' - Schedule')
@push('buttons')
    <a href="{{ route('academic.rooms') }}" class="btn btn-sm btn-encodex-list no-print">
        <i class="fas fa-arrow-left"></i> All Rooms
    </a>
    <a href="{{ route('academic.rooms.schedule.print', $room->id) }}" target="_blank" class="btn btn-sm btn-encodex-print no-print">
        <i class="fas fa-print"></i> Print
    </a>
@endpush

@section('content')

<style>
@media print {
    .no-print { display: none !important; }
}
.slot-occupied {
    background-color: #d1e7dd;
    border-left: 4px solid #198754;
    padding: 6px 10px;
    border-radius: 4px;
    margin-bottom: 4px;
}
.slot-free {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 6px 10px;
    border-radius: 4px;
    color: #856404;
    font-style: italic;
}
.day-card { margin-bottom: 1rem; }
.time-badge {
    font-size: 0.75rem;
    font-weight: 600;
    color: #495057;
}
</style>

<div class="card shadow mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">{{ $room->name }}</h5>
                @if($room->building)
                    <small class="text-muted">{{ $room->building }}</small>
                @endif
            </div>
            @if($room->capacity)
            <div class="col-auto">
                <span class="badge bg-info-subtle text-info">Capacity: {{ $room->capacity }}</span>
            </div>
            @endif
            <div class="col-auto">
                <span class="badge {{ $room->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                    {{ $room->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @foreach($days as $day)
    @php $daySchedule = $scheduleByDay[$day]; @endphp
    <div class="col-md-6 col-xl-4 day-card">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 d-flex justify-content-between align-items-center
                {{ $daySchedule->isEmpty() ? 'bg-light' : 'bg-primary text-white' }}">
                <strong>{{ $day }}</strong>
                <span class="badge {{ $daySchedule->isEmpty() ? 'bg-secondary-subtle text-secondary' : 'bg-light-subtle text-dark' }}">
                    {{ $daySchedule->count() }} class{{ $daySchedule->count() != 1 ? 'es' : '' }}
                </span>
            </div>
            <div class="card-body p-2">
                @if($daySchedule->isEmpty())
                    <div class="slot-free text-center py-2">
                        <i class="fas fa-calendar-times me-1"></i> No classes scheduled
                    </div>
                @else
                    @foreach($daySchedule as $routine)
                    <div class="slot-occupied">
                        <div class="time-badge mb-1">
                            <i class="fas fa-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($routine->start_time)->format('h:i A') }}
                            &ndash;
                            {{ \Carbon\Carbon::parse($routine->end_time)->format('h:i A') }}
                        </div>
                        <div class="fw-semibold">{{ $routine->subject->name ?? '—' }}</div>
                        <div class="small text-muted">
                            {{ $routine->class->name ?? '—' }}
                            @if($routine->section)
                                &bull; {{ $routine->section->name }}
                            @endif
                            @if($routine->title)
                                &bull; <span class="badge bg-secondary-subtle text-secondary">{{ $routine->title }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    @php
                        // Calculate free gaps between scheduled classes
                        $sorted = $daySchedule->sortBy('start_time')->values();
                        $gaps = [];
                        for ($i = 0; $i < $sorted->count() - 1; $i++) {
                            $endCurrent = $sorted[$i]->end_time;
                            $startNext = $sorted[$i + 1]->start_time;
                            if ($endCurrent < $startNext) {
                                $gaps[] = [
                                    'from' => \Carbon\Carbon::parse($endCurrent)->format('h:i A'),
                                    'to'   => \Carbon\Carbon::parse($startNext)->format('h:i A'),
                                ];
                            }
                        }
                    @endphp
                    @foreach($gaps as $gap)
                    <div class="slot-free mt-1">
                        <i class="fas fa-door-open me-1"></i>
                        Free: {{ $gap['from'] }} &ndash; {{ $gap['to'] }}
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
