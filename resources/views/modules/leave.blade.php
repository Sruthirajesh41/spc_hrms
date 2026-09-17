@extends('layouts.app')

@section('title', $module['title'])

@section('content')
@include('partials.topbar', [
    'title' => $module['title'],
    'eyebrow' => 'Workforce',
    'heroIcon' => 'fa-regular fa-calendar-days',
    'heroSummary' => 'Apply for leave, track balances, and approve your team\'s requests.',
    'heroStats' => $role !== 'super_admin' ? [
        ['label' => 'My requests', 'icon' => 'fa-regular fa-paper-plane', 'value' => $ownRequests->count()],
        ['label' => 'To approve', 'icon' => 'fa-solid fa-hourglass-half', 'value' => $pendingApprovals->count()],
        ['label' => 'Days left', 'icon' => 'fa-solid fa-scale-balanced', 'value' => rtrim(rtrim(number_format($balances->sum('remaining'),1),'0'),'.').'d'],
    ] : [
        ['label' => 'To approve', 'icon' => 'fa-solid fa-hourglass-half', 'value' => $pendingApprovals->count()],
    ],
])

<div class="content">
    @if($role !== 'super_admin' && $balances->isNotEmpty())
    @php $entitled = fn($b) => $b->opening_balance + $b->accrued + $b->carried_forward; @endphp
    <div class="stat-tiles" style="grid-template-columns:repeat({{ min($balances->count(), 4) }},1fr);">
        @foreach($balances as $b)
        @php $pct = $entitled($b) > 0 ? round($b->remaining / $entitled($b) * 100) : 0; @endphp
        <div class="ring-card">
            <div class="ring" style="--pct:{{ $pct }};"><b>{{ rtrim(rtrim(number_format($b->remaining,1),'0'),'.') }}</b></div>
            <h4>{{ $b->leaveType->name }}</h4>
            <small>{{ $pct }}% of {{ rtrim(rtrim(number_format($entitled($b),1),'0'),'.') }}d left</small>
        </div>
        @endforeach
    </div>
    @endif

    @if($role !== 'super_admin')
    <div class="grid-2">
        <div class="card">
            <div class="widget-head">
                <div class="wh-ico"><i class="fa-solid fa-plane-departure"></i></div>
                <div>
                    <h3>Apply for leave</h3>
                    <p>Submitted requests are routed to your reporting manager.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('leave.apply') }}">
                @csrf
                <div class="field-grid">
                    <div class="field">
                        <label>Leave type</label>
                        <select name="leave_type_id" required>
                            @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>From</label><input type="date" name="start_date" required></div>
                    <div class="field"><label>To</label><input type="date" name="end_date" required></div>
                    <div class="field full"><label>Reason</label><textarea name="reason"
                            placeholder="Brief reason"></textarea></div>
                </div>
                <div class="form-actions"><button type="submit" class="btn-primary">Submit leave request</button></div>
            </form>
        </div>

        <div class="table-card">
            <div class="tc-head">
                <h3><span class="wh-ico"><i class="fa-regular fa-clock"></i></span>Your recent applications</h3>
                <span class="pill pill-muted">{{ $ownRequests->count() }} total</span>
            </div>
            <div class="tc-body">
                @if($ownRequests->isEmpty())
                <div class="empty-widget">
                    <div class="ew-ico"><i class="fa-regular fa-folder-open"></i></div>
                    <b>No applications yet</b>
                    <span>Your leave requests will appear here.</span>
                </div>
                @else
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ownRequests as $r)
                        <tr>
                            <td><b>{{ $r->leaveType->name }}</b> &middot; {{ rtrim(rtrim(number_format($r->days,1),'0'),'.') }}d</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->start_date)->format('M j, Y') }}&ndash;{{ \Illuminate\Support\Carbon::parse($r->end_date)->format('M j, Y') }}</td>
                            <td>
                                @php $p = ['approved'=>'pill-ok','pending'=>'pill-warn','rejected'=>'pill-bad','cancelled'=>'pill-muted'][$r->status] ?? 'pill-muted'; @endphp
                                <span class="pill {{ $p }}">{{ $r->status === 'pending' ? 'Awaiting approval' : ucfirst($r->status) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="section-head" style="margin-top:30px;">
        <h2><i class="fa-solid fa-clipboard-check"></i>Leave requests to approve</h2>
        <span class="hint">{{ $role === 'manager' ? 'From your direct reports' : 'Across the organization' }}</span>
    </div>
    <div class="table-card">
        <div class="tc-head">
            <h3><span class="wh-ico"><i class="fa-solid fa-list-check"></i></span>Pending approvals</h3>
            <span class="pill {{ $pendingApprovals->isNotEmpty() ? 'pill-warn' : 'pill-ok' }}">{{ $pendingApprovals->count() }} waiting</span>
        </div>
        <div class="tc-body">
            @if($pendingApprovals->isEmpty())
            <div class="empty-widget">
                <div class="ew-ico"><i class="fa-solid fa-circle-check"></i></div>
                <b>All caught up</b>
                <span>No leave requests waiting for your approval.</span>
            </div>
            @else
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Reason</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingApprovals as $r)
                    <tr>
                        <td>
                            <div class="cell-emp">
                                <div class="av">{{ strtoupper(substr($r->employee->user->name,0,1).substr(strstr($r->employee->user->name,' ') ?: '',1,1)) }}</div>
                                <div><b>{{ $r->employee->user->name }}</b><span>{{ $r->employee->department->name ?? '—' }}</span></div>
                            </div>
                        </td>
                        <td>{{ $r->leaveType->name }} &middot; {{ rtrim(rtrim(number_format($r->days,1),'0'),'.') }}d</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($r->start_date)->format('M j, Y') }}&ndash;{{ \Illuminate\Support\Carbon::parse($r->end_date)->format('M j, Y') }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($r->reason ?: '—', 30) }}</td>
                        <td>
                            <div class="row-actions">
                                <form method="POST" action="{{ route('leave.decide', $r) }}">@csrf<input type="hidden" name="action" value="approve"><button class="approve" type="submit">Approve</button></form>
                                <form method="POST" action="{{ route('leave.decide', $r) }}">@csrf<input type="hidden" name="action" value="reject"><button class="reject" type="submit">Reject</button></form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    @if($role === 'hr_admin' || $role === 'super_admin')
    <div class="section-head" style="margin-top:30px;">
        <h2><i class="fa-solid fa-table-list"></i>Organization leave register</h2>
        <span class="hint">All leave requests with department &amp; status filters</span>
    </div>
    <div class="stat-tiles">
        <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-layer-group"></i></div><div><b>{{ $leaveCounts['total'] }}</b><span>Total requests</span></div></div>
        <div class="stat-tile alt"><div class="st-ico"><i class="fa-solid fa-hourglass-half"></i></div><div><b>{{ $leaveCounts['pending'] }}</b><span>Pending</span></div></div>
        <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-circle-check"></i></div><div><b>{{ $leaveCounts['approved'] }}</b><span>Approved</span></div></div>
        <div class="stat-tile warn"><div class="st-ico"><i class="fa-solid fa-circle-xmark"></i></div><div><b>{{ $leaveCounts['rejected'] }}</b><span>Rejected</span></div></div>
    </div>

    <form method="GET" action="{{ route('leave.index') }}" class="filters" style="display:flex;align-items:end;gap:10px;flex-wrap:wrap;margin:0 0 16px;">
        <div class="field" style="min-width:190px;">
            <label for="leaveDeptFilter">Department</label>
            <select id="leaveDeptFilter" name="dept">
                <option value="">All Departments</option>
                @foreach($leaveDepartments as $department)
                <option value="{{ $department->id }}" @selected((string) $leaveDeptFilter===(string) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="min-width:160px;">
            <label for="leaveStatusFilter">Status</label>
            <select id="leaveStatusFilter" name="status">
                <option value="all" @selected($leaveStatusFilter==='all')>All Statuses</option>
                <option value="pending" @selected($leaveStatusFilter==='pending')>Pending</option>
                <option value="approved" @selected($leaveStatusFilter==='approved')>Approved</option>
                <option value="rejected" @selected($leaveStatusFilter==='rejected')>Rejected</option>
            </select>
        </div>
        <div class="field" style="min-width:210px;">
            <label for="leaveEmployeeFilter">Employee</label>
            <input id="leaveEmployeeFilter" name="employee" type="search" placeholder="Search employee..." value="{{ $leaveEmployeeFilter }}">
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn-primary">Apply</button>
            <button type="submit" name="export" value="csv" class="btn-secondary" style="display:inline-flex;align-items:center;gap:7px;"><i class="fa-solid fa-file-csv"></i>Export</button>
        </div>
    </form>

    <div class="table-card">
        <div class="tc-body" style="padding-top:6px;">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allLeaveRequests as $r)
                    <tr>
                        <td>
                            <div class="cell-emp">
                                <div class="av">{{ strtoupper(substr($r->employee->user->name,0,1)) }}</div>
                                <div><b>{{ $r->employee->user->name }}</b></div>
                            </div>
                        </td>
                        <td>{{ $r->employee->department->name ?? '—' }}</td>
                        <td>{{ $r->leaveType->name }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($r->start_date)->format('d M') }} &ndash; {{ \Illuminate\Support\Carbon::parse($r->end_date)->format('d M Y') }}</td>
                        <td>{{ rtrim(rtrim(number_format($r->days,1),'0'),'.') }}</td>
                        <td>
                            @php $p = ['approved'=>'pill-ok','pending'=>'pill-warn','rejected'=>'pill-bad','cancelled'=>'pill-muted'][$r->status] ?? 'pill-muted'; @endphp
                            <span class="pill {{ $p }}">{{ ucfirst($r->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6">
                        <div class="empty-widget">
                            <div class="ew-ico"><i class="fa-regular fa-calendar"></i></div>
                            <b>No leave requests found</b>
                            <span>Try adjusting the filters above.</span>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
