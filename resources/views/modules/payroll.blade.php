@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
    @if($role === 'super_admin')
        <div class="kpi-row">
            <div class="kpi-card">
                <div class="kpi-label">Active employees</div>
                <div class="kpi-val">{{ $activeEmployeeCount }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Last cycle net pay</div>
                <div class="kpi-val">&#8377;{{ number_format($lastCycleNetPay, 0) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Cycles finalized</div>
                <div class="kpi-val">{{ $cyclesFinalized }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Current cycle</div>
                <div class="kpi-val" style="font-size:19px;">{{ $currentCycleMonth }}</div>
            </div>
        </div>

        <div class="tabs">
            <button type="button" class="tab active" data-tab="salary" onclick="payrollTab(this,'salary')">Salary Structure</button>
            <button type="button" class="tab" data-tab="run" onclick="payrollTab(this,'run')">Run Payroll</button>
            <button type="button" class="tab" data-tab="history" onclick="payrollTab(this,'history')">Payslip History</button>
        </div>

        <div class="tabpanel active" data-tabpanel="salary">
            <div class="card" style="padding:0 24px;">
                <div style="overflow-x:auto;">
                    <table>
                        <thead><tr><th>Employee</th><th>Gross</th><th>Basic</th><th>HRA</th><th>Allowances</th><th>PF</th><th></th></tr></thead>
                        <tbody>
                        @forelse($activeEmployees as $e)
                            @php $s = $e->currentSalaryStructure; @endphp
                            <tr>
                                <td class="cell-emp"><div class="av">{{ strtoupper(substr($e->user->name,0,1)) }}</div><div><b>{{ $e->user->name }}</b><span>{{ $e->employee_code }}</span></div></td>
                                <td>{{ $s ? '&#8377;'.number_format($s->gross_monthly,0) : '—' }}</td>
                                <td>{{ $s ? '&#8377;'.number_format($s->basic,0) : '—' }}</td>
                                <td>{{ $s ? '&#8377;'.number_format($s->hra,0) : '—' }}</td>
                                <td>{{ $s ? '&#8377;'.number_format($s->other_allowances,0) : '—' }}</td>
                                <td>{{ $s ? '&#8377;'.number_format($s->basic * 0.12,0) : '—' }}</td>
                                <td><button type="button" class="btn-ghost" onclick="document.getElementById('salary-dialog-{{ $e->id }}').showModal()">Edit</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px;">No active employees.</td></tr>
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
            <div class="card" style="max-width:520px;">
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
                        <button type="submit" class="btn-primary">&#9654; Run Payroll for {{ $activeEmployeeCount }} Employees</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="tabpanel" data-tabpanel="history">
            <div class="card" style="padding:0 24px;">
                <div style="overflow-x:auto;">
                    <table>
                        <thead><tr><th>Cycle</th><th>Employee</th><th>Gross</th><th>Deductions</th><th>Net Pay</th><th></th></tr></thead>
                        <tbody>
                        @forelse($allPayslips as $p)
                            <tr>
                                <td>{{ $p->payrollRun->monthLabel() }}</td>
                                <td class="cell-emp"><div class="av">{{ strtoupper(substr($p->employee->user->name ?? '?',0,1)) }}</div><div><b>{{ $p->employee->user->name ?? '—' }}</b></div></td>
                                <td>&#8377;{{ number_format($p->gross_pay,0) }}</td>
                                <td>&#8377;{{ number_format($p->pf_deduction + $p->esi_deduction + $p->professional_tax + $p->tds_deduction + $p->other_deductions,0) }}</td>
                                <td><b>&#8377;{{ number_format($p->net_pay,0) }}</b></td>
                                <td><a href="{{ route('payroll.payslip', $p) }}" target="_blank" class="btn-ghost">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:24px;">No payslips generated yet.</td></tr>
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
                <h3>Salary structure</h3>
                <p class="card-note">Fixed, variable pay &mdash; per employee.</p>
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
                        <div class="card" style="background:var(--paper);margin-top:18px;padding:16px 18px;">
                            <div class="bar-row" style="margin:0;"><span class="bar-label">Gross monthly</span><span class="bar-value" style="width:auto;font-family:'Fraunces',serif;font-size:15px;">&#8377;{{ number_format($salaryStructure->gross_monthly,0) }}</span></div>
                        </div>
                        @if($canEditSalary)
                            <div class="form-actions"><button type="submit" class="btn-primary">Save structure</button></div>
                        @else
                            <p class="field-hint" style="margin-top:14px;">Read-only &mdash; salary structure changes are Super Admin only.</p>
                        @endif
                    </form>
                @else
                    <p class="field-hint">No salary structure on file yet.</p>
                @endif
            </div>

            <div class="stack">
                <div class="card">
                    <h3>Payslips</h3>
                    <p class="card-note">{{ $viewedEmployee ? $viewedEmployee->user->name : 'No employee selected' }}</p>
                    @if($payslips->isEmpty())
                        <p class="field-hint">No payslips generated yet.</p>
                    @else
                        <table>
                            <thead><tr><th>Month</th><th>Net pay</th><th></th></tr></thead>
                            <tbody>
                                @foreach($payslips as $p)
                                    <tr>
                                        <td>{{ $p->payrollRun->monthLabel() }}</td>
                                        <td>&#8377;{{ number_format($p->net_pay,0) }}</td>
                                        <td><a href="{{ route('payroll.payslip', $p) }}" target="_blank" class="btn-ghost">View / print</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                @if(($role === 'hr_admin' || $role === 'super_admin') && $runsByDepartment->isNotEmpty())
                    <div class="card">
                        <h3>{{ $latestRun->monthLabel() }} payroll run</h3>
                        <p class="card-note">By department &mdash; status: {{ ucfirst($latestRun->status) }}.</p>
                        <table>
                            <thead><tr><th>Department</th><th>Employees</th><th>Gross</th></tr></thead>
                            <tbody>
                                @foreach($runsByDepartment as $d)
                                    <tr>
                                        <td>{{ $d->department }}</td>
                                        <td>{{ $d->headcount }}</td>
                                        <td>&#8377;{{ number_format($d->gross,0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        
    @endif
    </div>
@endsection
