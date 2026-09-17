@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', [
        'title' => $module['title'],
        'eyebrow' => 'Growth',
        'heroIcon' => 'fa-solid fa-user-plus',
        'heroSummary' => 'Requisitions, candidate pipeline and onboarding checklists.',
        'heroStats' => [
            ['label' => 'Requisitions', 'icon' => 'fa-solid fa-folder-open', 'value' => $requisitions->count()],
            ['label' => 'Candidates', 'icon' => 'fa-solid fa-users', 'value' => $requisitions->sum(fn ($r) => $r->candidates->count())],
            $onboardingCandidate ? ['label' => 'Onboarding', 'icon' => 'fa-solid fa-rocket', 'value' => $checklist->where('is_completed', true)->count() . '/' . $checklist->count()] : null,
        ],
    ])

    <div class="content">
        @if($role === 'hr_admin' || $role === 'super_admin')
            <div class="card">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-file-signature"></i></div>
                    <div>
                        <h3>New job requisition</h3>
                        <p>Opens directly for this demo &mdash; in production this would need department-head sign-off.</p>
                    </div>
                </div>
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
            <form method="GET" action="{{ url('/modules/recruitment') }}" style="margin:24px 0 0;max-width:440px;" onchange="this.submit()">
                <div class="field">
                    <label><i class="fa-solid fa-filter" style="color:var(--brand);margin-right:6px;font-size:11px;"></i>Candidate pipeline for</label>
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
            <div class="section-head" style="margin-top:24px;">
                <h2><i class="fa-solid fa-arrow-down-short-wide"></i>Candidate pipeline &mdash; {{ $selectedRequisition->title }}</h2>
                <span class="hint">Applied → Shortlisted → Interviewed → Offered → Hired</span>
            </div>
            <div class="pipeline">
                @foreach(['applied'=>'Applied','shortlisted'=>'Shortlisted','interviewed'=>'Interviewed','offered'=>'Offered','hired'=>'Hired'] as $stage => $label)
                    <div class="pipe-col">
                        <div class="pipe-head">
                            <span class="ph-dot" style="background:{{ ['applied'=>'#8FA79B','shortlisted'=>'#1FA97A','interviewed'=>'#1D6FA5','offered'=>'#A16207','hired'=>'#0E5239'][$stage] }};"></span>
                            <h4>{{ $label }}</h4>
                            <span>{{ ($pipeline[$stage] ?? collect())->count() }}</span>
                        </div>
                        @foreach($pipeline[$stage] ?? [] as $c)
                            <div class="pipe-card">
                                <div class="pc-av">{{ strtoupper(substr($c->name,0,1)) }}</div>
                                <div class="name">{{ $c->name }}</div>
                                <div class="meta"><i class="fa-solid fa-link" style="font-size:9px;margin-right:4px;"></i>{{ $c->source ?? 'Direct' }}</div>
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
                        @if(($pipeline[$stage] ?? collect())->isEmpty())
                            <div style="text-align:center;padding:14px 6px;color:#A7C9B8;font-size:11.5px;"><i class="fa-regular fa-circle" style="opacity:.6;"></i></div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="table-card" style="margin-top:24px;"><div class="tc-body">
                <div class="empty-widget">
                    <div class="ew-ico"><i class="fa-solid fa-user-plus"></i></div>
                    <b>No requisitions yet</b>
                    <span>Create your first job requisition above to start the pipeline.</span>
                </div>
            </div></div>
        @endif

        @if($onboardingCandidate)
            <div class="section-head" style="margin-top:32px;">
                <h2><i class="fa-solid fa-rocket"></i>Onboarding &mdash; {{ $onboardingCandidate->name }}</h2>
                <span class="hint">{{ $checklist->where('is_completed', true)->count() }} of {{ $checklist->count() }} steps done</span>
            </div>
            <div class="card">
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
            </div>
        @endif


    </div>
@endsection
