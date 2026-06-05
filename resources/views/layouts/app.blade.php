<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1a56db" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">
    <title>@yield('title', 'EduPay Manager') — EduPay Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
/* ═══════════════════════════════════════════════════════════
   LIGHT THEME (default)
═══════════════════════════════════════════════════════════ */
:root,
[data-theme="light"] {
    --primary:        #1a56db;
    --primary-dark:   #1342b0;
    --primary-light:  #e8f0fe;
    --primary-50:     #eff6ff;
    --primary-200:    #bfdbfe;
    --success:        #0e9f6e;
    --success-light:  #def7ec;
    --warning:        #d97706;
    --warning-light:  #fef3c7;
    --danger:         #e02424;
    --danger-light:   #fde8e8;
    /* Surfaces */
    --bg-page:        #f1f5f9;
    --bg-card:        #ffffff;
    --bg-sidebar:     #0f172a;
    --bg-topbar:      #ffffff;
    --bg-input:       #ffffff;
    --bg-muted:       #f8fafc;
    --bg-hover:       #f1f5f9;
    --bg-table-head:  #f8fafc;
    --bg-table-hover: #f8fafc;
    /* Borders */
    --border:         #e2e8f0;
    --border-input:   #cbd5e1;
    /* Text */
    --text-primary:   #0f172a;
    --text-secondary: #475569;
    --text-muted:     #94a3b8;
    --text-heading:   #0f172a;
    /* Sidebar text */
    --sidebar-text:       #94a3b8;
    --sidebar-text-hover: #f1f5f9;
    --sidebar-active-bg:  #1a56db;
    --sidebar-section:    #475569;
    --sidebar-border:     rgba(255,255,255,0.08);
    /* Modal */
    --modal-bg:       #ffffff;
    --modal-footer:   #f8fafc;
    /* Shadows */
    --shadow-sm:  0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
    --shadow-md:  0 4px 16px rgba(0,0,0,0.08);
    --shadow-lg:  0 20px 60px rgba(0,0,0,0.12);
    --shadow-card:0 1px 3px rgba(0,0,0,0.06);
    /* Misc */
    --sidebar-w:  280px;
    --header-h:   64px;
    --radius:     12px;
    --radius-sm:  8px;
    --radius-lg:  16px;
}

/* ═══════════════════════════════════════════════════════════
   DARK THEME
═══════════════════════════════════════════════════════════ */
[data-theme="dark"] {
    --primary:        #3b82f6;
    --primary-dark:   #2563eb;
    --primary-light:  rgba(59,130,246,0.15);
    --primary-50:     rgba(59,130,246,0.08);
    --primary-200:    rgba(59,130,246,0.25);
    --success:        #10b981;
    --success-light:  rgba(16,185,129,0.15);
    --warning:        #f59e0b;
    --warning-light:  rgba(245,158,11,0.15);
    --danger:         #ef4444;
    --danger-light:   rgba(239,68,68,0.15);
    /* Surfaces */
    --bg-page:        #0b1120;
    --bg-card:        #131c2e;
    --bg-sidebar:     #080e1a;
    --bg-topbar:      #131c2e;
    --bg-input:       #1e2d45;
    --bg-muted:       #1a2540;
    --bg-hover:       #1e2d45;
    --bg-table-head:  #1a2540;
    --bg-table-hover: #1e2d45;
    /* Borders */
    --border:         #1e3a5f;
    --border-input:   #2a4a7f;
    /* Text */
    --text-primary:   #e2e8f0;
    --text-secondary: #94a3b8;
    --text-muted:     #64748b;
    --text-heading:   #f1f5f9;
    /* Sidebar text */
    --sidebar-text:       #64748b;
    --sidebar-text-hover: #e2e8f0;
    --sidebar-active-bg:  #1d4ed8;
    --sidebar-section:    #334155;
    --sidebar-border:     rgba(255,255,255,0.06);
    /* Modal */
    --modal-bg:       #131c2e;
    --modal-footer:   #0f1929;
    /* Shadows */
    --shadow-sm:  0 1px 3px rgba(0,0,0,0.4);
    --shadow-md:  0 4px 16px rgba(0,0,0,0.4);
    --shadow-lg:  0 20px 60px rgba(0,0,0,0.6);
    --shadow-card:0 1px 3px rgba(0,0,0,0.3);
}

