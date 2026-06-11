<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Dashboard') — {{ $site_settings['business_name'] ?? 'Bacha Stylo' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ $site_settings['favicon_url'] ?? asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
    /* ─── TOKENS ─────────────────────────────────────────── */
    :root {
        --red:        #d92d20;
        --red-dk:     #b42318;
        --red-bg:     rgba(217,45,32,.10);
        --sb-bg:      #0d1726;
        --sb-panel:   rgba(255,255,255,.04);
        --sb-bd:      rgba(255,255,255,.08);
        --sb-w:       272px;
        --tb-h:       68px;
        --bg:         #eef2f6;
        --bg-accent:  #dbe6f3;
        --surf:       rgba(255,255,255,.88);
        --surf-solid: #ffffff;
        --surf2:      #f7f9fc;
        --surf3:      #edf2f7;
        --bd:         rgba(15,23,42,.08);
        --bd-strong:  rgba(15,23,42,.14);
        --bd2:        #cbd5e1;
        --t1:         #0f172a;
        --t2:         #334155;
        --t3:         #64748b;
        --t4:         #94a3b8;
        --txt:        var(--t1);
        --txt-2:      var(--t2);
        --txt-3:      var(--t3);
        --primary:    var(--red);
        --r:          14px;
        --rlg:        20px;
        --sh:         0 20px 60px rgba(15,23,42,.08);
        --sh-md:      0 28px 70px rgba(15,23,42,.12);
        --tr:         all .18s ease;
        --page-max:   1480px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html {
        background:
            radial-gradient(circle at top left, rgba(217,45,32,.10), transparent 26%),
            radial-gradient(circle at top right, rgba(59,130,246,.10), transparent 28%),
            linear-gradient(180deg, #f7fafc 0%, var(--bg) 40%, #edf2f7 100%);
    }

    body {
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        background: transparent;
        color: var(--t1);
        font-size: .875rem;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
        min-height: 100vh;
        overflow-x: hidden;
    }

    .admin-shell {
        min-height: 100vh;
        position: relative;
    }

    .admin-shell::before {
        content: '';
        position: fixed;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(135deg, rgba(255,255,255,.42), transparent 42%),
            linear-gradient(0deg, rgba(255,255,255,.12), rgba(255,255,255,.12));
        z-index: -2;
    }

    /* ─── SIDEBAR ───────────────────────────────────────── */
    .sidebar {
        position: fixed; top: 0; left: 0; bottom: 0;
        width: var(--sb-w);
        background:
            linear-gradient(180deg, rgba(255,255,255,.04), transparent 26%),
            linear-gradient(180deg, #0f172a 0%, var(--sb-bg) 100%);
        display: flex; flex-direction: column;
        z-index: 1050;
        transition: transform .25s cubic-bezier(.4,0,.2,1);
        overflow: hidden;
        border-right: 1px solid var(--sb-bd);
        box-shadow: 24px 0 60px rgba(2,6,23,.18);
    }

    .sb-logo {
        height: var(--tb-h);
        display: flex; align-items: center;
        padding: 0 18px;
        border-bottom: 1px solid var(--sb-bd);
        flex-shrink: 0;
        gap: 12px;
        background: rgba(255,255,255,.02);
    }
    .sb-logo a { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .sb-logo img { height: 32px; max-width: 160px; object-fit: contain; background: #fff; border-radius: 8px; padding: 4px 8px; }
    .sb-logo .brand-name {
        color: #fff; font-weight: 800; font-size: .92rem;
        letter-spacing: -.4px; white-space: nowrap;
    }
    .sb-logo .brand-dot { color: var(--red); }

    .sb-body {
        flex: 1; overflow-y: auto; padding: 10px 10px 16px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.06) transparent;
    }

    .sb-label {
        display: block;
        font-size: .56rem; font-weight: 800;
        letter-spacing: 1.6px; text-transform: uppercase;
        color: rgba(255,255,255,.28);
        padding: 14px 10px 6px;
    }

    .sb-list { list-style: none; padding: 0; margin: 0; }
    .sb-item { margin: 1px 0; }

    .sb-link {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 10px;
        color: rgba(255,255,255,.62);
        text-decoration: none;
        font-size: .78rem; font-weight: 700;
        border-radius: 12px;
        transition: var(--tr);
        position: relative;
        overflow: hidden;
        border: 1px solid transparent;
    }
    .sb-link:hover {
        color: rgba(255,255,255,.94);
        background: rgba(255,255,255,.06);
        border-color: rgba(255,255,255,.06);
        transform: translateX(2px);
    }
    .sb-link.active {
        color: #fff;
        background: linear-gradient(135deg, rgba(217,45,32,.24), rgba(255,255,255,.06));
        border-color: rgba(217,45,32,.22);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
    }
    .sb-link.active::before {
        content: ''; position: absolute; left: 0; top: 50%;
        transform: translateY(-50%);
        width: 3px; height: 20px;
        background: var(--red); border-radius: 0 3px 3px 0;
    }
    .sb-link i {
        font-size: .98rem; min-width: 18px;
        text-align: center; opacity: .6;
        transition: var(--tr); flex-shrink: 0;
    }
    .sb-link:hover i, .sb-link.active i { opacity: 1; color: var(--red); }

    .sb-foot {
        flex-shrink: 0; padding: 12px 10px 14px;
        border-top: 1px solid var(--sb-bd);
        background: rgba(255,255,255,.02);
    }
    .sb-logout {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 10px;
        color: rgba(255,255,255,.52);
        font-size: .78rem; font-weight: 700;
        border-radius: 12px; border: 1px solid transparent;
        background: none; width: 100%;
        cursor: pointer; transition: var(--tr);
        text-align: left;
    }
    .sb-logout:hover {
        color: rgba(255,255,255,.9);
        background: rgba(255,255,255,.06);
        border-color: rgba(255,255,255,.06);
    }
    .sb-logout i { font-size: 1.05rem; }

    /* ─── MAIN WRAP ─────────────────────────────────────── */
    .main-wrap {
        margin-left: var(--sb-w);
        min-height: 100vh;
        width: calc(100vw - var(--sb-w));
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-width: 0;
    }

    .page-shell {
        width: min(calc(100% - 32px), var(--page-max));
        margin: 0 auto;
    }

    /* ─── TOPBAR ────────────────────────────────────────── */
    .topbar {
        min-height: var(--tb-h);
        background: rgba(255,255,255,.56);
        backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(255,255,255,.65);
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 0;
        position: sticky; top: 0; z-index: 900;
        flex-shrink: 0;
    }
    .topbar .page-shell {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .topbar-left { display: flex; align-items: center; gap: 12px; }
    .topbar-right { display: flex; align-items: center; gap: 10px; }
    .page-title { font-size: .94rem; font-weight: 800; color: var(--t1); letter-spacing: -.02em; }

    .tb-icon-btn {
        width: 36px; height: 36px; border-radius: 12px;
        border: 1px solid rgba(255,255,255,.88); background: rgba(255,255,255,.78);
        color: var(--t3); cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none; transition: var(--tr);
        font-size: .92rem;
        box-shadow: 0 10px 30px rgba(15,23,42,.05);
    }
    .tb-icon-btn:hover { border-color: rgba(217,45,32,.18); color: var(--red); background: #fff; transform: translateY(-1px); }

    .tb-user {
        display: flex; align-items: center; gap: 8px;
        padding: 4px 10px 4px 4px;
        border-radius: 999px; border: 1px solid rgba(255,255,255,.88);
        background: rgba(255,255,255,.78); text-decoration: none;
        box-shadow: 0 10px 30px rgba(15,23,42,.05);
    }
    .tb-avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: linear-gradient(135deg, var(--red), #f97316);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: .68rem; font-weight: 800;
    }
    .tb-uname { font-size: .74rem; font-weight: 800; color: var(--t1); }

    .mobile-toggle {
        display: none; border: 1px solid rgba(255,255,255,.88); background: rgba(255,255,255,.78);
        color: var(--t3); width: 36px; height: 36px; border-radius: 12px;
        align-items: center; justify-content: center; cursor: pointer;
        box-shadow: 0 10px 30px rgba(15,23,42,.05);
    }

    /* ─── PAGE CONTENT ──────────────────────────────────── */
    .page-content { padding: 18px 0 28px; flex: 1; }

    /* ─── PAGE HEADER ───────────────────────────────────── */
    .ph,
    .page-header {
        display: flex; align-items: flex-start;
        justify-content: space-between; gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .ph h4,
    .page-header h4 {
        font-size: clamp(1.15rem, 1.8vw, 1.5rem);
        font-weight: 800;
        color: var(--t1);
        letter-spacing: -.04em;
        margin: 0;
        line-height: 1.1;
    }
    .ph-sub {
        font-size: .76rem;
        color: var(--t3);
        margin-top: 6px;
        max-width: 720px;
    }

    .page-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .side-stack {
        display: grid;
        gap: 16px;
    }

    .req {
        color: var(--red);
    }

    .table-link {
        color: var(--t1);
        font-weight: 800;
        text-decoration: none;
    }
    .table-link:hover {
        color: var(--red);
    }

    .text-muted {
        color: var(--t3) !important;
    }

    .text-strong {
        font-weight: 800 !important;
    }

    .surface-strip {
        background: var(--surf2);
    }

    .hero-banner {
        background: linear-gradient(135deg, rgba(15,23,42,.97), rgba(15,23,42,.86) 52%, rgba(217,45,32,.88) 100%);
        color: #fff;
        border-radius: 22px;
        padding: clamp(18px, 3vw, 26px);
        box-shadow: var(--sh);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .hero-banner::before {
        content: '';
        position: absolute;
        inset: auto -40px -60px auto;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,.18), transparent 68%);
    }
    .hero-banner > * { position: relative; z-index: 1; }
    .hero-kicker {
        font-size: .62rem;
        text-transform: uppercase;
        letter-spacing: .22em;
        color: rgba(255,255,255,.66);
        font-weight: 800;
        margin-bottom: 8px;
    }
    .hero-title {
        font-size: clamp(1.35rem, 2.2vw, 1.95rem);
        line-height: 1.02;
        font-weight: 800;
        letter-spacing: -.05em;
        margin: 0;
    }
    .hero-subtitle {
        max-width: 720px;
        font-size: .84rem;
        color: rgba(255,255,255,.78);
        margin-top: 8px;
    }
    .hero-meta {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }
    .hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.12);
        color: #fff;
        font-size: .74rem;
        font-weight: 700;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }
    .kpi-grid > * {
        grid-column: span 3;
    }

    /* ─── CARDS ─────────────────────────────────────────── */
    .bcard {
        background: var(--surf);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(255,255,255,.62);
        border-radius: var(--rlg);
        box-shadow: var(--sh);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .bcard-head {
        padding: 14px 18px;
        border-bottom: 1px solid var(--bd);
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .bcard-title { font-size: .74rem; font-weight: 800; color: var(--t1); text-transform: uppercase; letter-spacing: .11em; }
    .bcard-body { padding: 18px; }

    /* Bootstrap .card override */
    .card {
        background: var(--surf) !important;
        backdrop-filter: blur(14px);
        border: 1px solid rgba(255,255,255,.62) !important;
        border-radius: var(--rlg) !important;
        box-shadow: var(--sh) !important;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .card-header {
        background: rgba(247,249,252,.74) !important;
        border-bottom: 1px solid var(--bd) !important;
        padding: 14px 18px !important;
        display: flex; align-items: center; justify-content: space-between;
    }
    .card-header-title, .header-title {
        font-size: .74rem; font-weight: 800; color: var(--t1);
        text-transform: uppercase; letter-spacing: .12em;
        margin: 0;
    }
    .card-body { padding: 18px !important; }
    .card-footer {
        background: rgba(247,249,252,.74) !important;
        border-top: 1px solid var(--bd) !important;
        padding: 14px 18px !important;
    }

    /* ─── STAT CARDS ────────────────────────────────────── */
    .stat-card {
        background: linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.82));
        border: 1px solid rgba(255,255,255,.72);
        border-radius: 20px;
        padding: 18px;
        box-shadow: var(--sh);
        transition: var(--tr);
        position: relative; overflow: hidden;
        height: 100%;
    }
    .stat-card:hover { box-shadow: var(--sh-md); transform: translateY(-4px); }
    .stat-card::after {
        content: ''; position: absolute;
        bottom: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, var(--a1), var(--a2));
        opacity: 0; transition: var(--tr);
    }
    .stat-card:hover::after { opacity: 1; }
    .stat-icon {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; margin-bottom: 14px;
    }
    .stat-label { font-size: .64rem; font-weight: 800; color: var(--t4); text-transform: uppercase; letter-spacing: .12em; }
    .stat-value { font-size: clamp(1.5rem, 2vw, 1.9rem); font-weight: 800; color: var(--t1); letter-spacing: -0.05em; line-height: 1; margin-top: 4px; }
    .stat-sub { font-size: .7rem; color: var(--t3); margin-top: 6px; }

    /* ─── TABLES ────────────────────────────────────────── */
    .table-wrap {
        overflow-x: auto;
    }
    .table { width: 100%; border-collapse: collapse; }
    .table thead th {
        padding: 11px 16px;
        font-size: .62rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .14em;
        color: var(--t4); white-space: nowrap;
        background: rgba(247,249,252,.78);
        border-bottom: 1px solid var(--bd);
    }
    .table tbody td {
        padding: 13px 16px;
        border-bottom: 1px solid var(--bd);
        vertical-align: middle;
        color: var(--t1); font-size: .8rem;
    }
    .table tbody tr:last-child td { border-bottom: none; }
    .table tbody tr { transition: background .12s; }
    .table tbody tr:hover td { background: rgba(248,250,252,.92); }

    .table-stack td::before {
        content: attr(data-label);
        display: none;
    }

    /* ─── STATUS BADGES ─────────────────────────────────── */
    .status-badge {
        display: inline-flex; align-items: center;
        padding: 5px 9px; border-radius: 999px;
        font-size: .62rem; font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        border: 1px solid rgba(15,23,42,.05);
    }

    .soft-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 999px;
        background: var(--surf2);
        border: 1px solid var(--bd);
        color: var(--t2);
        font-size: .68rem;
        font-weight: 700;
    }

    /* ─── BUTTONS ───────────────────────────────────────── */
    .btn {
        display: inline-flex; align-items: center; gap: 5px;
        font-family: inherit; font-weight: 800; font-size: .76rem;
        border-radius: 12px; border: none; cursor: pointer;
        transition: var(--tr); text-decoration: none;
        line-height: 1; white-space: nowrap;
        padding: 9px 14px;
    }
    .btn-primary { background: linear-gradient(135deg, var(--red), #f97316); color: #fff; box-shadow: 0 12px 28px rgba(217,45,32,.22); }
    .btn-primary:hover { color: #fff; box-shadow: 0 18px 40px rgba(217,45,32,.28); transform: translateY(-1px); }
    .btn-light { background: rgba(255,255,255,.92); color: var(--t2); border: 1px solid var(--bd); box-shadow: 0 8px 24px rgba(15,23,42,.05); }
    .btn-light:hover { background: #fff; color: var(--t1); border-color: var(--bd-strong); }
    .btn-danger { background: #ef4444; color: #fff; }
    .btn-danger:hover { background: #dc2626; color: #fff; }
    .btn-outline-danger { background: transparent; color: #ef4444; border: 1px solid #ef4444; }
    .btn-outline-danger:hover { background: #ef4444; color: #fff; }
    .btn-sm { padding: 7px 10px; font-size: .68rem; border-radius: 10px; }
    .btn-lg { padding: 11px 18px; font-size: .82rem; }
    .btn-icon { padding: 0; width: 34px; height: 34px; justify-content: center; border-radius: 10px; }
    .btn-ghost-del { background: #fff1f2; color: #dc2626; border: 1px solid rgba(220,38,38,.12); padding: 6px 9px; border-radius: 10px; font-size: .68rem; cursor: pointer; transition: var(--tr); }
    .btn-ghost-del:hover { background: #ef4444; color: #fff; }
    .d-grid { display: grid; }

    .btn-library {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 7px 14px;
        font-family: inherit;
        font-size: .72rem;
        font-weight: 700;
        color: var(--red);
        background: linear-gradient(135deg, rgba(217,45,32,.06), rgba(249,115,22,.04));
        border: 1.5px solid rgba(217,45,32,.18);
        border-radius: 10px;
        cursor: pointer;
        transition: all .18s ease;
        text-decoration: none;
    }
    .btn-library:hover {
        background: linear-gradient(135deg, var(--red), #f97316);
        color: #fff;
        border-color: var(--red);
        box-shadow: 0 8px 24px rgba(217,45,32,.22);
        transform: translateY(-1px);
    }
    .btn-library i { font-size: .88rem; }

    .action-group {
        display: flex;
        gap: 6px;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
    }

    /* ─── FORMS ─────────────────────────────────────────── */
    .form-label {
        display: block;
        font-size: .68rem; font-weight: 800;
        color: var(--t2); margin-bottom: 6px;
        letter-spacing: .02em;
    }
    .form-control, .form-select {
        display: block; width: 100%;
        padding: 10px 12px;
        font-family: inherit; font-size: .82rem;
        color: var(--t1);
        background: rgba(255,255,255,.94);
        border: 1.5px solid rgba(148,163,184,.28);
        border-radius: 12px;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        -webkit-appearance: none;
        line-height: 1.5;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--red);
        box-shadow: 0 0 0 4px rgba(217,45,32,.10);
    }
    .form-control::placeholder { color: var(--bd2); }
    textarea.form-control { resize: vertical; min-height: 88px; }
    .form-check-input:checked { background-color: var(--red); border-color: var(--red); }
    .form-hint { font-size: .66rem; color: var(--t3); margin-top: 5px; }
    .form-group { margin-bottom: 14px; }
    .form-check-label {
        font-size: .76rem;
        font-weight: 600;
        color: var(--t2);
    }

    .section-stack {
        display: grid;
        gap: 14px;
    }

    .fieldset-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 14px;
    }
    .fieldset-grid > * {
        grid-column: span 12;
    }
    .field-span-6 {
        grid-column: span 6;
    }

    /* ─── ALERTS ────────────────────────────────────────── */
    .alert {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 14px; border-radius: 14px;
        font-size: .76rem; font-weight: 700;
        margin-bottom: 16px; border: none;
        box-shadow: 0 16px 40px rgba(15,23,42,.06);
    }
    .alert-success { background: #f0fdf4; color: #15803d; border-left: 3px solid #22c55e; }
    .alert-danger  { background: #fef2f2; color: #dc2626; border-left: 3px solid #ef4444; }
    .alert i { flex-shrink: 0; font-size: 1rem; }
    .alert .btn-close { margin-left: auto; padding: 0; }

    /* ─── PAGINATION ────────────────────────────────────── */
    .pagination { margin: 0; gap: 3px; }
    .page-link { border-radius: 10px !important; border: 1px solid var(--bd); color: var(--t2); font-size: .72rem; padding: 6px 10px; background: rgba(255,255,255,.92); }
    .page-link:hover { background: #fff; color: var(--t1); }
    .page-item.active .page-link { background: var(--red); border-color: var(--red); }

    /* ─── SECTION DIVIDER ───────────────────────────────── */
    .section-label {
        font-size: .6rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .14em;
        color: var(--t4); margin-bottom: 10px; margin-top: 6px;
    }

    .entity {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    .entity-thumb,
    .entity-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--surf3);
        border: 1px solid var(--bd);
        color: var(--t4);
        overflow: hidden;
    }
    .entity-avatar {
        border-radius: 50%;
        font-weight: 800;
        color: #fff;
        background: linear-gradient(135deg, #0f172a, #334155);
    }
    .entity-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .entity-copy {
        min-width: 0;
    }
    .entity-title {
        font-weight: 800;
        color: var(--t1);
        line-height: 1.3;
        font-size: .84rem;
    }
    .entity-meta {
        font-size: .68rem;
        color: var(--t3);
        margin-top: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .empty-state {
        padding: 38px 20px;
        text-align: center;
        color: var(--t3);
    }
    .empty-state i {
        display: block;
        font-size: 2.2rem;
        opacity: .35;
        margin-bottom: 12px;
    }
    .empty-state strong {
        display: block;
        color: var(--t1);
        margin-bottom: 6px;
        font-size: .9rem;
    }

    .metric-list {
        display: grid;
        gap: 10px;
    }
    .metric-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 10px 12px;
        border-radius: 14px;
        background: rgba(247,249,252,.84);
        border: 1px solid var(--bd);
    }
    .metric-row-label {
        color: var(--t3);
        font-size: .74rem;
        font-weight: 700;
    }
    .metric-row-value {
        color: var(--t1);
        font-size: .82rem;
        font-weight: 800;
        text-align: right;
    }

    .detail-grid {
        display: grid;
        gap: 14px;
    }
    .detail-item {
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(247,249,252,.84);
        border: 1px solid var(--bd);
    }
    .detail-label {
        font-size: .62rem;
        text-transform: uppercase;
        letter-spacing: .14em;
        color: var(--t4);
        font-weight: 800;
        margin-bottom: 6px;
    }
    .detail-value {
        color: var(--t1);
        font-weight: 700;
        line-height: 1.45;
    }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 14px;
    }
    .media-card {
        background: rgba(255,255,255,.82);
        border: 1px solid rgba(255,255,255,.72);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--sh);
    }
    .media-preview {
        aspect-ratio: 1;
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
    }
    .media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 14px;
    }
    .media-meta {
        padding: 12px;
    }
    .media-name {
        font-size: .74rem;
        font-weight: 800;
        color: var(--t1);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .media-size {
        font-size: .66rem;
        color: var(--t3);
        margin-top: 4px;
    }

    .modal-shell {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,.54);
        z-index: 2000;
        padding: 16px;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
    }
    .modal-panel {
        background: #fff;
        border-radius: 20px;
        padding: 22px;
        width: min(480px, 100%);
        box-shadow: 0 40px 90px rgba(15,23,42,.24);
    }
    .modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
    }
    .modal-head h6 {
        margin: 0;
        font-size: .92rem;
        font-weight: 800;
        color: var(--t1);
    }

    .preview-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 12px;
        padding: 12px;
        background: rgba(247,249,252,.84);
        border: 1px solid var(--bd);
        border-radius: 14px;
    }
    .preview-box img {
        max-height: 42px;
        object-fit: contain;
    }

    /* ─── OVERLAY (mobile) ───────────────────────────── */
    .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1040; }
    .overlay.show { display: block; }

    @media (max-width: 1200px) {
        .kpi-grid > * {
            grid-column: span 6;
        }
    }

    @media (max-width: 992px) {
        :root {
            --sb-w: 268px;
            --tb-h: 64px;
        }

        .page-shell {
            width: min(calc(100% - 24px), var(--page-max));
        }

        .field-span-6 {
            grid-column: span 12;
        }
    }

    @media (max-width: 768px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        .main-wrap { margin-left: 0; width: 100%; }
        .mobile-toggle { display: flex; }
        .topbar {
            padding: 10px 0;
        }
        .page-content {
            padding: 16px 0 24px;
        }
        .page-title {
            font-size: .88rem;
        }
        .tb-uname {
            display: none;
        }
        .hero-banner {
            border-radius: 18px;
        }
        .kpi-grid > * {
            grid-column: span 12;
        }
        .bcard-head,
        .bcard-body,
        .card-header,
        .card-body,
        .card-footer {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }
        .table-stack thead {
            display: none;
        }
        .table-stack,
        .table-stack tbody,
        .table-stack tr,
        .table-stack td {
            display: block;
            width: 100%;
        }
        .table-stack tr {
            padding: 14px;
            border-bottom: 1px solid var(--bd);
        }
        .table-stack tbody tr:last-child {
            border-bottom: none;
        }
        .table-stack td {
            padding: 0;
            border-bottom: none;
        }
        .table-stack td + td {
            margin-top: 10px;
        }
        .table-stack td::before {
            display: block;
            margin-bottom: 4px;
            color: var(--t4);
            font-size: .68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .14em;
        }
        .table-stack td[data-label="Actions"]::before {
            display: none;
        }
        .table-stack td[data-label="Actions"] {
            margin-top: 14px;
        }
        .action-group {
            justify-content: flex-start;
        }
    }
    </style>
    @stack('styles')
</head>
<body>
<div class="admin-shell">

{{-- ──── SIDEBAR ──────────────────────────────────── --}}
<nav class="sidebar" id="sidebar">
    <div class="sb-logo">
        <a href="{{ route('admin.dashboard') }}">
            @if(!empty($site_settings['logo_url']))
                <img src="{{ $site_settings['logo_url'] }}" alt="Logo">
            @else
                <span class="brand-name">Bacha<span class="brand-dot">·</span>Stylo</span>
            @endif
        </a>
    </div>

    <div class="sb-body">
        <span class="sb-label">Main</span>
        <ul class="sb-list">
            <li class="sb-item">
                <a href="{{ route('admin.dashboard') }}" class="sb-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="mdi mdi-view-dashboard-outline"></i> Dashboard
                </a>
            </li>
        </ul>

        <span class="sb-label">Ecommerce</span>
        <ul class="sb-list">
            <li class="sb-item">
                <a href="{{ route('admin.products.index') }}" class="sb-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                    <i class="mdi mdi-package-variant-closed"></i> Products
                </a>
            </li>
            <li class="sb-item">
                <a href="{{ route('admin.categories.index') }}" class="sb-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                    <i class="mdi mdi-tag-multiple-outline"></i> Categories
                </a>
            </li>
            <li class="sb-item">
                <a href="{{ route('admin.orders.index') }}" class="sb-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                    <i class="mdi mdi-shopping-outline"></i> Orders
                </a>
            </li>
            <li class="sb-item">
                <a href="{{ route('admin.customers.index') }}" class="sb-link {{ request()->routeIs('admin.customers*') ? 'active' : '' }}">
                    <i class="mdi mdi-account-group-outline"></i> Customers
                </a>
            </li>
            <li class="sb-item">
                <a href="{{ route('admin.abandoned-carts.index') }}" class="sb-link {{ request()->routeIs('admin.abandoned-carts*') ? 'active' : '' }}">
                    <i class="mdi mdi-cart-remove"></i> Abandoned Carts
                </a>
            </li>
        </ul>

        <span class="sb-label">Content</span>
        <ul class="sb-list">
            <li class="sb-item">
                <a href="{{ route('admin.blog.index') }}" class="sb-link {{ request()->routeIs('admin.blog*') ? 'active' : '' }}">
                    <i class="mdi mdi-post-outline"></i> Blog Posts
                </a>
            </li>
            <li class="sb-item">
                <a href="{{ route('admin.media.index') }}" class="sb-link {{ request()->routeIs('admin.media*') ? 'active' : '' }}">
                    <i class="mdi mdi-image-multiple-outline"></i> Media Library
                </a>
            </li>
            <li class="sb-item">
                <a href="{{ route('admin.newsletter.index') }}" class="sb-link {{ request()->routeIs('admin.newsletter*') ? 'active' : '' }}">
                    <i class="mdi mdi-email-newsletter"></i> Newsletter
                </a>
            </li>
            <li class="sb-item">
                <a href="{{ route('admin.reviews.index') }}" class="sb-link {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">
                    <i class="mdi mdi-star-outline"></i> Reviews
                </a>
            </li>
        </ul>

        <span class="sb-label">System</span>
        <ul class="sb-list">
            <li class="sb-item">
                <a href="{{ route('admin.settings.index') }}" class="sb-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <i class="mdi mdi-cog-outline"></i> Settings
                </a>
            </li>
            <li class="sb-item">
                <a href="{{ route('admin.seo.index') }}" class="sb-link {{ request()->routeIs('admin.seo*') ? 'active' : '' }}">
                    <i class="mdi mdi-web"></i> SEO
                </a>
            </li>
            <li class="sb-item">
                <a href="{{ route('admin.api-reference.index') }}" class="sb-link {{ request()->routeIs('admin.api-reference*') ? 'active' : '' }}">
                    <i class="mdi mdi-api"></i> API Reference
                </a>
            </li>
        </ul>
    </div>

    <div class="sb-foot">
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST">@csrf</form>
        <button class="sb-logout" onclick="document.getElementById('logout-form').submit()">
            <i class="mdi mdi-logout-variant"></i> Sign Out
        </button>
    </div>
</nav>

<div class="overlay" id="overlay" onclick="closeSB()"></div>

{{-- ──── MAIN ─────────────────────────────────────── --}}
<div class="main-wrap" id="mainWrap">

    {{-- TOPBAR --}}
    <header class="topbar">
        <div class="page-shell">
            <div class="topbar-left">
                <button class="mobile-toggle" id="burgerBtn" onclick="toggleSB()">
                    <i class="mdi mdi-menu" style="font-size:1.1rem;"></i>
                </button>
                <span class="page-title">Admin Panel</span>
            </div>
            <div class="topbar-right">
                <button class="tb-icon-btn" id="syncBtn" onclick="syncNow()" title="Sync to Frontend" style="position:relative;">
                    <i class="mdi mdi-sync" id="syncIcon"></i>
                </button>
                <a class="tb-icon-btn" href="{{ route('admin.dashboard') }}" title="Dashboard">
                    <i class="mdi mdi-home-outline"></i>
                </a>
                <div class="tb-user">
                    <div class="tb-avatar">{{ strtoupper(substr(Auth::guard('admin')->user()->username ?? 'A', 0, 1)) }}</div>
                    <span class="tb-uname">{{ Auth::guard('admin')->user()->username ?? 'Admin' }}</span>
                </div>
            </div>
<script>
function syncNow() {
    var btn = document.getElementById('syncBtn');
    var icon = document.getElementById('syncIcon');
    btn.disabled = true;
    icon.style.animation = 'spin 0.6s linear infinite';
    fetch('{{ route("admin.sync") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        icon.style.animation = '';
        btn.disabled = false;
        btn.style.borderColor = '#22c55e';
        btn.style.color = '#22c55e';
        setTimeout(() => { btn.style.borderColor = ''; btn.style.color = ''; }, 2000);
    })
    .catch(() => { icon.style.animation = ''; btn.disabled = false; });
}
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        </div>
    </header>

    {{-- CONTENT --}}
    <main class="page-content">
        <div class="page-shell">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="mdi mdi-check-circle-outline"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    {{ $errors->first() }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSB() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}
function closeSB() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
}
</script>
@stack('scripts')
</body>
</html>
