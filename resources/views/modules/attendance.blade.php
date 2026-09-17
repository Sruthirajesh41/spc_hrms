@extends('layouts.app')

@section('title', $module['title'])

@section('content')
@php $attendanceDepartments = $dailyAttendance ->pluck('employee.department.name') ->filter() ->unique() ->sort()
->values(); @endphp
@include('partials.topbar', [
    'title' => $module['title'],
    'eyebrow' => 'Workforce',
    'heroIcon' => 'fa-regular fa-clock',
    'heroSummary' => 'Check-in / check-out, date-wise reports, corrections and monthly summaries.',
    'heroStats' => [
        ['label' => 'Present today', 'icon' => 'fa-solid fa-user-check', 'value' => ($dailyCounts['present'] ?? 0) . '/' . ($dailyCounts['total'] ?? 0)],
        ['label' => 'WFH today', 'icon' => 'fa-solid fa-house-laptop', 'value' => $dailyCounts['wfh'] ?? 0],
        ['label' => 'Absent', 'icon' => 'fa-solid fa-user-xmark', 'value' => $dailyCounts['absent'] ?? 0],
        ['label' => 'Corrections', 'icon' => 'fa-solid fa-pen-to-square', 'value' => $pendingApprovals->count()],
    ],
])


<div class="content">
    @if($showOwnAttendance)
    <div class="grid-2">
        <div class="card">
            <div class="widget-head">
                <div class="wh-ico"><i class="fa-regular fa-clock"></i></div>
                <div>
                    <h3>Your attendance</h3>
                    <p>Check in and check out from this screen. Your latest records are shown below.</p>
                </div>
            </div>

            <div class="form-actions" style="margin:18px 0 20px;">
                @if(!$todayOwnAttendance || !$todayOwnAttendance->check_in)
                <form method="POST" action="{{ route('attendance.check-in') }}">
                    @csrf
                    <button type="submit" class="btn-primary">Check In</button>
                </form>
                @elseif(!$todayOwnAttendance->check_out)
                <form method="POST" action="{{ route('attendance.check-out') }}">
                    @csrf
                    <button type="submit" class="btn-primary">Check Out</button>
                </form>
                <span class="field-hint" style="margin-left:8px;">
                    Checked in at {{ \Illuminate\Support\Carbon::parse($todayOwnAttendance->check_in)->format('H:i') }}
                </span>
                @else
                <span class="pill pill-ok">Today's attendance completed</span>
                <span class="field-hint" style="margin-left:8px;">
                    {{ \Illuminate\Support\Carbon::parse($todayOwnAttendance->check_in)->format('H:i') }} –
                    {{ \Illuminate\Support\Carbon::parse($todayOwnAttendance->check_out)->format('H:i') }}
                </span>
                @endif
            </div>

            @if($ownRecords->isEmpty())
            <p class="field-hint">No attendance recorded yet for your account.</p>
            @else
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ownRecords as $rec)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($rec->attendance_date)->format('d M Y') }}</td>
                        <td>{{ $rec->check_in ? \Illuminate\Support\Carbon::parse($rec->check_in)->format('H:i') : '—' }}
                        </td>
                        <td>{{ $rec->check_out ? \Illuminate\Support\Carbon::parse($rec->check_out)->format('H:i') : '—' }}
                        </td>
                        <td>
                            @php
                            $p = [
                            'present'=>'pill-ok', 'late'=>'pill-warn', 'half_day'=>'pill-warn',
                            'on_leave'=>'pill-muted', 'absent'=>'pill-bad'
                            ][$rec->status] ?? 'pill-muted';
                            @endphp
                            <span class="pill {{ $p }}">{{ ucfirst(str_replace('_',' ',$rec->status)) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        <div class="stack">
            <div class="card">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-pen-to-square"></i></div>
                    <div>
                        <h3>Request regularization</h3>
                        <p>Missed punch or biometric issue? Submit it for approval.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('attendance.regularize') }}">
                    @csrf
                    <div class="field-grid">
                        <div class="field full"><label>Date</label><input type="date" name="attendance_date"
                                value="{{ now()->toDateString() }}" required></div>
                        <div class="field"><label>Check-in</label><input type="time" name="requested_check_in"
                                value="09:00"></div>
                        <div class="field"><label>Check-out</label><input type="time" name="requested_check_out"
                                value="18:00"></div>
                        <div class="field full"><label>Reason</label><textarea name="reason"
                                placeholder="Why the punch was missed" required></textarea></div>
                    </div>
                    <div class="form-actions"><button type="submit" class="btn-primary">Submit request</button></div>
                </form>
            </div>

            <div class="table-card">
                <div class="tc-head">
                    <h3><span class="wh-ico"><i class="fa-solid fa-clock-rotate-left"></i></span>Your regularization requests</h3>
                    <span class="pill pill-muted">{{ $ownRegularizations->count() }} total</span>
                </div>
                <div class="tc-body">
                @if($ownRegularizations->isEmpty())
                <div class="empty-widget">
                    <div class="ew-ico"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <b>None submitted yet</b>
                    <span>Missed-punch corrections you submit will appear here.</span>
                </div>
                @else
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ownRegularizations as $r)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->attendance->attendance_date)->format('d M Y') }}
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($r->reason, 28) }}</td>
                            <td>
                                @php $p =
                                ['approved'=>'pill-ok','pending'=>'pill-warn','rejected'=>'pill-bad'][$r->status] ??
                                'pill-muted'; @endphp
                                <span class="pill {{ $p }}">{{ ucfirst($r->status) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isAttendanceAdmin)
    <div class="tabs" style="margin-top:32px;">
        <button type="button" class="tab {{ $activeTab === 'daily' ? 'active' : '' }}" data-tab="daily"
            onclick="attendanceTab(this,'daily')">Daily (Today)</button>
        <button type="button" class="tab {{ $activeTab === 'report' ? 'active' : '' }}" data-tab="report"
            onclick="attendanceTab(this,'report')">Date-wise Report</button>
        <button type="button" class="tab {{ $activeTab === 'corrections' ? 'active' : '' }}" data-tab="corrections"
            onclick="attendanceTab(this,'corrections')">Corrections ({{ $pendingApprovals->count() }})</button>
        <button type="button" class="tab {{ $activeTab === 'monthly' ? 'active' : '' }}" data-tab="monthly"
            onclick="attendanceTab(this,'monthly')">Monthly Summary</button>
    </div>

    {{-- Daily (Today) --}}
    <div class="tabpanel {{ $activeTab === 'daily' ? 'active' : '' }}" data-tabpanel="daily">
        <div class="widget-head">
            <div class="wh-ico"><i class="fa-regular fa-clock"></i></div>
            <div>
                <h3>Daily attendance — {{ now()->format('d M Y') }}</h3>
                <p>Mark attendance for any active employee. Approved WFH is shown as WFH.</p>
            </div>
        </div>

        <div class="filters" style="display:flex;align-items:end;gap:10px;flex-wrap:wrap;margin:18px 0;">

            <div class="field" style="min-width:190px;">
                <label for="attendanceDepartmentFilter">Department</label>
                <select id="attendanceDepartmentFilter">
                    <option value="all">All Departments</option>
                    @foreach($attendanceDepartments as $department) <option value="{{ strtolower($department) }}">
                        {{ $department }}</option> @endforeach
                </select>
            </div>



            <div class="field" style="min-width:160px;">
                <label for="attendanceStatusFilter">Status</label>
                <select id="attendanceStatusFilter">
                    <option value="all">All Statuses</option>
                    <option value="present">Present</option>
                    <option value="late">Late</option>
                    <option value="half_day">Half Day</option>
                    <option value="absent">Absent</option>
                    <option value="on_leave">On Leave</option>
                    <option value="wfh">WFH</option>
                </select>
            </div>

            <div class="field" style="min-width:220px;">
                <label for="attendanceEmployeeSearch">Employee</label>
                <input id="attendanceEmployeeSearch" type="search" placeholder="Search employee...">
            </div>

            <div class="form-actions" style="margin:0;">
                <button type="button" class="employee-export" onclick="exportAttendance('daily')">↧ Export</button>
            </div>
        </div>

        <div class="stat-tiles" style="margin:16px 0;">
            <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-user-check"></i></div><div><b>{{ $dailyCounts['present'] }}</b><span>Present</span></div></div>
            <div class="stat-tile alt"><div class="st-ico"><i class="fa-regular fa-clock"></i></div><div><b>{{ $dailyCounts['late'] }}</b><span>Late</span></div></div>
            <div class="stat-tile info"><div class="st-ico"><i class="fa-solid fa-plane-departure"></i></div><div><b>{{ $dailyCounts['leave'] }}</b><span>On leave</span></div></div>
            <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-house-laptop"></i></div><div><b>{{ $dailyCounts['wfh'] }}</b><span>WFH</span></div></div>
            <div class="stat-tile warn"><div class="st-ico"><i class="fa-solid fa-user-xmark"></i></div><div><b>{{ $dailyCounts['absent'] }}</b><span>Absent</span></div></div>
            <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-layer-group"></i></div><div><b>{{ $dailyCounts['total'] }}</b><span>Total</span></div></div>
        </div>

        <div class="table-card">
            <div class="tc-body">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Late (min)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyAttendance as $row)
                    @php $e = $row->employee; @endphp
                    <tr data-attendance-department="{{ strtolower($e->department?->name ?? '') }}"
                        data-attendance-status="{{ strtolower($row->status) }}"
                        data-attendance-employee="{{ strtolower($e->user?->name ?? '') }}">
                        <td>
                            <div class="cell-emp">
                                <div class="av">
                                    {{ strtoupper(substr($e->user->name,0,1).substr(strstr($e->user->name,' ') ?: '',1,1)) }}
                                </div>
                                <div>
                                    <b>{{ $e->user->name }}</b>
                                    <span>{{ $e->department->name ?? '—' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $row->checkIn ? \Illuminate\Support\Carbon::parse($row->checkIn)->format('H:i') : '—' }}
                        </td>
                        <td>{{ $row->checkOut ? \Illuminate\Support\Carbon::parse($row->checkOut)->format('H:i') : '—' }}
                        </td>
                        <td>
                            @php
                            $pill =
                            ['present'=>'pill-ok','late'=>'pill-warn','half_day'=>'pill-warn','on_leave'=>'pill-muted','wfh'=>'pill-ok','absent'=>'pill-bad'][$row->status]
                            ?? 'pill-muted';
                            @endphp
                            <span
                                class="pill {{ $pill }}">{{ $row->status === 'wfh' ? 'WFH' : ucfirst(str_replace('_',' ',$row->status)) }}</span>
                        </td>
                        <td>{{ $row->lateMinutes ?: '—' }}</td>
                        <td>
                            @if($e->id !== optional($employee)->id)
                            <div class="row-actions">
                                <form method="POST" action="{{ route('attendance.mark', $e) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="present">
                                    <button class="approve" type="submit">Mark Present</button>
                                </form>
                                <form method="POST" action="{{ route('attendance.mark', $e) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="absent">
                                    <button class="reject" type="submit">Mark Absent</button>
                                </form>
                            </div>
                            @else
                            <span class="field-hint">Use self check-in/out</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-widget">
                                <div class="ew-ico"><i class="fa-regular fa-clock"></i></div>
                                <b>No active employees found</b>
                                <span>Daily attendance appears once employees exist.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {{-- Date-wise Report --}}
    <div class="tabpanel {{ $activeTab === 'report' ? 'active' : '' }}" data-tabpanel="report">
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
                <div class="widget-head" style="margin:0;">
                    <div class="wh-ico"><i class="fa-regular fa-calendar-check"></i></div>
                    <div>
                        <h3>Date-wise Attendance Report</h3>
                        <p>View attendance, leave and absence for a selected date.</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('attendance.index') }}"
                    style="display:flex;gap:10px;align-items:end;">
                    <input type="hidden" name="tab" value="report">
                    <div class="field" style="margin:0;">
                        <label>Date</label>
                        <input type="date" name="report_date" value="{{ $reportDate }}"
                            max="{{ now()->toDateString() }}">
                    </div>
                    <button type="submit" class="btn-primary">View Report</button>
                    <button type="submit" name="export" value="report" class="employee-export">↧ Export</button>
                </form>
            </div>

            <div class="stat-tiles" style="margin:18px 0 22px;">
                <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-user-check"></i></div><div><b>{{ $reportCounts['present'] }}</b><span>Present</span></div></div>
                <div class="stat-tile alt"><div class="st-ico"><i class="fa-regular fa-clock"></i></div><div><b>{{ $reportCounts['late'] }}</b><span>Late</span></div></div>
                <div class="stat-tile info"><div class="st-ico"><i class="fa-solid fa-plane-departure"></i></div><div><b>{{ $reportCounts['leave'] }}</b><span>On leave</span></div></div>
                <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-house-laptop"></i></div><div><b>{{ $reportCounts['wfh'] }}</b><span>WFH</span></div></div>
                <div class="stat-tile warn"><div class="st-ico"><i class="fa-solid fa-user-xmark"></i></div><div><b>{{ $reportCounts['absent'] }}</b><span>Absent</span></div></div>
                <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-layer-group"></i></div><div><b>{{ $reportCounts['total'] }}</b><span>Total</span></div></div>
            </div>
            @php $reportHasData = ($reportCounts['present'] + $reportCounts['late'] + $reportCounts['leave'] + $reportCounts['wfh']) > 0; @endphp
            @if(!$reportHasData)
            <div class="empty-widget" style="margin:22px 0 6px;">
                <div class="ew-ico"><i class="fa-regular fa-face-smile"></i></div>
                <b>No attendance recorded on {{ \Illuminate\Support\Carbon::parse($reportDate)->format('d M Y') }}</b>
                <span>This looks like a non-working day or holiday — pick a different date above.</span>
            </div>
            @else
            <div class="widget-head" style="margin:18px 0 12px;">
                <div class="wh-ico"><i class="fa-regular fa-calendar"></i></div>
                <div>
                    <h3>{{ \Illuminate\Support\Carbon::parse($reportDate)->format('d M Y') }}</h3>
                    <p>Employee attendance status for this date.</p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Late (min)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportRows as $row)
                    <tr>
                        <td>
                            <div class="cell-emp">
                                <div class="av">{{ strtoupper(substr($row->employee->user->name,0,1)) }}</div>
                                <div><b>{{ $row->employee->user->name }}</b></div>
                            </div>
                        </td>
                        <td>{{ $row->employee->department->name ?? '—' }}</td>
                        <td>{{ $row->checkIn ? \Illuminate\Support\Carbon::parse($row->checkIn)->format('H:i') : '—' }}
                        </td>
                        <td>{{ $row->checkOut ? \Illuminate\Support\Carbon::parse($row->checkOut)->format('H:i') : '—' }}
                        </td>
                        <td>
                            @php
                            $pill =
                            ['present'=>'pill-ok','late'=>'pill-warn','half_day'=>'pill-warn','on_leave'=>'pill-muted','wfh'=>'pill-ok','absent'=>'pill-bad'][$row->status]
                            ?? 'pill-muted';
                            @endphp
                            <span
                                class="pill {{ $pill }}">{{ $row->status === 'wfh' ? 'WFH' : ucfirst(str_replace('_',' ',$row->status)) }}</span>
                        </td>
                        <td>{{ $row->lateMinutes ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-widget">
                                <div class="ew-ico"><i class="fa-regular fa-calendar-check"></i></div>
                                <b>No employees found</b>
                                <span>Try a different date or check the filters.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Corrections --}}
    <div class="tabpanel {{ $activeTab === 'corrections' ? 'active' : '' }}" data-tabpanel="corrections">
        <div class="widget-head">
            <div class="wh-ico"><i class="fa-solid fa-pen-to-square"></i></div>
            <div>
                <h3>Correction requests</h3>
                <p>Regularization requests waiting for your approval.</p>
            </div>
        </div>
        <div class="table-card">
            <div class="tc-body">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Requested</th>
                        <th>Reason</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingApprovals as $r)
                    <tr>
                        <td>{{ $r->attendance->employee->user->name }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($r->attendance->attendance_date)->format('d M Y') }}
                        </td>
                        <td>{{ \Illuminate\Support\Carbon::parse($r->requested_check_in)->format('H:i') }}&ndash;{{ \Illuminate\Support\Carbon::parse($r->requested_check_out)->format('H:i') }}
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($r->reason, 34) }}</td>
                        <td>
                            <div class="row-actions">
                                <form method="POST" action="{{ route('attendance.decide', $r) }}">@csrf<input
                                        type="hidden" name="action" value="approve"><button class="approve"
                                        type="submit">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('attendance.decide', $r) }}">@csrf<input
                                        type="hidden" name="action" value="reject"><button class="reject"
                                        type="submit">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-widget">
                                <div class="ew-ico"><i class="fa-solid fa-circle-check"></i></div>
                                <b>No pending corrections</b>
                                <span>All regularization requests have been reviewed.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {{-- Monthly Summary --}}
    <div class="tabpanel {{ $activeTab === 'monthly' ? 'active' : '' }}" data-tabpanel="monthly">
        @php
        $monthlyDepartments = $monthlyRows->pluck('employee.department.name')->filter()->unique()->sort()->values();
        $monthlyHasData = $monthlyRows->contains(fn ($r) => ($r->present + $r->late + $r->leave + $r->absent) > 0);
        @endphp
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
            <div class="widget-head" style="margin:0;">
                <div class="wh-ico"><i class="fa-regular fa-calendar-days"></i></div>
                <div>
                    <h3>Monthly Summary</h3>
                    <p>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }} · Attendance breakdown per employee, month to date.</p>
                </div>
            </div>
            <form method="GET" action="{{ route('attendance.index') }}" style="display:flex;gap:10px;align-items:end;">
                <input type="hidden" name="tab" value="monthly">
                <div class="field" style="margin:0;">
                    <label>Month</label>
                    <input type="month" name="month" value="{{ $selectedMonth }}" max="{{ now()->format('Y-m') }}">
                </div>
                <button type="submit" class="btn-primary">View Month</button>
            </form>
        </div>

        @if(!$monthlyHasData)
        <div class="empty-widget" style="margin:22px 0 0;">
            <div class="ew-ico"><i class="fa-regular fa-calendar-xmark"></i></div>
            <b>No attendance recorded for {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}</b>
            <span>Check-ins will appear here as your team starts marking attendance. Try another month above.</span>
        </div>
        @else

        <div class="filters" style="display:flex;align-items:end;gap:10px;flex-wrap:wrap;margin:18px 0;">
            <div class="field" style="min-width:190px;">
                <label for="monthlyDepartmentFilter">Department</label>
                <select id="monthlyDepartmentFilter">
                    <option value="all">All Departments</option>
                    @foreach($monthlyDepartments as $department) <option value="{{ strtolower($department) }}">
                        {{ $department }}</option> @endforeach
                </select>
            </div>

            <div class="field" style="min-width:220px;">
                <label for="monthlyEmployeeSearch">Employee</label>
                <input id="monthlyEmployeeSearch" type="search" placeholder="Search employee...">
            </div>

            <div class="form-actions" style="margin:0;">
                <button type="button" class="employee-export" onclick="exportAttendance('monthly')">↧ Export</button>
            </div>
        </div>

        <div class="table-card">
            <div class="tc-body">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Present</th>
                        <th>Late</th>
                        <th>Leave</th>
                        <th>Absent</th>
                        <th>Attendance rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyRows as $row)
                    <tr data-monthly-department="{{ strtolower($row->employee->department->name ?? '') }}"
                        data-monthly-employee="{{ strtolower($row->employee->user->name ?? '') }}">
                        <td>
                            <div class="cell-emp">
                                <div class="av">{{ strtoupper(substr($row->employee->user->name,0,1)) }}</div>
                                <div><b>{{ $row->employee->user->name }}</b></div>
                            </div>
                        </td>
                        <td>{{ $row->employee->department->name ?? '—' }}</td>
                        <td>{{ $row->present }}</td>
                        <td>{{ $row->late }}</td>
                        <td>{{ $row->leave }}</td>
                        <td>{{ $row->absent }}</td>
                        <td>
                            @php
                            $pill = $row->rate >= 90 ? 'pill-ok' : ($row->rate >= 75 ? 'pill-warn' : 'pill-bad');
                            @endphp
                            <span class="pill {{ $pill }}">{{ $row->rate }}%</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-widget">
                                <div class="ew-ico"><i class="fa-regular fa-calendar-days"></i></div>
                                <b>No active employees found</b>
                                <span>Monthly summaries appear once attendance exists.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        @endif
    </div>

    <script>
    function attendanceTab(btn, name) {
        const scope = document.querySelector('.content');
        scope.querySelectorAll(':scope > .tabs .tab').forEach(t => t.classList.remove('active'));
        scope.querySelectorAll(':scope > .tabpanel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        scope.querySelectorAll('[data-tabpanel="' + name + '"]').forEach(p => p.classList.add('active'));
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


<script>
document.addEventListener('DOMContentLoaded', function() {

    const departmentFilter =
        document.getElementById('attendanceDepartmentFilter');

    const statusFilter =
        document.getElementById('attendanceStatusFilter');

    const employeeSearch =
        document.getElementById('attendanceEmployeeSearch');

    function applyAttendanceFilters() {

        const department =
            (departmentFilter?.value || 'all').toLowerCase();

        const status =
            (statusFilter?.value || 'all').toLowerCase();

        const employee =
            (employeeSearch?.value || '')
            .trim()
            .toLowerCase();

        document
            .querySelectorAll(
                'tr[data-attendance-department][data-attendance-status]'
            )
            .forEach(function(row) {

                const rowDepartment =
                    row.dataset.attendanceDepartment || '';

                const rowStatus =
                    row.dataset.attendanceStatus || '';

                const rowEmployee =
                    row.dataset.attendanceEmployee || '';

                const matchesDepartment =
                    department === 'all' ||
                    rowDepartment === department;

                const matchesStatus =
                    status === 'all' ||
                    rowStatus === status;

                const matchesEmployee = !employee ||
                    rowEmployee.includes(employee);

                row.style.display =
                    matchesDepartment &&
                    matchesStatus &&
                    matchesEmployee ?
                    '' :
                    'none';
            });
    }

    if (departmentFilter) {
        departmentFilter.addEventListener(
            'change',
            applyAttendanceFilters
        );
    }

    if (statusFilter) {
        statusFilter.addEventListener(
            'change',
            applyAttendanceFilters
        );
    }

    if (employeeSearch) {
        employeeSearch.addEventListener(
            'input',
            applyAttendanceFilters
        );
    }

    const monthlyDepartmentFilter =
        document.getElementById('monthlyDepartmentFilter');

    const monthlyEmployeeSearch =
        document.getElementById('monthlyEmployeeSearch');

    function applyMonthlyFilters() {

        const department =
            (monthlyDepartmentFilter?.value || 'all').toLowerCase();

        const employee =
            (monthlyEmployeeSearch?.value || '')
            .trim()
            .toLowerCase();

        document
            .querySelectorAll('tr[data-monthly-department]')
            .forEach(function(row) {

                const rowDepartment =
                    row.dataset.monthlyDepartment || '';

                const rowEmployee =
                    row.dataset.monthlyEmployee || '';

                const matchesDepartment =
                    department === 'all' ||
                    rowDepartment === department;

                const matchesEmployee = !employee ||
                    rowEmployee.includes(employee);

                row.style.display =
                    matchesDepartment &&
                    matchesEmployee ?
                    '' :
                    'none';
            });
    }

    if (monthlyDepartmentFilter) {
        monthlyDepartmentFilter.addEventListener(
            'change',
            applyMonthlyFilters
        );
    }

    if (monthlyEmployeeSearch) {
        monthlyEmployeeSearch.addEventListener(
            'input',
            applyMonthlyFilters
        );
    }

});

// Exports the Daily or Monthly tab, carrying whatever the client-side
// filter bar above it currently has selected. The Date-wise Report tab
// exports via its own GET form (see the "Export" submit button there).
function exportAttendance(kind) {
    const params = new URLSearchParams(window.location.search);
    params.set('tab', kind);
    params.set('export', kind);

    if (kind === 'daily') {
        params.set('dept', document.getElementById('attendanceDepartmentFilter')?.value || 'all');
        params.set('status', document.getElementById('attendanceStatusFilter')?.value || 'all');
        params.set('employee', document.getElementById('attendanceEmployeeSearch')?.value || '');
    }

    if (kind === 'monthly') {
        params.set('dept', document.getElementById('monthlyDepartmentFilter')?.value || 'all');
        params.set('employee', document.getElementById('monthlyEmployeeSearch')?.value || '');
    }

    window.location.href = "{{ route('attendance.index') }}?" + params.toString();
}
</script>

@endsection