/* ═══════════════════════════════════════════════════════════
   RESET & BASE
═══════════════════════════════════════════════════════════ */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { -webkit-text-size-adjust:100%; scroll-behavior:smooth; }
body {
    font-family:'Plus Jakarta Sans',sans-serif;
    background:var(--bg-page);
    color:var(--text-primary);
    min-height:100vh;
    font-size:15px;
    line-height:1.5;
    -webkit-font-smoothing:antialiased;
    transition:background 0.25s ease, color 0.25s ease;
}
a { color:inherit; }
img { max-width:100%; }

/* ═══════════════════════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════════════════════ */
.sidebar {
    position:fixed; top:0; left:0;
    width:var(--sidebar-w); height:100vh;
    background:var(--bg-sidebar);
    display:flex; flex-direction:column;
    z-index:200; overflow-y:auto; overflow-x:hidden;
    transition:transform 0.3s ease, width 0.3s ease;
    scrollbar-width:thin;
    scrollbar-color:rgba(255,255,255,0.08) transparent;
    border-right:1px solid var(--sidebar-border);
}
.sidebar-brand {
    padding:20px 16px;
    border-bottom:1px solid var(--sidebar-border);
    flex-shrink:0;
}
.brand-header {
    display:flex; align-items:center;
    justify-content:space-between; margin-bottom:12px;
}
.brand-icon {
    width:40px; height:40px; background:var(--primary);
    border-radius:10px; display:flex; align-items:center;
    justify-content:center; font-size:18px; color:#fff; flex-shrink:0;
}
.brand-name { font-size:15px; font-weight:800; color:#fff; line-height:1.2; }
.brand-sub  { font-size:11px; color:var(--sidebar-section); margin-top:3px; }
.sidebar-toggle-btn {
    display:flex; align-items:center; justify-content:center;
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.08);
    cursor:pointer; color:var(--sidebar-text);
    font-size:15px; width:32px; height:32px;
    border-radius:7px; flex-shrink:0;
    transition:background 0.15s, color 0.15s;
}
.sidebar-toggle-btn:hover { background:rgba(255,255,255,0.12); color:#fff; }
.sidebar-toggle-btn:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }
.sidebar-nav { padding:14px 10px; flex:1; }
.nav-section-label {
    font-size:10px; font-weight:700; color:var(--sidebar-section);
    letter-spacing:1px; text-transform:uppercase;
    padding:0 10px; margin:16px 0 5px;
}
.nav-link {
    display:flex; align-items:center; gap:10px;
    padding:10px 12px; border-radius:8px;
    color:var(--sidebar-text); text-decoration:none;
    font-size:14px; font-weight:500;
    transition:background 0.15s, color 0.15s;
    margin-bottom:2px; white-space:nowrap;
}
.nav-link:hover { background:rgba(255,255,255,0.07); color:var(--sidebar-text-hover); }
.nav-link.active { background:var(--sidebar-active-bg); color:#fff; }
.nav-link i { width:18px; text-align:center; font-size:14px; flex-shrink:0; }
.nav-badge {
    margin-left:auto; background:var(--danger);
    color:#fff; font-size:10px; font-weight:700;
    padding:2px 7px; border-radius:20px; flex-shrink:0;
}
.sidebar-footer {
    padding:14px; border-top:1px solid var(--sidebar-border); flex-shrink:0;
}
.user-info { display:flex; align-items:center; gap:10px; }
.user-avatar {
    width:34px; height:34px; background:var(--primary);
    border-radius:50%; display:flex; align-items:center;
    justify-content:center; color:#fff; font-size:13px;
    font-weight:700; flex-shrink:0;
}
.user-name  { font-size:13px; font-weight:600; color:#fff; }
.user-role  { font-size:11px; color:var(--sidebar-section); }
.logout-btn {
    margin-left:auto; background:transparent;
    border:1px solid rgba(255,255,255,0.1);
    cursor:pointer; color:var(--sidebar-text);
    width:30px; height:30px; border-radius:7px;
    display:flex; align-items:center; justify-content:center;
    font-size:13px; transition:background 0.15s, color 0.15s; flex-shrink:0;
}
.logout-btn:hover { background:rgba(255,255,255,0.1); color:#fff; }
.logout-btn:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }

/* ═══════════════════════════════════════════════════════════
   MAIN CONTENT & TOPBAR
═══════════════════════════════════════════════════════════ */
.main-content {
    margin-left:var(--sidebar-w);
    min-height:100vh;
    transition:margin-left 0.3s ease;
}
.topbar {
    height:var(--header-h);
    background:var(--bg-topbar);
    border-bottom:1px solid var(--border);
    display:flex; align-items:center;
    padding:0 20px; gap:10px;
    position:sticky; top:0; z-index:100;
    box-shadow:var(--shadow-sm);
}
.topbar-mobile-toggle {
    display:none;
    background:none; border:1px solid var(--border);
    cursor:pointer; color:var(--text-secondary);
    width:36px; height:36px; border-radius:8px;
    align-items:center; justify-content:center;
    font-size:16px; flex-shrink:0;
    transition:background 0.15s;
}
.topbar-mobile-toggle:hover { background:var(--bg-hover); }
.topbar-title {
    font-size:17px; font-weight:700;
    color:var(--text-heading); flex:1;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.topbar-actions {
    display:flex; align-items:center; gap:8px; flex-shrink:0;
}
/* Theme toggle button */
.theme-toggle {
    display:flex; align-items:center; justify-content:center;
    width:36px; height:36px; border-radius:8px;
    background:var(--bg-muted); border:1px solid var(--border);
    cursor:pointer; color:var(--text-secondary);
    font-size:15px; flex-shrink:0;
    transition:background 0.15s, color 0.15s, transform 0.2s;
}
.theme-toggle:hover { background:var(--bg-hover); color:var(--text-primary); transform:rotate(15deg); }
.theme-toggle:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }
.page-content { padding:20px; }

/* ═══════════════════════════════════════════════════════════
   CARDS
═══════════════════════════════════════════════════════════ */
.card {
    background:var(--bg-card);
    border-radius:var(--radius);
    border:1px solid var(--border);
    overflow:hidden;
    box-shadow:var(--shadow-card);
    transition:box-shadow 0.15s, border-color 0.15s;
}
.card-header {
    padding:16px 20px;
    border-bottom:1px solid var(--border);
    display:flex; align-items:center;
    justify-content:space-between; gap:12px; flex-wrap:wrap;
}
.card-title { font-size:15px; font-weight:700; color:var(--text-heading); }
.card-body { padding:20px; }

/* ═══════════════════════════════════════════════════════════
   STAT CARDS
═══════════════════════════════════════════════════════════ */
.stats-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(190px,1fr));
    gap:14px; margin-bottom:20px;
}
.stat-card {
    background:var(--bg-card);
    border-radius:var(--radius);
    border:1px solid var(--border);
    padding:18px;
    display:flex; align-items:flex-start; gap:14px;
    box-shadow:var(--shadow-card);
    transition:box-shadow 0.15s, transform 0.15s;
    text-decoration:none; color:inherit;
}
.stat-card:hover { box-shadow:var(--shadow-md); transform:translateY(-2px); }
.stat-icon {
    width:46px; height:46px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px; flex-shrink:0;
}
.stat-value { font-size:24px; font-weight:800; color:var(--text-heading); line-height:1; }
.stat-label { font-size:12px; color:var(--text-muted); font-weight:500; margin-top:4px; }

