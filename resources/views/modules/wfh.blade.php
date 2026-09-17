@extends('layouts.app')

@section('title', $module['title'])

@section('content')
@include('partials.topbar', [
    'title' => $module['title'],
    'eyebrow' => 'Workforce',
    'heroIcon' => 'fa-solid fa-house-laptop',
    'heroSummary' => 'Request work from home and manage your team\'s requests.',
    'heroStats' => $role !== 'super_admin' ? [
        ['label' => 'My requests', 'icon' => 'fa-regular fa-paper-plane', 'value' => $ownRequests->count()],
        ['label' => 'To approve', 'icon' => 'fa-solid fa-hourglass-half', 'value' => $pendingApprovals->count()],
    ] : [
        ['label' => 'To approve', 'icon' => 'fa-solid fa-hourglass-half', 'value' => $pendingApprovals->count()],
        ['label' => 'All requests', 'icon' => 'fa-solid fa-layer-group', 'value' => $wfhCounts['total'] ?? 0],
    ],
])

<div class="content">
    @if($role !== 'super_admin')
    <div class="grid-2">
        <div class="card">
            <div class="widget-head">
                <div class="wh-ico"><i class="fa-solid fa-house-laptop"></i></div>
                <div>
                    <h3>Request work from home</h3>
                    <p>Routed to your reporting manager for approval.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('wfh.store') }}">
                @csrf
                <div class="field-grid">
                    <div class="field"><label>From</label><input type="date" name="start_date" required></div>
                    <div class="field"><label>To</label><input type="date" name="end_date" required></div>
                    <div class="field"><label>Location</label><input name="location" placeholder="e.g. Home — Kochi"></div>
                    <div class="field"><label>Contact number</label><input name="contact_number" placeholder="Reachable number"></div>
                    <div class="field full"><label>Reason</label><textarea name="reason" placeholder="Brief reason"></textarea></div>
                </div>
                <div class="form-actions"><button type="submit" class="btn-primary">Submit WFH request</button></div>
            </form>
        </div>

        <div class="table-card">
            <div class="tc-head">
                <h3><span class="wh-ico"><i class="fa-regular fa-clock"></i></span>Your recent WFH requests</h3>
                <span class="pill pill-muted">{{ $ownRequests->count() }} total</span>
            </div>
            <div class="tc-body">
                @if($ownRequests->isEmpty())
                <div class="empty-widget">
                    <div class="ew-ico"><i class="fa-regular fa-folder-open"></i></div>
                    <b>No WFH requests yet</b>
                    <span>Submit your first request from the form.</span>
                </div>
                @else
                <table>
                    <thead>
                        <tr>
                            <th>Dates</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ownRequests as $r)
                        <tr>
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
        <h2><i class="fa-solid fa-clipboard-check"></i>WFH requests to approve</h2>
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
                <span>No WFH requests waiting for your approval.</span>
            </div>
            @else
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Dates</th>
                        <th>Location</th>
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
                        <td>{{ \Illuminate\Support\Carbon::parse($r->start_date)->format('M j, Y') }}&ndash;{{ \Illuminate\Support\Carbon::parse($r->end_date)->format('M j, Y') }}</td>
                        <td>{{ $r->location ?: '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($r->reason ?: '—', 30) }}</td>
                        <td>
                            <div class="row-actions">
                                <form method="POST" action="{{ route('wfh.decide', $r) }}">@csrf<input type="hidden" name="action" value="approve"><button class="approve" type="submit">Approve</button></form>
                                <form method="POST" action="{{ route('wfh.decide', $r) }}">@csrf<input type="hidden" name="action" value="reject"><button class="reject" type="submit">Reject</button></form>
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
        <h2><i class="fa-solid fa-table-list"></i>All WFH requests</h2>
        <span class="hint">Full register — filter by department, status or employee</span>
    </div>
    <div class="stat-tiles">
        <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-layer-group"></i></div><div><b>{{ $wfhCounts['total'] }}</b><span>Total</span></div></div>
        <div class="stat-tile alt"><div class="st-ico"><i class="fa-solid fa-hourglass-half"></i></div><div><b>{{ $wfhCounts['pending'] }}</b><span>Pending</span></div></div>
        <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-circle-check"></i></div><div><b>{{ $wfhCounts['approved'] }}</b><span>Approved</span></div></div>
        <div class="stat-tile warn"><div class="st-ico"><i class="fa-solid fa-circle-xmark"></i></div><div><b>{{ $wfhCounts['rejected'] }}</b><span>Rejected</span></div></div>
    </div>

    <form method="GET" action="{{ route('wfh.index') }}" class="filters" style="display:flex;align-items:end;gap:10px;flex-wrap:wrap;margin:0 0 16px;">
        <div class="field" style="min-width:190px;">
            <label for="wfhDepartmentFilter">Department</label>
            <select id="wfhDepartmentFilter" name="dept">
                <option value="">All Departments</option>
                @foreach($wfhDepartments as $department)
                <option value="{{ $department->id }}" @selected((string) $wfhDeptFilter===(string) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="min-width:160px;">
            <label for="wfhStatusFilter">Status</label>
            <select id="wfhStatusFilter" name="status">
                <option value="all" @selected($wfhStatusFilter==='all')>All Statuses</option>
                <option value="pending" @selected($wfhStatusFilter==='pending')>Pending</option>
                <option value="approved" @selected($wfhStatusFilter==='approved')>Approved</option>
                <option value="rejected" @selected($wfhStatusFilter==='rejected')>Rejected</option>
            </select>
        </div>
        <div class="field" style="min-width:220px;">
            <label for="wfhEmployeeFilter">Employee</label>
            <input id="wfhEmployeeFilter" type="search" name="employee" placeholder="Search employee..." value="{{ $wfhEmployeeFilter }}">
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn-primary">Apply</button>
            <button type="button" class="btn-secondary" onclick="exportWfhRequests()" style="display:inline-flex;align-items:center;gap:7px;"><i class="fa-solid fa-file-csv"></i>Export</button>
        </div>
    </form>

    <div class="table-card">
        <div class="tc-body" style="padding-top:6px;">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Dates</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allWfhRequests as $r)
                    <tr>
                        <td>
                            <div class="cell-emp">
                                <div class="av">{{ strtoupper(substr($r->employee->user->name,0,1)) }}</div>
                                <div><b>{{ $r->employee->user->name }}</b></div>
                            </div>
                        </td>
                        <td>{{ $r->employee->department->name ?? '—' }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($r->start_date)->format('d M') }} &ndash; {{ \Illuminate\Support\Carbon::parse($r->end_date)->format('d M Y') }}</td>
                        <td>{{ $r->location ?: '—' }}</td>
                        <td>
                            @php $p = ['approved'=>'pill-ok','pending'=>'pill-warn','rejected'=>'pill-bad','cancelled'=>'pill-muted'][$r->status] ?? 'pill-muted'; @endphp
                            <span class="pill {{ $p }}">{{ $r->status === 'pending' ? 'Awaiting approval' : ucfirst($r->status) }}</span>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($r->reason ?: '—', 30) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6">
                        <div class="empty-widget">
                            <div class="ew-ico"><i class="fa-solid fa-house-laptop"></i></div>
                            <b>No WFH requests match these filters</b>
                            <span>Try adjusting the filters above.</span>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function exportWfhRequests() {
        const params = new URLSearchParams(window.location.search);
        params.set('dept', document.getElementById('wfhDepartmentFilter').value);
        params.set('status', document.getElementById('wfhStatusFilter').value);
        params.set('employee', document.getElementById('wfhEmployeeFilter').value);
        params.set('export', 'csv');
        window.location.href = "{{ route('wfh.index') }}?" + params.toString();
    }
    </script>
    @endif

    <p class="access-note">
        Visible to:
        @foreach($module['roles'] as $r)
        {{ $roles[$r]['label'] }}{{ !$loop->last ? ', ' : '' }}
        @endforeach
    </p>
</div>
@endsection
