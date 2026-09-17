<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeHistory;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EmployeeRecordsController extends Controller
{
    public function index(Request $request)
    {
        $module = $this->abortUnlessModuleAllowed('employee-records');
        $employee = $this->currentEmployee();

        $directory = collect();
        $viewed = $employee;

        if ($this->isHrOrAbove()) {
            $query = Employee::with(['user', 'department', 'designation', 'reportingManager.user']);

            if ($request->filled('q')) {
                $q = $request->string('q');
                $query->where(function ($w) use ($q) {
                    $w->where('employee_code', 'like', "%{$q}%")
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
                });
            }

            if ($request->filled('dept')) {
                $query->where('department_id', $request->integer('dept'));
            }

            if ($request->filled('portal_role')) {
                $role = $request->string('portal_role');
                $query->whereHas('user', fn ($u) => $u->where('role', $role));
            }

            if ($request->filled('status')) {
                $query->where('employment_status', $request->string('status'));
            }

            $directory = $query->orderBy('employee_code')->get();

            $viewed = $request->filled('employee')
                ? Employee::with(['user', 'department', 'designation', 'reportingManager.user', 'documents', 'history'])->find($request->integer('employee'))
                : null;

            if ($request->string('export')->toString() === 'csv') {
                return response()->streamDownload(function () use ($directory) {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['Employee', 'ID', 'Department', 'Designation', 'Type', 'Status', 'Joined', 'Email']);
                    foreach ($directory as $e) {
                        fputcsv($out, [
                            $e->user->name ?? '',
                            $e->employee_code,
                            $e->department->name ?? '',
                            $e->designation->title ?? '',
                            $e->employment_type ?? 'Full-time',
                            ucfirst(str_replace('_', ' ', $e->employment_status)),
                            $e->date_of_joining,
                            $e->user->email ?? '',
                        ]);
                    }
                    fclose($out);
                }, 'employees.csv', ['Content-Type' => 'text/csv']);
            }
        } elseif ($employee) {
            $viewed = $employee->load(['user', 'department', 'designation', 'reportingManager.user', 'documents']);
        }

        // Anyone with an active status who could plausibly manage others —
        // used to populate the "Reporting manager" dropdown for HR.
        $possibleManagers = $this->isHrOrAbove()
            ? Employee::with('user')->where('employment_status', 'active')
                ->whereHas('user', fn ($u) => $u->whereIn('role', ['manager', 'hr_admin', 'super_admin']))
                ->orderBy('employee_code')->get()
            : collect();

        return view('modules.employee-records', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'employee-records',
            'directory' => $directory,
            'viewed' => $viewed,
            'departments' => Department::orderBy('name')->get(),
            'designations' => Designation::orderBy('title')->get(),
            'possibleManagers' => $possibleManagers,
            'search' => $request->string('q')->toString(),
            'deptFilter' => $request->integer('dept') ?: '',
            'roleFilter' => $request->string('portal_role')->toString(),
            'statusFilter' => $request->string('status')->toString(),
        ]));
    }

    /**
     * Every signed-in person's own profile — personal details, bank info,
     * documents and password/security, regardless of role. This is the
     * self-service counterpart to the HR-facing directory above: HR/Super
     * Admin still get the Employment tab's HR-controlled fields here too,
     * since that mirrors what they'd see opening their own record from the
     * directory — but department/designation/manager changes for anyone
     * else stay in Employee Records, not here.
     */
    public function myProfile()
    {
        $module = $this->abortUnlessModuleAllowed('my-profile');
        $employee = $this->currentEmployee();
        abort_unless($employee, 404);

        $employee->load(['user', 'department', 'designation', 'reportingManager.user', 'documents', 'history']);

        $possibleManagers = $this->isHrOrAbove()
            ? Employee::with('user')->where('employment_status', 'active')
                ->whereHas('user', fn ($u) => $u->whereIn('role', ['manager', 'hr_admin', 'super_admin']))
                ->orderBy('employee_code')->get()
            : collect();

        return view('modules.my-profile', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'my-profile',
            'viewed' => $employee,
            'departments' => Department::orderBy('name')->get(),
            'designations' => Designation::orderBy('title')->get(),
            'possibleManagers' => $possibleManagers,
            'todayAttendance' => Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', now()->toDateString())->first(),
        ]));
    }

    /**
     * HR / Super Admin create a brand-new employee (and their login account)
     * in one step — the "corporate" employee-lifecycle entry point that the
     * reference design's "+ Add Employee" button represents.
     */
    public function store(Request $request)
    {
        $this->abortUnlessModuleAllowed('employee-records');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'portal_role' => 'required|in:employee,manager',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'date_of_joining' => 'required|date',
        ]);

        $employee = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['portal_role'],
                'password' => Hash::make('changeme'),
                'is_active' => true,
            ]);

            $lastNumber = (int) Employee::query()
                ->selectRaw("MAX(CAST(SUBSTR(employee_code, 4) AS INTEGER)) as n")
                ->value('n');

            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_code' => 'EMP'.str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT),
                'department_id' => $data['department_id'] ?? null,
                'designation_id' => $data['designation_id'] ?? null,
                'reporting_manager_id' => $data['reporting_manager_id'] ?? null,
                'date_of_joining' => $data['date_of_joining'],
                'employment_status' => 'active',
            ]);

            AuditLog::create([
                'user_id' => $this->currentUser()->id,
                'action' => 'CREATE',
                'module' => 'employee-records',
                'record_id' => $employee->id,
                'new_value' => json_encode(['name' => $user->name, 'employee_code' => $employee->employee_code, 'role' => $user->role]),
                'created_at' => now(),
            ]);

            return $employee;
        });

        return redirect()->route('records.index', ['employee' => $employee->id])
            ->with('status', $employee->employee_code.' — '.$data['name'].' added. Temporary password: changeme');
    }

    public function toggleStatus(Employee $employee)
    {
        abort_unless($this->isHrOrAbove(), 403);

        $newStatus = $employee->employment_status === 'active' ? 'exited' : 'active';
        $employee->update([
            'employment_status' => $newStatus,
            'date_of_exit' => $newStatus === 'exited' ? now()->toDateString() : null,
        ]);
        $employee->user?->update(['is_active' => $newStatus === 'active']);

        return back()->with('status', $employee->employee_code.' is now '.ucfirst($newStatus).'.');
    }

    /**
     * HR / Super Admin quick toggle between active and exited, used by the
     * directory table's Activate/Deactivate button.
     */
    public function updateStatus(Request $request, Employee $employee)
    {
        abort_unless($this->isHrOrAbove(), 403);

        $newStatus = $employee->employment_status === 'active' ? 'exited' : 'active';
        $employee->update(['employment_status' => $newStatus]);

        return back()->with('status', $employee->employee_code.' marked '.$newStatus.'.');
    }

    public function update(Request $request, Employee $employee)
    {
        $isSelf = $this->currentEmployee() && $this->currentEmployee()->id === $employee->id;
        $isHr = $this->isHrOrAbove();
        abort_unless($isHr || $isSelf, 403);

        $data = $request->validate([
            'phone' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:120',
            'bank_account_number' => 'nullable|string|max:40',
            'bank_ifsc' => 'nullable|string|max:20',
        ]);

        // Everyone (self or HR) can update contact & bank details.
        $employee->update($data);

        // Department, designation, reporting manager, employment status, and
        // portal role (employee <-> manager) are HR-controlled — this is the
        // "corporate" lifecycle control the reference design's Employees
        // page implies. Elevating someone to hr_admin/super_admin still
        // requires Super Admin, via System & Access.
        if ($isHr) {
            $hrData = $request->validate([
                'department_id' => 'nullable|exists:departments,id',
                'designation_id' => 'nullable|exists:designations,id',
                'reporting_manager_id' => 'nullable|exists:employees,id|different:'.$employee->id,
                'employment_status' => 'nullable|in:active,on_notice,exited',
                'portal_role' => 'nullable|in:employee,manager',
            ]);

            $employee->loadMissing(['department', 'designation', 'reportingManager.user', 'user']);
            $changes = [];

            $fieldLabels = [
                'department_id' => ['Department', fn ($id) => Department::find($id)->name ?? '—'],
                'designation_id' => ['Designation', fn ($id) => Designation::find($id)->title ?? '—'],
                'reporting_manager_id' => ['Reporting manager', fn ($id) => Employee::find($id)->user->name ?? '—'],
                'employment_status' => ['Employment status', fn ($v) => ucfirst(str_replace('_', ' ', $v))],
            ];

            foreach ($fieldLabels as $field => [$label, $formatter]) {
                if (! $request->has($field)) {
                    continue;
                }
                $newValue = $hrData[$field] ?? null;
                $oldValue = $employee->{$field};
                if ($newValue == $oldValue) {
                    continue;
                }
                $changes[$field] = $newValue;
                EmployeeHistory::create([
                    'employee_id' => $employee->id,
                    'field_changed' => $label,
                    'old_value' => $oldValue ? $formatter($oldValue) : '—',
                    'new_value' => $newValue ? $formatter($newValue) : '—',
                    'effective_date' => now()->toDateString(),
                    'changed_by' => $this->currentUser()->id,
                    'created_at' => now(),
                ]);
            }

            if ($changes) {
                $employee->update($changes);
            }

            if (! empty($hrData['portal_role']) && $employee->user && $hrData['portal_role'] !== $employee->user->role) {
                $oldRole = $employee->user->roleLabel();
                $employee->user->update(['role' => $hrData['portal_role']]);
                EmployeeHistory::create([
                    'employee_id' => $employee->id,
                    'field_changed' => 'Portal role',
                    'old_value' => $oldRole,
                    'new_value' => $employee->user->fresh()->roleLabel(),
                    'effective_date' => now()->toDateString(),
                    'changed_by' => $this->currentUser()->id,
                    'created_at' => now(),
                ]);
            }
        }

        return back()->with('status', 'Profile updated for '.$employee->employee_code.'.');
    }

    /**
     * Self-service password change (requires current password) and an
     * admin reset path for HR/Super Admin (no current password needed).
     */
    public function updatePassword(Request $request, Employee $employee)
    {
        $isSelf = $this->currentEmployee() && $this->currentEmployee()->id === $employee->id;
        abort_unless($this->isHrOrAbove() || $isSelf, 403);

        $user = $employee->user;
        abort_unless($user, 404);

        $rules = [
            'new_password' => 'required|string|min:8|confirmed',
        ];

        // HR/Super Admin resetting someone else's password can skip the
        // current-password check; everyone changing their own must prove it.
        $requireCurrent = $isSelf;
        if ($requireCurrent) {
            $rules['current_password'] = 'required|string';
        }

        $data = $request->validate($rules);

        if ($requireCurrent && ! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        $user->update(['password' => Hash::make($data['new_password'])]);

        return back()->with('status', $isSelf
            ? 'Your password has been updated.'
            : 'Password reset for '.$user->name.'.');
    }

    /**
     * Real file upload for employee documents. Self-upload always lands as
     * "pending" for HR to verify; HR uploading on someone else's behalf can
     * mark it verified immediately since they're vouching for it.
     */
    public function uploadDocument(Request $request, Employee $employee)
    {
        $isSelf = $this->currentEmployee() && $this->currentEmployee()->id === $employee->id;
        abort_unless($this->isHrOrAbove() || $isSelf, 403);

        $data = $request->validate([
            'document_type' => 'required|string|max:60',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
            'expiry_date' => 'nullable|date',
        ]);

        $path = $request->file('file')->store('documents/'.$employee->id, 'public');

        $isHr = $this->isHrOrAbove();

        $document = EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_type' => $data['document_type'],
            'file_path' => $path,
            'expiry_date' => $data['expiry_date'] ?? null,
            'status' => $isHr ? 'verified' : 'pending',
            'uploaded_by' => $this->currentUser()->id,
            'uploaded_at' => now(),
            'verified_by' => $isHr ? $this->currentUser()->id : null,
            'verified_at' => $isHr ? now() : null,
        ]);

        if (! $isHr) {
            foreach (\App\Models\User::whereIn('role', ['hr_admin', 'super_admin'])->pluck('id') as $userId) {
                Notification::notify($userId, 'document_uploaded', $employee->user->name.' uploaded a '.$data['document_type'].' for verification.', '/modules/employee-records?employee='.$employee->id);
            }
        }

        return back()->with('status', $document->document_type.' uploaded'.($isHr ? ' and marked verified.' : ' — pending HR verification.'));
    }

    public function verifyDocument(Request $request, EmployeeDocument $document)
    {
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate(['action' => 'required|in:verified,rejected']);

        $document->update([
            'status' => $data['action'],
            'verified_by' => $this->currentUser()->id,
            'verified_at' => now(),
        ]);

        $document->loadMissing('employee.user');
        if ($document->employee && $document->employee->user) {
            Notification::notify(
                $document->employee->user->id,
                'document_'.$data['action'],
                'Your '.$document->document_type.' was '.$data['action'].'.',
                '/modules/employee-records'
            );
        }

        return back()->with('status', 'Document '.$data['action'].'.');
    }

    public function downloadDocument(EmployeeDocument $document)
    {
        $employee = $this->currentEmployee();
        abort_unless($this->isHrOrAbove() || ($employee && $document->employee_id === $employee->id), 403);
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->document_type.'-'.$document->employee_id.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION));
    }
}
