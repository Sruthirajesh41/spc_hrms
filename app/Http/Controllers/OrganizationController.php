<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Holiday;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $module = $this->abortUnlessModuleAllowed('organization');

        return view('modules.organization', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'organization',
            'departments' => Department::withCount('employees')->orderBy('name')->get(),
            'designations' => Designation::with('department')->withCount('employees')->orderBy('title')->get(),
            'holidays' => Holiday::orderBy('holiday_date')->get(),
        ]));
    }

    public function storeDepartment(Request $request)
    {
        $this->abortUnlessModuleAllowed('organization');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'required|string|max:20',
        ]);

        Department::create($data);

        return back()->with('status', 'Department "'.$data['name'].'" added.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $this->abortUnlessModuleAllowed('organization');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'required|string|max:20',
        ]);

        $department->update($data);

        return back()->with('status', 'Department updated.');
    }

    public function storeDesignation(Request $request)
    {
        $this->abortUnlessModuleAllowed('organization');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:120',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        Designation::create($data);

        return back()->with('status', 'Designation "'.$data['title'].'" added.');
    }

    public function updateDesignation(Request $request, Designation $designation)
    {
        $this->abortUnlessModuleAllowed('organization');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:120',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $designation->update($data);

        return back()->with('status', 'Designation updated.');
    }

    public function storeHoliday(Request $request)
    {
        $this->abortUnlessModuleAllowed('organization');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'holiday_date' => 'required|date',
            'is_optional' => 'nullable|boolean',
        ]);

        $data['is_optional'] = $request->boolean('is_optional');

        Holiday::create($data);

        return back()->with('status', 'Holiday "'.$data['name'].'" added.');
    }

    public function destroyHoliday(Holiday $holiday)
    {
        $this->abortUnlessModuleAllowed('organization');
        abort_unless($this->isHrOrAbove(), 403);

        $holiday->delete();

        return back()->with('status', 'Holiday removed.');
    }
}
