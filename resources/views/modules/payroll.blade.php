@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @php $currentCycleMonth = $currentCycleMonth ?? now()->format('F'); @endphp
    @include('partials.topbar', [
        'title' => $module['title'],
        'eyebrow' => 'Money',
        'heroIcon' => 'fa-solid fa-indian-rupee-sign',
        'heroSummary' => 'Salary structures, monthly payroll runs and payslip access.',
        'heroStats' => $role === 'super_admin' ? [
            ['label' => 'Employees', 'icon' => 'fa-solid fa-users', 'value' => $activeEmployeeCount],
            ['label' => 'Last net pay', 'icon' => 'fa-solid fa-money-check-dollar', 'value' => '₹' . number_format($lastCycleNetPay, 0)],
            ['label' => 'Cycles done', 'icon' => 'fa-solid fa-circle-check', 'value' => $cyclesFinalized],
            ['label' => 'Cycle', 'icon' => 'fa-regular fa-calendar', 'value' => $currentCycleMonth],
        ] : [
            ['label' => 'Payslips', 'icon' => 'fa-regular fa-file-lines', 'value' => $payslips->count()],
            ['label' => 'Cycle', 'icon' => 'fa-regular fa-calendar', 'value' => $currentCycleMonth],
        ],
    ])

    <div class="content">
    @if($role === 'super_admin')
        <div class="kpi-row">
            <div class="kpi-card">
                <div class="kpi-top"><div class="kpi-ico"><i class="fa-solid fa-users"></i></div></div>
                <div>
                    <div class="kpi-label">Active employees</div>
                    <div class="kpi-val">{{ $activeEmployeeCount }}</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><div class="kpi-ico"><i class="fa-solid fa-money-check-dollar"></i></div></div>
                <div>
                    <div class="kpi-label">Last cycle net pay</div>
                    <div class="kpi-val">₹{{ number_format($lastCycleNetPay, 0) }}</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><div class="kpi-ico"><i class="fa-solid fa-circle-check"></i></div></div>
                <div>
                    <div class="kpi-label">Cycles finalized</div>
                    <div class="kpi-val">{{ $cyclesFinalized }}</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><div class="kpi-ico"><i class="fa-regular fa-calendar"></i></div></div>
                <div>
                    <div class="kpi-label">Current cycle</div>
                    <div class="kpi-val" style="font-size:19px;">{{ $currentCycleMonth }}</div>
                </div>
            </div>
        </div>

        <div class="tabs">
            <button type="button" class="tab active" data-tab="salary" onclick="payrollTab(this,'salary')">Salary Structure</button>
            <button type="button" class="tab" data-tab="run" onclick="payrollTab(this,'run')">Run Payroll</button>
            <button type="button" class="tab" data-tab="history" onclick="payrollTab(this,'history')">Payslip History</button>
        </div>

        <div class="tabpanel active" data-tabpanel="salary">
            <div class="table-card">
                <div class="tc-head">
                    <h3><span class="wh-ico"><i class="fa-solid fa-file-invoice-dollar"></i></span>Salary structures</h3>
                    <span class="pill pill-muted">{{ $activeEmployees->count() }} employees</span>
                </div>
                <div class="tc-body">
                    <table>
                        <thead><tr><th>Employee</th><th>Gross</th><th>Basic</th><th>HRA</th><th>Allowances</th><th>PF</th><th></th></tr></thead>
                        <tbody>
                        @forelse($activeEmployees as $e)
                            @php $s = $e->currentSalaryStructure; @endphp
                            <tr>
                                <td class="cell-emp"><div class="av">{{ strtoupper(substr($e->user->name,0,1)) }}</div><div><b>{{ $e->user->name }}</b><span>{{ $e->employee_code }}</span></div></td>
                                <td>{{ $s ? '₹'.number_format($s->gross_monthly,0) : '—' }}</td>
                                <td>{{ $s ? '₹'.number_format($s->basic,0) : '—' }}</td>
                                <td>{{ $s ? '₹'.number_format($s->hra,0) : '—' }}</td>
                                <td>{{ $s ? '₹'.number_format($s->other_allowances,0) : '—' }}</td>
                                <td>{{ $s ? '₹'.number_format($s->basic * 0.12,0) : '—' }}</td>
                                <td><button type="button" class="btn-ghost" onclick="document.getElementById('salary-dialog-{{ $e->id }}').showModal()">Edit</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="7">
                                <div class="empty-widget">
                                    <div class="ew-ico"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                                    <b>No active employees</b>
                                    <span>Add employees to define salary structures.</span>
                                </div>
                            </td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @foreach($activeEmployees as $e)
                @php $s = $e->currentSalaryStructure; @endphp
                <dialog id="salary-dialog-{{ $e->id }}" class="app-dialog">
                    <form method="POST" action="{{ route('payroll.salary.update', $e) }}">
                        @csrf
                        <div class="dialog-head">
                            <div><h3 style="margin:0;">Edit salary structure</h3><p class="card-note" style="margin:2px 0 0;">{{ $e->user->name }}</p></div>
                            <button type="button" class="btn-ghost" onclick="this.closest('dialog').close()">&times;</button>
                        </div>
                        <div class="status-block" style="margin-bottom:16px;">Changes take effect from a new effective date; historical payslips remain unchanged.</div>
                        <div class="field-grid">
                            <div class="field"><label>Gross monthly</label><input type="number" step="0.01" value="{{ $s->gross_monthly ?? 0 }}" disabled></div>
                            <div class="field"><label>Basic</label><input type="number" step="0.01" name="basic" value="{{ $s->basic ?? 0 }}" required></div>
                            <div class="field"><label>HRA</label><input type="number" step="0.01" name="hra" value="{{ $s->hra ?? 0 }}" required></div>
                            <div class="field"><label>Allowances</label><input type="number" step="0.01" name="other_allowances" value="{{ $s->other_allowances ?? 0 }}" required></div>
                            <div class="field"><label>Variable pay</label><input type="number" step="0.01" name="variable_pay" value="{{ $s->variable_pay ?? 0 }}" required></div>
                            <div class="field"><label>Effective from</label><input type="date" name="effective_from" value="{{ now()->toDateString() }}"></div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="this.closest('dialog').close()">Cancel</button>
                            <button type="submit" class="btn-primary">Save structure</button>
                        </div>
                    </form>
                </dialog>
            @endforeach
        </div>

        <div class="tabpanel" data-tabpanel="run">
            <div class="card" style="max-width:540px;">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-play"></i></div>
                    <div>
                        <h3>Run payroll</h3>
                        <p>Generates payslips for all {{ $activeEmployeeCount }} active employees.</p>
                    </div>
                </div>
                <div class="status-block" style="margin-bottom:20px;">Running payroll locks attendance &amp; leave inputs for the period and generates payslips for all active employees. This action is logged.</div>
                <form method="POST" action="{{ route('payroll.run') }}">
                    @csrf
                    <div class="field-grid">
                        <div class="field">
                            <label>Month</label>
                            <select name="month">
                                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)
                                    <option value="{{ $i+1 }}" @selected(($i+1) == now()->month)>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field"><label>Year</label><input type="number" name="year" value="{{ now()->year }}"></div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Run payroll for {{ $activeEmployeeCount }} employees</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="tabpanel" data-tabpanel="history">
            <div class="table-card">
                <div class="tc-head">
                    <h3><span class="wh-ico"><i class="fa-regular fa-file-lines"></i></span>Payslip history</h3>
                    <span class="pill pill-muted">{{ $allPayslips->count() }} payslips</span>
                </div>
                <div class="tc-body">
                    <table>
                        <thead><tr><th>Cycle</th><th>Employee</th><th>Gross</th><th>Deductions</th><th>Net Pay</th><th></th></tr></thead>
                        <tbody>
                        @forelse($allPayslips as $p)
                            <tr>
                                <td>{{ $p->payrollRun->monthLabel() }}</td>
                                <td class="cell-emp"><div class="av">{{ strtoupper(substr($p->employee->user->name ?? '?',0,1)) }}</div><div><b>{{ $p->employee->user->name ?? '—' }}</b></div></td>
                                <td>₹{{ number_format($p->gross_pay,0) }}</td>
                                <td>₹{{ number_format($p->pf_deduction + $p->esi_deduction + $p->professional_tax + $p->tds_deduction + $p->other_deductions,0) }}</td>
                                <td><b>₹{{ number_format($p->net_pay,0) }}</b></td>
                                <td><a href="{{ route('payroll.payslip', $p) }}" target="_blank" class="btn-ghost">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">
                                <div class="empty-widget">
                                    <div class="ew-ico"><i class="fa-regular fa-file-lines"></i></div>
                                    <b>No payslips generated yet</b>
                                    <span>Run your first payroll cycle to generate payslips.</span>
                                </div>
                            </td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        function payrollTab(btn, name){
            const scope = document.querySelector('.content');
            scope.querySelectorAll(':scope > .tabs .tab').forEach(t => t.classList.remove('active'));
            scope.querySelectorAll(':scope > .tabpanel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            scope.querySelectorAll('[data-tabpanel="'+name+'"]').forEach(p => p.classList.add('active'));
        }
        </script>
    @else

        <div class="grid-2">
            <div class="card">
                <div class="widget-head">
                    <div class="wh-ico"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    <div>
                        <h3>Salary structure</h3>
                        <p>Fixed, variable pay &mdash; per employee.</p>
                    </div>
                </div>
                @if($viewedEmployee && $salaryStructure)
                    <form method="POST" action="{{ route('payroll.salary.update', $viewedEmployee) }}">
                        @csrf
                        @php $canEditSalary = $role === 'super_admin'; @endphp
                        <div class="field-grid">
                            <div class="field"><label>Basic</label><input type="number" step="0.01" name="basic" value="{{ $salaryStructure->basic }}" @disabled(!$canEditSalary)></div>
                            <div class="field"><label>HRA</label><input type="number" step="0.01" name="hra" value="{{ $salaryStructure->hra }}" @disabled(!$canEditSalary)></div>
                            <div class="field"><label>Other allowances</label><input type="number" step="0.01" name="other_allowances" value="{{ $salaryStructure->other_allowances }}" @disabled(!$canEditSalary)></div>
                            <div class="field"><label>Variable pay</label><input type="number" step="0.01" name="variable_pay" value="{{ $salaryStructure->variable_pay }}" @disabled(!$canEditSalary)></div>
                        </div>
                        <div class="stat-tiles" style="grid-template-columns:1fr;margin-top:18px;margin-bottom:0;">
                            <div class="stat-tile"><div class="st-ico"><i class="fa-solid fa-sack-dollar"></i></div><div><b>₹{{ number_format($salaryStructure->gross_monthly,0) }}</b><span>Gross monthly</span></div></div>
                        </div>
                        @if($canEditSalary)
                            <div class="form-actions"><button type="submit" class="btn-primary">Save structure</button></div>
                        @else
                            <p class="field-hint" style="margin-top:14px;">Read-only &mdash; salary structure changes are Super Admin only.</p>
                        @endif
                    </form>
                @else
                    <div class="empty-widget">
                        <div class="ew-ico"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                        <b>No salary structure on file yet</b>
                        <span>HR will set up your structure after onboarding.</span>
                    </div>
                @endif
            </div>

            <div class="stack">
                <div class="table-card">
                    <div class="tc-head">
                        <h3><span class="wh-ico"><i class="fa-regular fa-file-lines"></i></span>Payslips</h3>
                        <span class="pill pill-muted">{{ $payslips->count() }} total</span>
                    </div>
                    <div class="tc-body">
                        @if($payslips->isEmpty())
                            <div class="empty-widget">
                                <div class="ew-ico"><i class="fa-regular fa-file-lines"></i></div>
                                <b>No payslips yet</b>
                                <span>Your payslips appear after the first payroll run.</span>
                            </div>
                        @else
                            <table>
                                <thead><tr><th>Month</th><th>Net pay</th><th></th></tr></thead>
                                <tbody>
                                    @foreach($payslips as $p)
                                        <tr>
                                            <td><b>{{ $p->payrollRun->monthLabel() }}</b></td>
                                            <td>₹{{ number_format($p->net_pay,0) }}</td>
                                            <td><a href="{{ route('payroll.payslip', $p) }}" target="_blank" class="btn-ghost">View / print</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>

                @if(($role === 'hr_admin' || $role === 'super_admin') && $runsByDepartment->isNotEmpty())
                    <div class="table-card">
                        <div class="tc-head">
                            <h3><span class="wh-ico"><i class="fa-solid fa-sitemap"></i></span>{{ $latestRun->monthLabel() }} payroll run</h3>
                            <span class="pill pill-ok">{{ ucfirst($latestRun->status) }}</span>
                        </div>
                        <div class="tc-body">
                            <table>
                                <thead><tr><th>Department</th><th>Employees</th><th>Gross</th></tr></thead>
                                <tbody>
                                    @foreach($runsByDepartment as $d)
                                        <tr>
                                            <td>{{ $d->department }}</td>
                                            <td>{{ $d->headcount }}</td>
                                            <td>₹{{ number_format($d->gross,0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>


    @endif
    </div>
@endsection
