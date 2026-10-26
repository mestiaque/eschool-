@extends('me::printMaster2')
@section('title', 'Student ID Card - ' . $student->full_name)

@push('css')
<style>
    body { display: flex; justify-content: center; }
    .container { display: flex; justify-content: center; }
    .id-card { width: 85mm; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.2); font-family: Arial, sans-serif; }
    .card-header { background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 12px 15px; text-align: center; }
    .card-header h3 { font-size: 13px; margin-bottom: 2px; }
    .card-header p { font-size: 10px; opacity: 0.85; }
    .card-body { padding: 15px; }
    .photo-section { display: flex; gap: 12px; align-items: center; margin-bottom: 12px; }
    .photo { width: 60px; height: 70px; object-fit: cover; border: 2px solid #1a237e; border-radius: 4px; }
    .photo-placeholder { width: 60px; height: 70px; background: #e8eaf6; border: 2px solid #1a237e; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #1a237e; font-weight: bold; }
    .info h4 { font-size: 14px; color: #1a237e; margin-bottom: 3px; }
    .info p { font-size: 10px; color: #555; margin-bottom: 2px; }
    .details { border-top: 1px solid #e0e0e0; padding-top: 10px; }
    .detail-row { display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 10px; }
    .detail-row span:first-child { color: #888; }
    .detail-row span:last-child { font-weight: bold; color: #333; }
    .card-footer-custom { background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 8px 15px; text-align: center; font-size: 9px; }
    @media print { .id-card { box-shadow: none; } }
</style>
@endpush

@section('contents')
<div class="id-card">
    <div class="card-header">
        <h3>{{ sm_t('School Management System') }}</h3>
        <p>{{ sm_t('Student Identification Card') }}</p>
    </div>
    <div class="card-body">
        <div class="photo-section">
            @if($student->photo)
            <img src="{{ asset('storage/'.$student->photo) }}" class="photo" alt="Photo">
            @else
            <div class="photo-placeholder">{{ substr($student->first_name, 0, 1) }}</div>
            @endif
            <div class="info">
                <h4>{{ $student->full_name }}</h4>
                <p>ID: {{ $student->student_id }}</p>
                <p>Class: {{ $student->classe?->name }} {{ $student->section ? '| '.$student->section->name : '' }}</p>
                <p>Roll: {{ $student->roll_number ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="details">
            <div class="detail-row"><span>{{ sm_t('Date of Birth') }}</span><span>{{ sm_date($student->date_of_birth) }}</span></div>
            <div class="detail-row"><span>{{ sm_t('Blood Group') }}</span><span>{{ $student->blood_group ?? 'N/A' }}</span></div>
            <div class="detail-row"><span>{{ sm_t('Phone') }}</span><span>{{ $student->phone }}</span></div>
            @if($student->guardian)
            <div class="detail-row"><span>{{ sm_t('Guardian') }}</span><span>{{ $student->guardian->guardian_name ?? $student->guardian->father_name }}</span></div>
            <div class="detail-row"><span>{{ sm_t('Guardian Phone') }}</span><span>{{ $student->guardian->guardian_phone }}</span></div>
            @endif
            <div class="detail-row"><span>{{ sm_t('Admission Date') }}</span><span>{{ sm_date($student->admission_date) }}</span></div>
        </div>
    </div>
    <div class="card-footer-custom">
        Valid for Academic Year {{ date('Y') }}-{{ date('Y')+1 }}
    </div>
</div>
@endsection

@push('js')
<script>window.onload = function() { window.print(); }</script>
@endpush
