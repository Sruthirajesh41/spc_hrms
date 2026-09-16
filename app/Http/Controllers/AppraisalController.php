<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalCycle;
use Illuminate\Http\Request;

class AppraisalController extends Controller
{
    public function index()
    {
        $module = $this->abortUnlessModuleAllowed('appraisal');
        $employee = $this->currentEmployee();
        $role = $this->currentRole();

        $cycles = AppraisalCycle::orderByDesc('start_date')->get();

        $ownAppraisals = $employee
            ? Appraisal::with(['cycle', 'goals'])->where('employee_id', $employee->id)->orderByDesc('id')->get()
            : collect();

        $currentAppraisal = $ownAppraisals->firstWhere('status', '!=', 'completed') ?? $ownAppraisals->first();

        $toReview = collect();
        if ($this->isManagerOrAbove() && $employee) {
            $query = Appraisal::with(['employee.user', 'cycle', 'goals'])
                ->where('status', '!=', 'not_started')
                ->where('status', '!=', 'completed');

            if ($role === 'manager') {
                $query->where('manager_id', $employee->id);
            }

            $toReview = $query->orderByDesc('id')->get();
        }

        return view('modules.appraisal', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'appraisal',
            'cycles' => $cycles,
            'ownAppraisals' => $ownAppraisals,
            'currentAppraisal' => $currentAppraisal,
            'toReview' => $toReview,
        ]));
    }

    public function submitSelfAssessment(Request $request, Appraisal $appraisal)
    {
        $this->abortUnlessModuleAllowed('appraisal');
        $employee = $this->currentEmployee();
        abort_unless($employee && $appraisal->employee_id === $employee->id, 403);

        $data = $request->validate([
            'self_assessment' => 'required|string|max:2000',
            'goal_ratings' => 'nullable|array',
            'goal_ratings.*' => 'nullable|numeric|min:0|max:5',
        ]);

        foreach ($data['goal_ratings'] ?? [] as $goalId => $rating) {
            if ($rating !== null && $rating !== '') {
                $appraisal->goals()->where('id', $goalId)->update(['self_rating' => $rating]);
            }
        }

        $appraisal->update([
            'self_assessment' => $data['self_assessment'],
            'status' => 'self_review',
        ]);

        return back()->with('status', 'Self-assessment submitted for manager review.');
    }

    public function submitManagerReview(Request $request, Appraisal $appraisal)
    {
        $this->abortUnlessModuleAllowed('appraisal');
        abort_unless($this->isManagerOrAbove(), 403);

        $data = $request->validate([
            'manager_review' => 'required|string|max:2000',
            'final_rating' => 'required|numeric|min:0|max:5',
            'goal_ratings' => 'nullable|array',
            'goal_ratings.*' => 'nullable|numeric|min:0|max:5',
        ]);

        foreach ($data['goal_ratings'] ?? [] as $goalId => $rating) {
            if ($rating !== null && $rating !== '') {
                $appraisal->goals()->where('id', $goalId)->update(['manager_rating' => $rating]);
            }
        }

        $appraisal->update([
            'manager_review' => $data['manager_review'],
            'final_rating' => $data['final_rating'],
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('status', 'Appraisal review completed for '.$appraisal->employee->employee_code.'.');
    }
}
