@extends('layouts.app')

@section('title', $module['title'])

@section('content')
@include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

<div class="content">
    @if($role !== 'super_admin')
    <div class="grid-2">
        <div class="card">
            <h3>Request work from home</h3>
            <p class="card-note">Routed to your reporting manager for approval.</p>
            <form method="POST" action="{{ route('wfh.store') }}">
                @csrf
                <div class="field-grid">
                    <div class="field"><label>From</label><input type="date" name="start_date" required></div>
                    <div class="field"><label>To</label><input type="date" name="end_date" required></div>
                    <div class="field"><label>Location</label><input name="location" placeholder="e.g. Home — Kochi">
                    </div>
                    <div class="field"><label>Contact number</label><input name="contact_number"
                            placeholder="Reachable number"></div>
                    <div class="field full"><label>Reason</label><textarea name="reason"
                            placeholder="Brief reason"></textarea></div>
                </div>
                <div class="form-actions"><button type="submit" class="btn-primary">Submit WFH request</button></div>
            </form>
        </div>

        <div class="card">
            <h3>Your recent WFH requests</h3>
            @if($ownRequests->isEmpty())
            <p class="field-hint">No WFH requests yet.</p>
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
    <h2 class="section-title" style="margin-top:32px;">WFH Requests to Approve</h2>
    <p class="section-note">Pending requests
        {{ $role === 'manager' ? 'from your direct reports' : 'across the organization' }}.</p>
    <div class="card">
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
                    <td>{{ $r->employee->user->name }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($r->start_date)->format('M j, Y') }}&ndash;{{ \Illuminate\Support\Carbon::parse($r->end_date)->format('M j, Y') }}
                    </td>
                    <td>{{ $r->location ?: '—' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($r->reason ?: '—', 30) }}</td>
                    <td>
                        <div class="row-actions">
                            <form method="POST" action="{{ route('wfh.decide', $r) }}">@csrf<input type="hidden"
                                    name="action" value="approve"><button class="approve" type="submit">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('wfh.decide', $r) }}">@csrf<input type="hidden"
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
    <h2 class="section-title" style="margin-top:32px;">All WFH Requests</h2>
    <p class="section-note">Full WFH register across the organization — pending, approved and rejected.</p>

    <form method="GET" action="{{ route('wfh.index') }}" class="filters"
        style="display:flex;align-items:end;gap:10px;flex-wrap:wrap;margin:18px 0;">
        <div class="field" style="min-width:190px;">
            <label for="wfhDepartmentFilter">Department</label>
            <select id="wfhDepartmentFilter" name="dept">
                <option value="">All Departments</option>
                @foreach($wfhDepartments as $department)
                <option value="{{ $department->id }}" @selected((string) $wfhDeptFilter===(string) $department->
                    id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="field" style="min-width:160px;">
            <label for="wfhStatusFilter">Status</label>
            <select id="wfhStatusFilter" name="status">
                <option value="all" @selected($wfhStatusFilter==='all' )>All Statuses</option>
                <option value="pending" @selected($wfhStatusFilter==='pending' )>Pending</option>
                <option value="approved" @selected($wfhStatusFilter==='approved' )>Approved</option>
                <option value="rejected" @selected($wfhStatusFilter==='rejected' )>Rejected</option>
            </select>
        </div>

        <div class="field" style="min-width:220px;">
            <label for="wfhEmployeeFilter">Employee</label>
            <input id="wfhEmployeeFilter" type="search" name="employee" placeholder="Search employee..."
                value="{{ $wfhEmployeeFilter }}">
        </div>

        <div class="form-actions" style="margin:0;">
            <button type="submit" class="btn-primary">Apply Filters</button>
            <button type="button" class="employee-export" onclick="exportWfhRequests()">↧ Export</button>
        </div>
    </form>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;">
        <span class="pill pill-warn">Pending: {{ $wfhCounts['pending'] }}</span>
        <span class="pill pill-ok">Approved: {{ $wfhCounts['approved'] }}</span>
        <span class="pill pill-bad">Rejected: {{ $wfhCounts['rejected'] }}</span>
        <span class="pill pill-muted">Total: {{ $wfhCounts['total'] }}</span>
    </div>

    <div class="card">
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
                    <td>{{ $r->employee->user->name }}</td>
                    <td>{{ $r->employee->department->name ?? '—' }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($r->start_date)->format('M j, Y') }}&ndash;{{ \Illuminate\Support\Carbon::parse($r->end_date)->format('M j, Y') }}
                    </td>
                    <td>{{ $r->location ?: '—' }}</td>
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
                    <td colspan="6" class="empty-state">No WFH requests match these filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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