@extends('me::master')
@section('title', sm_t('Dashboard'))

@push('css')
<style>
/* Dashboard tokens — chart colours validated for colour-blind separation (blue/orange). */
.sm-dash {
    --surface:     #ffffff;
    --ink:         #0b0b0b;
    --ink-2:       #52514e;
    --muted:       #898781;
    --grid:        #e1e0d9;
    --hairline:    rgba(11, 11, 11, .08);
    --series-1:    #2a78d6;
    --series-2:    #eb6834;
    --track:       #ecebe6;
    --good-text:   #006300;
    --bad-text:    #b42318;
    font-variant-numeric: normal;
}
.sm-dash .dash-card { background: var(--surface); border: 1px solid var(--hairline); border-radius: 12px; height: 100%; }
.sm-dash .dash-card-head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: 1rem 1.25rem .25rem; }
.sm-dash .dash-card-head h6 { margin: 0; font-weight: 600; color: var(--ink); font-size: .95rem; }
.sm-dash .dash-card-head .sub { color: var(--muted); font-size: .78rem; }
.sm-dash .dash-card-body { padding: .75rem 1.25rem 1.25rem; }
.sm-dash .chart-box { position: relative; height: 280px; }
.sm-dash .chart-box.sm { height: 220px; }
.sm-dash .chart-table { display: none; max-height: 280px; overflow: auto; }
.sm-dash .show-table .chart-box { display: none; }
.sm-dash .show-table .chart-table { display: block; }
.sm-dash .chart-table table { font-variant-numeric: tabular-nums; }
.sm-dash .btn-view { font-size: .72rem; padding: .15rem .55rem; border: 1px solid var(--hairline); border-radius: 6px; background: transparent; color: var(--ink-2); }
.sm-dash .btn-view:hover { background: #f4f4f1; }

/* KPI tiles */
.sm-dash .kpi { padding: 1rem 1.15rem; display: flex; flex-direction: column; gap: .2rem; }
.sm-dash .kpi .kpi-top { display: flex; align-items: center; justify-content: space-between; }
.sm-dash .kpi .kpi-label { color: var(--ink-2); font-size: .8rem; font-weight: 500; }
.sm-dash .kpi .kpi-icon { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: .85rem; }
.sm-dash .kpi .kpi-value { font-size: 1.6rem; font-weight: 650; color: var(--ink); line-height: 1.2; }
.sm-dash .kpi .kpi-sub { color: var(--muted); font-size: .76rem; }
.sm-dash .delta-up { color: var(--good-text); font-weight: 600; }
.sm-dash .delta-down { color: var(--bad-text); font-weight: 600; }

/* Legends: text in ink, colour only on the swatch */
.sm-dash .legend { display: flex; flex-wrap: wrap; gap: 1rem; font-size: .8rem; color: var(--ink-2); }
.sm-dash .legend .sw { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: .4rem; vertical-align: -1px; }
.sm-dash .sw-inline { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: .45rem; vertical-align: -1px; }
.sm-dash .legend strong { color: var(--ink); font-weight: 600; }

/* Fee meter */
.sm-dash .meter { height: 10px; background: var(--track); border-radius: 6px; overflow: hidden; }
.sm-dash .meter > span { display: block; height: 100%; background: var(--series-1); border-radius: 6px; }
.sm-dash .stat-row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid var(--hairline); font-size: .85rem; }
.sm-dash .stat-row:last-child { border-bottom: 0; }
.sm-dash .stat-row .k { color: var(--ink-2); }
.sm-dash .stat-row .v { color: var(--ink); font-weight: 600; font-variant-numeric: tabular-nums; }

