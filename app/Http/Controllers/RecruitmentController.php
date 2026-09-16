<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Designation;
use App\Models\JobRequisition;
use App\Models\OnboardingChecklistItem;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    public function index(Request $request)
    {
        $module = $this->abortUnlessModuleAllowed('recruitment');

        $requisitions = JobRequisition::with(['department', 'designation', 'candidates'])
            ->orderByDesc('id')->get();

        $selectedRequisition = $request->filled('requisition')
            ? $requisitions->firstWhere('id', $request->integer('requisition'))
            : $requisitions->firstWhere('status', 'open') ?? $requisitions->first();

        $pipeline = [];
        if ($selectedRequisition) {
            foreach (['applied', 'shortlisted', 'interviewed', 'offered', 'hired'] as $stage) {
                $pipeline[$stage] = $selectedRequisition->candidates->where('stage', $stage)->values();
            }
        }

        $onboardingCandidate = $selectedRequisition
            ? $selectedRequisition->candidates->where('stage', 'hired')->sortByDesc('updated_at')->first()
            : null;
        $checklist = $onboardingCandidate ? $onboardingCandidate->checklistItems()->orderBy('id')->get() : collect();

        return view('modules.recruitment', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'recruitment',
            'departments' => Department::orderBy('name')->get(),
            'designations' => Designation::orderBy('title')->get(),
            'requisitions' => $requisitions,
            'selectedRequisition' => $selectedRequisition,
            'pipeline' => $pipeline,
            'onboardingCandidate' => $onboardingCandidate,
            'checklist' => $checklist,
        ]));
    }

    public function storeRequisition(Request $request)
    {
        $this->abortUnlessModuleAllowed('recruitment');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'openings' => 'required|integer|min:1',
        ]);

        $requisition = JobRequisition::create(array_merge($data, [
            'status' => 'open',
            'requested_by' => $this->currentUser()->id,
            'created_at' => now(),
        ]));

        return redirect()->route('recruitment.index', ['requisition' => $requisition->id])
            ->with('status', 'Requisition for "'.$requisition->title.'" submitted.');
    }

    public function updateCandidateStage(Request $request, Candidate $candidate)
    {
        $this->abortUnlessModuleAllowed('recruitment');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'stage' => 'required|in:applied,shortlisted,interviewed,offered,hired,rejected',
        ]);

        $candidate->update(['stage' => $data['stage']]);

        if ($data['stage'] === 'hired' && $candidate->checklistItems()->count() === 0) {
            foreach (['Offer accepted', 'ID proof & documents uploaded', 'Bank account details collected', 'Laptop & access provisioning', 'Day-1 orientation scheduled'] as $item) {
                OnboardingChecklistItem::create(['candidate_id' => $candidate->id, 'item' => $item, 'is_completed' => false]);
            }
        }

        return back()->with('status', $candidate->name.' moved to '.ucfirst($data['stage']).'.');
    }

    public function toggleChecklistItem(OnboardingChecklistItem $item)
    {
        $this->abortUnlessModuleAllowed('recruitment');
        abort_unless($this->isHrOrAbove(), 403);

        $newState = ! $item->is_completed;

        $item->update([
            'is_completed' => $newState,
            'completed_at' => $newState ? now() : null,
        ]);

        return back();
    }
}
