@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', [
        'title' => $module['title'],
        'eyebrow' => 'Records',
        'heroIcon' => 'fa-solid fa-chart-column',
        'heroSummary' => 'Workforce, payroll, recruitment and appraisal reporting across the organization.',
        'heroStats' => [
            ['label' => 'Headcount', 'icon' => 'fa-solid fa-users', 'value' => $headcountByDept->sum('employees_count')],
            ['label' => 'Funnel', 'icon' => 'fa-solid fa-filter', 'value' => $funnel->sum()],
            ['label' => 'Appraisals', 'icon' => 'fa-solid fa-clipboard-check', 'value' => $appraisalDone . '/' . $appraisalTotal],
        ],
    ])

    <div class="content">
        {{-- Live workforce snapshot --}}
        <div class="section-head" style="margin-top:6px;">
            <h2><i class="fa-solid fa-signal"></i>Today's snapshot</h2>
            <span class="hint">Live counts, refreshed on every visit</span>
        </div>
        <div class="stat-tiles cols-6">
            <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-user-check"></i></div><div><b>{{ $presentToday }}</b><span>Present</span></div></div>
            <div class="stat-tile alt"><div class="st-ico"><i class="fa-regular fa-clock"></i></div><div><b>{{ $lateToday }}</b><span>Late arrivals</span></div></div>
            <div class="stat-tile info"><div class="st-ico"><i class="fa-solid fa-house-laptop"></i></div><div><b>{{ $wfhToday }}</b><span>WFH today</span></div></div>
            <div class="stat-tile warn"><div class="st-ico"><i class="fa-solid fa-plane-departure"></i></div><div><b>{{ $onLeaveToday }}</b><span>On leave</span></div></div>
            <div class="stat-tile alt"><div class="st-ico"><i class="fa-regular fa-calendar-days"></i></div><div><b>{{ $leavePending }}</b><span>Leave to approve</span></div></div>
            <div class="stat-tile info"><div class="st-ico"><i class="fa-solid fa-user-plus"></i></div><div><b>{{ $openRequisitions }}</b><span>Open roles</span></div></div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <h3>Headcount by department</h3>
                        <p>Active employees only.</p>
                    </div>
                    <span class="pill pill-muted" style="margin-left:auto;">{{ $headcountByDept->sum('employees_count') }} total</span>
                </div>
                @forelse($headcountByDept as $d)
                    <div class="bar-row">
                        <span class="bar-label">{{ $d->name }}</span>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ $maxHeadcount ? round($d->employees_count / $maxHeadcount * 100) : 0 }}%;"></div></div>
                        <span class="bar-value">{{ $d->employees_count }}</span>
                    </div>
                @empty
                    <div class="empty-widget"><div class="ew-ico"><i class="fa-solid fa-users-slash"></i></div><b>No departments yet</b><span>Add departments to see headcount.</span></div>
                @endforelse
            </div>

            <div class="card">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-filter"></i></div>
                    <div>
                        <h3>Recruitment funnel</h3>
                        <p>All requisitions, all time.</p>
                    </div>
                    <span class="pill pill-muted" style="margin-left:auto;">{{ $funnel->sum() }} candidates</span>
                </div>
                @php
                    $funnelIcons = ['applied'=>'fa-paper-plane','shortlisted'=>'fa-list-check','interviewed'=>'fa-comments','offered'=>'fa-envelope-open-text','hired'=>'fa-handshake'];
                @endphp
                @foreach($funnel as $stage => $count)
                    <div class="bar-row">
                        <span class="bar-label"><i class="fa-solid {{ $funnelIcons[$stage] ?? 'fa-circle' }}" style="color:var(--brand);margin-right:7px;font-size:11px;"></i>{{ ucfirst($stage) }}</span>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ $maxFunnel ? round($count / $maxFunnel * 100) : 0 }}%;"></div></div>
                        <span class="bar-value">{{ $count }}</span>
                    </div>
                @endforeach
                @if($funnel->sum() > 0)
                    <div class="funnel-rate">
                        <i class="fa-solid fa-bullseye"></i>
                        Hire rate <b>{{ $funnel->sum() ? round($funnel['hired'] / $funnel->sum() * 100) : 0 }}%</b>
                        <span>&middot; {{ $funnel['hired'] }} hired of {{ $funnel->sum() }} candidates</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid-2" style="margin-top:20px;">
            @if($latestRun && $payrollByDept->isNotEmpty())
                <div class="card">
                    <div class="widget-head">
                        <div class="wh-ico"><i class="fa-solid fa-sack-dollar"></i></div>
                        <div>
                            <h3>Payroll cost by department</h3>
                            <p>{{ $latestRun->monthLabel() }} run.</p>
                        </div>
                        <span class="pill pill-warn" style="margin-left:auto;">₹{{ number_format($payrollByDept->sum('gross'),0) }} total</span>
                    </div>
                    @php
                        $maxPayroll = max(1, $payrollByDept->max('gross'));
                        $barTones = ['#1FA97A','#2BC08D','#5CCFA6','#8CDfC1','#B8E8D6'];
                    @endphp
                    @foreach($payrollByDept as $d)
                        <div class="bar-row">
                            <span class="bar-label">{{ $d->department }}</span>
                            <div class="bar-track"><div class="bar-fill" style="width:{{ round($d->gross / $maxPayroll * 100) }}%;background:linear-gradient(90deg,#0E6B4B,{{ $barTones[$loop->index % 5] }});"></div></div>
                            <span class="bar-value">₹{{ number_format($d->gross,0) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="card">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-clipboard-check"></i></div>
                    <div>
                        <h3>Appraisal completion</h3>
                        <p>Across all cycles on record.</p>
                    </div>
                </div>
                @php $pct = $appraisalTotal ? round($appraisalDone / $appraisalTotal * 100) : 0; @endphp
                <div class="appr-wrap">
                    <div class="donut" style="--pct:{{ $pct }};">
                        <div class="donut-hole">
                            <b>{{ $pct }}%</b>
                            <span>complete</span>
                        </div>
                    </div>
                    <div class="appr-meta">
                        <div class="appr-line"><i class="fa-solid fa-circle-check" style="color:var(--brand-bright);"></i><b>{{ $appraisalDone }}</b> completed</div>
                        <div class="appr-line"><i class="fa-regular fa-clock" style="color:#C9A227;"></i><b>{{ $appraisalTotal - $appraisalDone }}</b> pending</div>
                        <div class="appr-line"><i class="fa-solid fa-users" style="color:var(--brand);"></i><b>{{ $appraisalTotal }}</b> total appraisals</div>
                        <a href="{{ url('/modules/appraisal') }}" class="appr-link">Open Performance module <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
