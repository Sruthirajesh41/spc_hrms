@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', [
        'title' => $module['title'],
        'eyebrow' => 'Growth',
        'heroIcon' => 'fa-solid fa-trophy',
        'heroSummary' => 'Appraisal cycles, self-assessment and manager review.',
        'heroStats' => [
            ['label' => 'Cycles', 'icon' => 'fa-solid fa-rotate', 'value' => $cycles->count()],
            ['label' => 'To review', 'icon' => 'fa-solid fa-user-pen', 'value' => $toReview->count()],
            ['label' => 'Mine', 'icon' => 'fa-regular fa-id-badge', 'value' => $ownAppraisals->count()],
        ],
    ])

    <div class="content">
        @if($cycles->isNotEmpty())
            <div class="stat-tiles" style="grid-template-columns:repeat({{ min($cycles->count(), 3) }},1fr);">
                @foreach($cycles->take(3) as $cycle)
                    <div class="stat-tile">
                        <div class="st-ico"><i class="fa-solid fa-rotate"></i></div>
                        <div>
                            <b style="font-size:15px;">{{ $cycle->name }}</b>
                            <span>{{ ucfirst($cycle->status) }} · {{ \Illuminate\Support\Carbon::parse($cycle->start_date)->format('d M') }} &ndash; {{ \Illuminate\Support\Carbon::parse($cycle->end_date)->format('d M Y') }}</span>
                        </div>
                        <span class="pill {{ $cycle->status === 'active' ? 'pill-ok' : ($cycle->status === 'closed' ? 'pill-muted' : 'pill-warn') }}" style="margin-left:auto;">{{ ucfirst($cycle->status) }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        @if($currentAppraisal)
            <div class="card">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-user-pen"></i></div>
                    <div>
                        <h3>Self-assessment &mdash; {{ $currentAppraisal->cycle->name }}</h3>
                        <p>Status: {{ ucfirst(str_replace('_',' ',$currentAppraisal->status)) }}</p>
                    </div>
                    <span class="pill {{ $currentAppraisal->status === 'completed' ? 'pill-ok' : 'pill-warn' }} wh-right">{{ ucfirst(str_replace('_',' ',$currentAppraisal->status)) }}</span>
                </div>

                @if($currentAppraisal->goals->isNotEmpty())
                    @foreach($currentAppraisal->goals as $goal)
                        <div class="goal-row">
                            <div class="goal-title"><i class="fa-solid fa-bullseye" style="color:var(--brand);margin-right:8px;font-size:12px;"></i>{{ $goal->goal_text }}</div>
                            <div class="goal-desc">Weight: {{ $goal->weight_percent }}% &middot; Self rating: {{ $goal->self_rating ?? '—' }} &middot; Manager rating: {{ $goal->manager_rating ?? '—' }}</div>
                        </div>
                    @endforeach
                @endif

                @if($currentAppraisal->status === 'not_started' || $currentAppraisal->status === 'self_review')
                    <form method="POST" action="{{ route('appraisal.self', $currentAppraisal) }}" style="margin-top:16px;">
                        @csrf
                        <div class="field full"><label>Self-assessment</label><textarea name="self_assessment" required>{{ $currentAppraisal->self_assessment }}</textarea></div>
                        @foreach($currentAppraisal->goals as $goal)
                            <div class="field" style="margin-top:10px;max-width:200px;">
                                <label>Self rating &mdash; {{ \Illuminate\Support\Str::limit($goal->goal_text, 24) }}</label>
                                <input type="number" step="0.1" min="0" max="5" name="goal_ratings[{{ $goal->id }}]" value="{{ $goal->self_rating }}">
                            </div>
                        @endforeach
                        <div class="form-actions"><button type="submit" class="btn-primary">Submit self-assessment</button></div>
                    </form>
                @else
                    <p class="field-hint" style="margin-top:10px;">{{ $currentAppraisal->self_assessment }}</p>
                    @if($currentAppraisal->manager_review)
                        <p class="field-hint" style="margin-top:10px;"><strong>Manager review:</strong> {{ $currentAppraisal->manager_review }}</p>
                        <p class="field-hint">Final rating: {{ $currentAppraisal->final_rating }} / 5</p>
                    @endif
                @endif
            </div>
        @endif

        @if($ownAppraisals->isNotEmpty())
            <div class="section-head" style="margin-top:28px;">
                <h2><i class="fa-solid fa-clock-rotate-left"></i>Your appraisal history</h2>
            </div>
            <div class="table-card">
                <div class="tc-body">
                    <table>
                        <thead><tr><th>Cycle</th><th>Status</th><th>Final rating</th></tr></thead>
                        <tbody>
                            @foreach($ownAppraisals as $a)
                                <tr>
                                    <td><b>{{ $a->cycle->name }}</b></td>
                                    <td><span class="pill {{ $a->status === 'completed' ? 'pill-ok' : 'pill-warn' }}">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span></td>
                                    <td><b>{{ $a->final_rating ? number_format($a->final_rating,2).' / 5' : '—' }}</b></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($toReview->isNotEmpty())
            <div class="section-head" style="margin-top:32px;">
                <h2><i class="fa-solid fa-user-pen"></i>Reviews awaiting you</h2>
                <span class="hint">Self-assessments submitted by {{ $role === 'manager' ? 'your direct reports' : 'employees across the organization' }}</span>
            </div>
            @foreach($toReview as $a)
                <div class="card" style="margin-bottom:16px;">
                    <div class="widget-head">
                        <div class="wh-ico">{{ strtoupper(substr($a->employee->user->name,0,1)) }}</div>
                        <div>
                            <h3>{{ $a->employee->user->name }} &middot; {{ $a->cycle->name }}</h3>
                            <p>Self-assessment: {{ \Illuminate\Support\Str::limit($a->self_assessment ?? 'Not submitted yet', 80) }}</p>
                        </div>
                    </div>
                    @if($a->self_assessment)
                        <form method="POST" action="{{ route('appraisal.review', $a) }}">
                            @csrf
                            <div class="field full"><label>Manager review</label><textarea name="manager_review" required>{{ $a->manager_review }}</textarea></div>
                            <div class="field" style="max-width:160px;margin-top:10px;"><label>Final rating (0&ndash;5)</label><input type="number" step="0.1" min="0" max="5" name="final_rating" value="{{ $a->final_rating }}" required></div>
                            @foreach($a->goals as $goal)
                                <div class="field" style="margin-top:10px;max-width:220px;">
                                    <label>Manager rating &mdash; {{ \Illuminate\Support\Str::limit($goal->goal_text, 24) }}</label>
                                    <input type="number" step="0.1" min="0" max="5" name="goal_ratings[{{ $goal->id }}]" value="{{ $a->manager_rating ?? $goal->self_rating }}">
                                </div>
                            @endforeach
                            <div class="form-actions"><button type="submit" class="btn-primary">Complete review</button></div>
                        </form>
                    @endif
                </div>
            @endforeach
        @endif


    </div>
@endsection
