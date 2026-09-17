@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', [
        'title' => 'Employees',
        'eyebrow' => 'People',
        'heroIcon' => 'fa-solid fa-users',
        'heroSummary' => 'Employee master, directory and organization structure.',
        'heroStats' => [
            ['label' => 'Headcount', 'icon' => 'fa-solid fa-users', 'value' => $directory->count()],
            ['label' => 'Departments', 'icon' => 'fa-solid fa-sitemap', 'value' => $departments->count()],
            ['label' => 'Designations', 'icon' => 'fa-solid fa-briefcase', 'value' => $designations->count()],
        ],
    ])

    <div class="content employee-page">
        <div class="employee-page-heading" style="margin-bottom:18px;">
            <h2 style="display:flex;align-items:center;gap:10px;"><i class="fa-solid fa-address-book" style="color:var(--brand);background:var(--brand-soft);width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:14px;"></i>Employee directory</h2>
            <p>Search, filter and manage the employee master.</p>
        </div>

        @if($directory->isNotEmpty())
            <div class="employee-directory-card">
                <form method="GET" action="{{ url('/modules/employee-records') }}" class="employee-toolbar">
                    <div class="employee-search">
                        <span aria-hidden="true"><i class="fa-solid fa-magnifying-glass" style="font-size:12px;"></i></span>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Search name, ID or email">
                    </div>

                    <div class="employee-filters">
                        <select name="dept" onchange="this.form.submit()">
                            <option value="">All Departments</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" @selected($deptFilter == $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                        <select name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active" @selected($statusFilter === 'active')>Active</option>
                            <option value="on_notice" @selected($statusFilter === 'on_notice')>On notice</option>
                            <option value="exited" @selected($statusFilter === 'exited')>Exited</option>
                        </select>
                        <button type="button" class="employee-export" onclick="exportEmployees()"><i class="fa-solid fa-file-csv" style="margin-right:5px;font-size:12px;"></i>Export</button>
                        <button type="button" class="employee-add" onclick="openAddEmployee()">Add Employee</button>
                    </div>
                </form>

                <div class="employee-table-wrap">
                    <table class="employee-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>ID</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th class="actions-head"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($directory as $e)
                                @php
                                    $name = $e->user->name ?? '?';
                                    $initials = collect(preg_split('/\s+/', trim($name)))
                                        ->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
                                    $isActive = $e->employment_status === 'active';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="employee-person">
                                            <div class="employee-avatar">{{ $initials ?: '?' }}</div>
                                            <div>
                                                <div class="employee-name">{{ $name }}</div>
                                                <div class="employee-email">{{ $e->user->email ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="employee-id">{{ $e->employee_code }}</td>
                                    <td>{{ $e->department->name ?? '—' }}</td>
                                    <td>{{ $e->designation->title ?? '—' }}</td>
                                    <td>{{ $e->employment_type ?? 'Full-time' }}</td>
                                    <td>
                                        <span class="employee-status {{ $isActive ? 'active' : ($e->employment_status === 'on_notice' ? 'notice' : 'inactive') }}">
                                            <i></i>{{ ucfirst(str_replace('_', ' ', $e->employment_status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $e->date_of_joining ? \Illuminate\Support\Carbon::parse($e->date_of_joining)->format('M d, Y') : '—' }}</td>
                                    <td>
                                        <div class="employee-actions">
                                            <button type="button" class="ea-link"
                                                data-view="{{ json_encode([
                                                    'id' => $e->id,
                                                    'name' => $e->user->name ?? '',
                                                    'employee_code' => $e->employee_code,
                                                    'email' => $e->user->email ?? '',
                                                    'department' => $e->department->name ?? '—',
                                                    'designation' => $e->designation->title ?? '—',
                                                    'type' => $e->employment_type ?? 'Full-time',
                                                    'status' => ucfirst(str_replace('_', ' ', $e->employment_status ?? 'active')),
                                                    'joined' => $e->date_of_joining ? \Illuminate\Support\Carbon::parse($e->date_of_joining)->format('M d, Y') : '—',
                                                    'phone' => $e->phone ?? '—',
                                                    'personal_email' => $e->personal_email ?? '—',
                                                    'address' => trim(($e->address ? $e->address . ', ' : '') . ($e->city ?? '')) ?: '—',
                                                    'manager' => $e->reportingManager?->user?->name ?? '—',
                                                    'role' => $e->user->roleLabel() ?? 'Employee',
                                                ]) }}"
                                                onclick="openViewEmployee(this)">View</button>
                                            <button type="button" class="ea-link"
                                                data-emp="{{ json_encode([
                                                    'id' => $e->id,
                                                    'name' => $e->user->name ?? '',
                                                    'employee_code' => $e->employee_code,
                                                    'email' => $e->user->email ?? '',
                                                    'phone' => $e->phone ?? '',
                                                    'personal_email' => $e->personal_email ?? '',
                                                    'address' => $e->address ?? '',
                                                    'city' => $e->city ?? '',
                                                    'department_id' => $e->department_id ?? '',
                                                    'designation_id' => $e->designation_id ?? '',
                                                    'reporting_manager_id' => $e->reporting_manager_id ?? '',
                                                    'employment_status' => $e->employment_status ?? 'active',
                                                    'portal_role' => $e->user->role ?? 'employee',
                                                ]) }}"
                                                onclick="openEditEmployee(this)">Edit</button>
                                            <form method="POST" action="{{ route('records.status', $e) }}">
                                                @csrf
                                                <button type="submit" class="{{ $isActive ? 'deactivate' : 'activate' }}">
                                                    {{ $isActive ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="employee-directory-card empty-state">
                <div class="empty-widget" style="padding:30px 20px;">
                    <div class="ew-ico"><i class="fa-solid fa-magnifying-glass"></i></div>
                    <b>No employees found</b>
                    <span>Try changing the search or filters.</span>
                </div>
            </div>
        @endif

        @if($viewed)
            <div id="employee-profile" class="employee-profile-section">
                @include('partials.profile-card')
            </div>
        @endif
    </div>

    {{-- Creative add-employee modal --}}
    <dialog id="addEmployeeModal" class="modal-dialog">
        <div class="modal-grid">
            <div class="modal-side">
                <button type="button" class="modal-close" onclick="document.getElementById('addEmployeeModal').close()" aria-label="Close">&times;</button>
                <div class="modal-side-ico"><i class="fa-solid fa-user-plus"></i></div>
                <h3>Onboard a teammate</h3>
                <p>Create the employee master record and their portal login in one step.</p>
                <div class="modal-side-steps">
                    <div class="modal-step"><span class="num">1</span>Identity & portal access</div>
                    <div class="modal-step"><span class="num">2</span>Department & role</div>
                    <div class="modal-step"><span class="num">3</span>Reporting line</div>
                </div>
            </div>
            <form method="POST" action="{{ route('records.store') }}" class="modal-body">
                @csrf
                <div class="modal-note"><i class="fa-solid fa-key"></i>A temporary portal password (<code>changeme</code>) is emailed to the new joiner.</div>
                <div class="field-grid">
                    <div class="field"><label><i class="fa-regular fa-user" style="color:var(--brand);margin-right:6px;"></i>Full name</label><input name="name" value="{{ old('name') }}" placeholder="e.g. Ananya Menon" required></div>
                    <div class="field"><label><i class="fa-regular fa-envelope" style="color:var(--brand);margin-right:6px;"></i>Work email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="name@spc.com" required></div>
                    <div class="field">
                        <label><i class="fa-solid fa-user-shield" style="color:var(--brand);margin-right:6px;"></i>Portal role</label>
                        <select name="portal_role"><option value="employee">Employee</option><option value="manager">Reporting Manager</option></select>
                    </div>
                    <div class="field"><label><i class="fa-regular fa-calendar-plus" style="color:var(--brand);margin-right:6px;"></i>Date of joining</label><input type="date" name="date_of_joining" value="{{ old('date_of_joining', now()->toDateString()) }}" required></div>
                    <div class="field">
                        <label><i class="fa-solid fa-sitemap" style="color:var(--brand);margin-right:6px;"></i>Department</label>
                        <select name="department_id"><option value="">—</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
                    </div>
                    <div class="field">
                        <label><i class="fa-solid fa-briefcase" style="color:var(--brand);margin-right:6px;"></i>Designation</label>
                        <select name="designation_id"><option value="">—</option>@foreach($designations as $d)<option value="{{ $d->id }}">{{ $d->title }}</option>@endforeach</select>
                    </div>
                    <div class="field full">
                        <label><i class="fa-solid fa-user-tie" style="color:var(--brand);margin-right:6px;"></i>Reporting manager</label>
                        <select name="reporting_manager_id"><option value="">— None —</option>@foreach($possibleManagers as $m)<option value="{{ $m->id }}">{{ $m->user->name }} ({{ $m->user->roleLabel() }})</option>@endforeach</select>
                    </div>
                </div>
                <div class="modal-foot">
                    <span class="hint-secure"><i class="fa-solid fa-lock"></i>Only HR & Super Admin can add employees</span>
                    <button type="button" class="btn-secondary" onclick="document.getElementById('addEmployeeModal').close()">Cancel</button>
                    <button type="submit" class="btn-primary">Save employee</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Edit-employee modal (same creative split style) --}}
    <dialog id="editEmployeeModal" class="modal-dialog">
        <div class="modal-grid">
            <div class="modal-side">
                <button type="button" class="modal-close" onclick="document.getElementById('editEmployeeModal').close()" aria-label="Close">&times;</button>
                <div class="modal-side-ico"><i class="fa-solid fa-user-pen"></i></div>
                <h3 id="editEmpTitle">Edit employee</h3>
                <p id="editEmpSub">Update profile, role and reporting line.</p>
                <div class="modal-side-steps">
                    <div class="modal-step"><span class="num">1</span>Contact details</div>
                    <div class="modal-step"><span class="num">2</span>Department & role</div>
                    <div class="modal-step"><span class="num">3</span>Status</div>
                </div>
            </div>
            <form method="POST" id="editEmpForm" class="modal-body">
                @csrf
                <div class="modal-note"><i class="fa-solid fa-pen"></i>Changes are logged to the employee's history timeline.</div>
                <div class="field-grid">
                    <div class="field"><label><i class="fa-regular fa-user" style="color:var(--brand);margin-right:6px;"></i>Full name</label><input id="ee_name" value="" disabled></div>
                    <div class="field"><label><i class="fa-regular fa-envelope" style="color:var(--brand);margin-right:6px;"></i>Work email</label><input id="ee_email" value="" disabled></div>
                    <div class="field"><label><i class="fa-solid fa-id-badge" style="color:var(--brand);margin-right:6px;"></i>Employee code</label><input id="ee_code" value="" disabled></div>
                    <div class="field"><label><i class="fa-solid fa-phone" style="color:var(--brand);margin-right:6px;"></i>Phone</label><input name="phone" id="ee_phone"></div>
                    <div class="field full"><label><i class="fa-regular fa-envelope-open" style="color:var(--brand);margin-right:6px;"></i>Personal email</label><input type="email" name="personal_email" id="ee_pemail"></div>
                    <div class="field full"><label><i class="fa-solid fa-location-dot" style="color:var(--brand);margin-right:6px;"></i>Address</label><input name="address" id="ee_address"></div>
                    <div class="field">
                        <label><i class="fa-solid fa-city" style="color:var(--brand);margin-right:6px;"></i>City</label><input name="city" id="ee_city">
                    </div>
                    <div class="field">
                        <label><i class="fa-solid fa-sitemap" style="color:var(--brand);margin-right:6px;"></i>Department</label>
                        <select name="department_id" id="ee_dept"><option value="">—</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
                    </div>
                    <div class="field">
                        <label><i class="fa-solid fa-briefcase" style="color:var(--brand);margin-right:6px;"></i>Designation</label>
                        <select name="designation_id" id="ee_desig"><option value="">—</option>@foreach($designations as $d)<option value="{{ $d->id }}">{{ $d->title }}</option>@endforeach</select>
                    </div>
                    <div class="field">
                        <label><i class="fa-solid fa-user-tie" style="color:var(--brand);margin-right:6px;"></i>Reporting manager</label>
                        <select name="reporting_manager_id" id="ee_mgr"><option value="">— None —</option>@foreach($possibleManagers as $m)<option value="{{ $m->id }}">{{ $m->user->name }} ({{ $m->user->roleLabel() }})</option>@endforeach</select>
                    </div>
                    <div class="field">
                        <label><i class="fa-solid fa-user-shield" style="color:var(--brand);margin-right:6px;"></i>Portal role</label>
                        <select name="portal_role" id="ee_role"><option value="employee">Employee</option><option value="manager">Reporting Manager</option></select>
                    </div>
                    <div class="field full">
                        <label><i class="fa-solid fa-circle-half-stroke" style="color:var(--brand);margin-right:6px;"></i>Employment status</label>
                        <select name="employment_status" id="ee_status">
                            <option value="active">Active</option>
                            <option value="on_notice">On notice</option>
                            <option value="exited">Exited</option>
                        </select>
                    </div>
                </div>
                <div class="modal-foot">
                    <span class="hint-secure"><i class="fa-solid fa-clock-rotate-left"></i>Field changes create history entries</span>
                    <button type="button" class="btn-secondary" onclick="document.getElementById('editEmployeeModal').close()">Cancel</button>
                    <button type="submit" class="btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- View-employee modal (creative split, read-only) --}}
    <dialog id="viewEmployeeModal" class="modal-dialog">
        <div class="modal-grid">
            <div class="modal-side">
                <button type="button" class="modal-close" onclick="document.getElementById('viewEmployeeModal').close()" aria-label="Close">&times;</button>
                <div class="modal-side-ico"><i class="fa-regular fa-id-badge"></i></div>
                <h3 id="ve_name">Employee</h3>
                <p id="ve_sub">Employee profile</p>
                <div class="ve-chips">
                    <span class="ve-chip" id="ve_status"><i class="fa-solid fa-circle"></i>Active</span>
                    <span class="ve-chip" id="ve_role"><i class="fa-solid fa-user-shield"></i>Employee</span>
                </div>
                <div class="modal-side-steps">
                    <div class="modal-step"><span class="num"><i class="fa-solid fa-sitemap"></i></span><span id="ve_dept">—</span></div>
                    <div class="modal-step"><span class="num"><i class="fa-solid fa-briefcase"></i></span><span id="ve_desig">—</span></div>
                    <div class="modal-step"><span class="num"><i class="fa-solid fa-user-tie"></i></span><span id="ve_mgr">—</span></div>
                </div>
            </div>
            <div class="modal-body">
                <div class="modal-note"><i class="fa-regular fa-eye"></i>Read-only profile — use Edit to make changes.</div>
                <div class="ve-grid">
                    <div class="ve-item"><span><i class="fa-solid fa-id-card"></i>Employee code</span><b id="ve_code">—</b></div>
                    <div class="ve-item"><span><i class="fa-regular fa-envelope"></i>Work email</span><b id="ve_email">—</b></div>
                    <div class="ve-item"><span><i class="fa-solid fa-phone"></i>Phone</span><b id="ve_phone">—</b></div>
                    <div class="ve-item"><span><i class="fa-regular fa-envelope-open"></i>Personal email</span><b id="ve_pemail">—</b></div>
                    <div class="ve-item"><span><i class="fa-solid fa-location-dot"></i>Address</span><b id="ve_address">—</b></div>
                    <div class="ve-item"><span><i class="fa-regular fa-calendar-check"></i>Joined on</span><b id="ve_joined">—</b></div>
                    <div class="ve-item"><span><i class="fa-regular fa-clock"></i>Employment type</span><b id="ve_type">—</b></div>
                </div>
                <div class="modal-foot">
                    <span class="hint-secure"><i class="fa-solid fa-clock-rotate-left"></i>Full history on the profile page</span>
                    <button type="button" class="btn-secondary" onclick="document.getElementById('viewEmployeeModal').close()">Close</button>
                    <a id="ve_openFull" href="#" class="btn-primary" style="text-decoration:none;">Open full profile</a>
                </div>
            </div>
        </div>
    </dialog>

    <script>
    const empSelect = id => document.getElementById(id);

    function openEditEmployee(btn){
        const d = JSON.parse(btn.dataset.emp);
        empSelect('editEmpForm').action = '{{ url('/modules/employee-records') }}/' + d.id;
        empSelect('editEmpTitle').textContent = 'Edit — ' + (d.name || d.employee_code);
        empSelect('editEmpSub').textContent = d.employee_code + ' · ' + (d.email || '');
        empSelect('ee_name').value = d.name || '';
        empSelect('ee_email').value = d.email || '';
        empSelect('ee_code').value = d.employee_code || '';
        empSelect('ee_phone').value = d.phone || '';
        empSelect('ee_pemail').value = d.personal_email || '';
        empSelect('ee_address').value = d.address || '';
        empSelect('ee_city').value = d.city || '';
        empSelect('ee_dept').value = d.department_id || '';
        empSelect('ee_desig').value = d.designation_id || '';
        empSelect('ee_mgr').value = d.reporting_manager_id || '';
        empSelect('ee_role').value = d.portal_role || 'employee';
        empSelect('ee_status').value = d.employment_status || 'active';
        document.getElementById('editEmployeeModal').showModal();
    }

    function openViewEmployee(btn){
        const d = JSON.parse(btn.dataset.view);
        empSelect('ve_name').textContent = d.name || 'Employee';
        empSelect('ve_sub').textContent = d.designation + ' · ' + d.department;
        empSelect('ve_code').textContent = d.employee_code;
        empSelect('ve_email').textContent = d.email;
        empSelect('ve_phone').textContent = d.phone;
        empSelect('ve_pemail').textContent = d.personal_email;
        empSelect('ve_address').textContent = d.address;
        empSelect('ve_joined').textContent = d.joined;
        empSelect('ve_type').textContent = d.type;
        empSelect('ve_dept').textContent = d.department;
        empSelect('ve_desig').textContent = d.designation;
        empSelect('ve_mgr').textContent = 'Reports to ' + d.manager;
        empSelect('ve_role').textContent = d.role;
        const st = empSelect('ve_status');
        st.innerHTML = '<i class="fa-solid fa-circle"></i>' + d.status;
        st.classList.toggle('ok', d.status === 'Active');
        st.classList.toggle('warn', d.status === 'On notice');
        st.classList.toggle('off', d.status === 'Exited');
        empSelect('ve_openFull').href = '{{ url('/modules/employee-records') }}?employee=' + d.id + '#employee-profile';
        document.getElementById('viewEmployeeModal').showModal();
    }

    function hrTab(btn, name){
        const card = btn.closest('.card');
        card.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        card.querySelectorAll('.tabpanel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        card.querySelectorAll('[data-tabpanel="'+name+'"]').forEach(p => p.classList.add('active'));
    }

    function openAddEmployee(){
        document.getElementById('addEmployeeModal').showModal();
    }
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', openAddEmployee);
    @endif

    function exportEmployees() {
        const params = new URLSearchParams(new FormData(document.querySelector('.employee-toolbar')));
        params.set('export', 'csv');
        window.location.href = '{{ url('/modules/employee-records') }}?' + params.toString();
    }
    </script>
@endsection
