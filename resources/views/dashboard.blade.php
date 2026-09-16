@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @include('partials.topbar', ['title' => $roleData['label'] . ' dashboard', 'eyebrow' => 'HR Management Module'])

    <div class="content">
        <div class="kpi-row">
            @foreach($kpis as $kpi)
                <div class="kpi-card">
                    <div class="kpi-label">{{ $kpi['label'] }}</div>
                    <div class="kpi-val">{{ $kpi['value'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="section-head"><h2>Quick actions</h2></div>
        <div class="quick-actions">
            @foreach($quickActions as $qa)
                <a href="{{ $qa['url'] }}" class="qa-btn">{{ $qa['label'] }}</a>
            @endforeach
        </div>

        <div class="grid-2" style="margin-top:26px;">
            <div class="card">
                @if($role !== 'employee')
                    <div class="card-head">
                        <h3>Pending approvals</h3>
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
                                <tr><td style="text-align:center;color:var(--text-muted);padding:30px 12px;">All caught up — nothing pending.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="card-head"><h3>My requests</h3></div>
                    <div style="overflow-x:auto;">
                        <table>
                            <tbody>
                            @forelse($myRequests as $r)
                                <tr>
                                    <td><b>{{ $r['type'] }}</b><div class="card-note" style="margin:2px 0 0;">{{ $r['detail'] }}</div></td>
                                    <td style="text-align:right;"><span class="pill {{ $r['pill'] }}">{{ $r['status'] }}</span></td>
                                </tr>
                            @empty
                                <tr><td style="text-align:center;color:var(--text-muted);padding:30px 12px;">No leave, WFH, or attendance correction requests yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="stack">
                @if($deptDistribution)
                    <div class="card card-pad">
                        <h3 style="margin:0 0 10px;font-size:13.5px;">Employee distribution</h3>
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
                    <div class="card card-pad">
                        <h3 style="margin:0 0 6px;font-size:13.5px;">Next holiday</h3>
                        <div style="font-size:18px;font-weight:650;">{{ $upcomingHoliday->name }}</div>
                        <p class="card-note" style="margin:4px 0 0;">
                            {{ \Illuminate\Support\Carbon::parse($upcomingHoliday->holiday_date)->format('d M Y (D)') }}
                            &middot; {{ $upcomingHoliday->is_optional ? 'Optional' : 'Mandatory' }}
                            &middot; in {{ now()->startOfDay()->diffInDays($upcomingHoliday->holiday_date) }} days
                        </p>
                    </div>
                @endif

                @if($upcomingBirthdays->isNotEmpty())
                    <div class="card card-pad">
                        <h3 style="margin:0 0 10px;font-size:13.5px;">Upcoming birthdays</h3>
                        @foreach($upcomingBirthdays as $b)
                            <div style="display:flex;justify-content:space-between;font-size:12.5px;padding:6px 0;border-bottom:1px solid var(--line-soft);">
                                <span>{{ $b->user->name }}</span>
                                <span style="color:var(--text-muted);">{{ $b->next_birthday->format('d M') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($recentActivity->isNotEmpty())
                    <div class="card card-pad">
                        <h3 style="margin:0 0 10px;font-size:13.5px;">Recent activity</h3>
                        @foreach($recentActivity as $log)
                            <div style="font-size:12px;padding:6px 0;border-bottom:1px solid var(--line-soft);">
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
