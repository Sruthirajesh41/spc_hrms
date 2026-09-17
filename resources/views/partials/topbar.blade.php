{{-- Sticky white topbar on every page: hamburger + title + bell + user --}}
<div class="topbar">
    <div class="topbar-left">
        <button type="button" class="hamburger" onclick="document.getElementById('appShell').classList.toggle('sidebar-open')" aria-label="Toggle menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <div class="eyebrow">{{ $eyebrow ?? 'HR Management Module' }}</div>
            <h1>{{ $title }}</h1>
        </div>
    </div>
    <div class="topbar-actions">
        @include('partials.nav-actions')
    </div>
</div>

{{-- Green accent banner below the topbar on module pages --}}
@if(isset($heroIcon) && $heroIcon)
<div class="hero-band">
<div class="page-hero">
    <div class="page-hero-ico"><i class="{{ $heroIcon }}"></i></div>
    <div class="page-hero-text">
        @if(!empty($heroSummary))<p>{{ $heroSummary }}</p>@endif
    </div>
    @if(!empty($heroStats))
    <div class="page-hero-stats">
        @foreach(array_filter($heroStats) as $s)
            @continue(!is_array($s))
            <div class="ph-stat">
                <b>@if(!empty($s['icon']))<i class="{{ $s['icon'] }}"></i>@endif{{ $s['value'] }}</b>
                <span>{{ $s['label'] }}</span>
            </div>
        @endforeach
    </div>
    @endif
</div>
</div>
@endif

@if(session('status'))
    <div class="flash" style="margin:22px 34px 0;">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="flash-errors" style="margin:22px 34px 0;">
        <strong>Please check the form:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
