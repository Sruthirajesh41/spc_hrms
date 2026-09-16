@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
        <div class="grid-2">
            <div class="card">
                <h3>Headcount by department</h3>
                <p class="card-note">Active employees only.</p>
                @foreach($headcountByDept as $d)
                    <div class="bar-row">
                        <span class="bar-label">{{ $d->name }}</span>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ $maxHeadcount ? round($d->employees_count / $maxHeadcount * 100) : 0 }}%;"></div></div>
                        <span class="bar-value">{{ $d->employees_count }}</span>
                    </div>
                @endforeach
            </div>

            <div class="card">
                <h3>Recruitment funnel</h3>
                <p class="card-note">All requisitions, all time.</p>
                @foreach($funnel as $stage => $count)
                    <div class="bar-row">
                        <span class="bar-label">{{ ucfirst($stage) }}</span>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ $maxFunnel ? round($count / $maxFunnel * 100) : 0 }}%;"></div></div>
                        <span class="bar-value">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid-2" style="margin-top:24px;">
            @if($latestRun && $payrollByDept->isNotEmpty())
                <div class="card">
                    <h3>Payroll cost by department</h3>
                    <p class="card-note">{{ $latestRun->monthLabel() }} run.</p>
                    @php $maxPayroll = max(1, $payrollByDept->max('gross')); @endphp
                    @foreach($payrollByDept as $d)
                        <div class="bar-row">
                            <span class="bar-label">{{ $d->department }}</span>
                            <div class="bar-track"><div class="bar-fill" style="width:{{ round($d->gross / $maxPayroll * 100) }}%;"></div></div>
                            <span class="bar-value">&#8377;{{ number_format($d->gross,0) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="card">
                <h3>Appraisal completion</h3>
                <p class="card-note">Across all cycles on record.</p>
                @php $pct = $appraisalTotal ? round($appraisalDone / $appraisalTotal * 100) : 0; @endphp
                <div class="bar-row">
                    <span class="bar-label">Completed</span>
                    <div class="bar-track"><div class="bar-fill" style="width:{{ $pct }}%;"></div></div>
                    <span class="bar-value">{{ $pct }}%</span>
                </div>
                <p class="field-hint">{{ $appraisalDone }} of {{ $appraisalTotal }} appraisals completed.</p>
            </div>
        </div>

        
    </div>
@endsection
