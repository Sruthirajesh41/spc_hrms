@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
        

        <div class="grid-2">
            <div class="card">
                <h3>PF contributions</h3>
                <p class="card-note">
                    @if($pfAccount)
                        UAN: {{ $pfAccount->uan_number }} &middot; PF No: {{ $pfAccount->pf_number }}
                    @else
                        No PF account on file.
                    @endif
                </p>
                @if($contributions->isEmpty())
                    <p class="field-hint">No contributions recorded yet.</p>
                @else
                    <table>
                        <thead><tr><th>Month</th><th>Employee share</th><th>Employer share</th></tr></thead>
                        <tbody>
                            @foreach($contributions as $c)
                                <tr>
                                    <td>{{ $c->payrollRun->monthLabel() }}</td>
                                    <td>&#8377;{{ number_format($c->employee_share,0) }}</td>
                                    <td>&#8377;{{ number_format($c->employer_share,0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="card">
                <h3>Gratuity eligibility</h3>
                <p class="card-note">Based on continuous tenure &mdash; eligible after 5 years.</p>
                @if($gratuity)
                    <div class="field-grid">
                        <div class="field"><label>Tenure so far</label><input value="{{ $gratuity->tenure_years }} yrs" disabled></div>
                        <div class="field"><label>Eligible</label><input value="{{ $gratuity->is_eligible ? 'Yes' : 'Not yet' }}" disabled></div>
                        <div class="field"><label>Eligible from</label><input value="{{ $gratuity->eligible_date ?? '—' }}" disabled></div>
                        <div class="field"><label>Estimated amount*</label><input value="&#8377;{{ number_format($gratuity->estimated_amount,0) }}" disabled></div>
                    </div>
                    <p class="field-hint" style="margin-top:14px;">*Estimate at current basic pay; recalculated each payroll cycle.</p>
                @else
                    <p class="field-hint">No gratuity record on file yet &mdash; typically created once tenure tracking begins.</p>
                @endif
            </div>
        </div>

        
    </div>
@endsection
