@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
@include('partials.topbar', [
'title' => 'My Profile',
'eyebrow' => 'Overview',
'heroIcon' => 'fa-regular fa-id-badge',
'heroSummary' => 'Your personal details, bank info, documents and password.',
'heroStats' => ($employee ?? null) ? [
['label' => 'Code', 'icon' => 'fa-solid fa-id-card', 'value' => $employee->employee_code],
['label' => 'Department', 'icon' => 'fa-solid fa-sitemap', 'value' =>
\Illuminate\Support\Str::limit($employee->department->name ?? '—', 14)],
['label' => 'Designation', 'icon' => 'fa-solid fa-briefcase', 'value' =>
\Illuminate\Support\Str::limit($employee->designation->title ?? '—', 14)],
] : [],
])

<div class="content">
    @php
    $in = $todayAttendance?->check_in;
    $out = $todayAttendance?->check_out;
    $canPunch = in_array($role, ['employee','manager','hr_admin','super_admin']);
    @endphp
    @if($canPunch)
    <div class="punch-card">
        <div class="punch-ring {{ $in && !$out ? 'working' : ($in && $out ? 'done' : 'idle') }}">
            <i class="fa-solid {{ $in && $out ? 'fa-mug-hot' : ($in ? 'fa-briefcase' : 'fa-fingerprint') }}"></i>
        </div>
        <div class="punch-info">
            <h3>
                @if($in && $out) Day complete — well done!
                @elseif($in) You're on the clock
                @else Ready to start your day?
                @endif
            </h3>
            <p>
                @if($in) In <b>{{ \Illuminate\Support\Carbon::parse($in)->format('h:i A') }}</b>@endif
                @if($out) · Out <b>{{ \Illuminate\Support\Carbon::parse($out)->format('h:i A') }}</b>@endif
                @if(!$in) Shift runs 09:00 – 18:00 · one tap marks your attendance.@endif
                @if($in && !$out) · Working since check-in — tap below when you wrap up.@endif
                @if($in && $out) · See you tomorrow! Attendance is already recorded.@endif
            </p>
        </div>
        <div class="punch-clock">
            <span id="punchClock">{{ now()->format('h:i') }}<small>:{{ now()->format('ss') }}
                    {{ now()->format('A') }}</small></span>
            <em>{{ now()->format('l, d M Y') }}</em>
        </div>
        <div class="punch-action">
            @if($in && $out)
            <span class="punch-done-pill"><i class="fa-solid fa-circle-check"></i>Recorded</span>
            @else
            <form method="POST" action="{{ $in ? route('attendance.check-out') : route('attendance.check-in') }}">@csrf
                <button type="submit" class="punch-btn {{ $in ? 'out' : '' }}">
                    <i class="fa-solid {{ $in ? 'fa-right-from-bracket' : 'fa-fingerprint' }}"></i>
                    {{ $in ? 'Check out' : 'Check in' }}
                </button>
            </form>
            @endif
        </div>
    </div>
    @endif

    @include('partials.profile-card')
</div>

<script>
(function() {
    var el = document.getElementById('punchClock');
    if (!el) return;

    var tick = function() {
        var d = new Date();
        var h = d.getHours() % 12 || 12;
        var m = String(d.getMinutes()).padStart(2, '0');
        var s = String(d.getSeconds()).padStart(2, '0');
        var ap = d.getHours() >= 12 ? 'PM' : 'AM';

        el.innerHTML = h + ':' + m + '<small>:' + s + ' ' + ap + '</small>';
    };

    setInterval(tick, 1000);
})();


function hrTab(btn, name) {

    const card = btn.closest('.card');

    /*
     * Activate selected tab
     */
    card.querySelectorAll('.tab').forEach(function(t) {
        t.classList.remove('active');
    });

    /*
     * Hide all tab panels
     */
    card.querySelectorAll('.tabpanel').forEach(function(p) {
        p.classList.remove('active');
    });

    /*
     * Activate clicked tab
     */
    btn.classList.add('active');

    /*
     * Show selected panel
     */
    card.querySelectorAll('[data-tabpanel="' + name + '"]').forEach(function(p) {
        p.classList.add('active');
    });


    /*
     * Show Save Changes ONLY on:
     * Personal
     * Employment
     * Bank & statutory
     *
     * Hide on:
     * Documents
     * Security
     * History
     */
    const saveChangesActions = card.querySelector('#saveChangesActions');

    if (saveChangesActions) {

        const editableTabs = [
            'personal',
            'employment',
            'bank'
        ];

        if (editableTabs.includes(name)) {
            saveChangesActions.style.display = '';
        } else {
            saveChangesActions.style.display = 'none';
        }
    }
}
</script>
@endsection