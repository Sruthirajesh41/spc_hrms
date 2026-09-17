@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', [
        'title' => $module['title'],
        'eyebrow' => 'Comms',
        'heroIcon' => 'fa-regular fa-circle-question',
        'heroSummary' => 'Raise tickets for HR, IT, payroll or facilities — and track every request in one place.',
        'heroStats' => [
            ['label' => 'My tickets', 'icon' => 'fa-regular fa-rectangle-list', 'value' => $ownTickets->count()],
            ['label' => 'Open org-wide', 'icon' => 'fa-solid fa-inbox', 'value' => $allTickets->where('status', 'open')->count() + $allTickets->where('status', 'in_progress')->count()],
        ],
    ])

    <div class="content">
        <div class="grid-2">
            <div class="card">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-headset"></i></div>
                    <div>
                        <h3>Raise a ticket</h3>
                        <p>HR, IT, Payroll, Facilities or Documents — routed to HR Admin / Super Admin.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('support.store') }}">
                    @csrf
                    <div class="field-grid">
                        <div class="field">
                            <label>Category</label>
                            <select name="category" required>
                                <option value="it">IT</option>
                                <option value="hr">HR</option>
                                <option value="payroll">Payroll</option>
                                <option value="facilities">Facilities</option>
                                <option value="documents">Documents</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Priority</label>
                            <select name="priority" required>
                                <option value="normal" selected>Normal</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="field full"><label>Subject</label><input name="subject" placeholder="Brief summary" required></div>
                        <div class="field full"><label>Description</label><textarea name="description" placeholder="Details HR needs to help" required></textarea></div>
                    </div>
                    <div class="form-actions"><button type="submit" class="btn-primary">Raise ticket</button></div>
                </form>
            </div>

            <div class="table-card">
                <div class="tc-head">
                    <h3><span class="wh-ico"><i class="fa-regular fa-rectangle-list"></i></span>Your tickets</h3>
                    <span class="pill pill-muted">{{ $ownTickets->count() }} total</span>
                </div>
                <div class="tc-body">
                    @if($ownTickets->isEmpty())
                        <div class="empty-widget">
                            <div class="ew-ico"><i class="fa-regular fa-face-smile"></i></div>
                            <b>No tickets yet</b>
                            <span>Anything you raise will show up here with its status.</span>
                        </div>
                    @else
                        <table>
                            <thead><tr><th>Subject</th><th>Category</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($ownTickets as $t)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Str::limit($t->subject, 30) }}</td>
                                        <td>{{ ucfirst($t->category) }}</td>
                                        <td>
                                            @php $p = ['open'=>'pill-warn','in_progress'=>'pill-warn','resolved'=>'pill-ok','closed'=>'pill-muted'][$t->status]; @endphp
                                            <span class="pill {{ $p }}">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span>
                                        </td>
                                    </tr>
                                    @if($t->resolution_note)
                                        <tr><td colspan="3" style="color:var(--text-muted);font-size:12.5px;padding-top:0;"><i class="fa-solid fa-circle-info" style="color:var(--brand);margin-right:6px;"></i>{{ $t->resolution_note }}</td></tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        @if($allTickets->isNotEmpty())
            <div class="section-head" style="margin-top:30px;">
                <h2><i class="fa-solid fa-inbox"></i>All tickets</h2>
                <span class="hint">Every ticket raised across the organization, open first</span>
            </div>
            <div class="table-card">
                <div class="tc-head">
                    <h3><span class="wh-ico"><i class="fa-solid fa-ticket"></i></span>Ticket queue</h3>
                    <span class="pill pill-warn">{{ $allTickets->where('status', '!=', 'closed')->count() }} open</span>
                </div>
                <div class="tc-body">
                    <table>
                        <thead><tr><th>Employee</th><th>Category</th><th>Priority</th><th>Subject</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($allTickets as $t)
                                <tr>
                                    <td>
                                        <div class="cell-emp">
                                            <div class="av">{{ strtoupper(substr($t->employee->user->name ?? '?',0,1)) }}</div>
                                            <div><b>{{ $t->employee->user->name ?? '—' }}</b></div>
                                        </div>
                                    </td>
                                    <td>{{ ucfirst($t->category) }}</td>
                                    <td>
                                        @php $pp = ['low'=>'pill-muted','normal'=>'pill-warn','high'=>'pill-bad'][$t->priority]; @endphp
                                        <span class="pill {{ $pp }}">{{ ucfirst($t->priority) }}</span>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($t->subject, 28) }}</td>
                                    <td>
                                        @php $p = ['open'=>'pill-warn','in_progress'=>'pill-warn','resolved'=>'pill-ok','closed'=>'pill-muted'][$t->status]; @endphp
                                        <span class="pill {{ $p }}">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span>
                                    </td>
                                    <td>
                                        @if($t->status !== 'closed')
                                            <form method="POST" action="{{ route('support.update', $t) }}" style="display:flex;gap:6px;align-items:center;">
                                                @csrf
                                                <select name="status" style="padding:5px 8px;font-size:12px;">
                                                    <option value="open" @selected($t->status==='open')>Open</option>
                                                    <option value="in_progress" @selected($t->status==='in_progress')>In progress</option>
                                                    <option value="resolved" @selected($t->status==='resolved')>Resolved</option>
                                                    <option value="closed" @selected($t->status==='closed')>Closed</option>
                                                </select>
                                                <input name="resolution_note" placeholder="Resolution note" value="{{ $t->resolution_note }}" style="padding:5px 8px;font-size:12px;width:140px;">
                                                <button type="submit" class="btn-ghost" style="padding:0;">Save</button>
                                            </form>
                                        @else
                                            <span class="field-hint">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif


    </div>
@endsection
