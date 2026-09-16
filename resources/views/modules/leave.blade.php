@extends('layouts.app')

@section('title', $module['title'])

@section('content')
@include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

<div class="content">
    @if($role !== 'super_admin' && $balances->isNotEmpty())
    <div class="grid-4" style="margin-bottom:28px;">
        @foreach($balances as $b)
        <div class="card">
            <h3>{{ $b->leaveType->name }}</h3>
            <div class="serif" style="font-size:22px;">{{ number_format($b->remaining, 1) }}</div>
            <div class="card-note" style="margin:2px 0 0;">of
                {{ number_format($b->opening_balance + $b->accrued + $b->carried_forward, 1) }} days remaining</div>
        </div>
        @endforeach
    </div>
    @endif

    @if($role !== 'super_admin')
    <div class="grid-2">
        <div class="card">
            <h3>Apply for leave</h3>
            <p class="card-note">Submitted requests are routed to your reporting manager.</p>
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

        <div class="card">
            <h3>Your recent leave applications</h3>
            @if($ownRequests->isEmpty())
            <p class="field-hint">No leave applications yet.</p>
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
                        <td>{{ $r->leaveType->name }} &middot; {{ rtrim(rtrim(number_format($r->days,1),'0'),'.') }}d
                        </td>
                        <td>{{ \Illuminate\Support\Carbon::parse($r->start_date)->format('M j, Y') }}&ndash;{{ \Illuminate\Support\Carbon::parse($r->end_date)->format('M j, Y') }}
                        </td>
                        <td>
                            @php $p =
                            ['approved'=>'pill-ok','pending'=>'pill-warn','rejected'=>'pill-bad','cancelled'=>'pill-muted'][$r->status]
                            ?? 'pill-muted'; @endphp
                            <span
                                class="pill {{ $p }}">{{ $r->status === 'pending' ? 'Awaiting approval' : ucfirst($r->status) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    @endif

    @if($pendingApprovals->isNotEmpty())
    <h2 class="section-title" style="margin-top:32px;">Leave Requests to Approve</h2>
    <p class="section-note">Pending leave requests
        {{ $role === 'manager' ? 'from your direct reports' : 'across the organization' }}.</p>
    <div class="card">
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
                    <td>{{ $r->employee->user->name }}</td>
                    <td>{{ $r->leaveType->name }} &middot; {{ rtrim(rtrim(number_format($r->days,1),'0'),'.') }}d</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($r->start_date)->format('M j, Y') }}&ndash;{{ \Illuminate\Support\Carbon::parse($r->end_date)->format('M j, Y') }}
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($r->reason ?: '—', 30) }}</td>
                    <td>
                        <div class="row-actions">
                            <form method="POST" action="{{ route('leave.decide', $r) }}">@csrf<input type="hidden"
                                    name="action" value="approve"><button class="approve" type="submit">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('leave.decide', $r) }}">@csrf<input type="hidden"
                                    name="action" value="reject"><button class="reject" type="submit">Reject</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($role === 'hr_admin' || $role === 'super_admin')
    <h2 class="section-title" style="margin-top:32px;">All Leave Requests</h2>
    <p class="section-note">Full leave register across the organization — pending, approved and rejected.</p>

    <form method="GET" action="{{ route('leave.index') }}" class="filters"
        style="display:flex;align-items:end;gap:10px;flex-wrap:wrap;margin:18px 0;">
        <div class="field" style="min-width:190px;">
            <label for="leaveDepartmentFilter">Department</label>
            <select id="leaveDepartmentFilter" name="dept">
                <option value="">All Departments</option>
                @foreach($leaveDepartments as $department)
                <option value="{{ $department->id }}" @selected((string) $leaveDeptFilter===(string) $department->
                    id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="field" style="min-width:160px;">
            <label for="leaveStatusFilter">Status</label>
            <select id="leaveStatusFilter" name="status">
                <option value="all" @selected($leaveStatusFilter==='all' )>All Statuses</option>
                <option value="pending" @selected($leaveStatusFilter==='pending' )>Pending</option>
                <option value="approved" @selected($leaveStatusFilter==='approved' )>Approved</option>
                <option value="rejected" @selected($leaveStatusFilter==='rejected' )>Rejected</option>
            </select>
        </div>

        <div class="field" style="min-width:220px;">
            <label for="leaveEmployeeFilter">Employee</label>
            <input id="leaveEmployeeFilter" type="search" name="employee" placeholder="Search employee..."
                value="{{ $leaveEmployeeFilter }}">
        </div>

        <div class="form-actions" style="margin:0;">
            <button type="submit" class="btn-primary">Apply Filters</button>
            <button type="button" class="employee-export" onclick="exportLeaveRequests()">↧ Export</button>
        </div>
    </form>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;">
        <span class="pill pill-warn">Pending: {{ $leaveCounts['pending'] }}</span>
        <span class="pill pill-ok">Approved: {{ $leaveCounts['approved'] }}</span>
        <span class="pill pill-bad">Rejected: {{ $leaveCounts['rejected'] }}</span>
        <span class="pill pill-muted">Total: {{ $leaveCounts['total'] }}</span>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Dates</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allLeaveRequests as $r)
                <tr>
                    <td>{{ $r->employee->user->name }}</td>
                    <td>{{ $r->employee->department->name ?? '—' }}</td>
                    <td>{{ $r->leaveType->name }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($r->start_date)->format('M j, Y') }}&ndash;{{ \Illuminate\Support\Carbon::parse($r->end_date)->format('M j, Y') }}
                    </td>
                    <td>{{ rtrim(rtrim(number_format($r->days,1),'0'),'.') }}</td>
                    <td>
                        @php $p =
                        ['approved'=>'pill-ok','pending'=>'pill-warn','rejected'=>'pill-bad','cancelled'=>'pill-muted'][$r->status]
                        ?? 'pill-muted'; @endphp
                        <span
                            class="pill {{ $p }}">{{ $r->status === 'pending' ? 'Awaiting approval' : ucfirst($r->status) }}</span>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($r->reason ?: '—', 30) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">No leave requests match these filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
    function exportLeaveRequests() {
        const params = new URLSearchParams(window.location.search);
        params.set('dept', document.getElementById('leaveDepartmentFilter').value);
        params.set('status', document.getElementById('leaveStatusFilter').value);
        params.set('employee', document.getElementById('leaveEmployeeFilter').value);
        params.set('export', 'csv');
        window.location.href = "{{ route('leave.index') }}?" + params.toString();
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