<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip — {{ $payslip->employee->user->name }} — {{ $payslip->payrollRun->monthLabel() }}</title>
    <style>
        :root{ --ink:#1B2430; --line:rgba(27,36,48,0.12); --text:#20262F; --text-dim:#6B7280;
        --sans:-apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif; }
        body{font-family:var(--sans);color:var(--text);max-width:640px;margin:48px auto;padding:0 20px;}
        h1{font-weight:650;letter-spacing:-0.01em;font-size:20px;margin-bottom:2px;}
        .muted{color:var(--text-dim);font-size:13px;}
        table{width:100%;border-collapse:collapse;margin-top:24px;font-size:14px;}
        td{padding:8px 0;border-bottom:1px solid var(--line);}
        td.amount{text-align:right;}
        .total td{font-weight:700;font-size:16px;border-top:2px solid var(--ink);border-bottom:none;padding-top:14px;}
        .print-btn{margin-top:28px;padding:10px 18px;background:var(--ink);color:#fff;border:none;border-radius:5px;cursor:pointer;font-family:inherit;font-weight:650;}
        @media print { .print-btn{display:none;} }
    </style>
</head>
<body>
    <h1>SPC Enterprises — Payslip</h1>
    <p class="muted">{{ $payslip->payrollRun->monthLabel() }}</p>

    <table>
        <tr><td>Employee</td><td class="amount">{{ $payslip->employee->user->name }} ({{ $payslip->employee->employee_code }})</td></tr>
        <tr><td>Department</td><td class="amount">{{ $payslip->employee->department->name ?? '—' }}</td></tr>
        <tr><td>Designation</td><td class="amount">{{ $payslip->employee->designation->title ?? '—' }}</td></tr>
    </table>

    <table>
        <tr><td>Gross pay</td><td class="amount">&#8377;{{ number_format($payslip->gross_pay, 2) }}</td></tr>
        <tr><td>PF deduction</td><td class="amount">&minus;&#8377;{{ number_format($payslip->pf_deduction, 2) }}</td></tr>
        <tr><td>ESI deduction</td><td class="amount">&minus;&#8377;{{ number_format($payslip->esi_deduction, 2) }}</td></tr>
        <tr><td>Professional tax</td><td class="amount">&minus;&#8377;{{ number_format($payslip->professional_tax, 2) }}</td></tr>
        <tr><td>TDS</td><td class="amount">&minus;&#8377;{{ number_format($payslip->tds_deduction, 2) }}</td></tr>
        <tr><td>Other deductions</td><td class="amount">&minus;&#8377;{{ number_format($payslip->other_deductions, 2) }}</td></tr>
        <tr class="total"><td>Net pay</td><td class="amount">&#8377;{{ number_format($payslip->net_pay, 2) }}</td></tr>
    </table>

    <button class="print-btn" onclick="window.print()">Print / save as PDF</button>
</body>
</html>
