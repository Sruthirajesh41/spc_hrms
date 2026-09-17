<details class="bell">
    <summary class="bell-icon">
        <i class="fa-regular fa-bell" style="font-size:15px;"></i>
        @if(($navUnreadCount ?? 0) > 0)
            <span class="bell-badge">{{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}</span>
        @endif
    </summary>
    <div class="bell-panel">
        @forelse(($navNotifications ?? collect()) as $n)
            <div class="bell-item">
                <form method="POST" action="{{ route('notifications.read', $n) }}">
                    @csrf
                    <button type="submit">
                        @if(!$n->read_at)<strong>&bull;</strong>@endif
                        {{ $n->message }}
                        <div style="color:var(--text-muted);margin-top:2px;font-size:11.5px;">{{ \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans() }}</div>
                    </button>
                </form>
            </div>
        @empty
            <div class="bell-empty"><i class="fa-regular fa-bell-slash"></i>No notifications yet.</div>
        @endforelse
        <div class="bell-foot"><a href="{{ route('notifications.index') }}">View all <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i></a></div>
    </div>
</details>
<details class="bell user-menu">
    <summary class="bell-icon user-chip-summary">
        <span class="user-chip">
            <span class="avatar">{{ strtoupper(substr($authUser->name ?? '?', 0, 1)) }}{{ strtoupper(substr(strstr($authUser->name ?? '', ' ') ?: '', 1, 1)) }}</span>
            <span class="who">
                <b>{{ $authUser->name }}</b>
                <span>{{ $authUser->roleLabel() }}</span>
            </span>
        </span>
    </summary>
    <div class="bell-panel user-panel">
        <div class="user-panel-head">
            <span class="avatar">{{ strtoupper(substr($authUser->name ?? '?', 0, 1)) }}{{ strtoupper(substr(strstr($authUser->name ?? '', ' ') ?: '', 1, 1)) }}</span>
            <div>
                <b>{{ $authUser->name }}</b>
                <div class="card-note" style="margin:0;">{{ $authUser->email }}</div>
            </div>
        </div>
        <div class="user-panel-detail">
            <span>Role</span><span>{{ $authUser->roleLabel() }}</span>
        </div>
        @if($employee ?? null)
            <div class="user-panel-detail">
                <span>Employee code</span><span>{{ $employee->employee_code }}</span>
            </div>
            <div class="user-panel-detail">
                <span>Department</span><span>{{ $employee->department->name ?? '—' }}</span>
            </div>
            <div class="user-panel-detail">
                <span>Designation</span><span>{{ $employee->designation->title ?? '—' }}</span>
            </div>
        @endif
        <a href="{{ route('profile.index') }}" class="bell-item" style="display:block;padding:10px 9px;"><i class="fa-regular fa-user" style="margin-right:8px;color:var(--brand);"></i>View full profile</a>
        <form method="POST" action="{{ route('logout') }}" style="padding:4px 9px 2px;">
            @csrf
            <button type="submit" class="btn-secondary" style="width:100%;"><i class="fa-solid fa-arrow-right-from-bracket" style="margin-right:8px;color:var(--bad);"></i>Sign out</button>
        </form>
    </div>
</details>
