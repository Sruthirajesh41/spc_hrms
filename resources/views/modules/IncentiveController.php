<?php

namespace App\Http\Controllers;

use App\Models\IncentivePayout;
use App\Models\IncentiveRule;
use Illuminate\Http\Request;

class IncentiveController extends Controller
{
    public function index()
    {
        $module = $this->abortUnlessModuleAllowed('incentive');
        $employee = $this->currentEmployee();
        $role = $this->currentRole();

        $rules = IncentiveRule::orderByDesc('id')->get();

        $pendingPayouts = collect();
        if ($this->currentRole() === 'super_admin') {
            $query = IncentivePayout::with(['employee.user', 'rule'])->where('status', 'pending');
            if ($role === 'manager' && $employee) {
                $reportIds = $employee->directReports()->pluck('id');
                $query->whereIn('employee_id', $reportIds);
            }
            $pendingPayouts = $query->orderByDesc('year')->orderByDesc('month')->get();
        }

        $ownPayouts = $employee
            ? $employee->incentivePayouts()->with('rule')->orderByDesc('year')->orderByDesc('month')->get()
            : collect();

        return view('modules.incentive', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'incentive',
            'rules' => $rules,
            'pendingPayouts' => $pendingPayouts,
            'ownPayouts' => $ownPayouts,
        ]));
    }

    public function storeRule(Request $request)
    {
        $this->abortUnlessModuleAllowed('incentive');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'applies_to_role' => 'required|in:employee,manager,hr_admin,super_admin',
            'target_metric' => 'nullable|string|max:100',
            'slab_from' => 'nullable|numeric|min:0',
            'slab_to' => 'nullable|numeric|min:0',
            'incentive_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        IncentiveRule::create(array_merge($data, [
            'effective_from' => now()->toDateString(),
            'is_active' => true,
        ]));

        return back()->with('status', 'Incentive rule "'.$data['name'].'" saved.');
    }

    public function approvePayout(IncentivePayout $payout)
    {
        $this->abortUnlessModuleAllowed('incentive');
        abort_unless($this->isManagerOrAbove(), 403);

        $payout->update([
            'status' => 'approved',
            'approved_by' => $this->currentUser()->id,
            'approved_at' => now(),
        ]);

        return back()->with('status', 'Payout approved — will be included in the next payroll run.');
    }
}
