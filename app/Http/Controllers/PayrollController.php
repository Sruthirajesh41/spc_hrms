<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PfContribution;
use App\Models\Payslip;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $module = $this->abortUnlessModuleAllowed('payroll');
        $employee = $this->currentEmployee();
        $role = $this->currentRole();

        $viewedEmployee = $employee;
        $directory = collect();

        if ($this->isHrOrAbove()) {
            $directory = Employee::with('user')->get();
            if ($request->filled('employee')) {
                $viewedEmployee = Employee::with('user')->find($request->integer('employee')) ?? $employee;
            } elseif (! $viewedEmployee) {
                $viewedEmployee = $directory->first();
            }
        }

        $salaryStructure = $viewedEmployee ? $viewedEmployee->salaryStructures()->orderByDesc('effective_from')->first() : null;
        $payslips = $viewedEmployee ? $viewedEmployee->payslips()->with('payrollRun')->orderByDesc('id')->get() : collect();

        $runs = collect();
        $runsByDepartment = collect();
        if ($this->isHrOrAbove()) {
            $runs = PayrollRun::orderByDesc('year')->orderByDesc('month')->get();
            $latestRun = $runs->first();
            if ($latestRun) {
                $runsByDepartment = Payslip::where('payroll_run_id', $latestRun->id)
                    ->join('employees', 'employees.id', '=', 'payslips.employee_id')
                    ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
                    ->selectRaw('COALESCE(departments.name, "Unassigned") as department, count(*) as headcount, sum(payslips.gross_pay) as gross')
                    ->groupBy('departments.name')
                    ->orderByDesc('gross')
                    ->get();
            }
        }

        // Super Admin only: data for the dedicated Salary Structure / Run
        // Payroll / Payslip History tabs. HR Admin and Employee use the
        // unchanged layout above and never touch any of this.
        $superAdminData = [];
        if ($role === 'super_admin') {
            $activeEmployees = Employee::with(['user', 'currentSalaryStructure'])
                ->where('employment_status', 'active')->orderBy('employee_code')->get();

            $lastRun = $runs->first();

            $superAdminData = [
                'activeEmployees' => $activeEmployees,
                'activeEmployeeCount' => $activeEmployees->count(),
                'lastCycleNetPay' => $lastRun ? $lastRun->payslips()->sum('net_pay') : 0,
                'cyclesFinalized' => $runs->count(),
                'currentCycleMonth' => now()->format('F'),
                'allPayslips' => Payslip::with(['employee.user', 'payrollRun'])
                    ->orderByDesc('id')->limit(50)->get(),
            ];
        }

        return view('modules.payroll', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'payroll',
            'viewedEmployee' => $viewedEmployee,
            'directory' => $directory,
            'salaryStructure' => $salaryStructure,
            'payslips' => $payslips,
            'runs' => $runs,
            'latestRun' => $runs->first(),
            'runsByDepartment' => $runsByDepartment,
        ], $superAdminData));
    }

    /**
     * Super Admin only: process a payroll cycle for every active employee
     * with a salary structure on file, generating one payslip and one PF
     * contribution row each. Nothing here changes what HR Admin or
     * Employees see or can do on this page.
     */
    public function runPayroll(Request $request)
    {
        $this->abortUnlessModuleAllowed('payroll');
        abort_unless($this->currentRole() === 'super_admin', 403);

        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        if (PayrollRun::where('month', $data['month'])->where('year', $data['year'])->exists()) {
            return back()->with('status', 'Payroll for that month has already been run.');
        }

        $processed = 0;

        DB::transaction(function () use ($data, &$processed) {
            $run = PayrollRun::create([
                'month' => $data['month'],
                'year' => $data['year'],
                'status' => 'processed',
                'processed_by' => $this->currentUser()->id,
                'processed_at' => now(),
            ]);

            $employees = Employee::where('employment_status', 'active')->with('currentSalaryStructure')->get();

            foreach ($employees as $employee) {
                $structure = $employee->currentSalaryStructure;
                if (! $structure) {
                    continue;
                }

                $gross = (float) $structure->gross_monthly;
                $pf = round((float) $structure->basic * 0.12, 2);
                $tds = round($gross * 0.04, 2);
                $net = $gross - $pf - $tds;

                Payslip::create([
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'gross_pay' => $gross,
                    'pf_deduction' => $pf,
                    'esi_deduction' => 0,
                    'professional_tax' => 0,
                    'tds_deduction' => $tds,
                    'other_deductions' => 0,
                    'net_pay' => $net,
                    'generated_at' => now(),
                ]);

                PfContribution::create([
                    'employee_id' => $employee->id,
                    'payroll_run_id' => $run->id,
                    'employee_share' => $pf,
                    'employer_share' => $pf,
                ]);

                $processed++;
            }
        });

        $label = \DateTime::createFromFormat('!m', $data['month'])->format('F').' '.$data['year'];

        return back()->with('status', 'Payroll run for '.$label.' processed for '.$processed.' employees.');
    }

    public function updateSalary(Request $request, Employee $employee)
    {
        $this->abortUnlessModuleAllowed('payroll');
        // Compensation changes are Super Admin only — HR Admin can view every
        // salary structure here but not edit it, same as any other employee.
        abort_unless($this->currentRole() === 'super_admin', 403);

        $data = $request->validate([
            'basic' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
            'other_allowances' => 'required|numeric|min:0',
            'variable_pay' => 'required|numeric|min:0',
            'effective_from' => 'nullable|date',
        ]);

        $effectiveFrom = $data['effective_from'] ?? now()->toDateString();
        unset($data['effective_from']);

        $gross = $data['basic'] + $data['hra'] + $data['other_allowances'] + $data['variable_pay'];

        $current = $employee->salaryStructures()->orderByDesc('effective_from')->first();
        if ($current && $current->effective_from === $effectiveFrom) {
            $current->update(array_merge($data, ['gross_monthly' => $gross]));
        } else {
            if ($current) {
                $current->update(['effective_to' => Carbon::parse($effectiveFrom)->subDay()->toDateString()]);
            }
            SalaryStructure::create(array_merge($data, [
                'employee_id' => $employee->id,
                'effective_from' => $effectiveFrom,
                'gross_monthly' => $gross,
                'created_at' => now(),
            ]));
        }

        return back()->with('status', 'Salary structure saved for '.$employee->employee_code.'.');
    }

    public function payslip(Payslip $payslip)
    {
        $this->abortUnlessModuleAllowed('payroll');
        $employee = $this->currentEmployee();

        abort_unless(
            $this->isHrOrAbove() || ($employee && $payslip->employee_id === $employee->id),
            403
        );

        $payslip->load(['employee.user', 'employee.department', 'employee.designation', 'payrollRun']);

        return view('modules.payslip-print', ['payslip' => $payslip]);
    }
}

