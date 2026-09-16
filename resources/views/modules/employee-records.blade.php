@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => 'Employees', 'eyebrow' => 'HR Admin'])

    <div class="content employee-page">
        <div class="employee-breadcrumb">
            <span>HR Admin</span><b>/</b><strong>Employees</strong>
        </div>
        <div class="employee-page-heading">
            <h2>Employees</h2>
            <p>Employee master and organization structure.</p>
        </div>

        @if($directory->isNotEmpty())
            <div class="employee-directory-card">
                <form method="GET" action="{{ url('/modules/employee-records') }}" class="employee-toolbar">
                    <div class="employee-search">
                        <span aria-hidden="true">⌕</span>
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
                        <button type="submit" class="employee-filter-btn">Filter</button>
                        <button type="button" class="employee-export" onclick="exportEmployees()">↧ Export</button>
                        <a href="#add-employee" class="employee-add">＋ Add Employee</a>
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
                                            <a href="{{ url('/modules/employee-records?employee='.$e->id) }}#employee-profile">View</a>
                                            <a href="{{ url('/modules/employee-records?employee='.$e->id) }}#employee-profile">Edit</a>
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
                <div class="glyph">⌕</div>
                <strong>No employees found</strong>
                <p>Try changing the search or filters.</p>
            </div>
        @endif

        @if($viewed)
            <div id="employee-profile" class="employee-profile-section">
                @include('partials.profile-card')
            </div>
        @endif

        <div class="employee-add-section" id="add-employee">
            <div class="card" style="margin-top:20px;">
                <h3>Add employee</h3>
                <p class="card-note">Creates the employee master record and a portal login (temporary password: <code>changeme</code>).</p>
                <form method="POST" action="{{ route('records.store') }}">
                    @csrf
                    <div class="field-grid">
                        <div class="field"><label>Full name</label><input name="name" value="{{ old('name') }}" required></div>
                        <div class="field"><label>Work email</label><input type="email" name="email" value="{{ old('email') }}" required></div>
                        <div class="field">
                            <label>Portal role</label>
                            <select name="portal_role"><option value="employee">Employee</option><option value="manager">Reporting Manager</option></select>
                        </div>
                        <div class="field"><label>Date of joining</label><input type="date" name="date_of_joining" value="{{ old('date_of_joining', now()->toDateString()) }}" required></div>
                        <div class="field">
                            <label>Department</label>
                            <select name="department_id"><option value="">—</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
                        </div>
                        <div class="field">
                            <label>Designation</label>
                            <select name="designation_id"><option value="">—</option>@foreach($designations as $d)<option value="{{ $d->id }}">{{ $d->title }}</option>@endforeach</select>
                        </div>
                        <div class="field full">
                            <label>Reporting manager</label>
                            <select name="reporting_manager_id"><option value="">— None —</option>@foreach($possibleManagers as $m)<option value="{{ $m->id }}">{{ $m->user->name }} ({{ $m->user->roleLabel() }})</option>@endforeach</select>
                        </div>
                    </div>
                    <div class="form-actions"><button type="submit" class="btn-primary">Save employee</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function hrTab(btn, name){
        const card = btn.closest('.card');
        card.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        card.querySelectorAll('.tabpanel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        card.querySelectorAll('[data-tabpanel="'+name+'"]').forEach(p => p.classList.add('active'));
    }

    function exportEmployees() {
        const params = new URLSearchParams(new FormData(document.querySelector('.employee-toolbar')));
        params.set('export', 'csv');
        window.location.href = '{{ url('/modules/employee-records') }}?' + params.toString();
    }
    </script>
@endsection
