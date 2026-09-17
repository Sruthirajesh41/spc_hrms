@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', [
        'title' => $module['title'],
        'eyebrow' => 'System',
        'heroIcon' => 'fa-solid fa-shield-halved',
        'heroSummary' => 'Manage access, roles and the organization-wide audit trail.',
        'heroStats' => [
            ['label' => 'Users', 'icon' => 'fa-solid fa-user-gear', 'value' => $users->count()],
            ['label' => 'Active', 'icon' => 'fa-solid fa-user-check', 'value' => $users->where('is_active', true)->count()],
            ['label' => 'Audit entries', 'icon' => 'fa-solid fa-timeline', 'value' => $auditLog->count()],
        ],
    ])

    <div class="content">
        <div class="table-card">
            <div class="tc-head">
                <h3><span class="wh-ico"><i class="fa-solid fa-users-gear"></i></span>Users</h3>
                <span class="pill pill-muted">{{ $users->count() }} accounts</span>
            </div>
            <div class="tc-body">
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td>
                                    <div class="cell-emp">
                                        <div class="av">{{ strtoupper(substr($u->name,0,1)) }}</div>
                                        <div><b>{{ $u->name }}</b></div>
                                    </div>
                                </td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->roleLabel() }}</td>
                                <td><span class="pill {{ $u->is_active ? 'pill-ok' : 'pill-bad' }}">{{ $u->is_active ? 'Active' : 'Suspended' }}</span></td>
                                <td><a href="{{ url('/modules/system?user='.$u->id) }}" class="btn-ghost"><i class="fa-solid fa-pen" style="font-size:11px;"></i> Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top:20px;">
            <div class="widget-head">
                <div class="wh-ico"><i class="fa-solid {{ $editingUser ? 'fa-user-pen' : 'fa-user-plus' }}"></i></div>
                <div>
                    <h3>{{ $editingUser ? 'Edit user' : 'Add user' }}</h3>
                    <p>{{ $editingUser ? 'Updating '.$editingUser->name : 'Creates a portal account with the chosen role.' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ $editingUser ? route('system.user.update', $editingUser) : route('system.user.store') }}">
                @csrf
                <div class="field-grid">
                    <div class="field"><label>Full name</label><input name="name" value="{{ $editingUser->name ?? '' }}" required></div>
                    <div class="field"><label>Email</label><input name="email" value="{{ $editingUser->email ?? '' }}" required></div>
                    <div class="field">
                        <label>Role</label>
                        <select name="role">
                            @foreach($roles as $key => $data)
                                <option value="{{ $key }}" @selected(($editingUser->role ?? '') === $key)>{{ $data['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($editingUser)
                        <div class="field" style="flex-direction:row;align-items:center;gap:8px;">
                            <input type="checkbox" name="is_active" value="1" style="width:auto;" @checked($editingUser->is_active)>
                            <label style="margin:0;">Active</label>
                        </div>
                    @endif
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">{{ $editingUser ? 'Save user' : 'Create user' }}</button>
                    @if($editingUser)<a href="{{ url('/modules/system') }}" class="btn-secondary">Cancel</a>@endif
                </div>
            </form>
        </div>

        <div class="section-head" style="margin-top:30px;">
            <h2><i class="fa-solid fa-timeline"></i>Audit log</h2>
            <span class="hint">Every privileged action, newest first</span>
        </div>
        <div class="table-card">
            <div class="tc-body">
                @if($auditLog->isEmpty())
                    <div class="empty-widget">
                        <div class="ew-ico"><i class="fa-solid fa-timeline"></i></div>
                        <b>No audit entries yet</b>
                        <span>Privileged actions will be recorded here.</span>
                    </div>
                @else
                    <table>
                        <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Module</th></tr></thead>
                        <tbody>
                            @foreach($auditLog as $log)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d M, H:i') }}</td>
                                    <td>
                                        <div class="cell-emp">
                                            <div class="av">{{ strtoupper(substr($log->user->name ?? 'S',0,1)) }}</div>
                                            <div><b>{{ $log->user->name ?? 'System' }}</b></div>
                                        </div>
                                    </td>
                                    <td><span class="pill pill-muted">{{ $log->action }}</span></td>
                                    <td>{{ ucfirst(str_replace('_',' ',$log->module)) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>


    </div>
@endsection
