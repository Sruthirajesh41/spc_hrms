@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
        <div class="grid-2">
            <div class="card">
                <h3>Raise a ticket</h3>
                <p class="card-note">HR, IT, Payroll, Facilities or Documents — routed to HR Admin / Super Admin.</p>
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

            <div class="card">
                <h3>Your tickets</h3>
                @if($ownTickets->isEmpty())
                    <p class="field-hint">No tickets raised yet.</p>
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
                                    <tr><td colspan="3" style="color:var(--text-muted);font-size:12.5px;padding-top:0;">{{ $t->resolution_note }}</td></tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        @if($allTickets->isNotEmpty())
            <h2 class="section-title" style="margin-top:32px;">All tickets</h2>
            <p class="section-note">Every ticket raised across the organization, open first.</p>
            <div class="card">
                <table>
                    <thead><tr><th>Employee</th><th>Category</th><th>Priority</th><th>Subject</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($allTickets as $t)
                            <tr>
                                <td>{{ $t->employee->user->name ?? '—' }}</td>
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
        @endif

        
    </div>
@endsection