/* ═══════════════════════════════════════════════════════════
   TABLES
═══════════════════════════════════════════════════════════ */
.table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
table { width:100%; border-collapse:collapse; min-width:480px; }
thead th {
    background:var(--bg-table-head);
    padding:11px 14px;
    font-size:11px; font-weight:700; color:var(--text-muted);
    text-transform:uppercase; letter-spacing:0.5px;
    border-bottom:1px solid var(--border);
    text-align:left; white-space:nowrap;
}
tbody td {
    padding:13px 14px; font-size:14px;
    color:var(--text-secondary);
    border-bottom:1px solid var(--border);
}
tbody tr:last-child td { border-bottom:none; }
tbody tr:hover td { background:var(--bg-table-hover); }

/* ═══════════════════════════════════════════════════════════
   BADGES
═══════════════════════════════════════════════════════════ */
.badge {
    display:inline-flex; align-items:center;
    padding:3px 9px; border-radius:20px;
    font-size:11px; font-weight:600;
}
.badge-success { background:var(--success-light); color:var(--success); }
.badge-warning { background:var(--warning-light); color:var(--warning); }
.badge-danger  { background:var(--danger-light);  color:var(--danger); }
.badge-primary { background:var(--primary-light); color:var(--primary); }
.badge-gray    { background:var(--bg-muted);      color:var(--text-secondary); }

/* ═══════════════════════════════════════════════════════════
   TEXT HELPERS
═══════════════════════════════════════════════════════════ */
.text-success  { color:var(--success); }
.text-warning  { color:var(--warning); }
.text-info     { color:var(--primary); }
.text-danger   { color:var(--danger); }
.text-muted    { color:var(--text-muted); }
.text-secondary{ color:var(--text-secondary); }
.text-gray-600 { color:var(--text-secondary); }
.font-mono     { font-family:'JetBrains Mono',monospace; }

