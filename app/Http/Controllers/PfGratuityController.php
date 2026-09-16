<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class PfGratuityController extends Controller
{
    public function index(Request $request)
    {
        $module = $this->abortUnlessModuleAllowed('pf-gratuity');
        $employee = $this->currentEmployee();

        $directory = collect();
        $viewed = $employee;

        if ($this->isHrOrAbove()) {
            $directory = Employee::with('user')->orderBy('employee_code')->get();
            $viewed = $request->filled('employee')
                ? Employee::find($request->integer('employee'))
                : ($employee ?: $directory->first());
        }

        $pfAccount = $viewed ? $viewed->pfAccount : null;
        $contributions = $viewed
            ? $viewed->pfContributions()->with('payrollRun')->orderByDesc('id')->limit(12)->get()
            : collect();
        $gratuity = $viewed ? $viewed->gratuityRecord : null;

        return view('modules.pf-gratuity', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'pf-gratuity',
            'directory' => $directory,
            'viewed' => $viewed,
            'pfAccount' => $pfAccount,
            'contributions' => $contributions,
            'gratuity' => $gratuity,
        ]));
    }
}
