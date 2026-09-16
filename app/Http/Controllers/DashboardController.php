<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRegularization;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\IncentivePayout;
use App\Models\JobRequisition;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\WfhRequest;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $role = $this->currentRole();
        $employee = $this->currentEmployee();

        return view('dashboard', array_merge($this->baseViewData(), [
            'kpis' => $this->kpisFor($role, $employee),
            'moduleMeta' => $this->moduleMeta($role, $employee),
            'quickActions' => $this->quickActionsFor($role, $employee),
            'pendingApprovals' => $this->pendingApprovalsFor($role, $employee),
            'myRequests' => $role === 'employee' ? $this->myRequestsFor($employee) : collect(),
            'deptDistribution' => $this->isHrOrAbove() ? $this->departmentDistribution() : null,
            'upcomingBirthdays' => $this->upcomingBirthdays(),
            'upcomingHoliday' => Holiday::where('holiday_date', '>=', now()->toDateString())->orderBy('holiday_date')->first(),
            'recentActivity' => $this->isHrOrAbove() ? AuditLog::with('user')->orderByDesc('id')->limit(6)->get() : collect(),
        ]));
    }

    private function kpisFor(string $role, ?Employee $employee): array
    {
        if ($role === 'employee' && $employee) {
            $month = now()->format('Y-m');
            $present = $employee->attendances()
                ->where('attendance_date', 'like', $month.'%')
                ->whereIn('status', ['present', 'late', 'half_day'])
                ->count();
            $workingDays = $employee->attendances()->where('attendance_date', 'like', $month.'%')->count();

            $balances = $employee->leaveBalances()->where('year', now()->year)->get();
            $totalRemaining = $balances->sum(fn ($b) => (float) $b->opening_balance + (float) $b->accrued + (float) $b->carried_forward - (float) $b->used);

            $latestPayslip = $employee->payslips()->orderByDesc('id')->first();
            $latestAppraisal = $employee->appraisals()->whereNotNull('final_rating')->orderByDesc('id')->first();

            return [
                ['label' => 'Attendance this month', 'value' => $workingDays ? "{$present} / {$workingDays} days" : '—'],
                ['label' => 'Leave balance', 'value' => number_format($totalRemaining, 1).' days'],
                ['label' => 'Last payslip', 'value' => $latestPayslip ? '₹'.number_format($latestPayslip->net_pay, 0) : '—'],
                ['label' => 'Latest appraisal rating', 'value' => $latestAppraisal ? number_format($latestAppraisal->final_rating, 1).' / 5' : 'Not rated yet'],
            ];
        }

        if ($role === 'manager' && $employee) {
            $reportIds = $employee->directReports()->pluck('id');
            $today = now()->toDateString();
            $present = \App\Models\Attendance::whereIn('employee_id', $reportIds)->where('attendance_date', $today)->whereIn('status', ['present', 'late', 'half_day'])->count();
            $pendingLeave = LeaveRequest::whereIn('employee_id', $reportIds)->where('status', 'pending')->count();
            $appraisalsDue = \App\Models\Appraisal::whereIn('employee_id', $reportIds)->where('status', '!=', 'completed')->count();
            $incentiveSum = IncentivePayout::whereIn('employee_id', $reportIds)->whereIn('status', ['approved', 'included_in_payroll', 'paid'])->sum('incentive_amount');

            return [
                ['label' => 'Team attendance today', 'value' => "{$present} / {$reportIds->count()} present"],
                ['label' => 'Pending leave approvals', 'value' => (string) $pendingLeave],
                ['label' => 'Appraisals in progress', 'value' => (string) $appraisalsDue],
                ['label' => 'Team incentive payout', 'value' => '₹'.number_format($incentiveSum, 0)],
            ];
        }

        // hr_admin / super_admin — org-wide
        $headcount = Employee::where('employment_status', 'active')->count();
        $latestRun = PayrollRun::orderByDesc('year')->orderByDesc('month')->first();
        $payrollCost = $latestRun ? $latestRun->payslips()->sum('gross_pay') : 0;
        $openPositions = JobRequisition::where('status', 'open')->sum('openings');
        $appraisalTotal = \App\Models\Appraisal::count();
        $appraisalDone = \App\Models\Appraisal::where('status', 'completed')->count();
        $completion = $appraisalTotal ? round($appraisalDone / $appraisalTotal * 100) : 0;

        if ($role === 'super_admin') {
            $today = now()->toDateString();

            // Employees who have an attendance record today and are marked
            // present, late, or half-day. Only active employees are counted.
            $presentToday = \App\Models\Attendance::whereDate('attendance_date', $today)
                ->whereIn('status', ['present', 'late', 'half_day'])
                ->whereHas('employee', function ($query) {
                    $query->where('employment_status', 'active');
                })
                ->distinct()
                ->count('employee_id');

            // Active employees with an approved WFH request covering today.
            $wfhToday = WfhRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereHas('employee', function ($query) {
                    $query->where('employment_status', 'active');
                })
                ->distinct()
                ->count('employee_id');

            return [
                ['label' => 'Present Today', 'value' => "{$presentToday} / {$headcount}"],
                ['label' => 'WFH Today', 'value' => (string) $wfhToday],
                ['label' => 'Open positions', 'value' => (string) $openPositions],
                ['label' => 'Payroll cost (latest run)', 'value' => '₹'.number_format($payrollCost, 0)],
            ];
        }

        return [
            ['label' => 'Headcount', 'value' => (string) $headcount],
            ['label' => 'Payroll cost (latest run)', 'value' => '₹'.number_format($payrollCost, 0)],
            ['label' => 'Open positions', 'value' => (string) $openPositions],
            ['label' => 'Appraisal completion', 'value' => "{$completion}%"],
        ];
    }

    /**
     * Small "N pending" / "N open" strings shown on the right of each
     * module row on the dashboard — computed live per role, same idea
     * as the reference design's module-row-meta but from real data.
     */
    private function moduleMeta(string $role, ?Employee $employee): array
    {
        $meta = [];

        $meta['attendance'] = AttendanceRegularization::where('status', 'pending')->count().' pending regularizations';
        $meta['leave'] = LeaveRequest::where('status', 'pending')->count().' pending approvals';

        $latestRun = PayrollRun::orderByDesc('year')->orderByDesc('month')->first();
        $meta['payroll'] = $latestRun ? ucfirst($latestRun->status).' — '.$latestRun->monthLabel() : 'No payroll run yet';

        $meta['recruitment'] = JobRequisition::where('status', 'open')->count().' open requisitions';
        $meta['employee-records'] = Employee::count().' profiles';

        $appraisalTotal = \App\Models\Appraisal::count();
        $appraisalDone = \App\Models\Appraisal::where('status', 'completed')->count();
        $meta['appraisal'] = $appraisalTotal ? round($appraisalDone / $appraisalTotal * 100).'% complete' : 'No cycle yet';

        $meta['pf-gratuity'] = \App\Models\GratuityRecord::where('is_eligible', true)->count().' eligible for gratuity';
        $meta['incentive'] = IncentivePayout::where('status', 'pending')->count().' payouts pending approval';
        $meta['reports'] = Employee::count().' employees in scope';
        $meta['system'] = \App\Models\AuditLog::count().' audit entries';

        $meta['wfh'] = WfhRequest::where('status', 'pending')->count().' pending requests';
        $meta['announcements'] = \App\Models\Announcement::count().' published';
        $meta['support'] = \App\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count().' open tickets';
        $meta['organization'] = Holiday::where('holiday_date', '>=', now()->toDateString())->count().' upcoming holidays';
        $meta['settings'] = 'Company & policy configuration';

        return $meta;
    }

    /**
     * Role-appropriate shortcut buttons for the dashboard's "Quick actions"
     * row — approval counts are baked into the label the way the reference
     * design does it (e.g. "Approve Leave (3)").
     */
    private function quickActionsFor(string $role, ?Employee $employee): array
    {
        if ($role === 'employee') {
            return [
                ['label' => 'Apply for Leave', 'url' => route('leave.index')],
                ['label' => 'Request WFH', 'url' => route('wfh.index')],
                ['label' => 'View Payslip', 'url' => route('payroll.index')],
                ['label' => 'Attendance Correction', 'url' => route('attendance.index')],
                ['label' => 'Help & Support', 'url' => route('support.index')],
            ];
        }

        $reportIds = ($role === 'manager' && $employee) ? $employee->directReports()->pluck('id') : null;

        $pendingLeave = LeaveRequest::where('status', 'pending')
            ->when($reportIds, fn ($q) => $q->whereIn('employee_id', $reportIds))
            ->count();
        $pendingWfh = WfhRequest::where('status', 'pending')
            ->when($reportIds, fn ($q) => $q->whereIn('employee_id', $reportIds))
            ->count();
        $pendingCorrections = AttendanceRegularization::where('status', 'pending')
            ->when($reportIds, fn ($q) => $q->whereHas('attendance', fn ($qq) => $qq->whereIn('employee_id', $reportIds)))
            ->count();

        $actions = [
            ['label' => "Approve Leave ({$pendingLeave})", 'url' => route('leave.index')],
            ['label' => "Approve WFH ({$pendingWfh})", 'url' => route('wfh.index')],
            ['label' => "Review Corrections ({$pendingCorrections})", 'url' => route('attendance.index')],
        ];

        if ($role === 'manager') {
            $actions[] = ['label' => 'Team Appraisals', 'url' => route('appraisal.index')];

            return $actions;
        }

        // hr_admin / super_admin
        $actions[] = ['label' => 'Manage Employees', 'url' => route('records.index')];
        $actions[] = ['label' => 'Run Payroll', 'url' => route('payroll.index')];
        $actions[] = ['label' => 'Generate Report', 'url' => route('reports.index')];
        $actions[] = ['label' => 'New Announcement', 'url' => route('announcements.index')];

        if ($role === 'super_admin') {
            $actions[] = ['label' => 'System & Access', 'url' => route('system.index')];
        }

        return $actions;
    }

    /**
     * Merged leave / WFH / attendance-correction queue for managers and
     * above, scoped to direct reports for managers and org-wide for HR —
     * mirrors what each module's own "pending approvals" panel shows.
     */
    private function pendingApprovalsFor(string $role, ?Employee $employee)
    {
        if (! $this->isManagerOrAbove()) {
            return collect();
        }

        $reportIds = ($role === 'manager' && $employee) ? $employee->directReports()->pluck('id') : null;

        $leave = LeaveRequest::with(['employee.user', 'leaveType'])->where('status', 'pending')
            ->when($reportIds, fn ($q) => $q->whereIn('employee_id', $reportIds))
            ->orderBy('start_date')->get()
            ->map(function ($r) {
                $name = $r->employee->user->name ?? 'Unknown';

                return [
                    'initials' => $this->initials($name),
                    'employee' => $name,
                    'detail' => 'Leave · '.$r->leaveType->name.' · '.rtrim(rtrim(number_format($r->days, 1), '0'), '.').'d',
                    'route' => route('leave.decide', $r),
                ];
            });

        $wfh = WfhRequest::with('employee.user')->where('status', 'pending')
            ->when($reportIds, fn ($q) => $q->whereIn('employee_id', $reportIds))
            ->orderBy('start_date')->get()
            ->map(function ($r) {
                $name = $r->employee->user->name ?? 'Unknown';

                return [
                    'initials' => $this->initials($name),
                    'employee' => $name,
                    'detail' => 'WFH · '.Carbon::parse($r->start_date)->format('d M'),
                    'route' => route('wfh.decide', $r),
                ];
            });

        $corrections = AttendanceRegularization::with('attendance.employee.user')->where('status', 'pending')
            ->when($reportIds, fn ($q) => $q->whereHas('attendance', fn ($qq) => $qq->whereIn('employee_id', $reportIds)))
            ->orderByDesc('id')->get()
            ->map(function ($c) {
                $name = $c->attendance->employee->user->name ?? 'Unknown';

                return [
                    'initials' => $this->initials($name),
                    'employee' => $name,
                    'detail' => 'Correction · '.Carbon::parse($c->attendance->attendance_date)->format('d M'),
                    'route' => route('attendance.decide', $c),
                ];
            });

        return $leave->concat($wfh)->concat($corrections)->take(8)->values();
    }

    /**
     * An employee's own recent leave / WFH / attendance-correction requests
     * and their current status — replaces the generic "Your modules" list
     * on the employee dashboard with something actually about them.
     */
    private function myRequestsFor(?Employee $employee)
    {
        if (! $employee) {
            return collect();
        }

        $statusPill = fn ($status) => match ($status) {
            'approved' => 'pill-ok',
            'rejected' => 'pill-bad',
            default => 'pill-warn',
        };

        $leave = LeaveRequest::with('leaveType')->where('employee_id', $employee->id)
            ->orderByDesc('id')->limit(5)->get()
            ->map(fn ($r) => [
                'type' => 'Leave',
                'detail' => $r->leaveType->name.' · '.rtrim(rtrim(number_format($r->days, 1), '0'), '.').'d · from '.Carbon::parse($r->start_date)->format('d M'),
                'status' => ucfirst($r->status),
                'pill' => $statusPill($r->status),
                'sort' => $r->id,
            ]);

        $wfh = WfhRequest::where('employee_id', $employee->id)
            ->orderByDesc('id')->limit(5)->get()
            ->map(fn ($r) => [
                'type' => 'WFH',
                'detail' => 'from '.Carbon::parse($r->start_date)->format('d M').' to '.Carbon::parse($r->end_date)->format('d M'),
                'status' => ucfirst($r->status),
                'pill' => $statusPill($r->status),
                'sort' => $r->id,
            ]);

        $corrections = AttendanceRegularization::with('attendance')->whereHas('attendance', fn ($q) => $q->where('employee_id', $employee->id))
            ->orderByDesc('id')->limit(5)->get()
            ->map(fn ($c) => [
                'type' => 'Attendance correction',
                'detail' => $c->attendance ? Carbon::parse($c->attendance->attendance_date)->format('d M Y') : '—',
                'status' => ucfirst($c->status),
                'pill' => $statusPill($c->status),
                'sort' => $c->id,
            ]);

        return $leave->concat($wfh)->concat($corrections)->sortByDesc('sort')->take(8)->values();
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));

        return strtoupper(substr($parts[0] ?? '', 0, 1).substr($parts[1] ?? '', 0, 1));
    }

    /**
     * Active headcount per department, for the dashboard's employee
     * distribution bar chart — same query shape as ReportsController.
     */
    private function departmentDistribution(): array
    {
        $departments = Department::withCount(['employees' => fn ($q) => $q->where('employment_status', 'active')])
            ->orderByDesc('employees_count')
            ->get();

        return [
            'departments' => $departments,
            'max' => max(1, $departments->max('employees_count') ?? 1),
        ];
    }

    /**
     * Employees with a birthday in the next 30 days, ordered by how soon —
     * wraps year-end correctly (e.g. late-Dec today shows early-Jan birthdays).
     */
    private function upcomingBirthdays()
    {
        $today = now();

        return Employee::with('user')
            ->whereNotNull('date_of_birth')
            ->where('employment_status', 'active')
            ->get()
            ->map(function ($e) use ($today) {
                $next = Carbon::parse($e->date_of_birth)->year($today->year);
                if ($next->lt($today->startOfDay())) {
                    $next->addYear();
                }
                $e->next_birthday = $next;
                $e->days_away = $today->startOfDay()->diffInDays($next);

                return $e;
            })
            ->filter(fn ($e) => $e->days_away <= 30)
            ->sortBy('days_away')
            ->take(5)
            ->values();
    }
}
