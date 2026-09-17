<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') · SPC Universal HR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
    /* ============================================================
       SPC HR — "Evergreen v2" design system
       Creative deep-green identity · Kanit display / Outfit body
       ============================================================ */
    :root{
      --brand:#146C4E; --brand-strong:#0E5239; --brand-ink:#0A3D2C;
      --brand-deep:#08301F; --brand-bright:#1FA97A; --brand-glow:rgba(31,169,122,.35);
      --brand-soft:#E4F3EB; --brand-softer:#F2F9F5;
      --paper:#F0F5F1; --surface:#FFFFFF;
      --line:rgba(18,58,40,0.13); --line-soft:rgba(18,58,40,0.07);
      --text:#22352C; --text-muted:#61756B;
      --sidebar-text:#BFD6CB; --sidebar-bright:#EAF5EF;
      --role-accent: {{ $roleData['accent'] ?? '#146C4E' }};
      --role-accent-soft: color-mix(in srgb, var(--role-accent) 12%, white);
      --ok:#15803D; --ok-soft:#DCF3E4; --warn:#B45309; --warn-soft:#FCF0D8; --bad:#C03434; --bad-soft:#FBE7E4;
      --radius:14px; --radius-sm:10px;
      --shadow-sm:0 1px 2px rgba(10,61,44,.05);
      --shadow-md:0 14px 34px -16px rgba(10,61,44,.28);
      --shadow-lg:0 28px 60px -24px rgba(8,48,31,.4);
      --sidebar-w:268px;
      --font-head:'Kanit',sans-serif; --font-body:'Outfit',sans-serif;
    }
    *{box-sizing:border-box;}
    html{scroll-behavior:smooth;}
    body{
      margin:0;font-family:var(--font-body);color:var(--text);background:var(--paper);
      -webkit-font-smoothing:antialiased;font-size:14.5px;line-height:1.55;
    }
    body::before{
      content:"";position:fixed;inset:0;z-index:-1;pointer-events:none;
      background:
        radial-gradient(1000px 460px at 90% -10%, rgba(31,169,122,0.12), transparent 62%),
        radial-gradient(760px 420px at -8% 8%, rgba(20,108,78,0.09), transparent 58%),
        var(--paper);
    }
    h1,h2,h3,h4,.font-head{font-family:var(--font-head);font-weight:600;letter-spacing:.01em;color:var(--brand-ink);margin:0;}
    a{color:inherit;text-decoration:none;}
    :focus-visible{outline:2px solid var(--brand-bright);outline-offset:2px;border-radius:6px;}
    button{font-family:inherit;}
    button, .btn-primary, .btn-secondary, .btn-ghost{white-space:nowrap;}
    .bell-panel .bell-item button, .flash button, .tab, .list-group-item button{white-space:normal;}
    button[type="submit"], button[type="button"]{flex-shrink:0;}
    .modal-foot .btn-primary, .modal-foot .btn-secondary{flex-shrink:0;}
    @media (max-width:560px){
      .modal-foot{flex-wrap:wrap;}
      .modal-foot .hint-secure{width:100%;margin:0 0 8px;}
    }
    ::-webkit-scrollbar{width:9px;height:9px;}
    ::-webkit-scrollbar-thumb{background:rgba(18,58,40,.2);border-radius:99px;border:2px solid transparent;background-clip:content-box;}
    ::-webkit-scrollbar-track{background:transparent;}

    .shell{display:flex;min-height:100vh;}

    /* ================= Sidebar — creative deep-green ================= */
    .sidebar{
      width:var(--sidebar-w);flex-shrink:0;display:flex;flex-direction:column;
      position:fixed;top:0;left:0;bottom:0;overflow-y:auto;z-index:60;
      background:
        radial-gradient(480px 300px at 115% -6%, rgba(31,169,122,0.28), transparent 60%),
        radial-gradient(420px 320px at -25% 108%, rgba(31,169,122,0.18), transparent 55%),
        linear-gradient(175deg,#0E4A35 0%, var(--brand-ink) 55%, var(--brand-deep) 100%);
      color:var(--sidebar-text);
      transform:translateX(0);transition:transform .28s cubic-bezier(.4,0,.2,1);
    }
    .sidebar::after{content:"";position:absolute;top:120px;right:-70px;width:200px;height:200px;border-radius:50%;border:1.5px solid rgba(31,169,122,.18);pointer-events:none;}
    .sidebar-inner{position:relative;z-index:1;display:flex;flex-direction:column;min-height:100%;}
    .brand{display:flex;align-items:center;gap:12px;padding:24px 22px 18px;}
    .brand-mark{
      width:42px;height:42px;border-radius:13px;flex-shrink:0;
      background:linear-gradient(135deg,#2BC08D 0%,#128A62 60%,#0C6B4B 100%);
      display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:700;font-size:19px;color:#fff;
      box-shadow:0 10px 22px -8px rgba(31,169,122,.65), inset 0 1.5px 0 rgba(255,255,255,.35);
      position:relative;
    }
    .brand-mark::after{content:"";position:absolute;inset:0;border-radius:13px;border:1px solid rgba(255,255,255,.22);}
    .brand-logo{height:34px;max-width:132px;width:auto;display:block;object-fit:contain;filter:brightness(0) invert(1);}
    .brand-name{font-family:var(--font-head);font-size:17px;font-weight:600;color:#fff;letter-spacing:.015em;line-height:1.2;}
    .brand-tag{font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#63C79E;margin-top:1px;}
    .brand-row{display:flex;align-items:center;justify-content:center;position:relative;padding-right:14px;}
    .brand-row .sidebar-close{position:absolute;right:14px;top:50%;transform:translateY(-50%);}
    .sidebar-close{display:none;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);color:#fff;width:32px;height:32px;border-radius:9px;cursor:pointer;font-size:13px;align-items:center;justify-content:center;}

    .role-chip{
      margin:4px 16px 10px;border-radius:16px;padding:14px 16px;position:relative;
      background:linear-gradient(140deg,rgba(255,255,255,.12),rgba(255,255,255,.05));
      border:1px solid rgba(255,255,255,.14);
    }
    .role-chip::after{content:"\f2bd";font-family:"Font Awesome 6 Free";font-weight:900;position:absolute;right:14px;top:13px;font-size:16px;color:rgba(95,224,178,.55);}
    .role-chip .role-person{display:flex;align-items:center;gap:11px;min-width:0;padding-right:26px;}
    .role-chip .role-av{
      width:38px;height:38px;border-radius:12px;flex-shrink:0;
      background:linear-gradient(135deg,#2BC08D,#0C6B4B);
      display:flex;align-items:center;justify-content:center;
      font-family:var(--font-head);font-weight:600;font-size:14px;color:#fff;
      box-shadow:0 8px 16px -8px rgba(31,169,122,.7), inset 0 1.5px 0 rgba(255,255,255,.3);
    }
    .role-chip .role-name{
      font-family:var(--font-head);font-size:14px;font-weight:600;color:#fff;line-height:1.3;
      display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;word-break:break-word;
    }
    .role-chip .role-tagline{font-size:11px;color:var(--sidebar-text);margin-top:9px;padding-top:9px;border-top:1px dashed rgba(255,255,255,.12);line-height:1.55;}
    .role-chip .role-tagline b{color:#5FE0B2;font-weight:600;letter-spacing:.04em;}
    .role-chip .role-email{font-size:10.5px;color:#63C79E;margin-top:5px;display:flex;align-items:center;gap:6px;word-break:break-all;}

    .nav-scroll{flex:1;padding-bottom:14px;}
    .nav-heading{font-family:var(--font-body);font-size:9.5px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#5E9E82;margin:18px 22px 7px;display:flex;align-items:center;gap:8px;}
    .nav-heading::after{content:"";flex:1;height:1px;background:linear-gradient(90deg,rgba(255,255,255,.12),transparent);}
    .nav-list{list-style:none;margin:0;padding:0 14px;display:flex;flex-direction:column;gap:2px;}
    .nav-item{
      display:flex;align-items:center;gap:11px;padding:9.5px 12px;border-radius:11px;font-size:13.5px;font-weight:400;
      color:var(--sidebar-text);border:none;width:100%;background:none;cursor:pointer;text-align:left;
      transition:background .16s,color .16s;position:relative;
    }
    .nav-item i{width:19px;text-align:center;font-size:14px;opacity:.8;transition:opacity .16s;}
    .nav-item:hover{background:rgba(255,255,255,.07);color:#fff;}
    .nav-item:hover i{opacity:1;}
    .nav-item.active{
      background:linear-gradient(92deg,rgba(43,192,141,.24),rgba(43,192,141,.1));
      color:#fff;font-weight:500;
      border:1px solid rgba(43,192,141,.32);
      box-shadow:0 8px 18px -10px var(--brand-glow), inset 0 1px 0 rgba(255,255,255,.08);
    }
    .nav-item.active i{opacity:1;color:#5FE0B2;}
    .nav-item.active::before{content:"";position:absolute;left:-14px;top:20%;bottom:20%;width:3.5px;border-radius:0 4px 4px 0;background:#2BC08D;box-shadow:0 0 12px rgba(43,192,141,.8);}
    .nav-badge{margin-left:auto;background:linear-gradient(135deg,#FF6B5E,#E5484D);color:#fff;font-size:10px;font-weight:700;line-height:1;padding:4px 8px;border-radius:99px;box-shadow:0 4px 10px -4px rgba(229,72,77,.7);}
    .sidebar-foot{font-size:10.5px;color:#5E9E82;line-height:1.65;padding:16px 22px 20px;border-top:1px solid rgba(255,255,255,.07);margin-top:14px;}
    .logout-link{display:inline-flex;align-items:center;gap:6px;margin-top:6px;font-size:11px;color:#63C79E;font-weight:500;cursor:pointer;background:none;border:none;padding:0;font-family:inherit;}
    .logout-link:hover{color:#8FE7C6;}

    /* ================= Main + overlay ================= */
    .main{flex:1;min-width:0;margin-left:var(--sidebar-w);transition:margin .28s cubic-bezier(.4,0,.2,1);}
    .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(8,48,31,.5);backdrop-filter:blur(2px);z-index:55;}
    .sidebar-open .sidebar-overlay{display:block;}

    .topbar{
      display:flex;align-items:center;justify-content:space-between;padding:0 34px;height:68px;
      border-bottom:1px solid var(--line);background:rgba(255,255,255,.85);backdrop-filter:blur(10px);
      position:sticky;top:0;z-index:50;gap:14px;flex-wrap:nowrap;
    }
    .topbar-left{display:flex;align-items:center;gap:14px;min-width:0;flex:1;}
    .topbar-left > div{min-width:0;}
    .hamburger{
      display:none;width:40px;height:40px;border-radius:11px;border:1px solid var(--line);background:var(--surface);
      color:var(--brand-ink);font-size:15px;cursor:pointer;align-items:center;justify-content:center;flex-shrink:0;
      box-shadow:var(--shadow-sm);
    }
    .hamburger:active{transform:scale(.95);}
    .topbar h1{margin:0;font-size:20px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .topbar .eyebrow{font-family:var(--font-body);font-size:9.5px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--brand-bright);margin-bottom:2px;display:flex;align-items:center;gap:6px;white-space:nowrap;overflow:hidden;}
    .topbar .eyebrow::before{content:"";width:16px;height:2px;border-radius:2px;background:var(--brand-bright);}

    .topbar-actions{display:flex;align-items:center;gap:12px;margin-left:auto;flex-shrink:0;}
    .bell{position:relative;cursor:pointer;list-style:none;}
    .bell::-webkit-details-marker{display:none;}
    .bell-icon{
      width:40px;height:40px;border:1px solid var(--line);border-radius:12px;display:flex;align-items:center;justify-content:center;
      background:var(--surface);color:var(--text-muted);transition:all .16s;box-shadow:var(--shadow-sm);
    }
    .bell-icon:hover{border-color:var(--brand-bright);color:var(--brand);background:var(--brand-softer);box-shadow:0 6px 14px -8px var(--brand-glow);}
    .bell-badge{position:absolute;top:-4px;right:-4px;background:linear-gradient(135deg,#FF6B5E,#E5484D);color:#fff;font-size:9.5px;line-height:1;border-radius:10px;padding:3.5px 5.5px;min-width:16px;text-align:center;font-weight:700;border:2px solid #fff;}
    details.bell[open] summary.bell-icon{border-color:var(--brand-bright);color:var(--brand);background:var(--brand-softer);}
    .bell-panel{
      position:absolute;right:0;top:48px;width:330px;background:var(--surface);border:1px solid var(--line);border-radius:16px;
      box-shadow:var(--shadow-lg);z-index:20;padding:10px;animation:popIn .18s ease;
    }
    @keyframes popIn{from{opacity:0;transform:translateY(-6px) scale(.98);}to{opacity:1;transform:none;}}
    .bell-panel .bell-item{display:block;padding:10px 10px;border-bottom:1px solid var(--line-soft);font-size:13px;color:var(--text);border-radius:9px;}
    .bell-panel .bell-item:hover{background:var(--brand-softer);}
    .bell-panel .bell-item:last-of-type{border-bottom:none;}
    .bell-panel .bell-item form{margin:0;}
    .bell-panel .bell-item button{background:none;border:none;padding:0;text-align:left;font-size:13px;color:inherit;cursor:pointer;width:100%;font-family:inherit;}
    .bell-panel .bell-empty{padding:20px 8px;color:var(--text-muted);font-size:13px;text-align:center;}
    .bell-panel .bell-empty i{display:block;font-size:20px;color:var(--brand-soft);margin-bottom:8px;color:#A7C9B8;}
    .bell-panel .bell-foot{padding:9px 8px 3px;text-align:center;border-top:1px solid var(--line-soft);margin-top:4px;}
    .bell-panel .bell-foot a{font-size:12.5px;color:var(--brand);font-weight:600;}
    .user-chip{display:flex;align-items:center;gap:10px;font-size:13px;text-align:right;}
    .avatar{
      width:37px;height:37px;border-radius:12px;color:#fff;font-weight:600;font-size:13px;font-family:var(--font-head);
      background:linear-gradient(135deg,#2BC08D,#0C6B4B);display:flex;align-items:center;justify-content:center;flex-shrink:0;
      box-shadow:0 6px 14px -6px var(--brand-glow), inset 0 1.5px 0 rgba(255,255,255,.3);
    }
    .user-chip .who{text-align:left;line-height:1.3;}
    .user-chip .who b{display:block;font-family:var(--font-head);font-size:13.5px;font-weight:500;color:var(--brand-ink);}
    .user-chip .who span{color:var(--text-muted);font-size:11.5px;}
    .user-chip-summary{width:auto;height:auto;border-radius:13px;padding:4.5px 13px 4.5px 5.5px;background:var(--surface);border:1px solid var(--line);box-shadow:var(--shadow-sm);}
    details.user-menu[open] summary.user-chip-summary{border-color:var(--brand-bright);background:var(--brand-softer);}
    .user-panel{width:284px;}
    .user-panel-head{display:flex;align-items:center;gap:12px;padding:8px 8px 13px;border-bottom:1px solid var(--line-soft);margin-bottom:6px;}
    .user-panel-head .avatar{width:42px;height:42px;font-size:15px;}
    .user-panel-head b{display:block;font-family:var(--font-head);font-size:14px;color:var(--brand-ink);}
    .user-panel-detail{display:flex;justify-content:space-between;gap:10px;padding:6.5px 9px;font-size:12.5px;}
    .user-panel-detail span:first-child{color:var(--text-muted);}
    .user-panel-detail span:last-child{font-weight:500;text-align:right;color:var(--brand-ink);}

    .content{padding:30px 34px 74px;max-width:100%;width:100%;margin:0 auto;}

    .flash{background:var(--ok-soft);border:1px solid rgba(21,128,61,0.28);color:#14663A;padding:13px 17px;border-radius:12px;margin-bottom:22px;font-size:13.5px;font-weight:500;display:flex;align-items:center;gap:10px;}
    .flash::before{content:"\f058";font-family:"Font Awesome 6 Free";font-weight:900;}
    .flash-errors{background:var(--bad-soft);border:1px solid rgba(192,52,52,0.3);color:#8F2323;padding:13px 17px;border-radius:12px;margin-bottom:22px;font-size:13.5px;}
    .flash-errors::before{content:"\f06a";font-family:"Font Awesome 6 Free";font-weight:900;margin-right:8px;}
    .flash-errors ul{margin:4px 0 0;padding-left:18px;}

    /* ================= Dashboard hero ================= */
    .dash-hero{
      position:relative;overflow:hidden;border-radius:20px;padding:26px 30px;margin-bottom:26px;
      background:
        radial-gradient(420px 200px at 92% -20%, rgba(43,192,141,.35), transparent 60%),
        radial-gradient(360px 220px at -6% 120%, rgba(43,192,141,.22), transparent 55%),
        linear-gradient(135deg,#10553D 0%, var(--brand-ink) 55%, #07281B 100%);
      color:#fff;box-shadow:var(--shadow-lg);
    }
    .dash-hero::after{content:"\f1ad";font-family:"Font Awesome 6 Free";font-weight:900;position:absolute;right:26px;top:50%;transform:translateY(-50%);font-size:110px;color:rgba(43,192,141,.13);}
    .dash-hero::before{content:"";position:absolute;top:-70px;right:110px;width:220px;height:220px;border-radius:50%;border:1.5px solid rgba(255,255,255,.09);}
    .dash-hero-text h2{margin:0 0 5px;font-size:23px;font-weight:600;color:#fff;}
    .dash-hero-text p{margin:0;font-size:13.5px;color:#A8CDBB;max-width:56ch;}
    .dash-hero-date{text-align:right;position:relative;z-index:1;}
    .dash-hero-date b{display:block;font-family:var(--font-head);font-size:14px;color:#fff;font-weight:500;}
    .dash-hero-date span{font-size:12px;color:#5FE0B2;font-weight:500;display:flex;align-items:center;gap:6px;justify-content:flex-end;margin-top:3px;}

    /* ================= Check-in / out widget (dashboard) ================= */
    .ci-box{
      position:relative;z-index:1;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);
      border-radius:16px;padding:14px 16px;min-width:210px;backdrop-filter:blur(6px);
    }
    .ci-box .ci-state{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#8FB9A5;display:flex;align-items:center;gap:7px;margin-bottom:9px;}
    .ci-pulse{width:9px;height:9px;border-radius:50%;background:#2BC08D;box-shadow:0 0 0 0 rgba(43,192,141,.6);animation:ciPulse 1.6s infinite;}
    @keyframes ciPulse{0%{box-shadow:0 0 0 0 rgba(43,192,141,.55);}70%{box-shadow:0 0 0 9px rgba(43,192,141,0);}100%{box-shadow:0 0 0 0 rgba(43,192,141,0);}}
    .ci-clock{font-family:var(--font-head);font-size:26px;font-weight:600;color:#fff;line-height:1.1;font-variant-numeric:tabular-nums;}
    .ci-sub{font-size:11.5px;color:#A8CDBB;margin-top:3px;}
    .ci-btn{
      margin-top:11px;width:100%;display:inline-flex;align-items:center;justify-content:center;gap:9px;
      background:linear-gradient(135deg,#2BC08D,#0C6B4B);color:#fff;border:1px solid rgba(255,255,255,.25);
      padding:10.5px 18px;border-radius:11px;font-size:13.5px;font-weight:600;cursor:pointer;font-family:var(--font-body);
      box-shadow:0 12px 24px -10px rgba(0,0,0,.5), inset 0 1.5px 0 rgba(255,255,255,.3);transition:filter .15s,transform .1s;
    }
    .ci-btn:hover{filter:brightness(1.08);}
    .ci-btn:active{transform:translateY(1px);}
    .ci-btn.out{background:rgba(255,255,255,.14);}
    .ci-done{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-head);font-size:15px;color:#5FE0B2;}

    /* ================= Reports widgets ================= */
    .stat-tiles.cols-6{grid-template-columns:repeat(6,1fr);}
    @media (max-width:1200px){.stat-tiles.cols-6{grid-template-columns:repeat(3,1fr);}}
    @media (max-width:640px){
      .stat-tiles.cols-6{grid-template-columns:repeat(2,1fr);}
      .stat-tile{padding:13px 14px;gap:11px;}
      .stat-tile .st-ico{width:38px;height:38px;border-radius:11px;font-size:14px;}
      .stat-tile b{font-size:16px;}
      .stat-tile span{font-size:9.5px;}
    }
    .funnel-rate{
      display:flex;align-items:center;gap:8px;margin-top:16px;padding:11px 15px;border-radius:12px;
      background:var(--brand-softer);border:1px dashed rgba(31,169,122,.4);font-size:12.5px;color:var(--text-muted);
    }
    .funnel-rate i{color:var(--brand-bright);}
    .funnel-rate b{font-family:var(--font-head);font-size:15px;color:var(--brand-ink);margin:0 2px;}
    .appr-wrap{display:flex;align-items:center;gap:26px;padding:14px 4px;flex-wrap:wrap;}
    .donut{
      --pct:0;--size:132px;width:var(--size);height:var(--size);border-radius:50%;flex-shrink:0;
      background:conic-gradient(var(--brand-bright) calc(var(--pct)*1%), #E3EFE8 0);
      display:flex;align-items:center;justify-content:center;
    }
    .donut-hole{
      width:calc(var(--size) - 30px);height:calc(var(--size) - 30px);border-radius:50%;background:#fff;
      display:flex;flex-direction:column;align-items:center;justify-content:center;box-shadow:inset 0 0 0 1px var(--line-soft);
    }
    .donut-hole b{font-family:var(--font-head);font-size:27px;font-weight:600;color:var(--brand-ink);line-height:1;}
    .donut-hole span{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);margin-top:4px;}
    .appr-meta{display:flex;flex-direction:column;gap:9px;flex:1;min-width:180px;}
    .appr-line{display:flex;align-items:center;gap:9px;font-size:13px;color:var(--text);}
    .appr-line b{font-family:var(--font-head);font-size:15px;color:var(--brand-ink);margin-right:2px;}
    .appr-link{
      display:inline-flex;align-items:center;gap:7px;margin-top:6px;font-size:12.5px;font-weight:600;color:var(--brand);
      background:var(--brand-soft);border-radius:10px;padding:8px 13px;text-decoration:none;transition:all .15s;
    }
    .appr-link:hover{background:var(--brand);color:#fff;}
    @media (max-width:700px){
      .appr-wrap{justify-content:center;text-align:center;flex-direction:column;}
      .appr-meta{align-items:center;}
    }

    /* ================= Punch card (check-in/out) ================= */
    .punch-card{
      position:relative;overflow:hidden;display:flex;align-items:center;gap:18px;flex-wrap:wrap;
      background:linear-gradient(135deg,#11573F 0%, var(--brand-ink) 58%, #0A3423 100%);
      border-radius:20px;padding:20px 24px;margin-bottom:26px;box-shadow:var(--shadow-lg);color:#fff;
    }
    .punch-card::before{content:"";position:absolute;top:-64px;right:120px;width:190px;height:190px;border-radius:50%;border:1.5px solid rgba(255,255,255,.1);}
    .punch-card::after{content:"";position:absolute;bottom:-70px;left:-40px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle, rgba(43,192,141,.25), transparent 70%);}
    .punch-ring{
      width:64px;height:64px;border-radius:20px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:24px;
      background:linear-gradient(135deg,rgba(43,192,141,.9),rgba(12,107,75,.9));
      box-shadow:0 14px 30px -10px rgba(0,0,0,.5), inset 0 1.5px 0 rgba(255,255,255,.35);position:relative;z-index:1;
    }
    .punch-ring.idle{background:rgba(255,255,255,.12);box-shadow:inset 0 0 0 1.5px rgba(95,224,178,.5);}
    .punch-ring.working{animation:punchGlow 2s ease-in-out infinite;}
    @keyframes punchGlow{0%,100%{box-shadow:0 14px 30px -10px rgba(0,0,0,.5), 0 0 0 0 rgba(43,192,141,.45);}50%{box-shadow:0 14px 30px -10px rgba(0,0,0,.5), 0 0 0 12px rgba(43,192,141,0);}}
    .punch-info{flex:1;min-width:220px;position:relative;z-index:1;}
    .punch-info h3{margin:0 0 4px;font-family:var(--font-head);font-size:18px;font-weight:600;color:#fff;}
    .punch-info p{margin:0;font-size:12.5px;color:#A8CDBB;}
    .punch-info p b{color:#5FE0B2;font-weight:600;}
    .punch-clock{text-align:right;position:relative;z-index:1;}
    .punch-clock span{display:block;font-family:var(--font-head);font-size:27px;font-weight:600;color:#fff;font-variant-numeric:tabular-nums;line-height:1.1;}
    .punch-clock span small{font-size:15px;color:#5FE0B2;font-weight:500;}
    .punch-clock em{font-style:normal;font-size:11px;color:#8FB9A5;}
    .punch-action{position:relative;z-index:1;}
    .punch-btn{
      display:inline-flex;align-items:center;gap:10px;border:1px solid rgba(255,255,255,.25);cursor:pointer;
      background:linear-gradient(135deg,#2BC08D,#0C6B4B);color:#fff;padding:12px 24px;border-radius:13px;
      font-size:14px;font-weight:600;font-family:var(--font-body);white-space:nowrap;flex-shrink:0;
      box-shadow:0 14px 28px -10px rgba(0,0,0,.5), inset 0 1.5px 0 rgba(255,255,255,.3);transition:filter .15s,transform .1s;
    }
    .punch-btn:hover{filter:brightness(1.08);}
    .punch-btn:active{transform:translateY(1px);}
    .punch-btn.out{background:rgba(255,255,255,.14);}
    .punch-done-pill{
      display:inline-flex;align-items:center;gap:8px;background:rgba(43,192,141,.16);border:1px solid rgba(95,224,178,.45);
      color:#5FE0B2;border-radius:99px;padding:10px 20px;font-size:13px;font-weight:600;white-space:nowrap;
    }
    @media (max-width:700px){
      .punch-card{flex-direction:column;align-items:stretch;text-align:center;padding:18px 16px;}
      .punch-ring{margin:0 auto;}
      .punch-clock{text-align:center;order:3;}
      .punch-action{order:4;}
      .punch-btn{width:100%;justify-content:center;}
      .punch-done-pill{width:100%;justify-content:center;}
    }

    /* ================= Announcement ticker ================= */
    .ticker{
      display:flex;align-items:center;gap:14px;background:var(--surface);border:1px solid var(--line);
      border-radius:14px;padding:10px 14px;margin:24px 0 26px;box-shadow:var(--shadow-sm);overflow:hidden;
    }
    .ticker-label{
      font-family:var(--font-head);font-size:10.5px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;
      color:#fff;background:linear-gradient(135deg,#1FA97A,#0E6B4B);border-radius:9px;padding:7px 11px;
      display:inline-flex;align-items:center;gap:7px;flex-shrink:0;
    }
    .ticker-items{display:flex;gap:28px;overflow-x:auto;white-space:nowrap;scrollbar-width:none;flex:1;}
    .ticker-items::-webkit-scrollbar{display:none;}
    .ticker-item{font-size:12.5px;color:var(--text);display:inline-flex;align-items:center;gap:8px;flex-shrink:0;}
    .ticker-item i{color:var(--brand);font-size:11px;}
    .ticker-item b{color:var(--brand-ink);font-weight:600;}
    .ticker-item .t-date{color:var(--text-muted);font-size:11px;}
    .ticker-link{font-size:12px;font-weight:600;color:var(--brand);flex-shrink:0;display:inline-flex;align-items:center;gap:5px;}
    .ticker-link:hover{text-decoration:underline;}
    .ticker-link i{font-size:9px;}
    @media (max-width:700px){
      .ticker{padding:8px 10px;gap:10px;}
      .ticker-label span{display:none;}
      .ticker-item{max-width:70vw;overflow:hidden;text-overflow:ellipsis;}
    }

    /* ================= KPIs ================= */
    .kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin:24px 0 8px;}
    .kpi-card{
      position:relative;overflow:hidden;background:var(--surface);border:1px solid var(--line);border-radius:18px;
      padding:20px 20px 18px;box-shadow:var(--shadow-sm);transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease;
      display:flex;flex-direction:column;gap:14px;
    }
    .kpi-card::after{content:"";position:absolute;left:0;top:0;bottom:0;width:3.5px;background:linear-gradient(180deg,#2BC08D,#0E6B4B);opacity:0;transition:opacity .18s;}
    .kpi-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-md);border-color:rgba(31,169,122,.4);}
    .kpi-card:hover::after{opacity:1;}
    .kpi-top{display:flex;align-items:center;justify-content:space-between;}
    .kpi-ico{
      width:46px;height:46px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:18px;
      background:linear-gradient(135deg,var(--brand-soft),#D2EEDF);color:var(--brand);
      box-shadow:inset 0 0 0 1px rgba(20,108,78,.1);
    }
    .kpi-trend{font-size:10.5px;font-weight:600;color:var(--ok);background:var(--ok-soft);padding:3.5px 9px;border-radius:99px;display:inline-flex;align-items:center;gap:4px;}
    .kpi-card .kpi-label{font-size:11.5px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;font-family:var(--font-body);}
    .kpi-card .kpi-val{font-family:var(--font-head);font-size:28px;font-weight:600;margin-top:-8px;font-variant-numeric:tabular-nums;color:var(--brand-ink);letter-spacing:.01em;}
    .kpi-card .kpi-sub{font-size:12px;margin-top:-4px;color:var(--text-muted);display:flex;align-items:center;gap:6px;}
    .kpi-card .kpi-sub i{color:var(--brand-bright);font-size:10px;}

    /* ================= Quick actions ================= */
    .quick-actions{display:flex;flex-wrap:wrap;gap:10px;}
    .qa-btn{
      border:1px solid var(--line);background:var(--surface);border-radius:14px;padding:10.5px 17px;font-size:13px;font-weight:500;
      color:var(--brand-ink);display:inline-flex;align-items:center;gap:9px;box-shadow:var(--shadow-sm);transition:all .16s;
    }
    .qa-btn i{color:var(--brand);font-size:12.5px;background:var(--brand-soft);width:26px;height:26px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .16s;}
    .qa-btn:hover{border-color:var(--brand-bright);background:var(--brand-softer);transform:translateY(-2px);box-shadow:0 10px 22px -12px var(--brand-glow);}
    .qa-btn:hover i{background:var(--brand);color:#fff;}

    .section-title{font-size:19px;margin:0 0 4px;}
    .section-note{font-size:13.5px;color:var(--text-muted);margin:0 0 18px;max-width:64ch;}
    .section-head{display:flex;align-items:center;justify-content:space-between;margin:30px 0 14px;}
    .section-head h2{font-size:17px;font-weight:600;display:flex;align-items:center;gap:10px;}
    .section-head h2 i{color:var(--brand);font-size:14px;background:var(--brand-soft);width:30px;height:30px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;}
    .section-head .hint{font-size:12.5px;color:var(--text-muted);}

    .grid-2{display:grid;grid-template-columns:1.3fr 1fr;gap:18px;align-items:start;}
    .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
    .grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;}
    .card{border:1px solid var(--line);background:var(--surface);border-radius:18px;padding:24px;box-shadow:var(--shadow-sm);}
    .card + .card{margin-top:20px;}
    .card h3{font-size:15.5px;margin:0 0 3px;}
    .card .card-note{font-size:12.5px;color:var(--text-muted);margin:0 0 18px;}
    .card-head{margin:-24px -24px 18px;padding:16px 20px;border-bottom:1px solid var(--line-soft);display:flex;align-items:center;justify-content:space-between;background:linear-gradient(180deg,#F7FBF9,#fff);border-radius:18px 18px 0 0;}
    .card-head h3{margin:0;font-size:14.5px;font-weight:600;display:flex;align-items:center;gap:9px;}
    .card-head h3 i{color:var(--brand);font-size:13px;background:var(--brand-soft);width:28px;height:28px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;}
    .card-pad{padding:16px 18px;}
    .stack{display:flex;flex-direction:column;gap:18px;}

    .field-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px 22px;}
    .field-grid.cols-3{grid-template-columns:repeat(3,1fr);}
    .field{display:flex;flex-direction:column;gap:6px;}
    .field.full{grid-column:1 / -1;}
    .field label{font-size:12px;font-weight:600;color:#4A5B52;}
    input,select,textarea{
      font-family:var(--font-body);font-size:13.5px;padding:10px 13px;border:1px solid var(--line);border-radius:11px;background:#FBFDFC;
      color:var(--text);width:100%;outline:none;transition:border-color .15s,background .15s,box-shadow .15s;
    }
    input:focus,select:focus,textarea:focus{border-color:var(--brand-bright);background:#fff;box-shadow:0 0 0 3.5px rgba(31,169,122,.14);}
    textarea{resize:vertical;min-height:72px;}
    .field-hint{font-size:11.5px;color:var(--text-muted);}
    .form-actions{display:flex;gap:10px;margin-top:22px;padding-top:18px;border-top:1px solid var(--line-soft);align-items:center;}
    .btn-primary{
      background:linear-gradient(135deg,#1FA97A,#0E6B4B);color:#fff;border:none;padding:10.5px 19px;border-radius:12px;
      font-size:13.5px;font-weight:500;cursor:pointer;font-family:var(--font-body);
      box-shadow:0 10px 20px -10px var(--brand-glow), inset 0 1.5px 0 rgba(255,255,255,.22);
      transition:filter .15s,transform .1s;display:inline-flex;align-items:center;gap:8px;white-space:nowrap;flex-shrink:0;
    }
    .btn-primary::before{content:"\f00c";font-family:"Font Awesome 6 Free";font-weight:900;font-size:11px;}
    .btn-primary:hover{filter:brightness(1.07);}
    .btn-primary:active{transform:translateY(1px);}
    .btn-secondary{background:var(--surface);border:1px solid var(--line);color:var(--text);padding:10.5px 19px;border-radius:12px;font-size:13.5px;font-weight:500;cursor:pointer;transition:all .15s;white-space:nowrap;flex-shrink:0;}
    .btn-secondary:hover{border-color:var(--brand-bright);color:var(--brand);}
    .btn-ghost{background:transparent;border:none;color:var(--brand);padding:6px 0;font-size:13px;cursor:pointer;font-weight:600;}
    .btn-ghost:hover{color:var(--brand-strong);text-decoration:underline;}

    .pill{font-size:11px;padding:4px 12px;border-radius:99px;display:inline-flex;align-items:center;gap:5px;font-weight:600;white-space:nowrap;border:1px solid transparent;font-family:var(--font-body);}
    .pill::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0;}
    .pill-ok{background:var(--ok-soft);color:#116A38;border-color:rgba(21,128,61,.18);}
    .pill-warn{background:var(--warn-soft);color:#8A5A10;border-color:rgba(180,83,9,.2);}
    .pill-bad{background:var(--bad-soft);color:#942B2B;border-color:rgba(192,52,52,.2);}
    .pill-muted{background:#EDF3EF;color:#52645B;border-color:rgba(18,58,40,.1);}

    .table-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px;flex-wrap:wrap;}
    .search-box{display:flex;align-items:center;gap:8px;border:1px solid var(--line);border-radius:12px;padding:8px 11px;background:var(--surface);min-width:230px;}
    .search-box i{color:var(--text-muted);font-size:12px;}
    .search-box input{border:none;outline:none;background:transparent;font-size:13px;width:100%;color:var(--text);padding:0;box-shadow:none;}
    .filters{display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
    .filter-select{border:1px solid var(--line);border-radius:11px;padding:7px 10px;font-size:12.5px;background:var(--surface);color:var(--text);font-family:var(--font-body);}
    .empty-state{text-align:center;padding:44px 20px;color:var(--text-muted);}
    .empty-state .glyph{font-size:26px;margin-bottom:10px;color:#A7C9B8;}
    .badge-count{background:var(--brand);color:#fff;font-size:11px;font-weight:600;padding:2px 8px;border-radius:99px;}
    details.disclosure > summary{cursor:pointer;list-style:none;}
    details.disclosure > summary::-webkit-details-marker{display:none;}

    table{width:100%;border-collapse:collapse;font-size:13.5px;}
    th{
      text-align:left;font-weight:600;color:#5B6E63;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;
      padding:0 12px 10px;border-bottom:1px solid var(--line);white-space:nowrap;font-family:var(--font-body);
    }
    td{padding:12px;border-bottom:1px solid var(--line-soft);vertical-align:middle;}
    tr:last-child td{border-bottom:none;}
    tbody tr:hover{background:#F4FAF7;}
    .row-actions{display:flex;gap:8px;}
    .row-actions button{font-size:12.5px;padding:6px 13px;border-radius:9px;cursor:pointer;font-weight:500;transition:filter .15s;font-family:var(--font-body);}
    .approve,.reject{
      display:inline-flex;align-items:center;justify-content:center;gap:6px;font-size:12.5px;padding:6px 14px;border-radius:9px;
      cursor:pointer;font-weight:500;transition:filter .15s, border-color .15s, color .15s;font-family:var(--font-body);white-space:nowrap;flex-shrink:0;
    }
    .approve{background:linear-gradient(135deg,#1FA97A,#0E6B4B);color:#fff;border:none;box-shadow:0 5px 12px -6px var(--brand-glow);}
    .approve::before{content:"\f00c";font-family:"Font Awesome 6 Free";font-weight:900;font-size:10px;}
    .approve:hover{filter:brightness(1.08);}
    .reject{background:var(--surface);border:1px solid var(--line);color:var(--text-muted);}
    .reject::before{content:"\f00d";font-family:"Font Awesome 6 Free";font-weight:900;font-size:10px;}
    .reject:hover{border-color:var(--bad);color:var(--bad);}
    form:has(> .approve), form:has(> .reject){display:inline-flex;}

    .cell-emp{display:flex;align-items:center;gap:10px;}
    .cell-emp .av{
      width:30px;height:30px;border-radius:10px;background:linear-gradient(135deg,var(--brand-soft),#D2EEDF);color:var(--brand);font-weight:600;font-size:11px;font-family:var(--font-head);
      display:flex;align-items:center;justify-content:center;flex-shrink:0;
    }
    .cell-emp b{font-size:13.5px;font-weight:500;display:block;color:var(--brand-ink);}
    .cell-emp span{font-size:11.5px;color:var(--text-muted);}

    /* ================= Dashboard hero layout ================= */
    .dash-hero{display:flex;align-items:center;gap:24px;}
    .dash-hero-text{flex:1;min-width:0;}
    .dash-hero-check{flex-shrink:0;}
    .module-grid{display:grid;grid-template-columns:1fr 1fr;gap:2px 34px;}
    @media (max-width:1100px){.module-grid{grid-template-columns:1fr;}}

    .module-list{border-top:1px solid var(--line-soft);}
    .module-row{display:flex;align-items:center;gap:16px;padding:14px 8px;border-bottom:1px solid var(--line-soft);cursor:pointer;background:none;width:100%;border-left:none;border-right:none;text-align:left;border-radius:13px;transition:background .15s;}
    .module-row:hover{background:var(--brand-softer);}
    .module-row:last-child{border-bottom:none;}
    .module-row .nav-code{
      background:linear-gradient(135deg,var(--brand-soft),#D2EEDF);color:var(--brand);border:none;width:38px;height:38px;border-radius:12px;font-size:12px;font-family:var(--font-head);font-weight:600;
      display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s;
    }
    .module-row:hover .nav-code{background:linear-gradient(135deg,#2BC08D,#0C6B4B);color:#fff;box-shadow:0 6px 14px -6px var(--brand-glow);}
    .module-row-title{font-family:var(--font-head);font-size:14.5px;font-weight:500;color:var(--brand-ink);}
    .module-row-summary{font-size:12.5px;color:var(--text-muted);margin-top:2px;}
    .module-row-meta{margin-left:auto;font-size:12.5px;color:var(--text-muted);white-space:nowrap;text-align:right;}
    .module-row-meta b{color:var(--brand);font-weight:600;}

    .tabs{display:flex;gap:4px;border-bottom:1px solid var(--line);margin-bottom:24px;flex-wrap:wrap;}
    .tab{
      padding:10px 4px;margin-right:22px;font-size:13.5px;color:var(--text-muted);border-bottom:3px solid transparent;cursor:pointer;
      background:none;border-top:none;border-left:none;border-right:none;font-weight:500;transition:color .14s;font-family:var(--font-body);
    }
    .tab:hover{color:var(--brand-ink);}
    .tab.active{color:var(--brand);border-bottom-color:var(--brand-bright);}
    .tabpanel{display:none;}
    .tabpanel.active{display:block;animation:fadeUp .24s ease;}
    @keyframes fadeUp{from{opacity:0;transform:translateY(5px);}to{opacity:1;transform:none;}}

    .pipeline{display:grid;grid-template-columns:repeat(5,1fr);gap:13px;}
    .pipe-col{background:linear-gradient(180deg,#F8FBF9,#F2F8F4);border:1px solid var(--line);border-radius:15px;padding:12px;min-height:120px;}
    .pipe-col h4{font-size:10.5px;font-weight:600;color:#5B6E63;margin:0 0 10px;display:flex;justify-content:space-between;text-transform:uppercase;letter-spacing:.07em;font-family:var(--font-body);}
    .pipe-card{background:#fff;border:1px solid var(--line-soft);border-radius:12px;padding:11px;font-size:12.5px;margin-bottom:9px;box-shadow:var(--shadow-sm);transition:all .15s;}
    .pipe-card:hover{border-color:var(--brand-bright);transform:translateY(-1.5px);box-shadow:var(--shadow-md);}
    .pipe-card .name{font-family:var(--font-head);font-size:13.5px;font-weight:500;color:var(--brand-ink);}
    .pipe-card .meta{color:var(--text-muted);font-size:11.5px;margin-top:2px;}
    .pipe-card form{margin-top:6px;}
    .pipe-card select{font-size:11px;padding:4px 7px;}

    .checklist{list-style:none;margin:0;padding:0;border-top:1px solid var(--line-soft);}
    .checklist li{display:flex;align-items:center;gap:12px;padding:11px 4px;border-bottom:1px solid var(--line-soft);font-size:13.5px;border-radius:9px;}
    .checklist form{margin:0;}
    .checklist .num{
      font-size:11px;font-weight:600;width:24px;height:24px;border-radius:50%;border:1.5px solid var(--line);
      display:flex;align-items:center;justify-content:center;color:var(--text-muted);flex-shrink:0;background:#fff;cursor:pointer;transition:all .16s;font-family:var(--font-head);
    }
    .checklist .num:hover{border-color:var(--brand-bright);color:var(--brand);}
    .checklist li.done .num{background:linear-gradient(135deg,#2BC08D,#0C6B4B);border-color:transparent;color:#fff;}
    .checklist li.done{color:var(--text-muted);text-decoration:line-through;}

    .bar-row{display:flex;align-items:center;gap:12px;margin-bottom:12px;font-size:12.5px;}
    .bar-label{width:150px;color:var(--text-muted);flex-shrink:0;}
    .bar-track{flex:1;height:9px;background:#E4EFE8;border-radius:99px;overflow:hidden;}
    .bar-fill{height:100%;background:linear-gradient(90deg,#1FA97A,#0E6B4B);border-radius:99px;transition:width .35s;}
    .bar-value{width:60px;text-align:right;flex-shrink:0;font-variant-numeric:tabular-nums;font-weight:600;color:var(--brand-ink);font-family:var(--font-head);}

    .goal-row{border:1px solid var(--line);border-radius:14px;padding:16px 18px;margin-bottom:12px;background:var(--surface);box-shadow:var(--shadow-sm);}
    .goal-row .goal-title{font-family:var(--font-head);font-size:14.5px;font-weight:500;color:var(--brand-ink);margin-bottom:4px;}
    .goal-row .goal-desc{font-size:12.5px;color:var(--text-muted);margin-bottom:12px;}

    .status-block{
      background:var(--brand-softer);border:1px solid color-mix(in srgb, var(--brand) 22%, white);
      border-left:4px solid var(--brand);padding:14px 18px;margin-bottom:28px;border-radius:0 14px 14px 0;font-size:13.5px;color:var(--brand-ink);
    }
    .status-block strong{font-weight:600;}
    .access-note{font-size:12.5px;color:var(--text-muted);margin-top:28px;padding-top:14px;border-top:1px dashed var(--line);}

    .app-dialog{border:none;border-radius:18px;padding:24px;max-width:480px;width:90vw;box-shadow:var(--shadow-lg);}
    .app-dialog::backdrop{background:rgba(8,48,31,.5);backdrop-filter:blur(3px);}
    .dialog-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px;}

    /* ================= Creative split modal ================= */
    .modal-dialog{
      border:none;border-radius:24px;padding:0;max-width:760px;width:94vw;box-shadow:0 40px 90px -20px rgba(4,34,22,.55);
      background:var(--surface);overflow:hidden;
    }
    .modal-dialog::backdrop{background:rgba(6,36,23,.55);backdrop-filter:blur(5px);}
    .modal-dialog[open]{animation:modalIn .32s cubic-bezier(.2,.9,.3,1.2);}
    @keyframes modalIn{from{opacity:0;transform:translateY(26px) scale(.96);}to{opacity:1;transform:none;}}
    .modal-grid{display:grid;grid-template-columns:250px 1fr;min-height:480px;}
    .modal-side{
      position:relative;overflow:hidden;color:#fff;padding:26px 22px;display:flex;flex-direction:column;
      background:
        radial-gradient(240px 160px at 90% -10%, rgba(43,192,141,.45), transparent 60%),
        radial-gradient(200px 200px at -20% 110%, rgba(43,192,141,.25), transparent 55%),
        linear-gradient(160deg,#11573F 0%, var(--brand-ink) 55%, #07281B 100%);
    }
    .modal-side::after{content:"";position:absolute;top:-56px;right:-56px;width:150px;height:150px;border-radius:50%;border:1.5px solid rgba(255,255,255,.1);}
    .modal-side::before{content:"";position:absolute;bottom:-40px;left:-50px;width:130px;height:130px;border-radius:50%;background:radial-gradient(circle, rgba(43,192,141,.28), transparent 70%);}
    .modal-side-ico{
      width:50px;height:50px;border-radius:15px;display:flex;align-items:center;justify-content:center;font-size:20px;
      background:linear-gradient(135deg,rgba(43,192,141,.9),rgba(12,107,75,.9));
      box-shadow:0 14px 28px -10px rgba(0,0,0,.5), inset 0 1.5px 0 rgba(255,255,255,.3);margin-bottom:16px;
    }
    .modal-side h3{margin:0 0 6px;font-family:var(--font-head);font-size:18.5px;font-weight:600;color:#fff;}
    .modal-side p{margin:0;font-size:12px;color:#A8CDBB;line-height:1.55;position:relative;z-index:1;}
    .modal-side-steps{margin-top:auto;position:relative;z-index:1;display:flex;flex-direction:column;gap:9px;padding-top:22px;}
    .modal-step{display:flex;align-items:center;gap:9px;font-size:11.5px;color:#CDE7DB;}
    .modal-step .num{
      width:20px;height:20px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;
      font-family:var(--font-head);font-size:10px;font-weight:600;background:rgba(255,255,255,.12);color:#5FE0B2;
    }
    .modal-body{padding:26px 28px 22px;overflow-y:auto;max-height:78vh;}
    .modal-close{
      position:absolute;top:14px;right:14px;width:32px;height:32px;border-radius:10px;border:1px solid rgba(255,255,255,.2);
      background:rgba(255,255,255,.12);color:#fff;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;z-index:2;
    }
    .modal-close:hover{background:rgba(255,255,255,.24);}
    .modal-note{
      background:var(--brand-softer);border:1px solid color-mix(in srgb, var(--brand) 24%, white);
      border-radius:12px;padding:10px 13px;font-size:12px;color:var(--brand-ink);margin-bottom:18px;line-height:1.5;
    }
    .modal-note i{color:var(--brand);margin-right:7px;}
    .modal-note code{white-space:nowrap;}
    .modal-foot{display:flex;align-items:center;gap:10px;justify-content:flex-end;margin-top:22px;padding-top:16px;border-top:1px solid var(--line-soft);}
    .modal-foot .hint-secure{margin-right:auto;font-size:11.5px;color:var(--text-muted);display:inline-flex;align-items:center;gap:6px;min-width:0;}
    .modal-foot .hint-secure span, .modal-foot .hint-secure{white-space:normal;}
    .modal-foot .hint-secure i{color:var(--brand);}
    @media (max-width:700px){
      .modal-grid{grid-template-columns:1fr;}
      .modal-side{padding:20px;}
      .modal-side-steps{display:none;}
      .modal-body{padding:20px 16px;max-height:70vh;}
    }

    /* View-employee modal details */
    .ve-chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;position:relative;z-index:1;}
    .ve-chip{
      display:inline-flex;align-items:center;gap:6px;font-size:10.5px;font-weight:600;letter-spacing:.04em;
      background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);color:#fff;
      border-radius:99px;padding:5px 12px;
    }
    .ve-chip i{font-size:6px;}
    .ve-chip.ok i{color:#5FE0B2;}
    .ve-chip.warn i{color:#FFC96B;}
    .ve-chip.off i{color:#FF8F8F;}
    .modal-side-steps .modal-step .num i{font-size:9px;}
    .ve-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px 16px;margin-bottom:6px;}
    .ve-item{
      background:var(--brand-softer);border:1px solid color-mix(in srgb, var(--brand) 14%, white);
      border-radius:12px;padding:10px 13px;min-width:0;
    }
    .ve-item span{display:flex;align-items:center;gap:6px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;}
    .ve-item span i{color:var(--brand);font-size:10px;}
    .ve-item b{display:block;font-size:13px;font-weight:600;color:var(--brand-ink);word-break:break-word;}
    @media (max-width:560px){
      .ve-grid{grid-template-columns:1fr;}
    }

    /* ===== Employee directory ===== */
    .employee-page{max-width:100%;padding-top:20px;}
    .employee-breadcrumb{display:flex;gap:6px;align-items:center;font-size:13px;margin-bottom:22px;color:var(--text-muted);}
    .employee-breadcrumb b{font-weight:500;color:#93A69B;}
    .employee-breadcrumb strong{color:var(--brand-ink);font-weight:600;}
    .employee-page-heading{margin-bottom:24px;}
    .employee-page-heading h2{margin:0;font-size:21px;}
    .employee-page-heading p{margin:5px 0 0;color:var(--text-muted);font-size:13px;}
    .employee-directory-card{background:#fff;border:1px solid var(--line);border-radius:18px;overflow:hidden;box-shadow:var(--shadow-sm);}
    .employee-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:18px 20px 16px;background:#fff;}
    .employee-search{height:41px;width:240px;display:flex;align-items:center;gap:9px;border:1px solid var(--line);border-radius:12px;padding:0 12px;background:#FBFDFC;}
    .employee-search:focus-within{border-color:var(--brand-bright);background:#fff;box-shadow:0 0 0 3.5px rgba(31,169,122,.14);}
    .employee-search span{font-size:14px;color:var(--text-muted);}
    .employee-search input{border:0;background:transparent;padding:0;font-size:13px;min-width:0;outline:0;box-shadow:none;}
    .employee-filters{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .employee-filters select{height:41px;width:150px;padding:0 10px;border-color:var(--line);background:#fff;font-size:13px;border-radius:12px;}
    .employee-filter-btn,.employee-export{height:41px;border:0;background:transparent;color:var(--brand);font-size:12.5px;font-weight:600;cursor:pointer;padding:0 8px;border-radius:10px;font-family:var(--font-body);}
    .employee-filter-btn:hover,.employee-export:hover{background:var(--brand-softer);}
    .employee-export{font-size:13px;}
    .employee-add{
      height:41px;display:inline-flex;align-items:center;gap:8px;padding:0 17px;border-radius:12px;border:none;
      background:linear-gradient(135deg,#1FA97A,#0E6B4B);color:#fff;font-size:13px;font-weight:500;cursor:pointer;font-family:var(--font-body);
      box-shadow:0 10px 20px -10px var(--brand-glow);
    }
    .employee-add::before{content:"\f067";font-family:"Font Awesome 6 Free";font-weight:900;font-size:11px;}
    .employee-add:hover{filter:brightness(1.07);}
    .employee-table-wrap{overflow-x:auto;border-top:1px solid var(--line-soft);}
    .employee-table{min-width:1040px;border-collapse:collapse;font-size:13.5px;}
    .employee-table th{padding:12px;border-bottom:1px solid var(--line-soft);background:#F7FBF8;color:#5B6E63;font-size:10.5px;letter-spacing:.07em;}
    .employee-table td{padding:12px;border-bottom:1px solid var(--line-soft);color:var(--text);white-space:nowrap;height:64px;}
    .employee-table tbody tr:last-child td{border-bottom:0;}
    .employee-table tbody tr:hover{background:#F4FAF7;}
    .employee-table th:first-child,.employee-table td:first-child{padding-left:14px;}
    .employee-table th.actions-head{width:220px;}
    .employee-person{display:flex;align-items:center;gap:10px;min-width:205px;}
    .employee-avatar{
      width:34px;height:34px;border-radius:11px;background:linear-gradient(135deg,var(--brand-soft),#D2EEDF);color:var(--brand);
      display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;font-family:var(--font-head);
    }
    .employee-name{font-family:var(--font-head);font-size:13.5px;font-weight:500;line-height:1.3;color:var(--brand-ink);}
    .employee-email{font-size:11.5px;color:var(--text-muted);line-height:1.35;margin-top:2px;}
    .employee-id{font-family:var(--font-body);font-size:11.5px;color:#64756B;}
    .employee-status{display:inline-flex;align-items:center;gap:6px;padding:5.5px 12px;border-radius:999px;font-size:11px;font-weight:600;}
    .employee-status i{width:6px;height:6px;border-radius:50%;display:block;background:currentColor;}
    .employee-status.active{background:var(--ok-soft);color:#116A38;}
    .employee-status.notice{background:var(--warn-soft);color:#8A5A10;}
    .employee-status.inactive{background:var(--bad-soft);color:#942B2B;}
    .employee-actions{display:flex;align-items:center;justify-content:flex-end;gap:15px;}
    .employee-actions a{font-size:12.5px;color:var(--brand);font-weight:600;}
    .employee-actions a:hover{color:var(--brand-strong);text-decoration:underline;}
    .employee-actions .ea-link{background:none;border:none;font-size:12.5px;color:var(--brand);font-weight:600;cursor:pointer;font-family:var(--font-body);padding:0;}
    .employee-actions .ea-link:hover{color:var(--brand-strong);text-decoration:underline;}
    .employee-actions form{margin:0;}
    .employee-actions button{height:31px;border-radius:9px;padding:0 13px;font:500 11.5px/1 var(--font-body);cursor:pointer;}
    .employee-actions .deactivate{border:1px solid #C23A3A;background:#C23A3A;color:#fff;}
    .employee-actions .deactivate:hover{filter:brightness(1.08);}
    .employee-actions .activate{border:1px solid var(--brand);background:var(--brand);color:#fff;}
    .employee-actions .activate:hover{filter:brightness(1.08);}
    .employee-profile-section{margin-top:22px;scroll-margin-top:90px;}
    .employee-add-section{margin-top:22px;}
    .employee-add-section .card{border-radius:18px;}
    .employee-directory-card.empty-state{padding:55px 20px;}
    @media (max-width:1100px){
      .employee-toolbar{align-items:stretch;flex-direction:column;}
      .employee-search{width:100%;}
      .employee-filters{justify-content:flex-start;}
    }

    /* ================= Page header widget ================= */
    .hero-band{padding:18px 34px 0;}
    .hero-band .page-hero{border-radius:18px;}
    .page-hero{
      position:relative;overflow:hidden;border-radius:18px;padding:15px 22px;margin-bottom:24px;
      display:flex;align-items:center;gap:16px;flex-wrap:wrap;
      background:
        radial-gradient(380px 180px at 96% -30%, rgba(43,192,141,.4), transparent 62%),
        linear-gradient(130deg,#11573F 0%, var(--brand-ink) 60%, #0A3423 100%);
      color:#fff;box-shadow:var(--shadow-lg);
    }
    .page-hero::after{content:"";position:absolute;top:-60px;right:130px;width:170px;height:170px;border-radius:50%;border:1.5px solid rgba(255,255,255,.1);}
    .page-hero::before{content:"";position:absolute;bottom:-70px;right:-40px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle, rgba(43,192,141,.25), transparent 70%);}
    .page-hero-ico{
      width:44px;height:44px;border-radius:13px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;
      background:linear-gradient(135deg,rgba(43,192,141,.9),rgba(12,107,75,.9));color:#fff;
      box-shadow:0 12px 26px -10px var(--brand-glow), inset 0 1.5px 0 rgba(255,255,255,.35);
      position:relative;z-index:1;
    }
    .page-hero-text{position:relative;z-index:1;flex:1;min-width:200px;}
    .page-hero-text p{margin:0;font-size:12.5px;color:#A8CDBB;max-width:62ch;}
    .page-hero-stats{display:flex;gap:9px;flex-wrap:wrap;position:relative;z-index:1;}
    .ph-stat{
      background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.14);backdrop-filter:blur(6px);
      border-radius:12px;padding:7px 13px;min-width:92px;
    }
    .ph-stat b{display:block;font-family:var(--font-head);font-size:15.5px;font-weight:600;color:#fff;line-height:1.2;}
    .ph-stat span{font-size:9px;font-weight:600;letter-spacing:.09em;text-transform:uppercase;color:#8FB9A5;}
    .ph-stat i{font-size:10px;color:#5FE0B2;margin-right:5px;}

    /* ================= Stat tiles (module widgets) ================= */
    .stat-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;}
    .stat-tile{
      position:relative;overflow:hidden;background:var(--surface);border:1px solid var(--line);border-radius:16px;
      padding:16px 18px;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:14px;transition:all .18s;
    }
    .stat-tile:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);border-color:rgba(31,169,122,.4);}
    .stat-tile .st-ico{
      width:46px;height:46px;border-radius:14px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:16px;
      background:linear-gradient(135deg,var(--brand-soft),#D2EEDF);color:var(--brand);box-shadow:inset 0 0 0 1px rgba(20,108,78,.1);
    }
    .stat-tile.alt .st-ico{background:linear-gradient(135deg,#FCF0D8,#F7E3B3);color:#A16207;}
    .stat-tile.warn .st-ico{background:linear-gradient(135deg,#FDECEA,#F9D2CC);color:#C03434;}
    .stat-tile.info .st-ico{background:linear-gradient(135deg,#E3F0FA,#C9E2F5);color:#1D6FA5;}
    .stat-tile b{display:block;font-family:var(--font-head);font-size:22px;font-weight:600;color:var(--brand-ink);line-height:1.1;font-variant-numeric:tabular-nums;}
    .stat-tile span{font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;}

    /* ================= Balance ring widget ================= */
    .ring-card{background:var(--surface);border:1px solid var(--line);border-radius:16px;padding:16px 14px;box-shadow:var(--shadow-sm);text-align:center;transition:all .18s;}
    .ring-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);border-color:rgba(31,169,122,.4);}
    .ring{
      width:74px;height:74px;border-radius:50%;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;position:relative;
      background:conic-gradient(var(--brand-bright) calc(var(--pct,50)*1%), #E4EFE8 0);
    }
    .ring::before{content:"";position:absolute;inset:7px;border-radius:50%;background:#fff;}
    .ring b{position:relative;font-family:var(--font-head);font-size:17px;font-weight:600;color:var(--brand-ink);}
    .ring-card h4{font-size:12.5px;font-weight:600;color:var(--brand-ink);margin:0 0 2px;font-family:var(--font-head);}
    .ring-card small{font-size:10.5px;color:var(--text-muted);}

    /* ================= Widget cards / toolbars ================= */
    .widget-head{display:flex;align-items:center;gap:11px;margin-bottom:16px;}
    .widget-head .wh-ico{
      width:38px;height:38px;border-radius:11px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:14px;
      background:linear-gradient(135deg,var(--brand-soft),#D2EEDF);color:var(--brand);
    }
    .widget-head h3{margin:0;font-size:15px;font-weight:600;font-family:var(--font-head);color:var(--brand-ink);}
    .widget-head p{margin:1px 0 0;font-size:11.5px;color:var(--text-muted);}
    .widget-head .wh-right{margin-left:auto;}
    .table-card{background:var(--surface);border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow-sm);overflow:hidden;}
    .table-card .tc-body{padding:4px 20px 14px;overflow-x:auto;}
    .table-card .tc-head{padding:16px 20px;border-bottom:1px solid var(--line-soft);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:linear-gradient(180deg,#F7FBF9,#fff);}
    .table-card .tc-head h3{margin:0;font-size:14.5px;font-family:var(--font-head);font-weight:600;display:flex;align-items:center;gap:9px;color:var(--brand-ink);}
    .table-card .tc-head h3 .wh-ico{width:30px;height:30px;border-radius:9px;font-size:12px;background:linear-gradient(135deg,var(--brand-soft),#D2EEDF);color:var(--brand);display:inline-flex;align-items:center;justify-content:center;}

    /* ================= Pipeline v2 ================= */
    .pipe-col{border:none;background:linear-gradient(180deg,#F8FBF9,#F2F8F4);}
    .pipe-col .pipe-head{display:flex;align-items:center;gap:8px;margin:0 0 12px;font-family:var(--font-body);}
    .pipe-col .pipe-head .ph-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;}
    .pipe-col .pipe-head h4{font-size:10.5px;font-weight:700;color:#5B6E63;margin:0;flex:1;text-transform:uppercase;letter-spacing:.07em;}
    .pipe-col .pipe-head span{font-family:var(--font-head);font-size:12px;font-weight:600;color:var(--brand);background:#fff;border:1px solid var(--line);border-radius:99px;padding:1.5px 8px;}
    .pipe-card .pc-av{width:28px;height:28px;border-radius:9px;background:linear-gradient(135deg,var(--brand-soft),#D2EEDF);color:var(--brand);font-family:var(--font-head);font-weight:600;font-size:10.5px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:6px;}

    /* ================= Empty state v2 ================= */
    .empty-widget{text-align:center;padding:38px 20px;color:var(--text-muted);margin:6px;border-radius:14px;background:radial-gradient(320px 130px at 50% -20%, var(--brand-softer), transparent 70%);}
    .empty-widget .ew-ico{
      width:62px;height:62px;border-radius:20px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:21px;
      background:linear-gradient(135deg,var(--brand-soft),#D2EEDF);color:var(--brand);
      box-shadow:0 10px 24px -12px var(--brand-glow), inset 0 1.5px 0 rgba(255,255,255,.7);
      animation:ewFloat 3.2s ease-in-out infinite;
    }
    @keyframes ewFloat{0%,100%{transform:translateY(0);}50%{transform:translateY(-5px);}}
    .empty-widget b{display:block;font-family:var(--font-head);font-weight:500;color:var(--brand-ink);font-size:14.5px;margin-bottom:3px;}
    .empty-widget span{font-size:12.5px;}

    @media (max-width:1000px){
      .stat-tiles{grid-template-columns:repeat(2,1fr);}
      .page-hero{padding:14px 16px;}
      .hero-band{padding:14px 14px 0;}
    }
    @media (max-width:560px){
      .stat-tiles{grid-template-columns:1fr;}
      .ph-stat{min-width:84px;padding:7px 11px;}
      /* Data tables scroll instead of crushing on phones */
      table{min-width:620px;}
      table th, table td{white-space:nowrap;}
      .table-card .tc-body, .card table{overflow-x:auto;-webkit-overflow-scrolling:touch;}
      .cell-emp span{white-space:nowrap;}
      .empty-widget{padding:30px 14px;}
    }

    @media (max-width:1000px){
      .sidebar{transform:translateX(-100%);box-shadow:none;}
      .sidebar-open .sidebar{transform:translateX(0);box-shadow:var(--shadow-lg);}
      .main{margin-left:0;display:flex;flex-direction:column;}
      .hamburger{display:inline-flex;}
      .grid-2,.grid-3,.grid-4,.pipeline,.kpi-row{grid-template-columns:1fr;}
      .field-grid,.field-grid.cols-3{grid-template-columns:1fr;}
      .content{padding:16px 14px 60px;}
      .topbar{padding:0 18px;height:62px;gap:10px;}
      .hamburger{width:36px;height:36px;flex-shrink:0;}
      .topbar h1{font-size:16.5px;}
      .topbar .eyebrow{font-size:8.5px;}
      .topbar-actions{gap:8px;}
      .bell-icon{width:36px;height:36px;}
      .dash-hero{padding:20px;flex-direction:column;align-items:stretch;}
      .dash-hero::after{display:none;}
      .dash-hero-check{margin-top:6px;}
      .page-hero{border-radius:16px;margin-top:0;}
      .brand-row .sidebar-close{right:10px;}
      .dash-hero-date{display:none;}
      .user-chip .who{display:none;}
      .user-chip-summary{padding:4px;}
      .sidebar-close{display:inline-flex;}
    }
    </style>
</head>
<body>
<div class="shell" id="appShell">
    @include('partials.sidebar')
    <div class="sidebar-overlay" onclick="document.getElementById('appShell').classList.remove('sidebar-open')"></div>
    <div class="main">
        @yield('content')
    </div>
</div>
<script>
  (function(){
    var el = document.getElementById('liveClock');
    if(el){
      var tick = function(){
        var d = new Date();
        var h = d.getHours() % 12 || 12, m = String(d.getMinutes()).padStart(2,'0'), s = String(d.getSeconds()).padStart(2,'0');
        var ap = d.getHours() >= 12 ? 'PM' : 'AM';
        el.textContent = h + ':' + m + ':' + s + ' ' + ap;
        var el2 = document.getElementById('liveClock2');
        if(el2) el2.textContent = h + ':' + m + ' ' + ap;
        requestAnimationFrame ? setTimeout(tick, 1000) : null;
      };
      tick();
    }
  })();
</script>
</body>
</html>
