<aside class="sidebar">
    <div class="sidebar-inner">
        <div class="brand brand-row">
            <div style="display:flex;align-items:center;min-width:0;">
                <img src="{{ asset('images/spc-logo.png') }}" alt="SPC Universal" class="brand-logo">
            </div>
            <button type="button" class="sidebar-close" onclick="document.getElementById('appShell').classList.remove('sidebar-open')" aria-label="Close menu"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="role-chip">
            @php
                $nameParts = preg_split('/\s+/', trim($authUser->name ?? ''));
                $roleInitials = strtoupper(substr($nameParts[0] ?? 'U', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
            @endphp
            <div class="role-person">
                <div class="role-av">{{ $roleInitials }}</div>
                <div class="role-name" title="{{ $authUser->name }}">{{ $authUser->name }}</div>
            </div>
            <div class="role-tagline"><b>{{ strtoupper($roleData['label']) }}</b></div>
            <div class="role-email"><i class="fa-solid fa-envelope"></i>{{ $authUser->email }}</div>
        </div>

        <div class="nav-scroll">
            <p class="nav-heading">Overview</p>
            <ul class="nav-list">
                <li>
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.index') }}" class="nav-item {{ request()->routeIs('profile.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-id-badge"></i>
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

                // Font Awesome icon per module slug (design-system set, duotone feel
                // comes from the active-state styling in the layout).
                $moduleIcons = [
                    'attendance'       => 'fa-regular fa-clock',
                    'leave'            => 'fa-regular fa-calendar-days',
                    'payroll'          => 'fa-regular fa-credit-card',
                    'recruitment'      => 'fa-solid fa-user-plus',
                    'my-profile'       => 'fa-regular fa-user',
                    'employee-records' => 'fa-solid fa-users',
                    'appraisal'        => 'fa-solid fa-trophy',
                    'pf-gratuity'      => 'fa-solid fa-piggy-bank',
                    'incentive'        => 'fa-solid fa-medal',
                    'reports'          => 'fa-solid fa-chart-column',
                    'system'           => 'fa-solid fa-shield-halved',
                    'wfh'              => 'fa-solid fa-house-laptop',
                    'announcements'    => 'fa-solid fa-bullhorn',
                    'support'          => 'fa-regular fa-circle-question',
                    'settings'         => 'fa-solid fa-gear',
                    'organization'     => 'fa-regular fa-building',
                ];
            @endphp

            @foreach($groupOrder as $groupName)
                @continue(empty($grouped[$groupName]))
                <p class="nav-heading">{{ $groupName }}</p>
                <ul class="nav-list">
                    @foreach($grouped[$groupName] as $key => $module)
                        <li>
                            <a href="{{ url('/modules/'.$key) }}" class="nav-item {{ (($moduleKey ?? null) === $key) ? 'active' : '' }}">
                                <i class="{{ $moduleIcons[$key] ?? 'fa-solid fa-table-cells-large' }}"></i>
                                <span>{{ $module['nav_label'] ?? $module['title'] }}</span>
                                @if(!empty($navBadges[$key]))
                                    <span class="nav-badge">{{ $navBadges[$key] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>

        <div class="sidebar-foot">
            SPC Universal HR &middot; Developed by Gipra Business Solutions pvt ltd.<br>
        </div>
    </div>
</aside>
