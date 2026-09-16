@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
        <div class="card">
            <h3>Users</h3>
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->roleLabel() }}</td>
                            <td><span class="pill {{ $u->is_active ? 'pill-ok' : 'pill-bad' }}">{{ $u->is_active ? 'Active' : 'Suspended' }}</span></td>
                            <td><a href="{{ url('/modules/system?user='.$u->id) }}" class="btn-ghost">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>{{ $editingUser ? 'Edit user' : 'Add user' }}</h3>
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

        <h2 class="section-title" style="margin-top:32px;">Audit log</h2>
        <div class="card">
            @if($auditLog->isEmpty())
                <p class="field-hint">No audit entries yet.</p>
            @else
                <table>
                    <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Module</th></tr></thead>
                    <tbody>
                        @foreach($auditLog as $log)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d M, H:i') }}</td>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ ucfirst(str_replace('_',' ',$log->module)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        
    </div>
@endsection
