@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
        <div class="tabs">
            <button type="button" class="tab active" data-tab="departments" onclick="hrTab(this,'departments')">Departments</button>
            <button type="button" class="tab" data-tab="designations" onclick="hrTab(this,'designations')">Designations</button>
            <button type="button" class="tab" data-tab="holidays" onclick="hrTab(this,'holidays')">Holiday calendar</button>
        </div>

        <div class="tabpanel active" data-tabpanel="departments">
            <div class="grid-2">
                <div class="card">
                    <h3>Departments</h3>
                    <table>
                        <thead><tr><th>Name</th><th>Code</th><th>Employees</th></tr></thead>
                        <tbody>
                            @foreach($departments as $d)
                                <tr>
                                    <td>
                                        <form method="POST" action="{{ route('organization.department.update', $d) }}" style="display:flex;gap:6px;">
                                            @csrf
                                            <input name="name" value="{{ $d->name }}" style="padding:5px 8px;font-size:12.5px;">
                                            <input name="code" value="{{ $d->code }}" style="padding:5px 8px;font-size:12.5px;width:70px;">
                                            <button type="submit" class="btn-ghost" style="padding:0;">Save</button>
                                        </form>
                                    </td>
                                    <td>{{ $d->code }}</td>
                                    <td>{{ $d->employees_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h3>Add department</h3>
                    <form method="POST" action="{{ route('organization.department.store') }}">
                        @csrf
                        <div class="field-grid">
                            <div class="field"><label>Name</label><input name="name" placeholder="e.g. Finance" required></div>
                            <div class="field"><label>Code</label><input name="code" placeholder="e.g. FIN" required></div>
                        </div>
                        <div class="form-actions"><button type="submit" class="btn-primary">Add department</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="tabpanel" data-tabpanel="designations">
            <div class="grid-2">
                <div class="card">
                    <h3>Designations</h3>
                    <table>
                        <thead><tr><th>Title</th><th>Department</th><th>Employees</th></tr></thead>
                        <tbody>
                            @foreach($designations as $d)
                                <tr>
                                    <td>
                                        <form method="POST" action="{{ route('organization.designation.update', $d) }}" style="display:flex;gap:6px;">
                                            @csrf
                                            <input name="title" value="{{ $d->title }}" style="padding:5px 8px;font-size:12.5px;">
                                            <select name="department_id" style="padding:5px 8px;font-size:12.5px;">
                                                <option value="">&mdash;</option>
                                                @foreach($departments as $dep)
                                                    <option value="{{ $dep->id }}" @selected($d->department_id === $dep->id)>{{ $dep->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn-ghost" style="padding:0;">Save</button>
                                        </form>
                                    </td>
                                    <td>{{ $d->department->name ?? '—' }}</td>
                                    <td>{{ $d->employees_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h3>Add designation</h3>
                    <form method="POST" action="{{ route('organization.designation.store') }}">
                        @csrf
                        <div class="field-grid">
                            <div class="field"><label>Title</label><input name="title" placeholder="e.g. Finance Manager" required></div>
                            <div class="field">
                                <label>Department</label>
                                <select name="department_id">
                                    <option value="">&mdash;</option>
                                    @foreach($departments as $dep)<option value="{{ $dep->id }}">{{ $dep->name }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-actions"><button type="submit" class="btn-primary">Add designation</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="tabpanel" data-tabpanel="holidays">
            <div class="grid-2">
                <div class="card">
                    <h3>Holiday calendar</h3>
                    <table>
                        <thead><tr><th>Holiday</th><th>Date</th><th>Type</th><th></th></tr></thead>
                        <tbody>
                            @foreach($holidays as $h)
                                <tr>
                                    <td>{{ $h->name }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($h->holiday_date)->format('d M Y (D)') }}</td>
                                    <td><span class="pill {{ $h->is_optional ? 'pill-muted' : 'pill-ok' }}">{{ $h->is_optional ? 'Optional' : 'Mandatory' }}</span></td>
                                    <td>
                                        <form method="POST" action="{{ route('organization.holiday.destroy', $h) }}" onsubmit="return confirm('Remove this holiday?');">
                                            @csrf
                                            <button type="submit" class="btn-ghost">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h3>Add holiday</h3>
                    <form method="POST" action="{{ route('organization.holiday.store') }}">
                        @csrf
                        <div class="field-grid">
                            <div class="field full"><label>Name</label><input name="name" placeholder="e.g. Diwali" required></div>
                            <div class="field"><label>Date</label><input type="date" name="holiday_date" required></div>
                            <div class="field" style="flex-direction:row;align-items:center;gap:8px;margin-top:22px;">
                                <input type="checkbox" name="is_optional" value="1" style="width:auto;">
                                <label style="margin:0;">Optional holiday</label>
                            </div>
                        </div>
                        <div class="form-actions"><button type="submit" class="btn-primary">Add holiday</button></div>
                    </form>
                </div>
            </div>
        </div>

        
    </div>

    <script>
    function hrTab(btn, name){
        document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.content > .tabpanel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.querySelector('.content > [data-tabpanel="'+name+'"]').classList.add('active');
    }
    </script>
@endsection
