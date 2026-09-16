@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
        @if($cycles->isNotEmpty())
            <div class="grid-3" style="margin-bottom:28px;">
                @foreach($cycles->take(3) as $cycle)
                    <div class="card">
                        <h3>{{ $cycle->name }}</h3>
                        <span class="pill {{ $cycle->status === 'active' ? 'pill-ok' : ($cycle->status === 'closed' ? 'pill-muted' : 'pill-warn') }}">{{ ucfirst($cycle->status) }}</span>
                        <p class="card-note" style="margin-top:10px;">{{ \Illuminate\Support\Carbon::parse($cycle->start_date)->format('d M') }} &ndash; {{ \Illuminate\Support\Carbon::parse($cycle->end_date)->format('d M Y') }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        @if($currentAppraisal)
            <div class="card">
                <h3>Self-assessment &mdash; {{ $currentAppraisal->cycle->name }}</h3>
                <p class="card-note">Status: <span class="pill {{ $currentAppraisal->status === 'completed' ? 'pill-ok' : 'pill-warn' }}">{{ ucfirst(str_replace('_',' ',$currentAppraisal->status)) }}</span></p>

                @if($currentAppraisal->goals->isNotEmpty())
                    @foreach($currentAppraisal->goals as $goal)
                        <div class="goal-row">
                            <div class="goal-title">{{ $goal->goal_text }}</div>
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
            <h2 class="section-title" style="margin-top:28px;">Your appraisal history</h2>
            <div class="card">
                <table>
                    <thead><tr><th>Cycle</th><th>Status</th><th>Final rating</th></tr></thead>
                    <tbody>
                        @foreach($ownAppraisals as $a)
                            <tr>
                                <td>{{ $a->cycle->name }}</td>
                                <td><span class="pill {{ $a->status === 'completed' ? 'pill-ok' : 'pill-warn' }}">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span></td>
                                <td>{{ $a->final_rating ? number_format($a->final_rating,2).' / 5' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($toReview->isNotEmpty())
            <h2 class="section-title" style="margin-top:32px;">Reviews awaiting you</h2>
            <p class="section-note">Self-assessments submitted by {{ $role === 'manager' ? 'your direct reports' : 'employees across the organization' }}.</p>
            @foreach($toReview as $a)
                <div class="card">
                    <h3>{{ $a->employee->user->name }} &middot; {{ $a->cycle->name }}</h3>
                    <p class="card-note">Self-assessment: {{ $a->self_assessment ?? 'Not submitted yet' }}</p>
                    @if($a->self_assessment)
                        <form method="POST" action="{{ route('appraisal.review', $a) }}">
                            @csrf
                            <div class="field full"><label>Manager review</label><textarea name="manager_review" required>{{ $a->manager_review }}</textarea></div>
                            <div class="field" style="max-width:160px;margin-top:10px;"><label>Final rating (0&ndash;5)</label><input type="number" step="0.1" min="0" max="5" name="final_rating" value="{{ $a->final_rating }}" required></div>
                            @foreach($a->goals as $goal)
                                <div class="field" style="margin-top:10px;max-width:220px;">
                                    <label>Manager rating &mdash; {{ \Illuminate\Support\Str::limit($goal->goal_text, 24) }}</label>
                                    <input type="number" step="0.1" min="0" max="5" name="goal_ratings[{{ $goal->id }}]" value="{{ $goal->manager_rating ?? $goal->self_rating }}">
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
