<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $module = $this->abortUnlessModuleAllowed('leave');
        $employee = $this->currentEmployee();
        $role = $this->currentRole();

        $leaveTypes = LeaveType::orderBy('id')->get();

        $balances = $employee
            ? $employee->leaveBalances()->where('year', now()->year)->with('leaveType')->get()
            : collect();

        $ownRequests = ($employee && $role !== 'super_admin')
            ? $employee->leaveRequests()->with('leaveType')->orderByDesc('id')->limit(10)->get()
            : collect();

        $pendingApprovals = collect();
        if ($this->isManagerOrAbove()) {
            $query = LeaveRequest::with(['employee.user', 'leaveType'])->where('status', 'pending');
            if ($role === 'manager' && $employee) {
                $reportIds = $employee->directReports()->pluck('id');
                $query->whereIn('employee_id', $reportIds);
            } elseif ($employee) {
                $query->where('employee_id', '!=', $employee->id);
            }
            $pendingApprovals = $query->orderBy('start_date')->orderByDesc('id')->get();
        }

        // Org-wide leave register (All / Pending / Approved / Rejected) — HR Admin
        // and Super Admin only. Same dataset either way; filters below narrow it.
        $allLeaveRequests = collect();
        $leaveDepartments = collect();
        $leaveCounts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0];
        if (in_array($role, ['hr_admin', 'super_admin'], true)) {
            $query = LeaveRequest::with(['employee.user', 'employee.department', 'leaveType'])
                ->orderByDesc('start_date')->orderByDesc('id');

            if ($request->filled('dept')) {
                $query->whereHas('employee', fn ($e) => $e->where('department_id', $request->integer('dept')));
            }

            if ($request->filled('status') && $request->string('status')->toString() !== 'all') {
                $query->where('status', $request->string('status'));
            }

            if ($request->filled('employee')) {
                $q = $request->string('employee');
                $query->whereHas('employee.user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
            }

            $allLeaveRequests = $query->get();

            $leaveCounts = [
                'pending' => $allLeaveRequests->where('status', 'pending')->count(),
                'approved' => $allLeaveRequests->where('status', 'approved')->count(),
                'rejected' => $allLeaveRequests->where('status', 'rejected')->count(),
                'total' => $allLeaveRequests->count(),
            ];

            if ($request->string('export')->toString() === 'csv') {
                return response()->streamDownload(function () use ($allLeaveRequests) {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['Employee', 'Department', 'Type', 'Start', 'End', 'Days', 'Status', 'Reason', 'Approved At']);
                    foreach ($allLeaveRequests as $r) {
                        fputcsv($out, [
                            $r->employee->user->name ?? '',
                            $r->employee->department->name ?? '',
                            $r->leaveType->name ?? '',
                            $r->start_date,
                            $r->end_date,
                            $r->days,
                            ucfirst($r->status),
                            $r->reason ?? '',
                            $r->approved_at ?? '',
                        ]);
                    }
                    fclose($out);
                }, 'leave-requests.csv', ['Content-Type' => 'text/csv']);
            }

            $leaveDepartments = Department::orderBy('name')->get();
        }

        return view('modules.leave', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'leave',
            'leaveTypes' => $leaveTypes,
            'balances' => $balances,
            'ownRequests' => $ownRequests,
            'pendingApprovals' => $pendingApprovals,
            'allLeaveRequests' => $allLeaveRequests,
            'leaveDepartments' => $leaveDepartments,
            'leaveCounts' => $leaveCounts,
            'leaveDeptFilter' => $request->integer('dept') ?: '',
            'leaveStatusFilter' => $request->string('status')->toString() ?: 'all',
            'leaveEmployeeFilter' => $request->string('employee')->toString(),
        ]));
    }

    public function apply(Request $request)
    {
        $this->abortUnlessModuleAllowed('leave');
        $employee = $this->currentEmployee();
        abort_unless($employee && $this->currentRole() !== 'super_admin', 403);

        $data = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        return back()->with('status', 'Leave application submitted — routed to your reporting manager.');
    }

    public function decide(Request $request, LeaveRequest $leaveRequest)
    {
        $this->abortUnlessModuleAllowed('leave');
        abort_unless($this->isManagerOrAbove(), 403);

        $data = $request->validate(['action' => 'required|in:approve,reject']);

        $leaveRequest->update([
            'status' => $data['action'] === 'approve' ? 'approved' : 'rejected',
            'approved_by' => $this->currentUser()->id,
            'approved_at' => now(),
        ]);

        if ($data['action'] === 'approve') {
            $balance = $leaveRequest->employee->leaveBalances()
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', now()->year)
                ->first();

            if ($balance) {
                $balance->increment('used', $leaveRequest->days);
            }
        }

        return back()->with('status', 'Leave request '.($data['action'] === 'approve' ? 'approved.' : 'rejected.'));
    }
}
