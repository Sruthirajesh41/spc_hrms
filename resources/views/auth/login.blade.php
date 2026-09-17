<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · SPC Universal HR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
    :root{
      --brand:#146C4E; --brand-ink:#0A3D2C; --brand-bright:#1FA97A;
      --brand-soft:#E4F3EB; --brand-softer:#F2F9F5;
      --text:#22352C; --text-muted:#61756B;
      --font-head:'Kanit',sans-serif; --font-body:'Outfit',sans-serif;
    }
    *{box-sizing:border-box;}
    body{
      margin:0;font-family:var(--font-body);color:var(--text);min-height:100vh;
      background:
        radial-gradient(900px 500px at 105% -12%, rgba(31,169,122,0.14), transparent 60%),
        radial-gradient(700px 460px at -12% 110%, rgba(20,108,78,0.10), transparent 58%),
        linear-gradient(160deg,#F4FAF6 0%,#EAF4EE 55%,#F6FBF8 100%);
      -webkit-font-smoothing:antialiased;
      display:flex;flex-direction:column;
    }
    .deco{position:fixed;border-radius:34%;pointer-events:none;z-index:0;}
    .deco-1{width:180px;height:180px;top:-56px;right:14%;background:linear-gradient(135deg,rgba(43,192,141,.2),rgba(43,192,141,.05));transform:rotate(18deg);}
    .deco-2{width:110px;height:110px;bottom:12%;left:-38px;background:linear-gradient(135deg,rgba(31,169,122,.16),transparent);transform:rotate(-12deg);}
    .deco-3{width:64px;height:64px;top:22%;left:38%;border:1.5px solid rgba(31,169,122,.22);border-radius:50%;}
    .deco-4{width:16px;height:16px;top:64%;right:30%;background:#2BC08D;border-radius:50%;opacity:.5;}

    .top-brand{display:flex;align-items:center;gap:12px;padding:26px 42px 0;position:relative;z-index:2;}
    .top-brand img{height:25px;width:auto;max-width:150px;object-fit:contain;filter:brightness(0) invert(1) drop-shadow(0 4px 10px rgba(10,61,44,.35));}
    .top-brand .logo-tile{
      height:46px;padding:0 15px;border-radius:14px;flex-shrink:0;display:flex;align-items:center;justify-content:center;
      background:linear-gradient(135deg,#0E5239,#08301F);box-shadow:0 12px 24px -10px rgba(8,48,31,.55);
    }
    .top-brand b{display:block;font-family:var(--font-head);font-size:16px;font-weight:600;color:var(--brand-ink);line-height:1.2;}
    .top-brand span{font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--brand-bright);}

    .login-wrap{flex:1;display:flex;align-items:center;justify-content:center;gap:64px;padding:34px 42px 56px;position:relative;z-index:1;flex-wrap:wrap;}

    .pitch{max-width:520px;}
    .pitch-pill{
      display:inline-flex;align-items:center;gap:8px;font-size:10.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
      color:var(--brand);background:#fff;border:1px solid rgba(31,169,122,.3);border-radius:99px;padding:8px 15px;
      box-shadow:0 8px 20px -12px rgba(31,169,122,.4);margin-bottom:22px;
    }
    .pitch-pill i{font-size:11px;}
    .pitch h1{font-family:var(--font-head);font-weight:600;font-size:38px;line-height:1.16;color:var(--brand-ink);margin:0 0 16px;letter-spacing:.005em;}
    .pitch h1 .accent{color:var(--brand-bright);}
    .pitch p{font-size:14.5px;color:var(--text-muted);line-height:1.7;margin:0 0 26px;max-width:46ch;}
    .feat-row{display:flex;gap:12px;flex-wrap:wrap;}
    .feat{
      background:#fff;border:1px solid rgba(18,58,40,.1);border-radius:14px;padding:13px 15px;min-width:128px;
      box-shadow:0 10px 24px -16px rgba(10,61,44,.35);
    }
    .feat i{
      font-size:13px;color:var(--brand);width:30px;height:30px;border-radius:9px;background:var(--brand-soft);
      display:flex;align-items:center;justify-content:center;margin-bottom:9px;
    }
    .feat b{display:block;font-family:var(--font-head);font-size:12.5px;font-weight:500;color:var(--brand-ink);line-height:1.35;}

    .card{
      width:400px;max-width:100%;background:#fff;border-radius:22px;padding:36px 34px 30px;
      box-shadow:0 34px 70px -30px rgba(8,48,31,.45), 0 6px 18px -10px rgba(10,61,44,.2);
      border:1px solid rgba(255,255,255,.8);position:relative;
    }
    .card-logo{
      width:128px;height:60px;border-radius:18px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;
      background:linear-gradient(135deg,#0E5239,#08301F);box-shadow:0 16px 30px -12px rgba(8,48,31,.6), inset 0 1.5px 0 rgba(255,255,255,.18);
    }
    .card-logo img{height:30px;width:auto;max-width:98px;object-fit:contain;filter:brightness(0) invert(1);}
    .card h2{font-family:var(--font-head);font-weight:600;font-size:22px;color:var(--brand-ink);text-align:center;margin:0 0 4px;}
    .card .card-sub{text-align:center;font-size:12.5px;color:var(--text-muted);margin:0 0 24px;}

    .f-label{font-size:12.5px;font-weight:600;color:#3D4F46;margin:0 0 6px;display:block;}
    .input-wrap{position:relative;margin-bottom:15px;}
    .input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#8FA79B;font-size:13px;}
    .input-wrap input{
      width:100%;font-family:var(--font-body);font-size:13.5px;color:var(--text);
      padding:11.5px 14px 11.5px 40px;border:1px solid rgba(18,58,40,.16);border-radius:12px;background:#FBFDFC;outline:none;
      transition:border-color .15s,background .15s,box-shadow .15s;
    }
    .input-wrap input:focus{border-color:var(--brand-bright);background:#fff;box-shadow:0 0 0 3.5px rgba(31,169,122,.14);}
    .input-wrap input::placeholder{color:#A5B8AD;}

    .row-between{display:flex;align-items:center;justify-content:space-between;margin:2px 0 18px;}
    .remember{display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--text-muted);cursor:pointer;}
    .remember input{width:15px;height:15px;accent-color:var(--brand);cursor:pointer;}
    .forgot{font-size:12.5px;font-weight:600;color:var(--brand);}

    .btn-primary{
      width:100%;display:inline-flex;align-items:center;justify-content:center;gap:10px;
      background:linear-gradient(135deg,#1FA97A,#0E6B4B);color:#fff;border:none;border-radius:13px;
      padding:13px 18px;font-family:var(--font-body);font-size:14px;font-weight:600;cursor:pointer;letter-spacing:.01em;
      box-shadow:0 16px 30px -12px rgba(31,169,122,.6), inset 0 1.5px 0 rgba(255,255,255,.25);
      transition:filter .15s,transform .1s;
    }
    .btn-primary:hover{filter:brightness(1.07);}
    .btn-primary:active{transform:translateY(1px);}
    .demo-hint{
      display:flex;align-items:center;justify-content:center;gap:7px;font-size:11.5px;color:var(--brand-ink);
      background:var(--brand-softer);border:1px dashed rgba(31,169,122,.45);border-radius:10px;padding:8px 10px;margin:14px 0 12px;
    }
    .demo-hint i{color:var(--brand-bright);}
    .demo-hint code{font-family:ui-monospace,monospace;font-weight:600;background:#fff;padding:1px 6px;border-radius:6px;border:1px solid rgba(31,169,122,.3);}
    .card-foot{margin-top:18px;text-align:center;font-size:11px;color:#9AB0A5;}
    .card-foot a{color:var(--brand);font-weight:600;text-decoration:none;}

    .flash{max-width:400px;width:100%;margin:0 auto;background:#DCF3E4;border:1px solid rgba(21,128,61,.28);color:#14663A;padding:12px 16px;border-radius:12px;font-size:13px;display:flex;align-items:center;gap:10px;}
    .flash::before{content:"\f058";font-family:"Font Awesome 6 Free";font-weight:900;}
    .flash-errors{max-width:400px;width:100%;margin:0 auto;background:#FBE7E4;border:1px solid rgba(192,52,52,.3);color:#8F2323;padding:12px 16px;border-radius:12px;font-size:13px;}
    .flash-errors::before{content:"\f06a";font-family:"Font Awesome 6 Free";font-weight:900;margin-right:8px;}

    .page-foot{padding:0 42px 26px;font-size:11.5px;color:#9AB0A5;display:flex;align-items:center;gap:10px;position:relative;z-index:1;}
    .page-foot .foot-dot{width:7px;height:7px;border-radius:50%;background:var(--brand-bright);opacity:.7;}

    @media (max-width:900px){
      /* Mobile: drop the marketing pitch, show brand + card only */
      .pitch{display:none;}
      .login-wrap{gap:0;padding:18px 18px 30px;flex:1;}
      .top-brand{padding:22px 20px 0;justify-content:center;}
      .page-foot{justify-content:center;padding:0 20px 18px;}
    }
    @media (max-width:520px){
      .top-brand{padding-top:18px;}
      .top-brand b{font-size:14.5px;}
      .top-brand span{font-size:10px;}
      .top-brand .logo-tile{height:40px;padding:0 12px;}
      .top-brand img{height:21px;max-width:120px;}
      .card{padding:26px 20px 22px;width:100%;max-width:400px;}
      .card-logo{width:112px;height:54px;}
      .card-logo img{height:26px;max-width:86px;}
      .login-wrap{padding:14px 14px 24px;}
    }
    </style>
</head>
<body>
    <div class="deco deco-1"></div>
    <div class="deco deco-2"></div>
    <div class="deco deco-3"></div>
    <div class="deco deco-4"></div>

    <div class="top-brand">
        <div class="logo-tile"><img src="{{ asset('images/spc-logo.png') }}" alt="SPC Universal"></div>
        <div>
            <b>SPC Universal</b>
            <span>HR Management Suite</span>
        </div>
    </div>

    <div class="login-wrap">
        <div class="pitch">
            <span class="pitch-pill"><i class="fa-solid fa-shield-halved"></i>HR Management Suite</span>
            <h1>One secure gateway to<br><span class="accent">your entire workforce.</span></h1>
            <p>Sign in to manage attendance, leave, payroll, recruitment and performance through one unified platform built for modern teams.</p>
            <div class="feat-row">
                <div class="feat"><i class="fa-solid fa-user-shield"></i><b>Role based access</b></div>
                <div class="feat"><i class="fa-solid fa-chart-column"></i><b>Live insights</b></div>
                <div class="feat"><i class="fa-solid fa-layer-group"></i><b>10+ modules</b></div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:14px;align-items:center;">
            @if(session('status'))
                <div class="flash">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="flash-errors">
                    <ul style="margin:0;padding-left:16px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-logo"><img src="{{ asset('images/spc-logo.png') }}" alt="SPC"></div>
                <h2>SPC Portal</h2>
                <p class="card-sub">Secure HR Access &middot; Management System</p>
                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <label class="f-label" for="email">Email</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Enter your work email" required autofocus>
                    </div>
                    <label class="f-label" for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input id="password" type="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="row-between">
                        <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
                        <a class="forgot" href="#">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn-primary">Sign in securely <i class="fa-solid fa-arrow-right"></i></button>
                </form>
                <div class="demo-hint"><i class="fa-solid fa-circle-info"></i>Demo access &mdash; any seed email + <code>Password@123</code></div>
                <div class="card-foot">By signing in, you agree to the Portal's <a href="#">Terms</a> &amp; <a href="#">Privacy Policy</a></div>
            </div>
        </div>
    </div>

    <div class="page-foot"><span class="foot-dot"></span>&copy; {{ date('Y') }} SPC Universal &middot; Developed by Gipra Business Solutions Pvt Ltd</div>
</body>
</html>
