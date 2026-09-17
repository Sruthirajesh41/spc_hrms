<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip — {{ $payslip->employee->user->name }} — {{ $payslip->payrollRun->monthLabel() }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;600&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --brand:#146C4E; --brand-strong:#0A3D2C; --brand-soft:#E4F3EB;
            --line:rgba(18,58,40,0.14); --text:#22352C; --text-dim:#61756B;
            --sans:'Outfit',-apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif;
            --head:'Kanit',sans-serif;
        }
        body{font-family:var(--sans);color:var(--text);max-width:640px;margin:48px auto;padding:0 20px;}
        .slip-head{display:flex;align-items:center;gap:12px;border-bottom:3px solid var(--brand);padding-bottom:14px;margin-bottom:6px;}
        .slip-mark{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#1B8A5F,var(--brand-strong));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:17px;}
        h1{font-family:var(--head);font-weight:600;letter-spacing:.01em;font-size:20px;margin:0;color:var(--brand-strong);}
        .muted{color:var(--text-dim);font-size:13px;}
        table{width:100%;border-collapse:collapse;margin-top:24px;font-size:14px;}
        td{padding:8px 0;border-bottom:1px solid var(--line);}
        td.amount{text-align:right;}
        .total td{font-family:var(--head);font-weight:600;font-size:16px;color:var(--brand-strong);border-top:2px solid var(--brand);border-bottom:none;padding-top:14px;}
        .print-btn{margin-top:28px;padding:10px 18px;background:linear-gradient(180deg,#1B8A5F,#136B4C);color:#fff;border:none;border-radius:9px;cursor:pointer;font-family:inherit;font-weight:650;}
        @media print { .print-btn{display:none;} }
    </style>
</head>
<body>
    <div class="slip-head">
        <div class="slip-mark">S</div>
        <div>
            <h1>SPC Enterprises — Payslip</h1>
            <p class="muted" style="margin:2px 0 0;">{{ $payslip->payrollRun->monthLabel() }}</p>
        </div>
    </div>

    <table>
        <tr><td>Employee</td><td class="amount">{{ $payslip->employee->user->name }} ({{ $payslip->employee->employee_code }})</td></tr>
        <tr><td>Department</td><td class="amount">{{ $payslip->employee->department->name ?? '—' }}</td></tr>
        <tr><td>Designation</td><td class="amount">{{ $payslip->employee->designation->title ?? '—' }}</td></tr>
    </table>

    <table>
        <tr><td>Gross pay</td><td class="amount">₹{{ number_format($payslip->gross_pay, 2) }}</td></tr>
        <tr><td>PF deduction</td><td class="amount">&minus;₹{{ number_format($payslip->pf_deduction, 2) }}</td></tr>
        <tr><td>ESI deduction</td><td class="amount">&minus;₹{{ number_format($payslip->esi_deduction, 2) }}</td></tr>
        <tr><td>Professional tax</td><td class="amount">&minus;₹{{ number_format($payslip->professional_tax, 2) }}</td></tr>
        <tr><td>TDS</td><td class="amount">&minus;₹{{ number_format($payslip->tds_deduction, 2) }}</td></tr>
        <tr><td>Other deductions</td><td class="amount">&minus;₹{{ number_format($payslip->other_deductions, 2) }}</td></tr>
        <tr class="total"><td>Net pay</td><td class="amount">₹{{ number_format($payslip->net_pay, 2) }}</td></tr>
    </table>

    <button class="print-btn" onclick="window.print()">Print / save as PDF</button>
</body>
</html>
