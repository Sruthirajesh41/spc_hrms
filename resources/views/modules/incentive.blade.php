@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
        <div class="grid-2">
            @if($role === 'super_admin')
                <div class="card">
                    <h3>New incentive rule</h3>
                    <p class="card-note">Applies to a role; calculates from linked sales/performance data.</p>
                    <form method="POST" action="{{ route('incentive.rule.store') }}">
                        @csrf
                        <div class="field-grid">
                            <div class="field full"><label>Rule name</label><input name="name" placeholder="e.g. Sales Executive Quarterly Incentive" required></div>
                            <div class="field">
                                <label>Applies to</label>
                                <select name="applies_to_role" required>
                                    <option value="employee">Employee</option>
                                    <option value="manager">Reporting Manager</option>
                                </select>
                            </div>
                            <div class="field"><label>Target metric</label><input name="target_metric" placeholder="e.g. monthly_sales_revenue"></div>
                            <div class="field"><label>Slab from</label><input type="number" step="0.01" name="slab_from"></div>
                            <div class="field"><label>Slab to</label><input type="number" step="0.01" name="slab_to"></div>
                            <div class="field"><label>Incentive %</label><input type="number" step="0.01" name="incentive_percent"></div>
                        </div>
                        <div class="form-actions"><button type="submit" class="btn-primary">Save rule</button></div>
                    </form>
                </div>
            @endif

            @if($pendingPayouts->isNotEmpty())
                <div class="card">
                    <h3>Payouts pending approval</h3>
                    <table>
                        <thead><tr><th>Employee</th><th>Achieved</th><th>Incentive</th><th></th></tr></thead>
                        <tbody>
                            @foreach($pendingPayouts as $p)
                                <tr>
                                    <td>{{ $p->employee->user->name }}</td>
                                    <td>&#8377;{{ number_format($p->achieved_value,0) }}</td>
                                    <td>&#8377;{{ number_format($p->incentive_amount,0) }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('incentive.payout.approve', $p) }}">
                                            @csrf
                                            <button class="approve" type="submit">Approve</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="field-hint" style="margin-top:12px;">Approved payouts are included in the next payroll run.</p>
                </div>
            @elseif($ownPayouts->isNotEmpty())
                <div class="card">
                    <h3>Your incentive payouts</h3>
                    <table>
                        <thead><tr><th>Period</th><th>Achieved</th><th>Incentive</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($ownPayouts as $p)
                                <tr>
                                    <td>{{ $p->month }}/{{ $p->year }}</td>
                                    <td>&#8377;{{ number_format($p->achieved_value,0) }}</td>
                                    <td>&#8377;{{ number_format($p->incentive_amount,0) }}</td>
                                    <td>
                                        @php $p2 = ['paid'=>'pill-ok','included_in_payroll'=>'pill-ok','approved'=>'pill-ok','pending'=>'pill-warn'][$p->status]; @endphp
                                        <span class="pill {{ $p2 }}">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($pendingPayouts->isNotEmpty() && $ownPayouts->isNotEmpty())
            <h2 class="section-title" style="margin-top:28px;">Your incentive payouts</h2>
            <div class="card">
                <table>
                    <thead><tr><th>Period</th><th>Achieved</th><th>Incentive</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($ownPayouts as $p)
                            <tr>
                                <td>{{ $p->month }}/{{ $p->year }}</td>
                                <td>&#8377;{{ number_format($p->achieved_value,0) }}</td>
                                <td>&#8377;{{ number_format($p->incentive_amount,0) }}</td>
                                <td>
                                    @php $p2 = ['paid'=>'pill-ok','included_in_payroll'=>'pill-ok','approved'=>'pill-ok','pending'=>'pill-warn'][$p->status]; @endphp
                                    <span class="pill {{ $p2 }}">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        
    </div>
@endsection
