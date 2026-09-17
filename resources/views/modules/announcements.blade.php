@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', [
        'title' => $module['title'],
        'eyebrow' => 'Comms',
        'heroIcon' => 'fa-solid fa-bullhorn',
        'heroSummary' => 'Company-wide and role-targeted announcements, newest first.',
        'heroStats' => ($role === 'hr_admin' || $role === 'super_admin') ? [
            ['label' => 'Published', 'icon' => 'fa-solid fa-paper-plane', 'value' => $announcements->count()],
            ['label' => 'Unread', 'icon' => 'fa-regular fa-envelope-open', 'value' => $announcements->filter(fn ($a) => !in_array($a->id, $readIds))->count()],
        ] : [
            ['label' => 'Latest', 'icon' => 'fa-regular fa-envelope-open', 'value' => $announcements->filter(fn ($a) => !in_array($a->id, $readIds))->count() . ' new'],
        ],
    ])

    <div class="content">
        @if($role === 'hr_admin' || $role === 'super_admin')
            <div class="card">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-pen-nib"></i></div>
                    <div>
                        <h3>Publish an announcement</h3>
                        <p>Delivered instantly to the notification bell of the chosen audience.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('announcements.store') }}">
                    @csrf
                    <div class="field-grid">
                        <div class="field full"><label>Title</label><input name="title" placeholder="e.g. Office closed for Onam" required></div>
                        <div class="field full"><label>Message</label><textarea name="body" placeholder="Announcement details" required></textarea></div>
                        <div class="field">
                            <label>Audience</label>
                            <select name="audience_role">
                                <option value="all">Everyone</option>
                                <option value="employee">Employees</option>
                                <option value="manager">Reporting Managers</option>
                                <option value="hr_admin">HR Admins</option>
                                <option value="super_admin">Super Admins</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions"><button type="submit" class="btn-primary">Publish announcement</button></div>
                </form>
            </div>
        @endif

        <div class="section-head" style="margin-top:30px;">
            <h2><i class="fa-solid fa-stream"></i>Latest announcements</h2>
        </div>
        @if($announcements->isEmpty())
            <div class="table-card"><div class="tc-body">
                <div class="empty-widget">
                    <div class="ew-ico"><i class="fa-solid fa-bullhorn"></i></div>
                    <b>No announcements yet</b>
                    <span>Company news will appear here as soon as it's published.</span>
                </div>
            </div></div>
        @endif
        @foreach($announcements as $a)
            <div class="card" style="margin-bottom:14px;">
                <div class="widget-head" style="margin-bottom:10px;">
                    <div class="wh-ico"><i class="fa-solid fa-comment-dots"></i></div>
                    <div>
                        <h3>{{ $a->title }}
                            @unless(in_array($a->id, $readIds))
                                <span class="pill pill-warn" style="margin-left:8px;vertical-align:middle;">New</span>
                            @endunless
                        </h3>
                        <p>{{ $a->createdBy->name ?? 'HR' }} &middot; {{ \Illuminate\Support\Carbon::parse($a->published_at)->format('d M Y, H:i') }}
                            @if($a->audience_role !== 'all') &middot; <i class="fa-solid fa-user-group" style="font-size:9px;"></i> {{ ucfirst(str_replace('_',' ',$a->audience_role)) }} only @endif
                        </p>
                    </div>
                </div>
                <p style="font-size:13.5px;line-height:1.65;margin:0;">{{ $a->body }}</p>
            </div>
        @endforeach


    </div>
@endsection
