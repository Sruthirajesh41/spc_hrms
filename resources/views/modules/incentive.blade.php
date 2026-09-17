@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', [
        'title' => $module['title'],
        'eyebrow' => 'Money',
        'heroIcon' => 'fa-solid fa-medal',
        'heroSummary' => 'Configurable commission rules, calculations and payout approval.',
        'heroStats' => [
            ['label' => 'Pending', 'icon' => 'fa-solid fa-hourglass-half', 'value' => $pendingPayouts->count()],
            ['label' => 'My payouts', 'icon' => 'fa-regular fa-user', 'value' => $ownPayouts->count()],
            ['label' => 'Earned', 'icon' => 'fa-solid fa-indian-rupee-sign', 'value' => '₹' . number_format($ownPayouts->whereIn('status', ['paid', 'approved', 'included_in_payroll'])->sum('incentive_amount'), 0)],
        ],
    ])

    <div class="content">
        <div class="grid-2">
            @if($role === 'super_admin')
                <div class="card">
                    <div class="widget-head">
                        <div class="wh-ico"><i class="fa-solid fa-scale-balanced"></i></div>
                        <div>
                            <h3>New incentive rule</h3>
                            <p>Applies to a role; calculates from linked sales/performance data.</p>
                        </div>
                    </div>
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
                <div class="table-card">
                    <div class="tc-head">
                        <h3><span class="wh-ico"><i class="fa-solid fa-hourglass-half"></i></span>Payouts pending approval</h3>
                        <span class="pill pill-warn">{{ $pendingPayouts->count() }} waiting</span>
                    </div>
                    <div class="tc-body">
                        <table>
                            <thead><tr><th>Employee</th><th>Achieved</th><th>Incentive</th><th></th></tr></thead>
                            <tbody>
                                @foreach($pendingPayouts as $p)
                                    <tr>
                                        <td>
                                            <div class="cell-emp">
                                                <div class="av">{{ strtoupper(substr($p->employee->user->name,0,1)) }}</div>
                                                <div><b>{{ $p->employee->user->name }}</b></div>
                                            </div>
                                        </td>
                                        <td>₹{{ number_format($p->achieved_value,0) }}</td>
                                        <td><b>₹{{ number_format($p->incentive_amount,0) }}</b></td>
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
                        <p class="field-hint" style="margin-top:12px;"><i class="fa-solid fa-circle-info" style="color:var(--brand);margin-right:6px;"></i>Approved payouts are included in the next payroll run.</p>
                    </div>
                </div>
            @elseif($ownPayouts->isNotEmpty())
                <div class="table-card">
                    <div class="tc-head">
                        <h3><span class="wh-ico"><i class="fa-solid fa-medal"></i></span>Your incentive payouts</h3>
                        <span class="pill pill-muted">{{ $ownPayouts->count() }} total</span>
                    </div>
                    <div class="tc-body">
                        <table>
                            <thead><tr><th>Period</th><th>Achieved</th><th>Incentive</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($ownPayouts as $p)
                                    <tr>
                                        <td><b>{{ $p->month }}/{{ $p->year }}</b></td>
                                        <td>₹{{ number_format($p->achieved_value,0) }}</td>
                                        <td>₹{{ number_format($p->incentive_amount,0) }}</td>
                                        <td>
                                            @php $p2 = ['paid'=>'pill-ok','included_in_payroll'=>'pill-ok','approved'=>'pill-ok','pending'=>'pill-warn'][$p->status]; @endphp
                                            <span class="pill {{ $p2 }}">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        @if($pendingPayouts->isNotEmpty() && $ownPayouts->isNotEmpty())
            <div class="section-head" style="margin-top:28px;">
                <h2><i class="fa-solid fa-medal"></i>Your incentive payouts</h2>
            </div>
            <div class="table-card">
                <div class="tc-body">
                    <table>
                        <thead><tr><th>Period</th><th>Achieved</th><th>Incentive</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($ownPayouts as $p)
                                <tr>
                                    <td><b>{{ $p->month }}/{{ $p->year }}</b></td>
                                    <td>₹{{ number_format($p->achieved_value,0) }}</td>
                                    <td>₹{{ number_format($p->incentive_amount,0) }}</td>
                                    <td>
                                        @php $p2 = ['paid'=>'pill-ok','included_in_payroll'=>'pill-ok','approved'=>'pill-ok','pending'=>'pill-warn'][$p->status]; @endphp
                                        <span class="pill {{ $p2 }}">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif


    </div>
@endsection
