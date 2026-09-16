@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
        @if($role === 'hr_admin' || $role === 'super_admin')
            <div class="card">
                <h3>New job requisition</h3>
                <p class="card-note">Opens directly for this demo &mdash; in production this would need department-head sign-off.</p>
                <form method="POST" action="{{ route('recruitment.requisition.store') }}">
                    @csrf
                    <div class="field-grid">
                        <div class="field full"><label>Job title</label><input name="title" placeholder="e.g. Senior Sales Executive" required></div>
                        <div class="field">
                            <label>Department</label>
                            <select name="department_id">
                                <option value="">&mdash;</option>
                                @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Designation</label>
                            <select name="designation_id">
                                <option value="">&mdash;</option>
                                @foreach($designations as $d)<option value="{{ $d->id }}">{{ $d->title }}</option>@endforeach
                            </select>
                        </div>
                        <div class="field"><label>Openings</label><input type="number" name="openings" value="1" min="1" required></div>
                    </div>
                    <div class="form-actions"><button type="submit" class="btn-primary">Create requisition</button></div>
                </form>
            </div>
        @endif

        @if($requisitions->isNotEmpty())
            <form method="GET" action="{{ url('/modules/recruitment') }}" style="margin:28px 0 8px;max-width:420px;" onchange="this.submit()">
                <div class="field">
                    <label>Candidate pipeline for</label>
                    <select name="requisition">
                        @foreach($requisitions as $req)
                            <option value="{{ $req->id }}" @selected($selectedRequisition && $selectedRequisition->id === $req->id)>
                                {{ $req->title }} &mdash; {{ ucfirst(str_replace('_',' ',$req->status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endif

        @if($selectedRequisition)
            <h2 class="section-title" style="margin-top:24px;">Candidate pipeline &mdash; {{ $selectedRequisition->title }}</h2>
            <p class="section-note">Applied &rarr; Shortlisted &rarr; Interviewed &rarr; Offered &rarr; Hired.</p>
            <div class="pipeline">
                @foreach(['applied'=>'Applied','shortlisted'=>'Shortlisted','interviewed'=>'Interviewed','offered'=>'Offered','hired'=>'Hired'] as $stage => $label)
                    <div class="pipe-col">
                        <h4>{{ $label }} <span>{{ ($pipeline[$stage] ?? collect())->count() }}</span></h4>
                        @foreach($pipeline[$stage] ?? [] as $c)
                            <div class="pipe-card">
                                <div class="name">{{ $c->name }}</div>
                                <div class="meta">{{ $c->source ?? 'Direct' }}</div>
                                @if($role === 'hr_admin' || $role === 'super_admin')
                                    <form method="POST" action="{{ route('recruitment.candidate.stage', $c) }}">
                                        @csrf
                                        <select name="stage" onchange="this.form.submit()">
                                            @foreach(['applied','shortlisted','interviewed','offered','hired','rejected'] as $s)
                                                <option value="{{ $s }}" @selected($c->stage === $s)>{{ ucfirst($s) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @else
            <p class="field-hint" style="margin-top:24px;">No requisitions yet.</p>
        @endif

        @if($onboardingCandidate)
            <h2 class="section-title" style="margin-top:32px;">Onboarding &mdash; {{ $onboardingCandidate->name }}</h2>
            <p class="section-note">Digital checklist and document collection for the new joiner.</p>
            <ul class="checklist">
                @foreach($checklist as $item)
                    <li class="{{ $item->is_completed ? 'done' : '' }}">
                        @if($role === 'hr_admin' || $role === 'super_admin')
                            <form method="POST" action="{{ route('recruitment.checklist.toggle', $item) }}">
                                @csrf
                                <button type="submit" class="num">{{ $item->is_completed ? '✓' : $loop->iteration }}</button>
                            </form>
                        @else
                            <span class="num">{{ $item->is_completed ? '✓' : $loop->iteration }}</span>
                        @endif
                        {{ $item->item }}
                    </li>
                @endforeach
            </ul>
        @endif

        
    </div>
@endsection
