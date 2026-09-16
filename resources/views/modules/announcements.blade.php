@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
        @if($role === 'hr_admin' || $role === 'super_admin')
            <div class="card">
                <h3>Publish an announcement</h3>
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
                    <div class="form-actions"><button type="submit" class="btn-primary">Publish</button></div>
                </form>
            </div>
        @endif

        <h2 class="section-title" style="margin-top:28px;">Latest</h2>
        @if($announcements->isEmpty())
            <p class="field-hint">No announcements yet.</p>
        @endif
        @foreach($announcements as $a)
            <div class="card">
                <h3>{{ $a->title }}
                    @unless(in_array($a->id, $readIds))
                        <span class="pill pill-warn" style="margin-left:8px;">New</span>
                    @endunless
                </h3>
                <p class="card-note">{{ $a->createdBy->name ?? 'HR' }} &middot; {{ \Illuminate\Support\Carbon::parse($a->published_at)->format('d M Y, H:i') }}
                    @if($a->audience_role !== 'all') &middot; {{ ucfirst(str_replace('_',' ',$a->audience_role)) }} only @endif
                </p>
                <p style="font-size:13.5px;line-height:1.6;">{{ $a->body }}</p>
            </div>
        @endforeach

        
    </div>
@endsection
