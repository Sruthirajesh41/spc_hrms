<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') · SPC Universal HR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root{
      --ink:#1B2430; --ink-soft:#2A3444; --paper:#F3F3EF; --surface:#FFFFFF;
      --line:rgba(27,36,48,0.12); --line-soft:rgba(27,36,48,0.07);
      --text:#20262F; --text-muted:#6B7280;
      --text-on-ink:#D8DCE3; --text-on-ink-muted:#8A93A3;
      --role-accent: {{ $roleData['accent'] ?? '#2F6F62' }};
      --role-accent-soft: color-mix(in srgb, var(--role-accent) 16%, white);
      --ok:#2F6F62; --ok-soft:#E1EEEA; --warn:#A87C2E; --warn-soft:#F1E6D2; --bad:#AE4A34; --bad-soft:#F5E2DD;
      --radius:6px;
      --mono: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
    }
    *{box-sizing:border-box;}
    body{margin:0;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;color:var(--text);background:var(--paper);-webkit-font-smoothing:antialiased;font-size:14px;line-height:1.5;}
    h1,h2,h3,.serif{font-weight:650;letter-spacing:-.01em;}
    a{color:inherit;text-decoration:none;}
    :focus-visible{outline:2px solid var(--role-accent);outline-offset:2px;}
    button{font-family:inherit;}

    .shell{display:flex;min-height:100vh;}

    .sidebar{width:250px;flex-shrink:0;background:var(--ink);color:var(--text-on-ink);padding:0;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;}
    .brand{display:flex;align-items:center;gap:10px;padding:20px 20px 16px;border-bottom:1px solid rgba(255,255,255,0.08);}
    .brand-mark{width:28px;height:28px;border-radius:5px;background:var(--role-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#211505;flex-shrink:0;}
    .brand-name{font-size:16.5px;font-weight:650;color:#fff;letter-spacing:-.01em;}
    .brand-sub{font-size:11.5px;color:var(--text-on-ink-muted);margin:0;padding:0 20px 16px;border-bottom:1px solid rgba(255,255,255,0.08);}
    .role-chip{border:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.04);border-radius:6px;padding:12px 14px;margin:16px 16px 6px;}
    .role-chip .role-label{font-size:14px;font-weight:650;}
    .role-chip .role-tagline{font-size:11.5px;color:var(--text-on-ink-muted);margin-top:3px;line-height:1.4;}
    .role-chip .role-email{font-size:11px;color:var(--text-on-ink-muted);margin-top:6px;}
    .nav-heading{font-size:10.5px;letter-spacing:.09em;text-transform:uppercase;color:var(--text-on-ink-muted);margin:14px 20px 6px;}
    .nav-list{list-style:none;margin:0;padding:0 10px;display:flex;flex-direction:column;gap:1px;}
    .nav-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:5px;font-size:13.3px;color:#C4CAD4;border:none;width:100%;background:none;cursor:pointer;text-align:left;}
    .nav-item:hover{background:rgba(255,255,255,0.06);color:#fff;}
    .nav-item.active{background:color-mix(in srgb, var(--role-accent) 22%, transparent);color:#fff;font-weight:600;}
    .nav-code{font-size:10px;font-weight:700;width:22px;height:22px;border-radius:4px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.08);color:var(--text-on-ink-muted);flex-shrink:0;}
    .nav-item.active .nav-code{background:var(--role-accent);color:#211505;}
    .nav-dot{width:5px;height:5px;border-radius:50%;background:currentColor;opacity:.55;flex-shrink:0;}
    .nav-item.active .nav-dot{opacity:1;background:var(--role-accent);}
    .nav-badge{margin-left:auto;background:var(--bad);color:#fff;font-size:10.5px;font-weight:700;line-height:1;padding:3px 7px;border-radius:99px;flex-shrink:0;}
    .sidebar-foot{margin-top:auto;font-size:11px;color:var(--text-on-ink-muted);line-height:1.5;padding:16px 20px;border-top:1px solid rgba(255,255,255,0.08);}
    .logout-link{display:inline-block;margin-top:8px;font-size:11.5px;color:var(--text-on-ink-muted);text-decoration:underline;cursor:pointer;background:none;border:none;padding:0;font-family:inherit;}

    .main{flex:1;min-width:0;}
    .topbar{display:flex;align-items:center;justify-content:space-between;padding:0 32px;height:60px;border-bottom:1px solid var(--line);background:var(--surface);position:sticky;top:0;z-index:5;gap:16px;flex-wrap:wrap;}
    .topbar h1{margin:0;font-size:19px;}
    .topbar .eyebrow{font-size:11.5px;color:var(--text-muted);margin-bottom:1px;}
    .role-select{font-family:inherit;font-size:13px;color:var(--text);background:var(--surface);border:1px solid var(--line);border-radius:5px;padding:7px 10px;}

    .topbar-actions{display:flex;align-items:center;gap:16px;}
    .bell{position:relative;cursor:pointer;list-style:none;}
    .bell::-webkit-details-marker{display:none;}
    .bell-icon{width:34px;height:34px;border:1px solid var(--line);border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--surface);font-size:14px;color:var(--text-muted);}
    .bell-badge{position:absolute;top:-3px;right:-3px;background:var(--bad);color:#fff;font-size:10px;line-height:1;border-radius:10px;padding:3px 5px;min-width:14px;text-align:center;font-weight:700;}
    details.bell[open] summary.bell-icon{border-color:var(--role-accent);}
    .bell-panel{position:absolute;right:0;top:42px;width:320px;background:var(--surface);border:1px solid var(--line);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:20;padding:10px;}
    .bell-panel .bell-item{display:block;padding:10px 8px;border-bottom:1px solid var(--line-soft);font-size:12.5px;color:var(--text);}
    .bell-panel .bell-item:last-child{border-bottom:none;}
    .bell-panel .bell-item form{margin:0;}
    .bell-panel .bell-item button{background:none;border:none;padding:0;text-align:left;font-size:12.5px;color:inherit;cursor:pointer;width:100%;}
    .bell-panel .bell-empty{padding:16px 8px;color:var(--text-muted);font-size:12.5px;text-align:center;}
    .bell-panel .bell-foot{padding:8px 8px 2px;text-align:center;}
    .bell-panel .bell-foot a{font-size:12px;color:var(--role-accent);font-weight:600;}
    .user-chip{display:flex;align-items:center;gap:9px;font-size:12.5px;text-align:right;}
    .user-chip .avatar{width:30px;height:30px;border-radius:50%;background:var(--role-accent);color:#211505;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}
    .user-chip .who{text-align:left;line-height:1.3;}
    .user-chip .who b{display:block;font-size:13px;font-weight:650;}
    .user-chip .who span{color:var(--text-muted);}
    .user-chip-summary{width:auto;height:auto;border-radius:99px;padding:4px 12px 4px 4px;background:var(--surface);}
    details.user-menu[open] summary.user-chip-summary{border-color:var(--role-accent);}
    .user-panel{width:270px;}
    .user-panel-head{display:flex;align-items:center;gap:10px;padding:6px 8px 12px;border-bottom:1px solid var(--line-soft);margin-bottom:6px;}
    .user-panel-head .avatar{width:38px;height:38px;border-radius:50%;background:var(--role-accent);color:#211505;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
    .user-panel-head b{display:block;font-size:13.5px;}
    .user-panel-detail{display:flex;justify-content:space-between;gap:10px;padding:6px 8px;font-size:12px;}
    .user-panel-detail span:first-child{color:var(--text-muted);}
    .user-panel-detail span:last-child{font-weight:600;text-align:right;}

    .content{padding:28px 32px 70px;max-width:1180px;width:100%;margin:0 auto;}

    .flash{background:var(--ok-soft);border:1px solid rgba(47,111,98,0.3);color:var(--ok);padding:12px 16px;border-radius:6px;margin-bottom:22px;font-size:13.5px;}
    .flash-errors{background:var(--bad-soft);border:1px solid rgba(174,74,52,0.3);color:var(--bad);padding:12px 16px;border-radius:6px;margin-bottom:22px;font-size:13.5px;}
    .flash-errors ul{margin:4px 0 0;padding-left:18px;}

    /* ===== KPIs ===== */
    .kpi-strip{display:flex;border:1px solid var(--line);border-radius:var(--radius);background:var(--surface);margin-bottom:26px;overflow:hidden;flex-wrap:wrap;}
    .kpi{flex:1;min-width:150px;padding:16px 20px;border-left:1px solid var(--line);}
    .kpi:first-child{border-left:none;}
    .kpi .kpi-value{font-size:23px;font-weight:700;font-variant-numeric:tabular-nums;}
    .kpi .kpi-label{font-size:11.5px;color:var(--text-muted);margin-top:4px;}

    .kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:8px;}
    .kpi-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:14px 14px 12px;transition:border-color .15s;}
    .kpi-card:hover{border-color:var(--role-accent);}
    .kpi-card .kpi-label{font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;}
    .kpi-card .kpi-val{font-size:24px;font-weight:700;margin-top:6px;font-variant-numeric:tabular-nums;}
    .kpi-card .kpi-sub{font-size:11.5px;margin-top:4px;color:var(--text-muted);}

    /* ===== Quick actions ===== */
    .quick-actions{display:flex;flex-wrap:wrap;gap:8px;}
    .qa-btn{border:1px solid var(--line);background:var(--surface);border-radius:5px;padding:9px 13px;font-size:12.5px;font-weight:600;color:var(--text);display:inline-flex;align-items:center;gap:7px;}
    .qa-btn:hover{border-color:var(--role-accent);background:var(--role-accent-soft);}

    .section-title{font-size:17px;margin:0 0 4px;}
    .section-note{font-size:13px;color:var(--text-muted);margin:0 0 18px;max-width:64ch;}
    .section-head{display:flex;align-items:center;justify-content:space-between;margin:28px 0 12px;}
    .section-head h2{font-size:15px;font-weight:650;margin:0;}
    .section-head .hint{font-size:12px;color:var(--text-muted);}

    .grid-2{display:grid;grid-template-columns:1.3fr 1fr;gap:16px;align-items:start;}
    .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
    .grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;}
    .card{border:1px solid var(--line);background:var(--surface);border-radius:var(--radius);padding:24px;}
    .card + .card{margin-top:20px;}
    .card h3{font-size:15px;margin:0 0 3px;}
    .card .card-note{font-size:12.5px;color:var(--text-muted);margin:0 0 18px;}
    .card-head{margin:-24px -24px 16px;padding:14px 20px;border-bottom:1px solid var(--line-soft);display:flex;align-items:center;justify-content:space-between;}
    .card-head h3{margin:0;font-size:14px;font-weight:650;}
    .card-pad{padding:16px 18px;}
    .stack{display:flex;flex-direction:column;gap:14px;}

    .field-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px 22px;}
    .field-grid.cols-3{grid-template-columns:repeat(3,1fr);}
    .field{display:flex;flex-direction:column;gap:6px;}
    .field.full{grid-column:1 / -1;}
    .field label{font-size:12.5px;color:var(--text-muted);}
    input,select,textarea{font-family:inherit;font-size:13.5px;padding:9px 11px;border:1px solid var(--line);border-radius:5px;background:#FCFCFB;color:var(--text);width:100%;outline:none;}
    input:focus,select:focus,textarea:focus{border-color:var(--role-accent);background:#fff;}
    textarea{resize:vertical;min-height:72px;}
    .field-hint{font-size:11.5px;color:var(--text-muted);}
    .form-actions{display:flex;gap:10px;margin-top:22px;padding-top:18px;border-top:1px solid var(--line);align-items:center;}
    .btn-primary{background:var(--ink);color:#fff;border:1px solid var(--ink);padding:9px 16px;border-radius:5px;font-size:13px;font-weight:600;cursor:pointer;}
    .btn-secondary{background:transparent;border:1px solid var(--line);color:var(--text);padding:9px 16px;border-radius:5px;font-size:13px;font-weight:600;cursor:pointer;}
    .btn-ghost{background:transparent;border:none;color:var(--role-accent);padding:6px 0;font-size:13px;cursor:pointer;font-weight:600;}

    .pill{font-size:11px;padding:3px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:5px;font-weight:650;white-space:nowrap;}
    .pill::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor;}
    .pill-ok{background:var(--ok-soft);color:var(--ok);}
    .pill-warn{background:var(--warn-soft);color:#8A5F16;}
    .pill-bad{background:var(--bad-soft);color:var(--bad);}
    .pill-muted{background:#EDEDEA;color:#5B6472;}

    /* ===== List toolbars (search + filters) ===== */
    .table-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px;flex-wrap:wrap;}
    .search-box{display:flex;align-items:center;gap:7px;border:1px solid var(--line);border-radius:5px;padding:7px 10px;background:var(--surface);min-width:220px;}
    .search-box input{border:none;outline:none;background:transparent;font-size:13px;width:100%;color:var(--text);padding:0;}
    .filters{display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
    .filter-select{border:1px solid var(--line);border-radius:5px;padding:6.5px 8px;font-size:12.5px;background:var(--surface);color:var(--text);}
    .empty-state{text-align:center;padding:40px 20px;color:var(--text-muted);}
    .empty-state .glyph{font-size:24px;margin-bottom:8px;}
    .badge-count{background:var(--role-accent);color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;}
    details.disclosure > summary{cursor:pointer;list-style:none;}
    details.disclosure > summary::-webkit-details-marker{display:none;}

    table{width:100%;border-collapse:collapse;font-size:13px;}
    th{text-align:left;font-weight:650;color:var(--text-muted);font-size:10.8px;text-transform:uppercase;letter-spacing:.05em;padding:0 12px 9px;border-bottom:1px solid var(--line);white-space:nowrap;}
    td{padding:11px 12px;border-bottom:1px solid var(--line-soft);vertical-align:middle;}
    tr:last-child td{border-bottom:none;}
    tbody tr:hover{background:rgba(27,36,48,0.02);}
    .row-actions{display:flex;gap:8px;}
    .row-actions button{font-size:12px;padding:5px 11px;border-radius:5px;cursor:pointer;font-weight:600;}
    .approve{background:var(--ok);color:#fff;border:none;}
    .reject{background:transparent;border:1px solid var(--line);color:var(--text-muted);}

    .cell-emp{display:flex;align-items:center;gap:9px;}
    .cell-emp .av{width:26px;height:26px;border-radius:50%;background:var(--ok-soft);color:var(--ok);font-weight:700;font-size:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .cell-emp b{font-size:13px;font-weight:600;display:block;}
    .cell-emp span{font-size:11.5px;color:var(--text-muted);}

    .module-list{border-top:1px solid var(--line);}
    .module-row{display:flex;align-items:center;gap:16px;padding:14px 4px;border-bottom:1px solid var(--line-soft);cursor:pointer;background:none;width:100%;border-left:none;border-right:none;text-align:left;}
    .module-row:last-child{border-bottom:none;}
    .module-row .nav-code{background:var(--paper);color:var(--text-muted);border:1px solid var(--line);}
    .module-row-title{font-size:14px;font-weight:650;}
    .module-row-summary{font-size:12.5px;color:var(--text-muted);margin-top:2px;}
    .module-row-meta{margin-left:auto;font-size:12px;color:var(--text-muted);white-space:nowrap;text-align:right;}

    .tabs{display:flex;gap:4px;border-bottom:1px solid var(--line);margin-bottom:22px;flex-wrap:wrap;}
    .tab{padding:9px 4px;margin-right:22px;font-size:13.3px;color:var(--text-muted);border-bottom:2px solid transparent;cursor:pointer;background:none;border-top:none;border-left:none;border-right:none;font-weight:600;}
    .tab.active{color:var(--text);border-bottom-color:var(--role-accent);}
    .tabpanel{display:none;}
    .tabpanel.active{display:block;}

    .pipeline{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;}
    .pipe-col{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:12px;min-height:120px;}
    .pipe-col h4{font-size:11.5px;color:var(--text-muted);margin:0 0 10px;display:flex;justify-content:space-between;text-transform:uppercase;letter-spacing:.04em;}
    .pipe-card{background:var(--paper);border:1px solid var(--line);border-radius:5px;padding:10px;font-size:12.5px;margin-bottom:8px;}
    .pipe-card .name{font-size:13.3px;font-weight:650;}
    .pipe-card .meta{color:var(--text-muted);font-size:11.5px;margin-top:2px;}
    .pipe-card form{margin-top:6px;}
    .pipe-card select{font-size:11px;padding:4px 6px;}

    .checklist{list-style:none;margin:0;padding:0;border-top:1px solid var(--line);}
    .checklist li{display:flex;align-items:center;gap:12px;padding:11px 2px;border-bottom:1px solid var(--line-soft);font-size:13.3px;}
    .checklist form{margin:0;}
    .checklist .num{font-size:11.5px;font-weight:650;width:22px;height:22px;border-radius:50%;border:1px solid var(--line);display:flex;align-items:center;justify-content:center;color:var(--text-muted);flex-shrink:0;background:none;cursor:pointer;}
    .checklist li.done .num{background:var(--ok);border-color:var(--ok);color:#fff;}
    .checklist li.done{color:var(--text-muted);text-decoration:line-through;}

    .bar-row{display:flex;align-items:center;gap:12px;margin-bottom:11px;font-size:12.3px;}
    .bar-label{width:150px;color:var(--text-muted);flex-shrink:0;}
    .bar-track{flex:1;height:7px;background:var(--line-soft);border-radius:99px;overflow:hidden;}
    .bar-fill{height:100%;background:var(--role-accent);border-radius:99px;}
    .bar-value{width:60px;text-align:right;flex-shrink:0;font-variant-numeric:tabular-nums;}

    .goal-row{border:1px solid var(--line);border-radius:var(--radius);padding:16px 18px;margin-bottom:12px;}
    .goal-row .goal-title{font-size:14px;font-weight:650;margin-bottom:4px;}
    .goal-row .goal-desc{font-size:12.5px;color:var(--text-muted);margin-bottom:12px;}

    .status-block{border-left:3px solid var(--role-accent);background:var(--surface);border:1px solid var(--line);border-left-width:3px;padding:14px 18px;margin-bottom:28px;border-radius:0 6px 6px 0;font-size:13.3px;color:var(--text);}
    .status-block strong{font-weight:650;}
    .access-note{font-size:12px;color:var(--text-muted);margin-top:26px;}

    .app-dialog{border:none;border-radius:10px;padding:24px;max-width:480px;width:90vw;box-shadow:0 20px 50px rgba(0,0,0,0.25);}
    .app-dialog::backdrop{background:rgba(20,24,31,0.5);}
    .dialog-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px;}


    /* ===== Employee directory reference layout ===== */
    .employee-page{max-width:1320px;padding-top:20px;}
    .employee-breadcrumb{display:flex;gap:6px;align-items:center;font-size:13px;margin-bottom:22px;color:#718096;}
    .employee-breadcrumb b{font-weight:500;color:#A0A7B2;}
    .employee-breadcrumb strong{color:#20262F;font-weight:650;}
    .employee-page-heading{margin-bottom:24px;}
    .employee-page-heading h2{margin:0;font-size:20px;letter-spacing:-.02em;}
    .employee-page-heading p{margin:5px 0 0;color:#718096;font-size:13px;}
    .employee-directory-card{background:#fff;border:1px solid rgba(27,36,48,.12);border-radius:6px;overflow:hidden;}
    .employee-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:18px 20px 16px;background:#fff;}
    .employee-search{height:40px;width:238px;display:flex;align-items:center;gap:8px;border:1px solid #D7DBE0;border-radius:5px;padding:0 11px;background:#fff;}
    .employee-search span{font-size:21px;line-height:1;color:#718096;transform:rotate(-20deg);}
    .employee-search input{border:0;background:transparent;padding:0;font-size:13px;min-width:0;outline:0;}
    .employee-filters{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .employee-filters select{height:40px;width:148px;padding:0 10px;border-color:#D7DBE0;background:#fff;font-size:13px;}
    .employee-filter-btn,.employee-export{height:40px;border:0;background:transparent;color:#3F4B5C;font-size:12.5px;font-weight:600;cursor:pointer;padding:0 7px;}
    .employee-export{font-size:13px;}
    .employee-add{height:40px;display:inline-flex;align-items:center;padding:0 15px;border-radius:5px;background:#1B2430;color:#fff;font-size:13px;font-weight:650;}
    .employee-table-wrap{overflow-x:auto;border-top:1px solid #E1E4E8;}
    .employee-table{min-width:1040px;border-collapse:collapse;font-size:13px;}
    .employee-table th{padding:11px 12px;border-bottom:1px solid #E1E4E8;background:#fff;color:#687386;font-size:10.5px;letter-spacing:.045em;}
    .employee-table td{padding:12px;border-bottom:1px solid #E8EAED;color:#20262F;white-space:nowrap;height:64px;}
    .employee-table tbody tr:last-child td{border-bottom:0;}
    .employee-table tbody tr:hover{background:#FBFCFC;}
    .employee-table th:first-child,.employee-table td:first-child{padding-left:14px;}
    .employee-table th.actions-head{width:220px;}
    .employee-person{display:flex;align-items:center;gap:10px;min-width:205px;}
    .employee-avatar{width:30px;height:30px;border-radius:50%;background:#E2EFEB;color:#2F6F62;display:flex;align-items:center;justify-content:center;font-size:10.5px;font-weight:750;flex-shrink:0;}
    .employee-name{font-size:13px;font-weight:650;line-height:1.3;}
    .employee-email{font-size:11.5px;color:#738094;line-height:1.35;margin-top:2px;}
    .employee-id{font-family:var(--mono);font-size:11.5px;color:#647083;}
    .employee-status{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;font-size:11px;font-weight:650;}
    .employee-status i{width:6px;height:6px;border-radius:50%;display:block;background:currentColor;}
    .employee-status.active{background:#E2EFEB;color:#2F6F62;}
    .employee-status.notice{background:#F3E9D7;color:#8A631F;}
    .employee-status.inactive{background:#F5E2DD;color:#AE4A34;}
    .employee-actions{display:flex;align-items:center;justify-content:flex-end;gap:15px;}
    .employee-actions a{font-size:12.5px;color:#39475A;font-weight:550;}
    .employee-actions a:hover{color:var(--role-accent);}
    .employee-actions form{margin:0;}
    .employee-actions button{height:30px;border-radius:5px;padding:0 11px;font:600 11.5px/1 inherit;cursor:pointer;}
    .employee-actions .deactivate{border:1px solid #B84D38;background:#B84D38;color:#fff;}
    .employee-actions .activate{border:1px solid #2F7B6D;background:#2F7B6D;color:#fff;}
    .employee-profile-section{margin-top:22px;scroll-margin-top:80px;}
    .employee-add-section{margin-top:22px;}
    .employee-add-section .card{border-radius:6px;}
    .employee-directory-card.empty-state{padding:55px 20px;}
    @media (max-width:1100px){
      .employee-toolbar{align-items:stretch;flex-direction:column;}
      .employee-search{width:100%;}
      .employee-filters{justify-content:flex-start;}
    }

    @media (max-width:1000px){
      .grid-2,.grid-3,.grid-4,.pipeline,.kpi-row{grid-template-columns:1fr;}
      .field-grid,.field-grid.cols-3{grid-template-columns:1fr;}
      .sidebar{position:static;height:auto;}
    }

    </style>
</head>
<body>
<div class="shell">
    @include('partials.sidebar')
    <div class="main">
        @yield('content')
    </div>
</div>
</body>
</html>
