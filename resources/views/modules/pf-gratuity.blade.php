@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', [
        'title' => $module['title'],
        'eyebrow' => 'Money',
        'heroIcon' => 'fa-solid fa-piggy-bank',
        'heroSummary' => 'Provident fund contributions and gratuity eligibility at a glance.',
        'heroStats' => array_filter([
            $pfAccount ? ['label' => 'UAN', 'icon' => 'fa-solid fa-fingerprint', 'value' => \Illuminate\Support\Str::limit($pfAccount->uan_number, 12, '')] : null,
            ['label' => 'Contributions', 'icon' => 'fa-solid fa-layer-group', 'value' => $contributions->count()],
            $gratuity ? ['label' => 'Tenure', 'icon' => 'fa-solid fa-hourglass-end', 'value' => rtrim(rtrim(number_format($gratuity->tenure_years,1),'0'),'.').' yrs'] : null,
        ]),
    ])

    <div class="content">
        <div class="grid-2">
            <div class="table-card">
                <div class="tc-head">
                    <h3><span class="wh-ico"><i class="fa-solid fa-piggy-bank"></i></span>PF contributions</h3>
                    @if($pfAccount)<span class="pill pill-muted">PF No: {{ $pfAccount->pf_number }}</span>@endif
                </div>
                <div class="tc-body">
                    @if($contributions->isEmpty())
                        <div class="empty-widget">
                            <div class="ew-ico"><i class="fa-solid fa-piggy-bank"></i></div>
                            <b>No contributions recorded yet</b>
                            <span>Your PF share appears after your first payroll cycle.</span>
                        </div>
                    @else
                        <table>
                            <thead><tr><th>Month</th><th>Employee share</th><th>Employer share</th></tr></thead>
                            <tbody>
                                @foreach($contributions as $c)
                                    <tr>
                                        <td><b>{{ $c->payrollRun->monthLabel() }}</b></td>
                                        <td>₹{{ number_format($c->employee_share,0) }}</td>
                                        <td>₹{{ number_format($c->employer_share,0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-award"></i></div>
                    <div>
                        <h3>Gratuity eligibility</h3>
                        <p>Based on continuous tenure &mdash; eligible after 5 years.</p>
                    </div>
                </div>
                @if($gratuity)
                    @php $gpct = min(100, round($gratuity->tenure_years / 5 * 100)); @endphp
                    <div class="ring-card" style="border:none;box-shadow:none;padding:0 0 14px;">
                        <div class="ring" style="--pct:{{ $gpct }};"><b>{{ rtrim(rtrim(number_format($gratuity->tenure_years,1),'0'),'.') }}y</b></div>
                        <h4>{{ $gratuity->is_eligible ? 'Eligible for gratuity' : 'Progress to 5 years' }}</h4>
                        <small>{{ $gratuity->is_eligible ? 'Eligible from '.$gratuity->eligible_date : $gpct.'% of the 5-year milestone' }}</small>
                    </div>
                    <div class="stat-tiles" style="grid-template-columns:1fr 1fr;margin-bottom:6px;">
                        <div class="stat-tile"><div class="st-ico"><i class="fa-regular fa-calendar-check"></i></div><div><b style="font-size:15px;">{{ $gratuity->eligible_date ?? '—' }}</b><span>Eligible from</span></div></div>
                        <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-indian-rupee-sign"></i></div><div><b style="font-size:15px;">₹{{ number_format($gratuity->estimated_amount,0) }}</b><span>Est. amount</span></div></div>
                    </div>
                    <p class="field-hint">*Estimate at current basic pay; recalculated each payroll cycle.</p>
                @else
                    <div class="empty-widget">
                        <div class="ew-ico"><i class="fa-solid fa-hourglass-start"></i></div>
                        <b>No gratuity record yet</b>
                        <span>Typically created once tenure tracking begins.</span>
                    </div>
                @endif
            </div>
        </div>


    </div>
@endsection
