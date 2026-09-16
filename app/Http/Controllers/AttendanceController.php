<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderIndex($request, $request->query('tab', 'daily'));
    }

    /**
     * Standalone entry point for the Date-wise Report tab (kept as its own
     * named route since it existed already), backed by the same view/data
     * as index() with the report tab pre-selected.
     */
    public function report(Request $request)
    {
        return $this->renderIndex($request, 'report');
    }

    private function renderIndex(Request $request, string $activeTabRequested)
    {
        $module = $this->abortUnlessModuleAllowed('attendance');
        $employee = $this->currentEmployee();
        $role = $this->currentRole();
        $today = now()->toDateString();
        $isAttendanceAdmin = in_array($role, ['hr_admin', 'super_admin'], true);
        $activeTab = $isAttendanceAdmin && in_array($activeTabRequested, ['daily', 'report', 'corrections', 'monthly'], true)
            ? $activeTabRequested
            : 'daily';

        // HR Admin can see and enter their own attendance. Super Admin does not have self-attendance.
        $showOwnAttendance = $role !== 'super_admin' && $employee;

        $ownRecords = $showOwnAttendance
            ? $employee->attendances()->orderByDesc('attendance_date')->limit(14)->get()
            : collect();

        $todayOwnAttendance = $showOwnAttendance
            ? $employee->attendances()->where('attendance_date', $today)->first()
            : null;

        $ownRegularizations = $showOwnAttendance
            ? AttendanceRegularization::whereHas('attendance', fn ($q) => $q->where('employee_id', $employee->id))
                ->with('attendance')->orderByDesc('id')->limit(10)->get()
            : collect();

        $pendingApprovals = collect();
        if ($this->isManagerOrAbove()) {
            $query = AttendanceRegularization::with(['attendance.employee.user'])
                ->where('status', 'pending');

            if ($role === 'manager' && $employee) {
                $reportIds = $employee->directReports()->pluck('id');
                $query->whereHas('attendance', fn ($q) => $q->whereIn('employee_id', $reportIds));
            } elseif ($employee) {
                // HR/Super Admin must never approve their own regularization.
                $query->whereHas('attendance', fn ($q) => $q->where('employee_id', '!=', $employee->id));
            }

            $pendingApprovals = $query->orderByDesc('id')->get();
        }

        // Organization-wide daily attendance is shown only to HR Admin and Super Admin.
        $dailyRows = collect();
        $dailyCounts = ['present' => 0, 'late' => 0, 'leave' => 0, 'wfh' => 0, 'absent' => 0, 'total' => 0];
        $reportDate = $today;
        $reportRows = collect();
        $reportCounts = ['present' => 0, 'late' => 0, 'leave' => 0, 'wfh' => 0, 'absent' => 0, 'total' => 0];
        $monthlyRows = collect();
        $selectedMonth = now()->format('Y-m');

        if ($isAttendanceAdmin) {
            ['rows' => $dailyRows, 'counts' => $dailyCounts] = $this->buildAttendanceRows($today);

            // Date-wise Report tab: attendance status for any single selected date.
            $requestedDate = $request->query('report_date');
            if ($requestedDate) {
                try {
                    $reportDate = Carbon::parse($requestedDate)->toDateString();
                } catch (\Exception $e) {
                    $reportDate = $today;
                }
            }

            ['rows' => $reportRows, 'counts' => $reportCounts] = $this->buildAttendanceRows($reportDate);

            // Monthly Summary tab: per-employee attendance breakdown for a selected month (defaults to current).
            $requestedMonth = $request->query('month');
            if ($requestedMonth) {
                try {
                    $selectedMonth = Carbon::createFromFormat('Y-m', $requestedMonth)->format('Y-m');
                } catch (\Exception $e) {
                    $selectedMonth = now()->format('Y-m');
                }
            }

            $monthPrefix = $selectedMonth.'%';
            $monthlyRows = Employee::with(['user', 'department'])
                ->where('employment_status', 'active')
                ->orderBy('id')
                ->get()
                ->map(function ($e) use ($monthPrefix) {
                    $counts = $e->attendances()
                        ->where('attendance_date', 'like', $monthPrefix)
                        ->selectRaw('status, count(*) as c')
                        ->groupBy('status')
                        ->pluck('c', 'status');

                    $present = ($counts['present'] ?? 0) + ($counts['half_day'] ?? 0);
                    $late = $counts['late'] ?? 0;
                    $leave = $counts['on_leave'] ?? 0;
                    $absent = $counts['absent'] ?? 0;
                    $marked = $present + $late + $leave + $absent;
                    $rate = $marked > 0 ? round((($present + $late) / $marked) * 100) : 0;

                    return (object) [
                        'employee' => $e,
                        'present' => $present,
                        'late' => $late,
                        'leave' => $leave,
                        'absent' => $absent,
                        'rate' => $rate,
                    ];
                });

            // CSV exports for the three admin tabs. Daily/Monthly honor the same
            // dept/status/employee filters their client-side JS filter bars use.
            $exportTarget = $request->query('export');

            if ($exportTarget === 'daily') {
                return $this->exportAttendanceRows(
                    $this->filterAttendanceRows($dailyRows, $request),
                    'attendance-daily-'.$today.'.csv'
                );
            }

            if ($exportTarget === 'report') {
                return $this->exportAttendanceRows(
                    $this->filterAttendanceRows($reportRows, $request),
                    'attendance-report-'.$reportDate.'.csv'
                );
            }

            if ($exportTarget === 'monthly') {
                return $this->exportMonthlyRows(
                    $this->filterMonthlyRows($monthlyRows, $request),
                    'attendance-monthly-'.$selectedMonth.'.csv'
                );
            }
        }

        $monthSummary = $showOwnAttendance
            ? $employee->attendances()->where('attendance_date', 'like', now()->format('Y-m').'%')
                ->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status')
            : collect();

        return view('modules.attendance', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'attendance',
            'ownRecords' => $ownRecords,
            'todayOwnAttendance' => $todayOwnAttendance,
            'ownRegularizations' => $ownRegularizations,
            'pendingApprovals' => $pendingApprovals,
            'dailyAttendance' => $dailyRows,
            'dailyCounts' => $dailyCounts,
            'monthSummary' => $monthSummary,
            'showOwnAttendance' => (bool) $showOwnAttendance,
            'isAttendanceAdmin' => $isAttendanceAdmin,
            'activeTab' => $activeTab,
            'reportDate' => $reportDate,
            'reportRows' => $reportRows,
            'reportCounts' => $reportCounts,
            'monthlyRows' => $monthlyRows,
            'selectedMonth' => $selectedMonth,
        ]));
    }

    /**
     * Build attendance rows + summary counts for a given date, for the
     * organization-wide admin views (Daily and Date-wise Report tabs).
     * Falls back to approved leave/WFH when there's no punch record, so a
     * day with real data never renders as fully absent.
     */
    private function buildAttendanceRows(string $date): array
    {
        $employees = Employee::with([
            'user',
            'department',
            'attendances' => fn ($q) => $q->where('attendance_date', $date),
            'leaveRequests' => fn ($q) => $q->where('status', 'approved')
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date),
            'wfhRequests' => fn ($q) => $q->where('status', 'approved')
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date),
        ])
            ->where('employment_status', 'active')
            ->orderBy('id')
            ->get();

        $rows = $employees->map(function ($e) {
            $rec = $e->attendances->first();

            if ($rec) {
                $status = $rec->status;
            } elseif ($e->leaveRequests->isNotEmpty()) {
                $status = 'on_leave';
            } elseif ($e->wfhRequests->isNotEmpty()) {
                $status = 'wfh';
            } else {
                $status = 'absent';
            }

            return (object) [
                'employee' => $e,
                'checkIn' => $rec->check_in ?? null,
                'checkOut' => $rec->check_out ?? null,
                'status' => $status,
                'lateMinutes' => $rec->late_minutes ?? null,
            ];
        });

        $counts = [
            'present' => $rows->whereIn('status', ['present', 'half_day'])->count(),
            'late' => $rows->where('status', 'late')->count(),
            'leave' => $rows->where('status', 'on_leave')->count(),
            'wfh' => $rows->where('status', 'wfh')->count(),
            'absent' => $rows->where('status', 'absent')->count(),
            'total' => $rows->count(),
        ];

        return ['rows' => $rows, 'counts' => $counts];
    }

    /**
     * Narrow a buildAttendanceRows() collection by the same dept/status/employee
     * values the Daily and Date-wise Report filter bars use client-side, so
     * server-side CSV exports match what's currently on screen.
     */
    private function filterAttendanceRows($rows, Request $request)
    {
        $dept = strtolower((string) $request->query('dept', ''));
        $status = strtolower((string) $request->query('status', ''));
        $employee = strtolower((string) $request->query('employee', ''));

        return $rows->filter(function ($row) use ($dept, $status, $employee) {
            $rowDept = strtolower($row->employee->department->name ?? '');
            $rowStatus = strtolower($row->status);
            $rowEmployee = strtolower($row->employee->user->name ?? '');

            $matchesDept = $dept === '' || $dept === 'all' || $rowDept === $dept;
            $matchesStatus = $status === '' || $status === 'all' || $rowStatus === $status;
            $matchesEmployee = $employee === '' || str_contains($rowEmployee, $employee);

            return $matchesDept && $matchesStatus && $matchesEmployee;
        })->values();
    }

    /**
     * Narrow a Monthly Summary rows collection by department/employee, matching
     * the tab's filter bar.
     */
    private function filterMonthlyRows($rows, Request $request)
    {
        $dept = strtolower((string) $request->query('dept', ''));
        $employee = strtolower((string) $request->query('employee', ''));

        return $rows->filter(function ($row) use ($dept, $employee) {
            $rowDept = strtolower($row->employee->department->name ?? '');
            $rowEmployee = strtolower($row->employee->user->name ?? '');

            $matchesDept = $dept === '' || $dept === 'all' || $rowDept === $dept;
            $matchesEmployee = $employee === '' || str_contains($rowEmployee, $employee);

            return $matchesDept && $matchesEmployee;
        })->values();
    }

    private function exportAttendanceRows($rows, string $filename)
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee', 'Department', 'Check-in', 'Check-out', 'Status', 'Late (min)']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->employee->user->name ?? '',
                    $row->employee->department->name ?? '',
                    $row->checkIn ? Carbon::parse($row->checkIn)->format('H:i') : '',
                    $row->checkOut ? Carbon::parse($row->checkOut)->format('H:i') : '',
                    ucfirst(str_replace('_', ' ', $row->status)),
                    $row->lateMinutes ?: 0,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function exportMonthlyRows($rows, string $filename)
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee', 'Department', 'Present', 'Late', 'Leave', 'Absent', 'Attendance Rate']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->employee->user->name ?? '',
                    $row->employee->department->name ?? '',
                    $row->present,
                    $row->late,
                    $row->leave,
                    $row->absent,
                    $row->rate.'%',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function checkIn()
    {
        $this->abortUnlessModuleAllowed('attendance');
        $role = $this->currentRole();
        $employee = $this->currentEmployee();

        abort_unless($employee && in_array($role, ['employee', 'manager', 'hr_admin'], true), 403);

        $today = now()->toDateString();
        $now = now();
        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $today],
            ['status' => 'present', 'late_minutes' => 0, 'early_exit_minutes' => 0]
        );

        if ($attendance->check_in) {
            return back()->with('status', 'You have already checked in today.');
        }

        $lateMinutes = max(0, Carbon::parse($today.' 09:00:00')->diffInMinutes($now, false));
        $attendance->update([
            'check_in' => $now,
            'status' => $lateMinutes > 0 ? 'late' : 'present',
            'late_minutes' => $lateMinutes,
        ]);

        return back()->with('status', 'Attendance checked in at '.$now->format('H:i').'.');
    }

    public function checkOut()
    {
        $this->abortUnlessModuleAllowed('attendance');
        $role = $this->currentRole();
        $employee = $this->currentEmployee();

        abort_unless($employee && in_array($role, ['employee', 'manager', 'hr_admin'], true), 403);

        $today = now()->toDateString();
        $now = now();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        abort_unless($attendance, 422, 'Please check in before checking out.');

        if (! $attendance->check_in) {
            return back()->with('status', 'Please check in before checking out.');
        }

        if ($attendance->check_out) {
            return back()->with('status', 'You have already checked out today.');
        }

        $earlyExitMinutes = max(0, $now->diffInMinutes(Carbon::parse($today.' 18:00:00'), false));
        $attendance->update([
            'check_out' => $now,
            'early_exit_minutes' => $earlyExitMinutes,
        ]);

        return back()->with('status', 'Attendance checked out at '.$now->format('H:i').'.');
    }

    public function mark(Request $request, Employee $employee)
    {
        $this->abortUnlessModuleAllowed('attendance');
        abort_unless(in_array($this->currentRole(), ['hr_admin', 'super_admin'], true), 403);
        abort_unless($employee->employment_status === 'active', 422, 'Only active employees can be marked.');

        // Super Admin does not have self-attendance, and HR uses their own check-in/check-out buttons.
        if ($employee->id === optional($this->currentEmployee())->id) {
            abort(403, 'Use your own check-in/check-out controls for self attendance.');
        }

        $data = $request->validate(['status' => 'required|in:present,absent']);
        $today = now()->toDateString();
        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $today],
            ['status' => 'absent', 'late_minutes' => 0, 'early_exit_minutes' => 0]
        );

        if ($data['status'] === 'present') {
            $checkIn = $attendance->check_in ?: now();
            $lateMinutes = max(0, Carbon::parse($today.' 09:00:00')->diffInMinutes(Carbon::parse($checkIn), false));
            $attendance->update([
                'status' => $lateMinutes > 0 ? 'late' : 'present',
                'check_in' => $checkIn,
                'late_minutes' => $lateMinutes,
            ]);
        } else {
            $attendance->update([
                'status' => 'absent',
                'check_in' => null,
                'check_out' => null,
                'late_minutes' => 0,
                'early_exit_minutes' => 0,
            ]);
        }

        return back()->with('status', $employee->user->name.' marked '.ucfirst($data['status']).' for today.');
    }

    public function regularize(Request $request)
    {
        $this->abortUnlessModuleAllowed('attendance');
        $employee = $this->currentEmployee();
        $role = $this->currentRole();
        abort_unless($employee && $role !== 'super_admin', 403);

        $data = $request->validate([
            'attendance_date' => 'required|date',
            'requested_check_in' => 'nullable',
            'requested_check_out' => 'nullable',
            'reason' => 'required|string|max:255',
        ]);

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $data['attendance_date']],
            ['status' => 'absent', 'late_minutes' => 0, 'early_exit_minutes' => 0]
        );

        AttendanceRegularization::create([
            'attendance_id' => $attendance->id,
            'requested_check_in' => $data['attendance_date'].' '.($data['requested_check_in'] ?: '09:00').':00',
            'requested_check_out' => $data['attendance_date'].' '.($data['requested_check_out'] ?: '18:00').':00',
            'reason' => $data['reason'],
            'status' => 'pending',
            'created_at' => now(),
        ]);

        return back()->with('status', "Regularization request submitted for your manager's approval.");
    }

    public function decide(Request $request, AttendanceRegularization $regularization)
    {
        $this->abortUnlessModuleAllowed('attendance');
        abort_unless($this->isManagerOrAbove(), 403);

        $regularization->loadMissing('attendance.employee');
        $currentEmployee = $this->currentEmployee();
        if ($currentEmployee && $regularization->attendance && $regularization->attendance->employee_id === $currentEmployee->id) {
            abort(403, 'You cannot approve your own regularization.');
        }

        $data = $request->validate(['action' => 'required|in:approve,reject']);

        if ($data['action'] === 'approve') {
            $regularization->attendance()->update([
                'check_in' => $regularization->requested_check_in,
                'check_out' => $regularization->requested_check_out,
                'status' => 'present',
                'late_minutes' => 0,
                'early_exit_minutes' => 0,
            ]);
        }

        $regularization->update([
            'status' => $data['action'] === 'approve' ? 'approved' : 'rejected',
            'approved_by' => $this->currentUser()->id,
            'approved_at' => now(),
        ]);

        return back()->with('status', 'Regularization request '.($data['action'] === 'approve' ? 'approved.' : 'rejected.'));
    }
}
