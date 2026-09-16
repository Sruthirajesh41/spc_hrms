@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    @include('partials.topbar', ['title' => 'Notifications', 'eyebrow' => 'HR Management Module'])

    <div class="content">
        <div class="card" style="max-width:760px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:18px;">
                <div>
                    <h3 style="margin:0 0 4px;">Notification history</h3>
                    <p class="card-note" style="margin:0;">Past and present notifications for your account. Latest notifications appear first.</p>
                </div>

                @if($notifications->contains(fn ($n) => ! $n->read_at))
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="btn-ghost">Mark all as read</button>
                    </form>
                @endif
            </div>

            @if($notifications->isEmpty())
                <p class="field-hint">You're all caught up — no notifications yet.</p>
            @else
                <ul class="checklist" style="border-top:1px solid var(--line);">
                    @foreach($notifications as $n)
                        <li style="align-items:flex-start;padding:15px 4px;{{ $n->read_at ? '' : 'background:rgba(42, 120, 102, .05);' }}">
                            <span class="num" style="border:none;background:{{ $n->read_at ? 'transparent' : 'var(--role-accent)' }};color:{{ $n->read_at ? 'var(--text-muted)' : '#fff' }};">&bull;</span>

                            <span style="flex:1;min-width:0;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                                    <form method="POST" action="{{ route('notifications.read', $n) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" style="background:none;border:none;padding:0;text-align:left;font-size:14px;font-weight:{{ $n->read_at ? '500' : '700' }};color:var(--text);cursor:pointer;">{{ $n->message }}</button>
                                    </form>

                                    @if(! $n->read_at)
                                        <span style="font-size:11px;font-weight:700;color:var(--role-accent);white-space:nowrap;">NEW</span>
                                    @endif
                                </div>

                                <div class="field-hint" style="margin-top:5px;">
                                    {{ \Illuminate\Support\Carbon::parse($n->created_at)->format('d M Y, h:i A') }}
                                    &middot;
                                    {{ \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans() }}
                                </div>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
