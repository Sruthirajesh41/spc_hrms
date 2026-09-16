<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $module = $this->abortUnlessModuleAllowed('payroll');
        $employee = $this->currentEmployee();

        $viewedEmployee = $employee;
        $directory = collect();

        if ($this->currentRole() === 'super_admin') {
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
        if ($this->currentRole() === 'super_admin') {
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
        ]));
    }

    public function updateSalary(Request $request, Employee $employee)
    {
        $this->abortUnlessModuleAllowed('payroll');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'basic' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
            'other_allowances' => 'required|numeric|min:0',
            'variable_pay' => 'required|numeric|min:0',
        ]);

        $gross = $data['basic'] + $data['hra'] + $data['other_allowances'] + $data['variable_pay'];

        $current = $employee->salaryStructures()->orderByDesc('effective_from')->first();
        if ($current && $current->effective_from === now()->toDateString()) {
            $current->update(array_merge($data, ['gross_monthly' => $gross]));
        } else {
            if ($current) {
                $current->update(['effective_to' => now()->subDay()->toDateString()]);
            }
            SalaryStructure::create(array_merge($data, [
                'employee_id' => $employee->id,
                'effective_from' => now()->toDateString(),
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