/* ═══════════════════════════════════════════════════════════
   BUTTONS
═══════════════════════════════════════════════════════════ */
.btn {
    display:inline-flex; align-items:center; gap:7px;
    padding:9px 18px; border-radius:var(--radius-sm);
    font-size:14px; font-weight:600;
    border:none; cursor:pointer;
    text-decoration:none; transition:all 0.15s;
    font-family:inherit; white-space:nowrap;
    -webkit-tap-highlight-color:transparent;
    line-height:1.4;
}
.btn:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }
.btn:hover { transform:translateY(-1px); box-shadow:var(--shadow-md); }
.btn:active { transform:translateY(0); box-shadow:none; }
.btn-sm { padding:6px 13px; font-size:13px; border-radius:7px; }
.btn-primary { background:var(--primary); color:#fff; }
.btn-primary:hover { background:var(--primary-dark); color:#fff; }
.btn-success { background:var(--success); color:#fff; }
.btn-danger  { background:var(--danger);  color:#fff; }
.btn-outline {
    background:var(--bg-card); color:var(--text-secondary);
    border:1px solid var(--border);
}
.btn-outline:hover { background:var(--bg-hover); color:var(--text-primary); }
.btn-icon {
    width:34px; height:34px; padding:0;
    border-radius:7px; justify-content:center; flex-shrink:0;
}

/* ═══════════════════════════════════════════════════════════
   FORMS
═══════════════════════════════════════════════════════════ */
.form-group { margin-bottom:18px; }
.form-label {
    display:block; margin-bottom:6px;
    font-size:13px; font-weight:600; color:var(--text-secondary);
}
.form-control {
    width:100%; padding:10px 13px;
    border:1.5px solid var(--border-input);
    border-radius:var(--radius-sm); font-size:14px;
    color:var(--text-primary); font-family:inherit;
    background:var(--bg-input);
    transition:border 0.15s, box-shadow 0.15s;
    outline:none; -webkit-appearance:none; appearance:none;
}
.form-control:focus {
    border-color:var(--primary);
    box-shadow:0 0 0 3px var(--primary-light);
}
.form-control.is-invalid { border-color:var(--danger); }
.form-control.is-invalid:focus { box-shadow:0 0 0 3px var(--danger-light); }
.invalid-feedback { color:var(--danger); font-size:12px; margin-top:4px; }
select.form-control {
    cursor:pointer;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath fill='%2394a3b8' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 12px center;
    padding-right:34px;
}
textarea.form-control { resize:vertical; min-height:80px; }
.form-row   { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
/* Readonly fields in dark mode */
.form-control[readonly] { background:var(--bg-muted); color:var(--text-muted); }

/* ═══════════════════════════════════════════════════════════
   ALERTS (flash messages)
═══════════════════════════════════════════════════════════ */
.alert {
    padding:12px 16px; border-radius:var(--radius-sm);
    margin-bottom:16px; font-size:14px;
    display:flex; align-items:flex-start; gap:10px;
}
.alert-success { background:var(--success-light); color:var(--success); border:1px solid var(--success); }
.alert-danger  { background:var(--danger-light);  color:var(--danger);  border:1px solid var(--danger); }
.alert-warning { background:var(--warning-light); color:var(--warning); border:1px solid var(--warning); }

/* ═══════════════════════════════════════════════════════════
   PAGINATION
═══════════════════════════════════════════════════════════ */
.pagination { display:flex; gap:4px; align-items:center; flex-wrap:wrap; }
.page-link {
    padding:6px 11px; border-radius:6px;
    font-size:13px; font-weight:500;
    color:var(--text-secondary); text-decoration:none;
    border:1px solid var(--border);
    background:var(--bg-card); cursor:pointer; transition:all 0.15s;
}
.page-link:hover { background:var(--bg-hover); color:var(--text-primary); }
.page-link.active { background:var(--primary); color:#fff; border-color:var(--primary); }

/* ═══════════════════════════════════════════════════════════
   FILTER BAR
═══════════════════════════════════════════════════════════ */
.filter-bar {
    display:flex; gap:10px; align-items:center; flex-wrap:wrap;
}
.filter-bar .form-control { width:auto; flex:1; min-width:150px; }

/* ═══════════════════════════════════════════════════════════
   DEADLINE PILLS & ALERT ROWS
═══════════════════════════════════════════════════════════ */
.alert-level-overdue td { background:rgba(239,68,68,0.06) !important; }
.alert-level-critical td { background:rgba(245,158,11,0.06) !important; }
.deadline-pill {
    font-size:10px; font-weight:700;
    padding:2px 7px; border-radius:20px; white-space:nowrap;
}
.deadline-overdue  { background:var(--danger-light);  color:var(--danger); }
.deadline-critical { background:var(--warning-light); color:var(--warning); }
.deadline-warning  { background:var(--warning-light); color:var(--warning); }
.deadline-normal   { background:var(--success-light); color:var(--success); }

/* ═══════════════════════════════════════════════════════════
   EMPTY STATE
═══════════════════════════════════════════════════════════ */
.empty-state { text-align:center; padding:40px 20px; color:var(--text-muted); }
.empty-state i { font-size:36px; margin-bottom:10px; display:block; }
.empty-state p { font-size:14px; }

/* ═══════════════════════════════════════════════════════════
   SIDEBAR OVERLAY
═══════════════════════════════════════════════════════════ */
.sidebar-overlay {
    position:fixed; inset:0;
    background:rgba(0,0,0,0.6);
    z-index:199; display:none;
    backdrop-filter:blur(2px);
}
.sidebar-overlay.open { display:block; }

/* ═══════════════════════════════════════════════════════════
   DELETE MODAL
═══════════════════════════════════════════════════════════ */
.modal-overlay {
    position:fixed; inset:0;
    background:rgba(0,0,0,0.6);
    z-index:1000;
    display:flex; align-items:center; justify-content:center;
    opacity:0; pointer-events:none;
    transition:opacity 0.2s ease;
    padding:16px;
}
.modal-overlay.active { opacity:1; pointer-events:auto; }
.modal {
    background:var(--modal-bg);
    border:1px solid var(--border);
    border-radius:var(--radius-lg);
    max-width:400px; width:100%;
    box-shadow:var(--shadow-lg);
    overflow:hidden;
    transform:translateY(20px) scale(0.97);
    transition:transform 0.2s ease;
}
.modal-overlay.active .modal { transform:translateY(0) scale(1); }
.modal-header { padding:24px 24px 14px; text-align:center; }
.modal-icon {
    width:56px; height:56px; border-radius:50%;
    background:var(--danger-light); color:var(--danger);
    display:flex; align-items:center; justify-content:center;
    font-size:24px; margin:0 auto 12px;
}
.modal-title { font-size:17px; font-weight:700; color:var(--text-heading); margin-bottom:6px; }
.modal-body { padding:0 24px 18px; text-align:center; color:var(--text-secondary); font-size:14px; }
.modal-footer {
    padding:14px 20px 18px;
    display:flex; gap:10px;
    border-top:1px solid var(--border);
    background:var(--modal-footer);
}
.modal-footer .btn { flex:1; justify-content:center; }

/* ═══════════════════════════════════════════════════════════
   LAYOUT GRIDS
═══════════════════════════════════════════════════════════ */
.detail-grid {
    display:grid;
    grid-template-columns:300px 1fr;
    gap:18px; align-items:start;
}
.payment-detail-grid {
    display:grid;
    grid-template-columns:1fr 280px;
    gap:18px; align-items:start;
}
.dashboard-grid {
    display:grid;
    grid-template-columns:1fr;
    gap:18px;
}
.alerts-grid {
    display:grid;
    grid-template-columns:1fr;
    gap:18px; margin-bottom:20px;
}

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════════ */
@media (min-width:1100px) {
    .dashboard-grid { grid-template-columns:1fr 340px; }
}
@media (min-width:768px)  { .alerts-grid { grid-template-columns:repeat(2,1fr); } }
@media (min-width:1200px) { .alerts-grid { grid-template-columns:repeat(3,1fr); } }

@media (max-width:1024px) {
    .sidebar { transform:translateX(-100%); }
    .sidebar.open { transform:translateX(0); }
    .main-content { margin-left:0; }
    .topbar-mobile-toggle { display:flex; }
}
@media (min-width:1025px) {
    /* Collapsed sidebar: only the toggle button is visible */
    .sidebar.collapsed { width:56px; }

    /* Hide everything except the toggle button wrapper */
    .sidebar.collapsed .brand-text,
    .sidebar.collapsed .brand-icon,
    .sidebar.collapsed .sidebar-nav,
    .sidebar.collapsed .sidebar-footer { display:none; }

    /* Keep brand header visible but centered, just for the toggle btn */
    .sidebar.collapsed .sidebar-brand {
        padding:12px 10px;
        border-bottom:none;
    }
    .sidebar.collapsed .brand-header {
        justify-content:center;
        margin-bottom:0;
    }

    .main-content.sidebar-collapsed { margin-left:56px; }
}
@media (max-width:900px) {
    .detail-grid,
    .payment-detail-grid { grid-template-columns:1fr; }
}
@media (max-width:768px) {
    .form-row, .form-row-3 { grid-template-columns:1fr; }
    .stats-grid { grid-template-columns:repeat(2,1fr); gap:10px; }
    .stat-value { font-size:20px; }
    .page-content { padding:14px; }
    .topbar { padding:0 14px; }
    .topbar-title { font-size:15px; }
    .topbar-actions .btn-sm span { display:none; }
    .topbar-actions .btn-sm i { margin:0; }
    .topbar-actions .btn-sm { padding:7px 9px; }
}
@media (max-width:480px) {
    .stats-grid { grid-template-columns:1fr 1fr; gap:8px; }
    .stat-card { padding:14px; gap:10px; }
    .stat-icon { width:38px; height:38px; font-size:17px; }
    .stat-value { font-size:18px; }
}

/* ═══════════════════════════════════════════════════════════
   FOCUS VISIBLE (accessibility)
═══════════════════════════════════════════════════════════ */
*:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }

/* ═══════════════════════════════════════════════════════════
   MISC UTILITIES
═══════════════════════════════════════════════════════════ */
.info-row {
    display:flex; align-items:flex-start; gap:10px;
    padding:10px 0; border-bottom:1px solid var(--border);
}
.info-row:last-child { border-bottom:none; }
.info-label-sm {
    font-size:10px; color:var(--text-muted);
    font-weight:600; text-transform:uppercase; letter-spacing:0.5px;
}
.info-val-sm { font-size:13px; font-weight:600; color:var(--text-primary); }
.mono { font-family:'JetBrains Mono',monospace; }
    </style>
    @yield('styles')
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay" role="presentation" aria-hidden="true"></div>

{{-- ── Sidebar ──────────────────────────────────────────────── --}}
<aside class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
    <div class="sidebar-brand">
        <div class="brand-header">
            <div class="brand-icon" aria-hidden="true"><i class="fas fa-graduation-cap"></i></div>
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn"
                    aria-label="Collapse sidebar" aria-expanded="true" aria-controls="sidebar">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
        </div>
        <div class="brand-text">
            <div class="brand-name">EduPay Manager</div>
            <div class="brand-sub">Payment Management System</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
            <i class="fas fa-th-large" aria-hidden="true"></i><span>Dashboard</span>
        </a>
        <a href="{{ route('revenue.index') }}" class="nav-link {{ request()->routeIs('revenue.*') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('revenue.*') ? 'page' : 'false' }}">
            <i class="fas fa-chart-line" aria-hidden="true"></i><span>Revenue Report</span>
        </a>
        <div class="nav-section-label" aria-hidden="true">Students</div>
        <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('students.*') ? 'page' : 'false' }}">
            <i class="fas fa-user-graduate" aria-hidden="true"></i><span>All Students</span>
        </a>
        <a href="{{ route('students.create') }}" class="nav-link">
            <i class="fas fa-user-plus" aria-hidden="true"></i><span>Enroll Student</span>
        </a>
        <div class="nav-section-label" aria-hidden="true">History</div>
        <a href="{{ route('history.monthly') }}" class="nav-link {{ request()->routeIs('history.*') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('history.*') ? 'page' : 'false' }}">
            <i class="fas fa-history" aria-hidden="true"></i><span>Monthly History</span>
        </a>
        <div class="nav-section-label" aria-hidden="true">Payments</div>
        <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.index') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('payments.index') ? 'page' : 'false' }}">
            <i class="fas fa-receipt" aria-hidden="true"></i><span>All Payments</span>
        </a>
        <a href="{{ route('payments.create') }}" class="nav-link">
            <i class="fas fa-plus-circle" aria-hidden="true"></i><span>New Payment</span>
        </a>
        <a href="{{ route('payments.alerts') }}" class="nav-link {{ request()->routeIs('payments.alerts*') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('payments.alerts*') ? 'page' : 'false' }}">
            <i class="fas fa-bell" aria-hidden="true"></i><span>Deadline Alerts</span>
            @php
                $alertCount = \App\Models\Student::where(function ($q) {
                    $q->where('status','active')->orWhereNull('status');
                })->with(['payments' => fn($q) => $q->orderByDesc('next_payment_date')
                                                    ->orderByDesc('id')])->get()
                ->filter(function ($s) {
                    $last = $s->payments->first();
                    if (!$last) return true;
                    $next = $last->next_payment_date;
                    if (!$next) return true;
                    return \Carbon\Carbon::parse($next)->lte(now()->addDays(3));
                })->count();
            @endphp
            @if($alertCount > 0)
                <span class="nav-badge" aria-label="{{ $alertCount }} alerts">{{ $alertCount }}</span>
            @endif
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            <div class="user-details">
                <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role ?? 'admin') }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn" title="Sign out" aria-label="Sign out">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ── Main content ─────────────────────────────────────────── --}}
<div class="main-content" id="mainContent">
    <header class="topbar" role="banner">
        <button class="topbar-mobile-toggle" id="mobileMenuBtn"
                aria-label="Open navigation menu" aria-expanded="false" aria-controls="sidebar">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
        {{-- Back button slot — top left, before title --}}
        @hasSection('topbar-back')
            @yield('topbar-back')
        @endif
        <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
        <div class="topbar-actions">
            @yield('topbar-actions')
            {{-- Theme toggle --}}
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode" title="Toggle theme">
                <i class="fas fa-moon" id="themeIcon" aria-hidden="true"></i>
            </button>
        </div>
    </header>
    <main class="page-content" id="main-content" role="main">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @yield('content')
    </main>
</div>

{{-- ── Delete Modal ─────────────────────────────────────────── --}}
<div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true"
     aria-labelledby="deleteModalTitle" aria-describedby="deleteModalBody">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-icon" aria-hidden="true"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="modal-title" id="deleteModalTitle">Delete Item</div>
        </div>
        <div class="modal-body" id="deleteModalBody">Are you sure? This cannot be undone.</div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="deleteModalCancel">Cancel</button>
            <form id="deleteModalForm" method="POST" style="flex:1;display:flex">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger" style="flex:1;justify-content:center">
                    <i class="fas fa-trash" aria-hidden="true"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>

@yield('scripts')

<script>
(function () {
    'use strict';

    /* ── Theme ───────────────────────────────────────────── */
    const html        = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon   = document.getElementById('themeIcon');
    const THEME_KEY   = 'edupay-theme';

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        themeToggle.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        try { localStorage.setItem(THEME_KEY, theme); } catch(e) {}
    }

    // Load saved theme or system preference
    (function () {
        let saved;
        try { saved = localStorage.getItem(THEME_KEY); } catch(e) {}
        if (saved === 'dark' || saved === 'light') {
            applyTheme(saved);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            applyTheme('dark');
        }
    })();

    themeToggle.addEventListener('click', function () {
        applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });

    // Listen for OS theme changes
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
            let saved;
            try { saved = localStorage.getItem(THEME_KEY); } catch(ex) {}
            if (!saved) applyTheme(e.matches ? 'dark' : 'light');
        });
    }

    /* ── Sidebar ─────────────────────────────────────────── */
    const sidebar     = document.getElementById('sidebar');
    const overlay     = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('mainContent');
    const desktopBtn  = document.getElementById('sidebarToggleBtn');
    const mobileBtn   = document.getElementById('mobileMenuBtn');

    function isDesktop() { return window.innerWidth >= 1025; }

    function openMobileSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
        overlay.removeAttribute('aria-hidden');
        mobileBtn.setAttribute('aria-expanded', 'true');
        const first = sidebar.querySelector('a,button');
        if (first) first.focus();
    }
    function closeMobileSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        mobileBtn.setAttribute('aria-expanded', 'false');
        mobileBtn.focus();
    }
    function toggleDesktopSidebar() {
        const collapsed = sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('sidebar-collapsed', collapsed);
        desktopBtn.setAttribute('aria-expanded', String(!collapsed));
        desktopBtn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        try { localStorage.setItem('edupay-sidebar', collapsed ? '1' : '0'); } catch(e) {}
    }

    // Restore desktop sidebar state
    try {
        if (isDesktop() && localStorage.getItem('edupay-sidebar') === '1') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
            desktopBtn.setAttribute('aria-expanded', 'false');
        }
    } catch(e) {}

    desktopBtn.addEventListener('click', function () {
        if (isDesktop()) toggleDesktopSidebar(); else closeMobileSidebar();
    });
    mobileBtn.addEventListener('click', openMobileSidebar);
    overlay.addEventListener('click', closeMobileSidebar);
    sidebar.querySelectorAll('.nav-link').forEach(function (l) {
        l.addEventListener('click', function () { if (!isDesktop()) closeMobileSidebar(); });
    });

    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (isDesktop()) { overlay.classList.remove('open'); sidebar.classList.remove('open'); }
        }, 100);
    });

    /* ── Delete Modal ────────────────────────────────────── */
    const deleteModal  = document.getElementById('deleteModal');
    const cancelBtn    = document.getElementById('deleteModalCancel');
    const modalTitle   = document.getElementById('deleteModalTitle');
    const modalBody    = document.getElementById('deleteModalBody');
    const modalForm    = document.getElementById('deleteModalForm');
    let lastFocus      = null;

    function openDeleteModal(url, title, body) {
        lastFocus = document.activeElement;
        modalTitle.textContent = title || 'Delete Item';
        modalBody.textContent  = body  || 'Are you sure? This cannot be undone.';
        modalForm.action = url;
        deleteModal.classList.add('active');
        cancelBtn.focus();
    }
    function closeDeleteModal() {
        deleteModal.classList.remove('active');
        if (lastFocus) lastFocus.focus();
    }

    cancelBtn.addEventListener('click', closeDeleteModal);
    deleteModal.addEventListener('click', function (e) { if (e.target === deleteModal) closeDeleteModal(); });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (!isDesktop() && sidebar.classList.contains('open')) closeMobileSidebar();
            if (deleteModal.classList.contains('active')) closeDeleteModal();
        }
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.delete-btn');
        if (!btn) return;
        e.preventDefault();
        openDeleteModal(btn.dataset.action, btn.dataset.title, btn.dataset.body);
    });

    /* Auto-dismiss flash messages */
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 500);
        }, 5000);
    });

    /* Auto-clear zero on numeric fee inputs */
    document.querySelectorAll('input[type="number"]').forEach(function (el) {
        el.addEventListener('focus', function () {
            if (this.value === '0' || this.value === '0.00' || this.value === '0.0') {
                this.value = '';
            }
        });
        el.addEventListener('blur', function () {
            if (this.value === '' || isNaN(parseFloat(this.value))) {
                this.value = this.min || '0';
            }
        });
    });

    /* Cross-tab auto-refresh via localStorage */
    /* When any tab makes a data change, all other tabs reload */
    var DATA_KEY = 'edupay_data_changed';

    /* If this page loaded because of a flash success/error, broadcast to other tabs */
    @if(session('success') || session('error'))
    try { localStorage.setItem(DATA_KEY, Date.now().toString()); } catch(e) {}
    @endif

    /* Listen for changes from other tabs and reload */
    window.addEventListener('storage', function (e) {
        if (e.key !== DATA_KEY) return;
        var active = document.activeElement;
        var inForm = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT');
        if (inForm) return;
        var path = window.location.pathname;
        if (path.indexOf('/create') !== -1 || path.indexOf('/edit') !== -1) return;
        window.location.reload();
    });

    /* Periodic badge refresh every 90s */
    (function () {
        setInterval(function () {
            if (document.hidden) return;
            var active = document.activeElement;
            if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) return;
            fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    var doc      = new DOMParser().parseFromString(html, 'text/html');
                    var newBadge = doc.querySelector('.nav-badge');
                    var curBadge = document.querySelector('.nav-badge');
                    if (newBadge && curBadge) {
                        if (newBadge.textContent.trim() !== curBadge.textContent.trim()) {
                            curBadge.textContent = newBadge.textContent.trim();
                        }
                    } else if (newBadge && !curBadge) {
                        var link = document.querySelector('a[href*="alerts"]');
                        if (link) link.appendChild(newBadge.cloneNode(true));
                    } else if (!newBadge && curBadge) {
                        curBadge.remove();
                    }
                })
                .catch(function () {});
        }, 90000);
    })();

    /* Fix inline style colors for dark mode */
    document.querySelectorAll('[data-balance-color]').forEach(function (el) {
        el.style.color = el.dataset.balanceColor;
    });
    document.querySelectorAll('[data-border-color]').forEach(function (el) {
        el.style.borderColor = el.dataset.borderColor;
    });
    document.querySelectorAll('[data-height]').forEach(function (el) {
        el.style.height = el.dataset.height + '%';
        el.style.opacity = el.dataset.opacity;
    });
}());
</script>
</body>
</html>