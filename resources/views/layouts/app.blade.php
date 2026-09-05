<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Buca Voyages VIP' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            color-scheme: light; 
            --ink: #0A1A2F; 
            --muted: #4B5563; 
            --line: #E2E8F0; 
            --brand: #D90429; 
            --brand-dark: #b3001e; 
            --danger: #DC2626; 
            --soft: #F2F5F7; 
            --panel: #ffffff; 
        }
        * { box-sizing: border-box; }
        body { margin:0; font-family: 'Inter', Arial, Helvetica, sans-serif; background:var(--soft); color:var(--ink); -webkit-font-smoothing: antialiased; }
        a { color:var(--brand); text-decoration:none; transition: color 0.15s ease; }
        a:hover { color:var(--brand-dark); }
        h1, h2, h3, h4, h5, h6, .success-panel h1 { font-family: 'Poppins', sans-serif; font-weight: 700; color: var(--ink); margin-top: 0; }
        
        .auth-page { min-height:100vh; display:grid; grid-template-columns: 1fr 440px; background:white; }
        .auth-visual { 
            padding:48px; 
            background: linear-gradient(135deg, rgba(217, 4, 41, 0.92), rgba(43, 10, 10, 0.90)), url('https://bucavoyages.cm/images/home/home_hero.jpg') no-repeat center center;
            background-size: cover;
            color:white; 
            display:flex; 
            flex-direction:column; 
            justify-content:space-between; 
            position: relative; 
            overflow: hidden; 
        }
        .auth-visual::before { 
            content: ""; 
            position: absolute; 
            inset: 0; 
            background: rgba(10, 26, 47, 0.15); 
            backdrop-filter: blur(2px); 
            pointer-events: none; 
            z-index: 1; 
        }
        .auth-visual h1 { margin:0; font-family: 'Poppins', sans-serif; font-size:42px; font-weight:800; line-height:1.15; max-width:680px; letter-spacing:-0.5px; z-index: 2; }
        .auth-visual p { max-width:620px; line-height:1.7; color:#ffe8e8; font-size: 15px; font-weight: 400; z-index: 2; }
        .brand { font-family: 'Poppins', sans-serif; font-weight:800; letter-spacing:.05em; font-size: 20px; text-transform: uppercase; z-index: 2; }
        
        .auth-panel { background:var(--panel); padding:56px 42px; display:flex; flex-direction:column; justify-content:center; border-left:1px solid var(--line); }
        .auth-panel h2 { margin:0 0 8px; font-family: 'Poppins', sans-serif; font-size:32px; font-weight:700; letter-spacing:-0.5px; color: var(--ink); }
        .auth-panel .lead { margin:0 0 26px; color:var(--muted); line-height:1.6; font-size: 15px; }
        
        label { display:block; font-weight:600; margin:16px 0 8px; font-size: 14px; color: var(--ink); }
        input, select { width:100%; height:46px; border:1px solid var(--line); border-radius:6px; padding:0 13px; font-size:15px; font-family: 'Inter', sans-serif; color: var(--ink); transition: border-color 0.15s ease, box-shadow 0.15s ease; background: white; }
        input:focus, select:focus { outline:none; border-color:var(--brand); box-shadow: 0 0 0 4px rgba(217, 4, 41, 0.15); }
        
        .check { display:flex; align-items:center; gap:8px; margin:16px 0; color:var(--muted); cursor: pointer; }
        .check input { width:16px; height:16px; cursor: pointer; }
        
        button { border:0; border-radius:6px; min-height:44px; padding:0 18px; font-family: 'Poppins', sans-serif; font-weight:600; cursor:pointer; transition: all 0.2s ease; font-size: 14px; }
        .primary { width:100%; background:var(--brand); color:white; box-shadow: 0 4px 6px -1px rgba(217, 4, 41, 0.12), 0 2px 4px -1px rgba(217, 4, 41, 0.08); }
        .primary:hover { background:var(--brand-dark); transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(217, 4, 41, 0.25), 0 4px 6px -4px rgba(217, 4, 41, 0.15); }
        .primary:active { transform: translateY(0); }
        
        .topline { display:flex; justify-content:space-between; gap:16px; align-items:center; margin-top:18px; font-size:14px; font-weight: 500; }
        .topline a { color: var(--brand); }
        .topline a:hover { color: var(--brand-dark); text-decoration: underline; }
        
        .alert { border-radius:6px; padding:12px 14px; margin:14px 0; line-height:1.5; font-size: 14px; font-weight: 500; border: 1px solid transparent; }
        .alert.ok { background:#ECFDF5; color:#065F46; border-color:#A7F3D0; }
        .alert.error { background:#FEF2F2; color:#991B1B; border-color:#FCA5A5; }
        .alert.warn { background:#FFFBEB; color:#92400E; border-color:#FDE68A; }
        .alert.warn a { font-weight:700; margin-left:6px; color: #B45309; }
        
        .shell { min-height:100vh; background:var(--soft); }
        .bar { height:76px; background:white; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; padding:0 28px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); }
        
        .bar strong {
            display: inline-block;
            font-size: 0;
            width: 140px;
            height: 32px;
            background: url('https://bucavoyages.cm/storage/settings/logo/01KCRPJWJ1463PAXKS62E9ZFDC.png') no-repeat center left;
            background-size: contain;
            vertical-align: middle;
            margin-right: 8px;
        }
        .bar strong::after {
            content: 'VIP';
            display: inline-block;
            font-size: 11px;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--brand);
            background: #FEF2F2;
            border: 1px solid #FCA5A5;
            padding: 1px 6px;
            border-radius: 4px;
            margin-left: 146px;
            vertical-align: middle;
            line-height: 1.2;
            letter-spacing: 0.5px;
        }
        
        .logout { background:#FEF2F2; color:var(--brand); border:1px solid #FEE2E2; border-radius: 6px; padding: 8px 16px; min-height: 38px; font-weight: 600; font-family: 'Poppins', sans-serif; }
        .logout:hover { background:#FEE2E2; color:var(--brand-dark); transform: translateY(-1px); }
        .logout:active { transform: translateY(0); }
        
        .success-wrap { min-height:calc(100vh - 76px); display:flex; align-items:center; justify-content:center; padding:32px 20px; }
        .success-panel { width:min(720px, 100%); background:white; border:1px solid var(--line); border-radius:12px; padding:38px; text-align:center; box-shadow:0 10px 25px -5px rgba(0, 0, 0, 0.02), 0 8px 10px -6px rgba(0, 0, 0, 0.02); }
        .success-mark { width:58px; height:58px; border-radius:50%; background:var(--brand); color:white; display:inline-flex; align-items:center; justify-content:center; font-size:30px; font-weight:700; margin-bottom:18px; box-shadow: 0 4px 10px rgba(217, 4, 41, 0.2); }
        .success-panel h1 { margin:0 0 10px; font-size:32px; letter-spacing:-0.5px; }
        .success-panel p { margin:0 auto; color:var(--muted); line-height:1.7; max-width:560px; font-size: 15px; }
        
        .module-note { margin-top:22px; padding:14px 16px; background:#FEF2F2; border:1px solid #FEE2E2; border-radius:6px; color:var(--brand-dark); font-weight:600; font-size: 14px; }
        
        .brand-line { display:flex; align-items:center; gap:20px; }
        .brand-line a {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s ease, border-color 0.2s ease;
            padding: 4px 0;
            border-bottom: 2px solid transparent;
            font-size: 14px;
        }
        .brand-line a:hover {
            color: var(--brand);
            border-bottom-color: var(--brand);
        }
        .brand-line a.active {
            color: var(--brand);
            border-bottom-color: var(--brand);
        }
        .brand-line span {
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            color: var(--muted);
            border-left: 1px solid var(--line);
            padding-left: 12px;
            font-size:14px;
        }
        
        .office-content { max-width:1180px; margin:0 auto; padding:34px 24px 52px; }
        .office-content.narrow { max-width:900px; }
        
        .page-head { display:flex; align-items:flex-start; justify-content:space-between; gap:22px; margin-bottom:25px; }
        .page-head h1 { margin:5px 0 8px; font-size:30px; letter-spacing:-0.5px; }
        .page-head p:not(.section-tag) { margin:0; color:var(--muted); font-size: 15px; }
        .section-tag { margin:0; font-size:12px; font-weight:700; color:var(--brand); text-transform:uppercase; letter-spacing: 1px; font-family: 'Poppins', sans-serif; }
        
        .command { display:inline-flex; align-items:center; justify-content:center; min-height:45px; padding:0 20px; border-radius:6px; font-weight:600; font-family: 'Poppins', sans-serif; white-space:nowrap; transition: all 0.2s ease; font-size: 14px; }
        .command.compact { min-height:38px; padding:0 15px; font-size:13px; }
        .primary-link { background:var(--brand); color:white; box-shadow: 0 4px 6px -1px rgba(217, 4, 41, 0.12); }
        .primary-link:hover { background:var(--brand-dark); transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(217, 4, 41, 0.2); }
        
        .filters { display:grid; grid-template-columns:minmax(250px, 1fr) 190px 110px; gap:14px; align-items:end; padding:16px; background:white; border:1px solid var(--line); border-radius:8px; margin-bottom:18px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02); }
        .filters label, .form-grid label { margin:0; }
        .filters span, .form-grid span { display:block; font-size:12px; margin-bottom:7px; color:var(--muted); font-weight:700; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .filter-button { height:46px; background:#FEF2F2; color:var(--brand); border:1px solid #FEE2E2; border-radius:6px; font-weight:600; font-family: 'Poppins', sans-serif; }
        .filter-button:hover { background:#FEE2E2; color:var(--brand-dark); transform: translateY(-1px); }
        
        .data-panel, .editor, .detail-panel { background:white; border:1px solid var(--line); border-radius:8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02); }
        .data-table { width:100%; border-collapse:collapse; }
        .data-table th { padding:14px 16px; color:var(--muted); font-size:12px; font-weight:700; text-align:left; border-bottom:1px solid var(--line); text-transform: uppercase; letter-spacing: 0.5px; }
        .data-table td { padding:15px 16px; border-bottom:1px solid #F1F5F9; }
        .data-table tbody tr:last-child td { border-bottom:0; }
        .data-table td strong { display:block; color: var(--ink); }
        .data-table td small { display:block; color:var(--muted); margin-top:4px; font-size: 12px; }
        
        .status { display:inline-flex; border-radius:999px; padding:4px 10px; font-size:11px; font-weight:700; text-transform:capitalize; background:#FEF2F2; color:var(--brand-dark); border: 1px solid #FEE2E2; line-height: 1.2; }
        .status.active { background:#E6F4EA; color:#137333; border-color: #CEEAD6; }
        .status.suspended { background:#FEF7E0; color:#B06000; border-color: #FEEFC3; }
        .status.archived, .status.expired { background:#F1F3F5; color:#5F6368; border-color: #E8EAED; }
        .status.pending_payment { background:#FEF7E0; color:#B06000; border-color: #FEEFC3; }
        .status.pending { background:#E8F0FE; color:#1A73E8; border-color: #D2E3FC; }
        .status.renewed, .status.completed { background:#F1F3F5; color:#5F6368; border-color: #E8EAED; }
        
        .actions { display:flex; justify-content:flex-end; gap:16px; font-weight:600; font-size:13px; font-family: 'Poppins', sans-serif; }
        .actions a { color: var(--brand); }
        .actions a:hover { color: var(--brand-dark); text-decoration: underline; }
        
        .empty { text-align:center; color:var(--muted); padding:40px !important; font-size: 15px; }
        .pagination { margin-top:18px; color:var(--muted); }
        
        .editor { padding:25px; }
        .form-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:18px; }
        .form-grid .wide { grid-column:1 / -1; }
        .form-buttons { display:flex; align-items:center; gap:14px; margin-top:26px; }
        
        .info-field { min-height:70px; padding:12px 14px; border:1px dashed var(--line); border-radius:6px; color:var(--muted); }
        .info-field span { display:block; margin-bottom:8px; font-size:12px; font-weight:700; text-transform: uppercase; }
        .info-field strong { color:var(--ink); }
        .action-button { width:auto; padding:0 24px; }
        
        .secondary-link { min-height:44px; display:inline-flex; align-items:center; padding:0 16px; border:1px solid var(--line); border-radius:6px; font-weight:600; font-family: 'Poppins', sans-serif; background: white; transition: all 0.2s ease; }
        .secondary-link:hover { border-color: var(--brand); color: var(--brand); transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        
        .detail-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:18px; margin-bottom:20px; }
        .detail-panel { padding:22px; }
        .detail-panel h2, .section-panel h2 { margin:0 0 18px; font-size:18px; font-family: 'Poppins', sans-serif; font-weight: 700; }
        .detail-panel dl { display:grid; grid-template-columns:150px 1fr; gap:13px 12px; margin:0; }
        .detail-panel dt { color:var(--muted); font-size:14px; font-weight: 500; }
        .detail-panel dd { margin:0; font-weight:600; color: var(--ink); }
        
        .section-panel { margin-top:18px; }
        .panel-title { display:flex; justify-content:space-between; align-items:center; padding:20px 20px 0; color:var(--muted); }
        .panel-title h2 { color:var(--ink); margin: 0; font-size:18px; }
        .panel-title a { font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 13px; }
        .empty-block { padding:0 20px 22px; color:var(--muted); font-size: 14px; }
        
        .archive-row { margin-top:22px; display:flex; justify-content:flex-end; gap:12px; }
        .danger-action { color:var(--danger); background:white; border:1px solid #FCA5A5; }
        .danger-action:hover { background: #FEF2F2; transform: translateY(-1px); }
        .delete-action { color:white; background:var(--danger); }
        .delete-action:hover { background: #B91C1C; transform: translateY(-1px); }
        
        .module-link { display:inline-flex; margin-top:24px; min-height:44px; align-items:center; padding:0 20px; background:var(--brand); color:white; border-radius:6px; font-weight:600; font-family: 'Poppins', sans-serif; transition: all 0.2s ease; box-shadow: 0 4px 6px -1px rgba(217, 4, 41, 0.12); }
        .module-link:hover { background:var(--brand-dark); transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(217, 4, 41, 0.2); }
        .module-links { display:flex; flex-wrap:wrap; gap:12px; justify-content:center; margin-top:24px; }
        .module-links .module-link { margin-top:0; }
        .module-link.secondary-module { background:white; color:var(--brand); border:1px solid var(--line); box-shadow: none; }
        .module-link.secondary-module:hover { border-color: var(--brand); color: var(--brand-dark); }
        
        .head-actions { display:flex; flex-wrap:wrap; gap:10px; justify-content:flex-end; }
        .filters .wide-filter { grid-column:1 / -1; }
        .payment-filters { grid-template-columns:minmax(250px, 1fr) 190px 110px; }
        
        .pending-badge { display:inline-flex; align-items:center; min-height:38px; padding:0 14px; border-radius:999px; background:#FEF7E0; color:#B06000; border: 1px solid #FEEFC3; font-weight:700; font-size:13px; }
        
        .receipt-panel { background:white; border:1px solid var(--line); border-radius:8px; padding:24px; margin-bottom:20px; }
        .receipt-header { display:flex; justify-content:space-between; gap:16px; align-items:center; padding-bottom:18px; margin-bottom:18px; border-bottom:1px solid var(--line); }
        .receipt-header strong { color:var(--brand); font-size:18px; font-family: 'Poppins', sans-serif; }
        .receipt-header span { color:var(--muted); font-size:14px; }
        .receipt-grid { display:grid; grid-template-columns:170px 1fr; gap:14px 12px; margin:0; }
        .receipt-grid dt { color:var(--muted); font-size:14px; }
        .receipt-grid dd { margin:0; font-weight:700; color: var(--ink); }
        
        .amount-value { color:var(--brand); font-size:24px; font-weight: 700; font-family: 'Poppins', sans-serif; }
        
        .confirm-panel h2 { margin:0 0 8px; font-size:19px; }
        .confirm-lead { margin:0 0 18px; color:var(--muted); line-height:1.5; }
        
        .intro-note { margin:0 20px 12px; }
        .inline-form { display:inline; }
        .inline-form button { background:none; border:0; color:var(--brand); font-weight:700; cursor:pointer; padding:0; font-family: inherit; font-size: inherit; }
        .inline-form button:hover { color: var(--brand-dark); text-decoration: underline; }
        
        .affiliate-panel { margin-top:18px; }
        .affiliate-form { margin-top:18px; padding-top:18px; border-top:1px solid var(--line); }
        
        .compact-btn { min-height:40px; }
        
        .metric-grid, .kpi-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:16px; margin-bottom:24px; }
        .kpi-card { padding:22px; background:white; border:1px solid var(--line); border-radius:8px; display:flex; flex-direction:column; gap:10px; color:inherit; transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease; }
        a.kpi-card:hover { border-color:var(--brand); transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(217, 4, 41, 0.08), 0 4px 6px -4px rgba(217, 4, 41, 0.08); }
        .kpi-card span { color:var(--muted); font-size:12px; font-weight:700; text-transform: uppercase; letter-spacing: 0.5px; }
        .kpi-card strong { font-size:34px; color:var(--ink); line-height:1; font-family: 'Poppins', sans-serif; font-weight: 800; }
        
        .kpi-card.important { border-color:var(--brand); }
        .kpi-card.important strong { color:var(--brand); }
        .kpi-card.warn strong { color:#B06000; }
        .kpi-card.alert strong { color:var(--danger); }
        .kpi-card.muted strong { color:#5F6368; }
        
        .dashboard-panels { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:18px; margin-bottom:24px; }
        .dashboard-panels .panel-title a { font-weight:600; font-size:13px; }
        .compact-table th, .compact-table td { padding:12px 16px; }
        
        .quick-actions { display:flex; flex-wrap:wrap; gap:12px; }
        
        .client-placeholder { background:white; border:1px solid var(--line); border-radius:8px; padding:28px; color:var(--muted); line-height:1.7; font-size: 15px; }
        .client-card-banner { background:white; border:1px solid var(--line); border-radius:8px; padding:24px; margin-bottom:20px; display:flex; flex-direction:column; gap:10px; }
        .client-card-banner h2 { margin:6px 0 0; font-size:28px; letter-spacing:-0.5px; font-family: 'Poppins', sans-serif; font-weight: 700; color: var(--ink); }
        
        .metric-grid { margin-bottom:20px; }
        .metric { padding:20px; background:white; border:1px solid var(--line); border-radius:8px; display:flex; flex-direction:column; gap:10px; }
        .metric span { color:var(--muted); font-size:12px; font-weight:700; text-transform: uppercase; letter-spacing: 0.5px; }
        .metric strong { font-size:30px; color:var(--ink); font-family: 'Poppins', sans-serif; font-weight: 800; }
        .metric.important { border-color:var(--brand); }
        .metric.important strong { color:var(--brand); }
        .metric .date-value { font-size:22px; font-weight: 700; }
        
        .reason { color:var(--muted); line-height:1.5; margin:0 0 18px; }
        .status-action { margin-top:14px; }
        
        .replace-check { display:flex; gap:10px; align-items:flex-start; margin:22px 0 0; padding:14px; background:#FEF2F2; border:1px solid #FEE2E2; border-radius:6px; }
        .replace-check input { width:17px; height:17px; flex-shrink:0; margin-top:2px; cursor: pointer; }
        .replace-check span { line-height:1.5; color:var(--brand-dark); font-weight:600; font-size: 14px; }
        
        /* Modern Dashboard System - Buca Voyages VIP */
        .modern-dash-wrap { max-width: 1360px; margin: 0 auto; padding: 28px 24px 60px; }
        .dash-topbar { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 26px; flex-wrap: wrap; }
        .dash-heading h1 { font-size: 28px; font-weight: 800; color: var(--ink); margin: 0 0 4px; letter-spacing: -0.5px; }
        .dash-heading p { margin: 0; color: #64748B; font-size: 14px; }
        .dash-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .dash-btn { display: inline-flex; align-items: center; gap: 8px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600; padding: 9px 18px; border-radius: 999px; text-decoration: none; transition: all 0.2s ease; cursor: pointer; }
        .dash-btn-primary { background: var(--brand); color: white; border: 1px solid var(--brand); box-shadow: 0 4px 14px rgba(217, 4, 41, 0.25); }
        .dash-btn-primary:hover { background: var(--brand-dark); color: white; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(217, 4, 41, 0.35); }
        .dash-btn-secondary { background: white; color: var(--ink); border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .dash-btn-secondary:hover { border-color: var(--brand); color: var(--brand); transform: translateY(-1px); }

        .dash-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px; }
        .dash-kpi-card { border-radius: 18px; padding: 22px; display: flex; flex-direction: column; justify-content: space-between; text-decoration: none; transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1); min-height: 145px; position: relative; overflow: hidden; }
        .dash-kpi-card:hover { transform: translateY(-3px); }
        
        .dash-kpi-card.hero { background: linear-gradient(135deg, #180205 0%, #380710 42%, #850617 80%, #D90429 100%); color: #ffffff; box-shadow: 0 12px 28px -6px rgba(217, 4, 41, 0.3); border: 1px solid rgba(255,255,255,0.08); }
        .dash-kpi-card.hero::after { content: ""; position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%); border-radius: 50%; pointer-events: none; }
        .dash-kpi-card.standard { background: #ffffff; border: 1px solid #E5E9F0; color: var(--ink); box-shadow: 0 2px 10px rgba(10, 26, 47, 0.03); }
        .dash-kpi-card.standard:hover { border-color: #CBD5E1; box-shadow: 0 10px 22px -4px rgba(10, 26, 47, 0.07); }
        
        .kpi-head { display: flex; justify-content: space-between; align-items: center; }
        .kpi-head .kpi-label { font-size: 13px; font-weight: 600; }
        .dash-kpi-card.hero .kpi-head .kpi-label { color: rgba(255,255,255,0.85); }
        .dash-kpi-card.standard .kpi-head .kpi-label { color: #64748B; }
        
        .kpi-circle-btn { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; transition: transform 0.2s ease; }
        .dash-kpi-card.hero .kpi-circle-btn { background: rgba(255,255,255,0.18); color: white; }
        .dash-kpi-card.standard .kpi-circle-btn { background: #F8FAFC; border: 1px solid #E2E8F0; color: #475569; }
        .dash-kpi-card:hover .kpi-circle-btn { transform: rotate(45deg); }
        
        .kpi-main-val { font-size: 34px; font-weight: 800; font-family: 'Poppins', sans-serif; line-height: 1.1; margin: 12px 0 10px; }
        .dash-kpi-card.hero .kpi-main-val { color: #ffffff; }
        .dash-kpi-card.standard .kpi-main-val { color: var(--ink); }
        
        .kpi-foot { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; }
        .dash-kpi-card.hero .hero-pill { background: rgba(255,255,255,0.15); padding: 3px 10px; border-radius: 999px; color: #ffffff; border: 1px solid rgba(255,255,255,0.2); }
        .dash-kpi-card.standard .trend-pill { display: inline-flex; align-items: center; gap: 4px; color: #10B981; }
        .dash-kpi-card.standard .trend-pill.subtle { color: #64748B; }

        .dash-row-grid { display: grid; grid-template-columns: 1.25fr 1.05fr 1.2fr; gap: 18px; margin-bottom: 20px; }
        .dash-row-grid.equal { grid-template-columns: repeat(3, 1fr); }
        
        .dash-card { background: #ffffff; border: 1px solid #E5E9F0; border-radius: 18px; padding: 22px; box-shadow: 0 2px 10px rgba(10, 26, 47, 0.03); display: flex; flex-direction: column; justify-content: space-between; }
        .dash-card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .dash-card-head h2 { font-size: 16px; font-weight: 700; margin: 0; color: var(--ink); font-family: 'Poppins', sans-serif; }
        .dash-card-pill { font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 999px; text-decoration: none; }
        .dash-card-pill.brand { background: #FFF1F2; color: var(--brand); border: 1px solid #FFE4E6; }
        .dash-card-pill.brand:hover { background: #FFE4E6; }

        /* Weekly Analytics Bar Chart */
        .analytics-chart { display: flex; align-items: flex-end; justify-content: space-between; height: 165px; padding: 12px 4px 0; gap: 6px; }
        .chart-col { display: flex; flex-direction: column; align-items: center; gap: 8px; flex: 1; height: 100%; justify-content: flex-end; }
        .chart-bar-wrap { width: 100%; display: flex; justify-content: center; align-items: flex-end; height: 125px; }
        .chart-bar { width: 28px; border-radius: 999px; transition: height 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); position: relative; }
        .chart-bar.striped { background: repeating-linear-gradient(45deg, #E2E8F0, #E2E8F0 4px, #F1F5F9 4px, #F1F5F9 8px); border: 1px solid #CBD5E1; }
        .chart-bar.active-day { background: linear-gradient(180deg, #D90429 0%, #8B0018 100%); border: 1px solid #8B0018; box-shadow: 0 6px 16px rgba(217, 4, 41, 0.35); }
        .chart-bar.past-day { background: linear-gradient(180deg, #FECDD3 0%, #FDA4AF 100%); border: 1px solid #FB7185; }
        .chart-label { font-size: 12px; font-weight: 700; color: #64748B; }
        .chart-col.is-today .chart-label { color: var(--brand); font-weight: 800; }

        /* Reminders / Next Departures Card */
        .departure-box { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px; padding: 16px; margin-bottom: 14px; }
        .departure-route { font-size: 15px; font-weight: 700; color: var(--ink); margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
        .departure-time { font-size: 13px; color: #64748B; font-weight: 500; }
        .departure-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: linear-gradient(135deg, #1C0307 0%, #8B0018 100%); color: white; padding: 12px 16px; border-radius: 12px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 13px; text-decoration: none; box-shadow: 0 4px 12px rgba(139, 0, 24, 0.25); transition: all 0.2s ease; }
        .departure-btn:hover { color: white; transform: translateY(-1px); box-shadow: 0 8px 18px rgba(139, 0, 24, 0.35); }

        /* List Items (Trips, Parcels) */
        .dash-list { display: flex; flex-direction: column; gap: 10px; }
        .dash-list-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 12px; background: #F8FAFC; border: 1px solid #F1F5F9; transition: background 0.15s ease; }
        .dash-list-item:hover { background: #F1F5F9; }
        .list-item-left { display: flex; align-items: center; gap: 12px; }
        .list-item-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0; }
        .list-item-icon.bus { background: #EFF6FF; color: #2563EB; border: 1px solid #DBEAFE; }
        .list-item-icon.parcel { background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A; }
        .list-item-icon.avatar { background: #FEE2E2; color: var(--brand); border: 1px solid #FECDD3; }
        .list-item-text strong { display: block; font-size: 13px; color: var(--ink); font-weight: 700; }
        .list-item-text small { display: block; font-size: 11px; color: #64748B; margin-top: 1px; }

        /* Gauge Progress Section */
        .gauge-container { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px 0 0; }
        .gauge-svg-wrap { position: relative; width: 220px; height: 120px; display: flex; justify-content: center; }
        .gauge-center-text { position: absolute; bottom: 8px; text-align: center; }
        .gauge-center-text .gauge-pct { font-size: 32px; font-weight: 800; font-family: 'Poppins', sans-serif; color: var(--ink); line-height: 1; }
        .gauge-center-text .gauge-sub { font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase; margin-top: 4px; }
        .gauge-legend { display: flex; justify-content: center; gap: 16px; margin-top: 16px; flex-wrap: wrap; }
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #475569; }
        .legend-dot { width: 8px; height: 8px; border-radius: 50%; }
        .legend-dot.consumed { background: var(--brand); }
        .legend-dot.remaining { background: #10B981; }
        .legend-dot.expired { background: #94A3B8; }

        /* Dark Topographic Agent Activity Widget */
        .dark-activity-widget { background: linear-gradient(145deg, #0B1320 0%, #111D31 55%, #182C49 100%); color: #ffffff; border-radius: 18px; padding: 22px; position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid #1E293B; box-shadow: 0 10px 25px -5px rgba(11, 19, 32, 0.4); }
        .dark-activity-widget::before { content: ""; position: absolute; inset: 0; background: radial-gradient(circle at 90% 10%, rgba(217, 4, 41, 0.25) 0%, transparent 60%), radial-gradient(circle at 10% 90%, rgba(37, 99, 235, 0.15) 0%, transparent 60%); pointer-events: none; }
        .dark-activity-head { display: flex; justify-content: space-between; align-items: center; z-index: 2; }
        .dark-activity-head span { font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; }
        .pulse-badge { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #34D399; background: rgba(16, 185, 129, 0.15); padding: 3px 8px; border-radius: 999px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .pulse-dot { width: 6px; height: 6px; border-radius: 50%; background: #34D399; box-shadow: 0 0 8px #34D399; }
        .dark-activity-main { z-index: 2; margin: 14px 0; }
        .dark-activity-counter { font-size: 40px; font-weight: 800; font-family: 'Poppins', sans-serif; line-height: 1; color: #ffffff; }
        .dark-activity-label { font-size: 12px; color: #94A3B8; margin-top: 6px; }
        .dark-activity-breakdown { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; z-index: 2; background: rgba(255,255,255,0.05); padding: 10px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); }
        .breakdown-col small { display: block; font-size: 10px; color: #94A3B8; font-weight: 600; text-transform: uppercase; }
        .breakdown-col strong { display: block; font-size: 14px; color: #ffffff; font-weight: 700; margin-top: 2px; }

        @media (max-width: 1120px) {
            .dash-kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .dash-row-grid, .dash-row-grid.equal { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .dash-kpi-grid { grid-template-columns: 1fr; }
            .modern-dash-wrap { padding: 18px 14px 40px; }
            .dash-topbar { flex-direction: column; align-items: flex-start; }
            .dash-actions { width: 100%; justify-content: flex-start; }
        }

        @media (max-width: 860px) { .auth-page { grid-template-columns:1fr; } .auth-visual { min-height:260px; } .auth-panel { border-left:0; } }
        @media (max-width: 860px) { .filters, .detail-grid, .metric-grid, .kpi-grid, .dashboard-panels { grid-template-columns:1fr; } }
        @media (max-width: 520px) { .auth-panel, .auth-visual, .success-panel { padding:30px 22px; } .office-content { padding:24px 14px; } .page-head { flex-direction:column; } .form-grid { grid-template-columns:1fr; } .data-panel { overflow-x:auto; } .auth-visual h1, .success-panel h1 { font-size:30px; } .topline { flex-direction:column; align-items:flex-start; } }
    </style>
</head>
<body>
    @yield('body')
</body>
</html>