/* Lists */
.sm-dash .list-row { display: flex; align-items: center; gap: .75rem; padding: .6rem 0; border-bottom: 1px solid var(--hairline); text-decoration: none; color: inherit; }
.sm-dash .list-row:last-child { border-bottom: 0; }
.sm-dash a.list-row:hover .list-title { color: var(--series-1); }
.sm-dash .list-icon { width: 32px; height: 32px; border-radius: 8px; background: #f4f4f1; color: var(--ink-2); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .8rem; }
.sm-dash .list-title { font-size: .85rem; color: var(--ink); font-weight: 500; line-height: 1.3; }
.sm-dash .list-meta { font-size: .75rem; color: var(--muted); }
.sm-dash .count-pill { margin-left: auto; min-width: 28px; text-align: center; font-size: .78rem; font-weight: 600; padding: .1rem .5rem; border-radius: 999px; }
.sm-dash .date-chip { width: 42px; flex-shrink: 0; text-align: center; border: 1px solid var(--hairline); border-radius: 8px; padding: .2rem 0; line-height: 1.1; }
.sm-dash .date-chip .d { font-weight: 650; color: var(--ink); font-size: .95rem; }
.sm-dash .date-chip .m { font-size: .65rem; color: var(--muted); text-transform: uppercase; }
.sm-dash .empty { color: var(--muted); font-size: .85rem; text-align: center; padding: 2.5rem 0; }
.sm-dash .mini-stat { border: 1px solid var(--hairline); border-radius: 10px; padding: .75rem; height: 100%; }
.sm-dash .mini-stat .t { font-size: .75rem; color: var(--ink-2); display: flex; align-items: center; gap: .4rem; }
.sm-dash .mini-stat .n { font-size: 1.2rem; font-weight: 650; color: var(--ink); }
.sm-dash .mini-stat .s { font-size: .72rem; color: var(--muted); }
.sm-dash .quick { display: flex; align-items: center; gap: .6rem; padding: .7rem .85rem; border: 1px solid var(--hairline); border-radius: 10px; background: var(--surface); text-decoration: none; color: var(--ink); font-size: .85rem; font-weight: 500; height: 100%; }
.sm-dash .quick:hover { border-color: var(--series-1); color: var(--series-1); }
.sm-dash .quick i { color: var(--ink-2); width: 16px; text-align: center; }
.sm-dash .section-title { font-size: .78rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin: .25rem 0 .75rem; }
</style>
@endpush

@section('content')
@php
    $tk  = fn($v) => sm_money((float) $v, 0);
    $net = $yearIncome - $yearExpense;
    $feeDelta = $feeLastMonth > 0 ? round(($feeThisMonth - $feeLastMonth) / $feeLastMonth * 100) : null;
    $perm = app(\ME\SchoolManagement\Http\Middleware\SchoolPermission::class);
    $can  = fn($route) => !($p = $perm->permissionFor($route)) || auth()->user()->hasPermission($p);
@endphp

<div class="sm-dash">

    {{-- Heading strip --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <div class="fw-semibold" style="font-size:1.05rem;color:var(--ink)">{{ sm_t('School overview') }}</div>
            <div class="small" style="color:var(--muted)">
                {{ now()->format('l, d F Y') }}
                @if($academicYear) · Academic year <strong style="color:var(--ink-2)">{{ $academicYear->name }}</strong>@endif
            </div>
        </div>
        <div class="small" style="color:var(--ink-2)">
            {{ sm_t('Collected today') }}: <strong style="color:var(--ink)">{{ $tk($todayFee) }}</strong>
        </div>
    </div>

    {{-- KPI tiles --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="dash-card kpi">
                <div class="kpi-top"><span class="kpi-label">{{ sm_t('Students') }}</span><span class="kpi-icon bg-primary-subtle text-primary"><i class="fas fa-user-graduate"></i></span></div>
                <div class="kpi-value">{{ number_format($students) }}</div>
                <div class="kpi-sub">{{ $newAdmissions }} admitted in {{ now()->year }} · {{ $classes }} classes</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="dash-card kpi">
                <div class="kpi-top"><span class="kpi-label">{{ sm_t('Teachers & staff') }}</span><span class="kpi-icon bg-info-subtle text-info"><i class="fas fa-chalkboard-teacher"></i></span></div>
                <div class="kpi-value">{{ number_format($teachers + $staffCount) }}</div>
                <div class="kpi-sub">{{ $teachers }} teachers · {{ $staffCount }} staff</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="dash-card kpi">
                <div class="kpi-top"><span class="kpi-label">Attendance {{ $attendance['isToday'] ? 'today' : '' }}</span><span class="kpi-icon bg-success-subtle text-success"><i class="fas fa-user-check"></i></span></div>
                <div class="kpi-value">{{ $attendance['rate'] !== null ? $attendance['rate'] . '%' : '—' }}</div>
                <div class="kpi-sub">
                    @if($attendance['marked'])
                        {{ $attendance['present'] }} of {{ $attendance['marked'] }} present{{ $attendance['isToday'] ? '' : ' · ' . $attendance['date']->format('d M') }}
                    @else
                        Not taken yet
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="dash-card kpi">
                <div class="kpi-top"><span class="kpi-label">{{ sm_t('Fees this month') }}</span><span class="kpi-icon bg-primary-subtle text-primary"><i class="fas fa-hand-holding-usd"></i></span></div>
                <div class="kpi-value">{{ $tk($feeThisMonth) }}</div>
                <div class="kpi-sub">
                    @if($feeDelta !== null)
                        <span class="{{ $feeDelta >= 0 ? 'delta-up' : 'delta-down' }}"><i class="fas fa-arrow-{{ $feeDelta >= 0 ? 'up' : 'down' }}"></i> {{ abs($feeDelta) }}%</span> vs {{ $tk($feeLastMonth) }} last month
                    @else
                        No collections last month
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="dash-card kpi">
                <div class="kpi-top"><span class="kpi-label">{{ sm_t('Outstanding dues') }}</span><span class="kpi-icon bg-danger-subtle text-danger"><i class="fas fa-file-invoice-dollar"></i></span></div>
                <div class="kpi-value">{{ $tk($totalDueFee) }}</div>
                <div class="kpi-sub">{{ $studentsWithDues }} student{{ $studentsWithDues == 1 ? '' : 's' }} with dues · {{ $feeSummary['overdue'] }} overdue</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="dash-card kpi">
                <div class="kpi-top"><span class="kpi-label">Net {{ now()->year }}</span><span class="kpi-icon bg-secondary-subtle text-secondary"><i class="fas fa-balance-scale"></i></span></div>
                <div class="kpi-value">{{ $net < 0 ? '−' : '' }}{{ $tk(abs($net)) }}</div>
                <div class="kpi-sub"><span class="{{ $net >= 0 ? 'delta-up' : 'delta-down' }}">{{ sm_t($net >= 0 ? 'Surplus' : 'Deficit') }}</span> · income {{ $tk($yearIncome) }}</div>
            </div>
        </div>
    </div>

    {{-- Finance --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="dash-card" data-chart-card="finance">
                <div class="dash-card-head">
                    <div><h6>{{ sm_t('Income vs expense') }}</h6><div class="sub">{{ now()->year }} · income = fees + other income · expense = all expenses (salaries included)</div></div>
                    <button type="button" class="btn-view" data-toggle-table>{{ sm_t('Table') }}</button>
                </div>
                <div class="dash-card-body">
                    <div class="legend mb-2">
                        <span><span class="sw" style="background:var(--series-1)"></span>{{ sm_t('Income') }} <strong>{{ $tk($yearIncome) }}</strong></span>
                        <span><span class="sw" style="background:var(--series-2)"></span>{{ sm_t('Expense') }} <strong>{{ $tk($yearExpense) }}</strong></span>
                    </div>
                    <div class="chart-box"><canvas id="financeChart" aria-label="Monthly income and expense"></canvas></div>
                    <div class="chart-table"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="dash-card">
                <div class="dash-card-head">
                    <div><h6>{{ sm_t('Fee collection') }}</h6><div class="sub">{{ $academicYear?->name ?? 'All years' }} · {{ number_format($feeSummary['invoices']) }} invoices</div></div>
                    @if($can('fees.collection'))<a href="{{ route('fees.collection') }}" class="small text-decoration-none">{{ sm_t('Open') }}</a>@endif
                </div>
                <div class="dash-card-body">
                    <div class="d-flex align-items-baseline justify-content-between mb-2">
                        <div class="kpi-value" style="font-size:1.6rem;font-weight:650;color:var(--ink)">{{ $feeSummary['rate'] !== null ? $feeSummary['rate'] . '%' : '—' }}</div>
                        <div class="small" style="color:var(--muted)">{{ sm_t('collected of billed') }}</div>
                    </div>
                    <div class="meter mb-3" role="img" aria-label="{{ $feeSummary['rate'] ?? 0 }} percent of billed fees collected">
                        <span style="width: {{ min(100, $feeSummary['rate'] ?? 0) }}%"></span>
                    </div>
                    <div class="stat-row"><span class="k">{{ sm_t('Billed') }}</span><span class="v">{{ $tk($feeSummary['billed']) }}</span></div>
                    <div class="stat-row"><span class="k"><span class="sw-inline" style="background:var(--series-1)"></span>{{ sm_t('Collected') }}</span><span class="v">{{ $tk($feeSummary['collected']) }}</span></div>
                    <div class="stat-row"><span class="k"><span class="sw-inline" style="background:var(--track)"></span>{{ sm_t('Due') }}</span><span class="v">{{ $tk($feeSummary['due']) }}</span></div>
                    <div class="stat-row"><span class="k">{{ sm_t('Overdue invoices') }}</span><span class="v">{{ $feeSummary['overdue'] }}</span></div>
                    <div class="stat-row"><span class="k">{{ sm_t('Collected today') }}</span><span class="v">{{ $tk($todayFee) }}</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance + Needs attention --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="dash-card" data-chart-card="attendance">
                <div class="dash-card-head">
                    <div><h6>{{ sm_t('Student attendance rate') }}</h6><div class="sub">Last {{ count($attendanceTrend['labels']) }} days attendance was taken · present + late</div></div>
                    <button type="button" class="btn-view" data-toggle-table>{{ sm_t('Table') }}</button>
                </div>
                <div class="dash-card-body">
                    @if(count($attendanceTrend['labels']))
                        <div class="chart-box"><canvas id="attendanceChart" aria-label="Daily attendance rate"></canvas></div>
                        <div class="chart-table"></div>
                    @else
                        <div class="empty"><i class="fas fa-user-check d-block mb-2"></i>{{ sm_t('No attendance recorded yet') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="dash-card">
                <div class="dash-card-head"><div><h6>{{ sm_t('Needs attention') }}</h6><div class="sub">{{ sm_t('Items waiting for action') }}</div></div></div>
                <div class="dash-card-body pt-1">
                    @php $open = $pending->filter(fn($p) => $p['count'] > 0); @endphp
                    @forelse($open as $item)
                        <a href="{{ $can($item['route']) ? route($item['route']) : '#' }}" class="list-row">
                            <span class="list-icon"><i class="fas {{ $item['icon'] }}"></i></span>
                            <span class="list-title">{{ $item['label'] }}</span>
                            <span class="count-pill bg-warning-subtle text-warning">{{ $item['count'] }}</span>
                        </a>
                    @empty
                        <div class="empty"><i class="fas fa-check-circle d-block mb-2" style="color:var(--good-text)"></i>All clear — nothing pending</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Enrolment + Spending --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="dash-card" data-chart-card="enrolment">
                <div class="dash-card-head">
                    <div><h6>{{ sm_t('Enrolment by class') }}</h6><div class="sub">{{ sm_t('Active students') }}</div></div>
                    <button type="button" class="btn-view" data-toggle-table>{{ sm_t('Table') }}</button>
                </div>
                <div class="dash-card-body">
                    <div class="legend mb-2">
                        <span><span class="sw" style="background:var(--series-1)"></span>{{ sm_t('Male') }} <strong>{{ array_sum($enrolment['male']) }}</strong></span>
                        <span><span class="sw" style="background:var(--series-2)"></span>{{ sm_t('Female') }} <strong>{{ array_sum($enrolment['female']) }}</strong></span>
                        @if(array_sum($enrolment['other']))<span><span class="sw" style="background:var(--muted)"></span>{{ sm_t('Other') }} <strong>{{ array_sum($enrolment['other']) }}</strong></span>@endif
                    </div>
                    <div class="chart-box"><canvas id="enrolmentChart" aria-label="Students per class by gender"></canvas></div>
                    <div class="chart-table"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="dash-card" data-chart-card="spending">
                <div class="dash-card-head">
                    <div><h6>{{ sm_t('Spending by category') }}</h6><div class="sub">{{ now()->year }} · from the expense ledger</div></div>
                    <button type="button" class="btn-view" data-toggle-table>{{ sm_t('Table') }}</button>
                </div>
                <div class="dash-card-body">
                    @if(count($spending['labels']))
                        <div class="legend mb-2"><span>{{ sm_t('Total') }} <strong>{{ $tk(array_sum($spending['values'])) }}</strong></span></div>
                        <div class="chart-box"><canvas id="spendingChart" aria-label="Spending by category"></canvas></div>
                        <div class="chart-table"></div>
                    @else
                        <div class="empty"><i class="fas fa-receipt d-block mb-2"></i>{{ sm_t('No expenses recorded this year') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Exams, upcoming, notices --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="dash-card" data-chart-card="grades">
                <div class="dash-card-head">
                    <div><h6>{{ sm_t('Latest exam results') }}</h6><div class="sub">{{ $examSummary['exam']->name ?? 'Grade distribution' }}</div></div>
                    @if($examSummary)<button type="button" class="btn-view" data-toggle-table>{{ sm_t('Table') }}</button>@endif
                </div>
                <div class="dash-card-body">
                    @if($examSummary)
                        <div class="row g-2 mb-2">
                            <div class="col-4"><div class="mini-stat"><div class="t">{{ sm_t('Students') }}</div><div class="n">{{ $examSummary['total'] }}</div></div></div>
                            <div class="col-4"><div class="mini-stat"><div class="t">{{ sm_t('Pass rate') }}</div><div class="n">{{ $examSummary['passRate'] }}%</div></div></div>
                            <div class="col-4"><div class="mini-stat"><div class="t">{{ sm_t('Avg GPA') }}</div><div class="n">{{ number_format($examSummary['avgGpa'], 2) }}</div></div></div>
                        </div>
                        <div class="chart-box sm"><canvas id="gradesChart" aria-label="Students per grade"></canvas></div>
                        <div class="chart-table"></div>
                    @else
                        <div class="empty"><i class="fas fa-poll d-block mb-2"></i>No results generated yet
                            @if($can('exams.results'))<div class="mt-2"><a href="{{ route('exams.results') }}" class="small">{{ sm_t('Go to results') }}</a></div>@endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="dash-card">
                <div class="dash-card-head"><div><h6>{{ sm_t('Coming up') }}</h6><div class="sub">{{ sm_t('Exams, events and holidays') }}</div></div></div>
                <div class="dash-card-body pt-1">
                    @forelse($upcoming as $u)
                        <a href="{{ $u['route'] }}" class="list-row">
                            <span class="date-chip"><div class="d">{{ $u['date']->format('d') }}</div><div class="m">{{ $u['date']->format('M') }}</div></span>
                            <span>
                                <div class="list-title">{{ \Illuminate\Support\Str::limit($u['title'], 42) }}</div>
                                <div class="list-meta"><i class="fas {{ $u['icon'] }} me-1"></i>{{ $u['type'] }}
                                    @if($u['end'] && !$u['end']->isSameDay($u['date'])) · until {{ $u['end']->format('d M') }}@endif
                                    · {{ $u['date']->isFuture() ? $u['date']->diffForHumans(['parts' => 1]) : 'ongoing' }}
                                </div>
                            </span>
                        </a>
                    @empty
                        <div class="empty"><i class="fas fa-calendar d-block mb-2"></i>{{ sm_t('Nothing scheduled') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="dash-card">
                <div class="dash-card-head">
                    <div><h6>{{ sm_t('Notices') }}</h6><div class="sub">{{ sm_t('Currently published') }}</div></div>
                    @if($can('notices.index'))<a href="{{ route('notices.index') }}" class="small text-decoration-none">{{ sm_t('View all') }}</a>@endif
                </div>
                <div class="dash-card-body pt-1">
                    @forelse($notices as $notice)
                        <div class="list-row">
                            <span class="list-icon"><i class="fas fa-bullhorn"></i></span>
                            <span>
                                <div class="list-title">{{ \Illuminate\Support\Str::limit($notice->title, 48) }}</div>
                                <div class="list-meta">{{ sm_date($notice->publish_date) }} · for {{ $notice->for }}</div>
                            </span>
                        </div>
                    @empty
                        <div class="empty"><i class="fas fa-bullhorn d-block mb-2"></i>{{ sm_t('No active notices') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Payments + operations --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="dash-card">
                <div class="dash-card-head">
                    <div><h6>{{ sm_t('Recent fee payments') }}</h6><div class="sub">Latest {{ $recentPayments->count() }}</div></div>
                    @if($can('fees.payment-history'))<a href="{{ route('fees.payment-history') }}" class="small text-decoration-none">{{ sm_t('Payment history') }}</a>@endif
                </div>
                <div class="dash-card-body pt-1">
                    @if($recentPayments->count())
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" style="font-size:.85rem">
                            <thead><tr style="color:var(--muted)"><th class="fw-medium">{{ sm_t('Student') }}</th><th class="fw-medium">{{ sm_t('Fee') }}</th><th class="fw-medium">{{ sm_t('Date') }}</th><th class="fw-medium text-end">{{ sm_t('Amount') }}</th></tr></thead>
                            <tbody style="font-variant-numeric: tabular-nums">
                                @foreach($recentPayments as $pay)
                                <tr>
                                    <td>{{ $pay->feeCollection?->student?->full_name ?? '—' }}<div class="list-meta">{{ $pay->feeCollection?->invoice_no }}</div></td>
                                    <td>{{ $pay->feeCollection?->feeType?->name ?? '—' }}</td>
                                    <td>{{ sm_date($pay->date) }}</td>
                                    <td class="text-end fw-semibold">{{ $tk($pay->amount) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div class="empty"><i class="fas fa-receipt d-block mb-2"></i>{{ sm_t('No payments yet') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="dash-card">
                <div class="dash-card-head"><div><h6>{{ sm_t('Operations') }}</h6><div class="sub">{{ sm_t('Across modules') }}</div></div></div>
                <div class="dash-card-body">
                    <div class="row g-2">
                        <div class="col-6"><div class="mini-stat">
                            <div class="t"><i class="fas fa-book-open"></i>{{ sm_t('Library') }}</div>
                            <div class="n">{{ number_format($modules['library']['books']) }} <span class="s">{{ sm_t('titles') }}</span></div>
                            <div class="s">{{ $modules['library']['issued'] }} issued · {{ $modules['library']['available'] }} of {{ $modules['library']['copies'] }} copies available</div>
                        </div></div>
                        <div class="col-6"><div class="mini-stat">
                            <div class="t"><i class="fas fa-bus"></i>{{ sm_t('Transport') }}</div>
                            <div class="n">{{ $modules['transport']['routes'] }} <span class="s">{{ sm_t('routes') }}</span></div>
                            <div class="s">{{ $modules['transport']['vehicles'] }} active vehicles</div>
                        </div></div>
                        <div class="col-6"><div class="mini-stat">
                            <div class="t"><i class="fas fa-boxes"></i>{{ sm_t('Inventory') }}</div>
                            <div class="n">{{ $modules['inventory']['products'] }} <span class="s">{{ sm_t('items') }}</span></div>
                            <div class="s">Stock value {{ $tk($modules['inventory']['value']) }}</div>
                        </div></div>
                        <div class="col-6"><div class="mini-stat">
                            <div class="t"><i class="fas fa-user-tie"></i>{{ sm_t('Teacher attendance') }}</div>
                            <div class="n">{{ $modules['teacherAttendance']['marked'] ? $modules['teacherAttendance']['present'] . '/' . $modules['teacherAttendance']['marked'] : '—' }}</div>
                            <div class="s">{{ $modules['teacherAttendance']['marked'] ? 'present today' : 'Not taken today' }}</div>
                        </div></div>
                        <div class="col-6"><div class="mini-stat">
                            <div class="t"><i class="fas fa-layer-group"></i>{{ sm_t('Academic') }}</div>
                            <div class="n">{{ $classes }} <span class="s">{{ sm_t('classes') }}</span></div>
                            <div class="s">{{ $sections }} sections</div>
                        </div></div>
                        <div class="col-6"><div class="mini-stat">
                            <div class="t"><i class="fas fa-users"></i>{{ sm_t('Student : teacher') }}</div>
                            <div class="n">{{ $teachers ? round($students / $teachers, 1) : '—' }}<span class="s"> : 1</span></div>
                            <div class="s">{{ sm_t('active students per teacher') }}</div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick access (only what this user may open) --}}
    @php
    $quickLinks = collect([
        ['students.index',      'fa-user-graduate',      'Students'],
        ['students.admission',  'fa-user-plus',          'Admission'],
        ['students.attendance', 'fa-clipboard-check',    'Attendance'],
        ['teachers.index',      'fa-chalkboard-teacher', 'Teachers'],
        ['fees.collection',     'fa-money-bill-wave',    'Fee collection'],
        ['fees.monthly',        'fa-calendar-alt',       'Monthly fees'],
        ['exams.index',         'fa-file-alt',           'Exams'],
        ['exams.marks',         'fa-pen',                'Enter marks'],
        ['academic.routines',   'fa-clock',              'Class routine'],
        ['library.books',       'fa-book-open',          'Library'],
        ['expenses.index',      'fa-receipt',            'Expenses'],
        ['reports.financial',   'fa-chart-line',         'Financial report'],
    ])->filter(fn($l) => $can($l[0]));
    @endphp
    @if($quickLinks->count())
    <div class="section-title">{{ sm_t('Quick access') }}</div>
    <div class="row g-2">
        @foreach($quickLinks as [$route, $icon, $label])
        <div class="col-6 col-md-3 col-xl-2">
            <a href="{{ route($route) }}" class="quick"><i class="fas {{ $icon }}"></i>{{ $label }}</a>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const root = document.querySelector('.sm-dash');
    const css  = n => getComputedStyle(root).getPropertyValue(n).trim();
    const C = {
        s1: css('--series-1'), s2: css('--series-2'), ink: css('--ink'), ink2: css('--ink-2'),
        muted: css('--muted'), grid: css('--grid'), surface: css('--surface'),
    };
    const money   = v => '{{ sm_currency() }}' + Number(v).toLocaleString('en-US', { maximumFractionDigits: 0 });
    const compact = v => '{{ sm_currency() }}' + Intl.NumberFormat('en-US', { notation: 'compact', maximumFractionDigits: 1 }).format(v);

    Chart.defaults.font.family = 'system-ui, -apple-system, "Segoe UI", sans-serif';
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = C.muted;
    Chart.defaults.plugins.legend.display = false; // HTML legends carry identity
    Object.assign(Chart.defaults.plugins.tooltip, {
        backgroundColor: C.surface, titleColor: C.ink, bodyColor: C.ink2,
        borderColor: 'rgba(11,11,11,.12)', borderWidth: 1, padding: 10, cornerRadius: 8,
        boxPadding: 4, usePointStyle: true, titleFont: { weight: '600' },
    });

    const valueAxis = (fmt, extra = {}) => ({
        beginAtZero: true, border: { display: false },
        grid: { color: C.grid, lineWidth: 1, drawTicks: false },
        ticks: { padding: 8, maxTicksLimit: 6, callback: fmt }, ...extra,
    });
    const catAxis = { grid: { display: false }, border: { color: C.grid }, ticks: { padding: 6 } };
    const bar = (label, data, color, extra = {}) => ({
        label, data, backgroundColor: color, hoverBackgroundColor: color,
        borderRadius: 4, borderSkipped: 'start', maxBarThickness: 28, ...extra,
    });

    const charts = {};

    // Income vs expense — grouped bars, one value axis
    const fin = @json($finance);
    charts.finance = new Chart(document.getElementById('financeChart'), {
        type: 'bar',
        data: { labels: fin.labels, datasets: [
            bar('Income', fin.income, C.s1, { categoryPercentage: .7, barPercentage: .85 }),
            bar('Expense', fin.expense, C.s2, { categoryPercentage: .7, barPercentage: .85 }),
        ]},
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { x: catAxis, y: valueAxis(compact) },
            plugins: { tooltip: { callbacks: {
                label: c => ` ${c.dataset.label}: ${money(c.parsed.y)}`,
                footer: items => items.length === 2 ? 'Net: ' + money(items[0].parsed.y - items[1].parsed.y) : '',
            }}},
        },
    });

    // Attendance rate — single line, endpoint labelled in the tooltip/table
    const att = @json($attendanceTrend);
    const attEl = document.getElementById('attendanceChart');
    if (attEl) charts.attendance = new Chart(attEl, {
        type: 'line',
        data: { labels: att.labels, datasets: [{
            label: 'Attendance', data: att.rate, borderColor: C.s1, borderWidth: 2, tension: .3,
            pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: C.s1,
            pointBorderColor: C.surface, pointBorderWidth: 2,
            fill: { target: 'origin' }, backgroundColor: 'rgba(42,120,214,.08)',
        }]},
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { x: catAxis, y: valueAxis(v => v + '%', { min: 0, max: 100, beginAtZero: false }) },
            plugins: { tooltip: { callbacks: {
                label: c => ` ${c.parsed.y}% present (${att.counts[c.dataIndex]})`,
            }}},
        },
    });

    // Enrolment — stacked horizontal bars, 2px surface gap between segments
    const enr = @json($enrolment);
    const enrSets = [
        bar('Male', enr.male, C.s1, { borderSkipped: false, borderRadius: 0, borderColor: C.surface, borderWidth: { right: 2 } }),
        bar('Female', enr.female, C.s2, { borderSkipped: false, borderRadius: { topRight: 4, bottomRight: 4 }, borderColor: C.surface, borderWidth: { right: 2 } }),
    ];
    if (enr.other.some(v => v > 0)) enrSets.push(bar('Other', enr.other, C.muted, { borderRadius: { topRight: 4, bottomRight: 4 } }));
    charts.enrolment = new Chart(document.getElementById('enrolmentChart'), {
        type: 'bar',
        data: { labels: enr.labels, datasets: enrSets },
        options: {
            indexAxis: 'y', maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false, axis: 'y' },
            scales: {
                x: valueAxis(v => v, { stacked: true, ticks: { precision: 0, padding: 8 } }),
                y: { ...catAxis, stacked: true },
            },
            plugins: { tooltip: { callbacks: {
                label: c => ` ${c.dataset.label}: ${c.parsed.x}`,
                footer: items => 'Total: ' + items.reduce((s, i) => s + i.parsed.x, 0),
            }}},
        },
    });

    // Spending — one series, one colour, sorted largest first
    const sp = @json($spending);
    const spEl = document.getElementById('spendingChart');
    if (spEl) charts.spending = new Chart(spEl, {
        type: 'bar',
        data: { labels: sp.labels, datasets: [bar('Spent', sp.values, C.s1, { borderSkipped: 'start', borderRadius: 4 })] },
        options: {
            indexAxis: 'y', maintainAspectRatio: false,
            scales: { x: valueAxis(compact), y: catAxis },
            plugins: { tooltip: { callbacks: {
                label: c => {
                    const total = sp.values.reduce((a, b) => a + b, 0);
                    return ` ${money(c.parsed.x)} (${total ? Math.round(c.parsed.x / total * 100) : 0}%)`;
                },
            }}},
        },
    });

    // Grade distribution — ordered categories, one colour
    const gr = @json($examSummary ? ['labels' => $examSummary['labels'], 'values' => $examSummary['values']] : null);
    const grEl = document.getElementById('gradesChart');
    if (grEl && gr) charts.grades = new Chart(grEl, {
        type: 'bar',
        data: { labels: gr.labels, datasets: [bar('Students', gr.values, C.s1)] },
        options: {
            maintainAspectRatio: false,
            scales: { x: catAxis, y: valueAxis(v => v, { ticks: { precision: 0, padding: 8 } }) },
            plugins: { tooltip: { callbacks: { label: c => ` ${c.parsed.y} student${c.parsed.y === 1 ? '' : 's'}` } } },
        },
    });

    // Table view: same data as the chart, for screen readers and exact values
    const fmtFor = { finance: money, spending: money, attendance: v => v + '%', enrolment: v => v, grades: v => v };
    document.querySelectorAll('[data-chart-card]').forEach(card => {
        const key = card.dataset.chartCard, chart = charts[key], btn = card.querySelector('[data-toggle-table]');
        if (!chart || !btn) return;
        const fmt = fmtFor[key];
        const rows = chart.data.labels.map((l, i) =>
            `<tr><td>${l}</td>${chart.data.datasets.map(d => `<td class="text-end">${fmt(d.data[i])}</td>`).join('')}</tr>`).join('');
        card.querySelector('.chart-table').innerHTML =
            `<table class="table table-sm mb-0" style="font-size:.85rem"><thead><tr><th></th>${
                chart.data.datasets.map(d => `<th class="text-end">${d.label}</th>`).join('')}</tr></thead><tbody>${rows}</tbody></table>`;
        btn.addEventListener('click', () => {
            const on = card.classList.toggle('show-table');
            btn.textContent = on ? 'Chart' : 'Table';
        });
    });
})();
</script>
@endpush
