<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $module = $this->abortUnlessModuleAllowed('reports');

        $headcountByDept = Department::withCount(['employees' => fn ($q) => $q->where('employment_status', 'active')])
            ->orderByDesc('employees_count')->get();
        $maxHeadcount = max(1, $headcountByDept->max('employees_count') ?? 1);

        $funnelStages = ['applied', 'shortlisted', 'interviewed', 'offered', 'hired'];
        $funnel = collect($funnelStages)->mapWithKeys(fn ($stage) => [$stage => Candidate::where('stage', $stage)->count()]);
        $maxFunnel = max(1, $funnel->max() ?: 1);

        $appraisalTotal = \App\Models\Appraisal::count();
        $appraisalDone = \App\Models\Appraisal::where('status', 'completed')->count();

        $latestRun = \App\Models\PayrollRun::orderByDesc('year')->orderByDesc('month')->first();
        $payrollByDept = collect();
        if ($latestRun) {
            $payrollByDept = \App\Models\Payslip::where('payroll_run_id', $latestRun->id)
                ->join('employees', 'employees.id', '=', 'payslips.employee_id')
                ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
                ->selectRaw('COALESCE(departments.name, "Unassigned") as department, sum(payslips.gross_pay) as gross')
                ->groupBy('departments.name')->orderByDesc('gross')->get();
        }

        $today = now()->toDateString();

        return view('modules.reports', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'reports',
            'departments' => Department::orderBy('name')->get(),
            'headcountByDept' => $headcountByDept,
            'maxHeadcount' => $maxHeadcount,
            'funnel' => $funnel,
            'maxFunnel' => $maxFunnel,
            'appraisalTotal' => $appraisalTotal,
            'appraisalDone' => $appraisalDone,
            'latestRun' => $latestRun,
            'payrollByDept' => $payrollByDept,
            'presentToday' => \App\Models\Attendance::whereDate('attendance_date', $today)->whereIn('status', ['present', 'late'])->count(),
            'lateToday' => \App\Models\Attendance::whereDate('attendance_date', $today)->where('status', 'late')->count(),
            'wfhToday' => \App\Models\WfhRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count(),
            'onLeaveToday' => \App\Models\LeaveRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count(),
            'leavePending' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
            'openRequisitions' => \App\Models\JobRequisition::where('status', 'open')->count(),
            'reportType' => $request->string('report_type')->toString() ?: 'headcount',
        ]));
    }
}
