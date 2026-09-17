@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @include('partials.topbar', ['title' => $roleData['label'] . ' dashboard', 'eyebrow' => 'HR Management Module'])

    <div class="content">
        @php
            $hour = (int) now()->format('G');
            $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            $greetIcon = $hour < 12 ? 'fa-solid fa-mug-hot' : ($hour < 17 ? 'fa-solid fa-sun' : 'fa-solid fa-moon');
            // Icon per KPI slot, per role — matches the order of kpisFor() in DashboardController.
            $kpiIcons = match($role) {
                'employee'    => ['fa-regular fa-calendar-check', 'fa-solid fa-plane-departure', 'fa-solid fa-money-check-dollar', 'fa-solid fa-star'],
                'manager'     => ['fa-solid fa-user-check', 'fa-solid fa-hourglass-half', 'fa-solid fa-clipboard-list', 'fa-solid fa-coins'],
                'hr_admin'    => ['fa-solid fa-users', 'fa-solid fa-sack-dollar', 'fa-solid fa-briefcase', 'fa-solid fa-chart-line'],
                default       => ['fa-solid fa-chart-pie', 'fa-solid fa-arrow-trend-up', 'fa-solid fa-indian-rupee-sign', 'fa-solid fa-shield-halved'],
            };
        @endphp
        <div class="dash-hero">
            <div class="dash-hero-text">
                @php $in = $todayAttendance?->check_in; $out = $todayAttendance?->check_out; @endphp
                <h2>{{ $greeting }}, {{ explode(' ', $authUser->name)[0] }} <i class="{{ $greetIcon }}" style="font-size:19px;color:#5FE0B2;"></i></h2>
                <p>{{ $roleData['tagline'] }}. Here's what's happening across HR today.</p>
                @if($employee && in_array($role, ['employee','manager','hr_admin']))
                    <div class="dash-hero-check">
                        @if($in && $out)
                            <span class="ci-done"><i class="fa-solid fa-circle-check"></i>Checked in {{ \Illuminate\Support\Carbon::parse($in)->format('h:i A') }} &middot; Out {{ \Illuminate\Support\Carbon::parse($out)->format('h:i A') }}</span>
                        @else
                            <form method="POST" action="{{ $out ? route('attendance.check-out') : route('attendance.check-in') }}" style="display:inline;">@csrf
                                <button type="submit" class="ci-btn {{ $in ? 'out' : '' }}">
                                    <i class="fa-solid {{ $in ? 'fa-right-from-bracket' : 'fa-fingerprint' }}"></i>
                                    {{ $in ? 'Check out' : 'Check in' }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
            @if($employee && in_array($role, ['employee','manager','hr_admin']))
            <div class="ci-box">
                <div class="ci-state"><span class="ci-pulse"></span>{{ $in && !$out ? 'On the clock' : ($in && $out ? 'Day complete' : 'Not checked in') }}</div>
                <div class="ci-clock" id="liveClock">{{ now()->format('h:i A') }}</div>
                <div class="ci-sub">
                    @if($in)
                        In {{ \Illuminate\Support\Carbon::parse($in)->format('h:i A') }}@if($out) &middot; Out {{ \Illuminate\Support\Carbon::parse($out)->format('h:i A') }}@endif
                    @else
                        Shift 09:00 &ndash; 18:00
                    @endif
                </div>
            </div>
            @endif
            <div class="dash-hero-date">
                <b>{{ now()->format('l, d M Y') }}</b>
                <span><i class="fa-regular fa-clock"></i><span id="liveClock2">{{ now()->format('h:i A') }}</span></span>
            </div>
        </div>

        @if($tickerAnnouncements->isNotEmpty())
        <div class="ticker">
            <span class="ticker-label"><i class="fa-solid fa-bullhorn"></i><span>Notices</span></span>
            <div class="ticker-items">
                @foreach($tickerAnnouncements as $ta)
                    <span class="ticker-item"><i class="fa-solid fa-circle" style="font-size:5px;"></i><b>{{ $ta->title }}</b><span class="t-date">{{ \Illuminate\Support\Carbon::parse($ta->published_at ?? $ta->created_at)->format('d M') }}</span></span>
                @endforeach
            </div>
            <a href="{{ route('announcements.index') }}" class="ticker-link">All notices <i class="fa-solid fa-chevron-right"></i></a>
        </div>
        @endif

        <div class="kpi-row">
            @foreach($kpis as $kpi)
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-ico"><i class="{{ $kpiIcons[$loop->index] ?? 'fa-solid fa-chart-simple' }}"></i></div>
                    </div>
                    <div>
                        <div class="kpi-label">{{ $kpi['label'] }}</div>
                        <div class="kpi-val">{{ $kpi['value'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="section-head">
            <h2><i class="fa-solid fa-bolt"></i>Quick actions</h2>
            <span class="hint">Jump straight into the things you do most</span>
        </div>
        <div class="quick-actions">
            @foreach($quickActions as $qa)
                <a href="{{ $qa['url'] }}" class="qa-btn"><i class="fa-solid fa-arrow-up-right-from-square"></i>{{ $qa['label'] }}</a>
            @endforeach
        </div>

        <div class="grid-2" style="margin-top:30px;">
            <div class="card">
                @if($role !== 'employee')
                    <div class="card-head">
                        <h3><i class="fa-solid fa-clipboard-check"></i>Pending approvals</h3>
                        <span class="pill pill-warn">{{ $pendingApprovals->count() }} pending</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table>
                            <tbody>
                            @forelse($pendingApprovals as $p)
                                <tr>
                                    <td class="cell-emp">
                                        <div class="av">{{ $p['initials'] }}</div>
                                        <div><b>{{ $p['employee'] }}</b><span>{{ $p['detail'] }}</span></div>
                                    </td>
                                    <td class="row-actions">
                                        <form method="POST" action="{{ $p['route'] }}">@csrf<input type="hidden" name="action" value="approve"><button class="approve" type="submit">Approve</button></form>
                                        <form method="POST" action="{{ $p['route'] }}">@csrf<input type="hidden" name="action" value="reject"><button class="reject" type="submit">Reject</button></form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td style="text-align:center;color:var(--text-muted);padding:36px 12px;">
                                    <div class="empty-state" style="padding:0;"><i class="fa-solid fa-circle-check glyph"></i>All caught up — nothing pending.</div>
                                </td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="card-head"><h3><i class="fa-regular fa-paper-plane"></i>My requests</h3><span class="pill pill-muted">Latest first</span></div>
                    <div style="overflow-x:auto;">
                        <table>
                            <tbody>
                            @forelse($myRequests as $r)
                                <tr>
                                    <td><b>{{ $r['type'] }}</b><div class="card-note" style="margin:2px 0 0;">{{ $r['detail'] }}</div></td>
                                    <td style="text-align:right;"><span class="pill {{ $r['pill'] }}">{{ $r['status'] }}</span></td>
                                </tr>
                            @empty
                                <tr><td style="text-align:center;color:var(--text-muted);padding:36px 12px;">
                                    <div class="empty-state" style="padding:0;"><i class="fa-regular fa-calendar glyph"></i>No leave, WFH, or attendance requests yet.</div>
                                </td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="stack">
                @if($deptDistribution)
                    <div class="card card-pad">
                        <h3 style="margin:0 0 12px;font-size:13.5px;"><i class="fa-solid fa-chart-pie" style="color:var(--brand);margin-right:8px;"></i>Employee distribution</h3>
                        @foreach($deptDistribution['departments'] as $d)
                            <div class="bar-row">
                                <span class="bar-label">{{ $d->name }}</span>
                                <div class="bar-track"><div class="bar-fill" style="width:{{ round($d->employees_count / $deptDistribution['max'] * 100) }}%;"></div></div>
                                <span class="bar-value">{{ $d->employees_count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($upcomingHoliday)
                    <div class="card card-pad" style="background:linear-gradient(135deg,var(--brand-softer),#fff);border-color:rgba(31,169,122,.3);">
                        <h3 style="margin:0 0 6px;font-size:13.5px;"><i class="fa-solid fa-umbrella-beach" style="color:var(--brand);margin-right:8px;"></i>Next holiday</h3>
                        <div style="font-family:var(--font-head);font-size:18px;font-weight:600;color:var(--brand-ink);">{{ $upcomingHoliday->name }}</div>
                        <p class="card-note" style="margin:4px 0 0;">
                            {{ \Illuminate\Support\Carbon::parse($upcomingHoliday->holiday_date)->format('d M Y (D)') }}
                            &middot; {{ $upcomingHoliday->is_optional ? 'Optional' : 'Mandatory' }}
                            &middot; in {{ now()->startOfDay()->diffInDays($upcomingHoliday->holiday_date) }} days
                        </p>
                    </div>
                @endif

                @if($upcomingBirthdays->isNotEmpty())
                    <div class="card card-pad">
                        <h3 style="margin:0 0 10px;font-size:13.5px;"><i class="fa-solid fa-cake-candles" style="color:var(--brand);margin-right:8px;"></i>Upcoming birthdays</h3>
                        @foreach($upcomingBirthdays as $b)
                            <div style="display:flex;justify-content:space-between;align-items:center;font-size:12.5px;padding:7px 0;border-bottom:1px solid var(--line-soft);">
                                <span class="cell-emp"><span class="av" style="width:25px;height:25px;font-size:10px;">{{ strtoupper(substr($b->user->name,0,1)) }}</span>{{ $b->user->name }}</span>
                                <span style="color:var(--brand);font-weight:600;"><i class="fa-solid fa-gift" style="font-size:10px;margin-right:5px;"></i>{{ $b->next_birthday->format('d M') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($recentActivity->isNotEmpty())
                    <div class="card card-pad">
                        <h3 style="margin:0 0 10px;font-size:13.5px;"><i class="fa-solid fa-wave-square" style="color:var(--brand);margin-right:8px;"></i>Recent activity</h3>
                        @foreach($recentActivity as $log)
                            <div style="font-size:12.5px;padding:7px 0;border-bottom:1px solid var(--line-soft);">
                                <strong>{{ $log->user->name ?? 'System' }}</strong> &middot; {{ ucfirst(strtolower($log->action)) }} on {{ str_replace('_',' ',$log->module) }}
                                <div style="color:var(--text-muted);">{{ \Illuminate\Support\Carbon::parse($log->created_at)->diffForHumans() }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <p class="access-note">Use the sidebar to switch modules, or sign out from your profile menu to sign in as someone else.</p>
    </div>
@endsection
