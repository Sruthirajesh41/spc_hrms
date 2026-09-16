<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · SPC Universal HR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{ --ink:#1B2430; --paper:#F3F3EF; --surface:#FFFFFF; --line:rgba(27,36,48,0.12); --text:#20262F; --text-muted:#6B7280; --brass:#A87C2E; --brass-soft:#F1E6D2; --bad:#AE4A34; --bad-soft:#F5E2DD; }
        *{box-sizing:border-box;}
        body{margin:0;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;background:var(--paper);color:var(--text);-webkit-font-smoothing:antialiased;}
        .wrap{max-width:420px;margin:0 auto;padding:70px 24px;}
        .brand{display:flex;align-items:center;gap:10px;margin-bottom:6px;}
        .brand-mark{width:32px;height:32px;border-radius:6px;background:var(--brass);color:#211505;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;}
        .brand-name{font-size:16px;font-weight:650;letter-spacing:-.01em;}
        h1{font-weight:650;font-size:23px;letter-spacing:-.01em;margin:22px 0 6px;}
        p.lead{color:var(--text-muted);font-size:13.5px;margin:0 0 28px;}
        .card{border:1px solid var(--line);background:var(--surface);border-radius:8px;padding:26px;}
        .field{display:flex;flex-direction:column;gap:6px;margin-bottom:16px;}
        .field label{font-size:12px;font-weight:650;color:#4A5163;}
        input{font-family:inherit;font-size:13.5px;padding:10px 12px;border:1px solid var(--line);border-radius:5px;background:#FCFCFB;color:var(--text);width:100%;outline:none;}
        input:focus{border-color:var(--brass);background:#fff;}
        .btn-primary{width:100%;background:var(--ink);color:#fff;border:1px solid var(--ink);padding:11px;border-radius:5px;font-size:13.5px;font-weight:600;cursor:pointer;margin-top:4px;}
        .btn-primary:hover{filter:brightness(1.1);}
        .errors{background:var(--bad-soft);border:1px solid rgba(174,74,52,0.3);color:var(--bad);padding:11px 14px;border-radius:6px;margin-bottom:18px;font-size:13px;}
        .demo-note{font-size:12px;color:var(--text-muted);margin-top:26px;border-top:1px solid var(--line);padding-top:16px;line-height:1.7;}
        .demo-note strong{color:var(--text);}
        .demo-note code{background:var(--brass-soft);color:#7A5416;padding:1px 5px;border-radius:3px;font-size:11.5px;}
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand"><div class="brand-mark">S</div><div class="brand-name">SPC Universal</div></div>
    <h1>Sign in to HR Management</h1>
    <p class="lead">Enter your work email and password to continue.</p>

    <div class="card">
        @if($errors->any())
            <div class="errors">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="you@spc.com" required autofocus>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
            </div>
            <button type="submit" class="btn-primary">Sign in</button>
        </form>
    </div>

    <p class="demo-note">
        <strong>Demo dataset:</strong> every seeded account shares the password <code>Password@123</code>.
        Try <code>arjun.nair@spc.com</code> (Super Admin), <code>priya.menon@spc.com</code> (HR Admin),
        <code>rahul.varma@spc.com</code> (Manager) or <code>kiran.pillai@spc.com</code> (Employee).
        You can change your own password from Employee Records once signed in.
    </p>
</div>
</body>
</html>
