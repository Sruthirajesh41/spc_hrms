<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark">S</div>
        <div class="brand-name">SPC Universal</div>
    </div>
    <p class="brand-sub">HR Management Module</p>

    <div class="role-chip">
        <div class="role-label">{{ $authUser->name }}</div>
        <div class="role-tagline">{{ $roleData['label'] }} &middot; {{ $roleData['tagline'] }}</div>
        <div class="role-email">{{ $authUser->email }}</div>
    </div>

    <p class="nav-heading">Overview</p>
    <ul class="nav-list">
        <li>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="{{ route('profile.index') }}" class="nav-item {{ request()->routeIs('profile.index') ? 'active' : '' }}">
                <span class="nav-dot"></span>
                <span>My Profile</span>
            </a>
        </li>
    </ul>

    @php
        // Bucket modules by nav group while keeping each module's own slug
        // as the array key — Collection::groupBy() re-indexes numerically
        // and silently breaks every link/active-state/badge lookup below.
        $groupOrder = ['People', 'Workforce', 'Money', 'Growth', 'Records', 'Comms', 'System'];
        $grouped = [];
        foreach ($modules as $key => $module) {
            $grouped[$module['group'] ?? 'Other'][$key] = $module;
        }
    @endphp

    @foreach($groupOrder as $groupName)
        @continue(empty($grouped[$groupName]))
        <p class="nav-heading">{{ $groupName }}</p>
        <ul class="nav-list">
            @foreach($grouped[$groupName] as $key => $module)
                <li>
                    <a href="{{ url('/modules/'.$key) }}" class="nav-item {{ (($moduleKey ?? null) === $key) ? 'active' : '' }}">
                        <span class="nav-dot"></span>
                        <span>{{ $module['nav_label'] ?? $module['title'] }}</span>
                        @if(!empty($navBadges[$key]))
                            <span class="nav-badge">{{ $navBadges[$key] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    @endforeach

    <div class="sidebar-foot">
        SPC Universal HR &middot; Developed by Gipra Business Solutions pvt ltd.<br>
        
    </div>
</aside>
