{{-- Shared header for printMaster2-based print views. Pass $title (report heading) and optionally $subtitle (filter summary line). --}}
@php
    $schoolName = \ME\SchoolManagement\Models\SchoolSetting::where('key', 'school_name')->value('value') ?? config('app.name', 'School');
@endphp
<div class="print-header">
    <div class="company-info">
        <div class="company-name">{{ $schoolName }}</div>
        <div class="print-time">Printed: {{ now()->format('d M Y, h:i A') }}</div>
    </div>
    <div class="report-title">
        <span>{{ $title }}</span>
    </div>
    @isset($subtitle)
    <div class="text-center" style="font-size:11px;color:#666;margin-top:4px">{{ $subtitle }}</div>
    @endisset
</div>
