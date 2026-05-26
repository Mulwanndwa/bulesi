<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' — Bulesi' : 'Bulesi' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root { --sb-w: 230px; --sb-bg: #12121f; --accent: #e94560; }
        body { background: #f2f4f8; font-size: .9rem; }

        /* ── Sidebar ── */
        #sidebar {
            width: var(--sb-w); min-height: 100vh; background: var(--sb-bg);
            position: fixed; top: 0; left: 0;
            display: flex; flex-direction: column; z-index: 200;
        }
        .sb-brand {
            padding: 18px 20px; color: #fff; font-size: 1.05rem; font-weight: 700;
            display: flex; align-items: center; gap: 10px; text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,.07);
            letter-spacing: .3px;
        }
        .sb-brand:hover { color: #fff; }
        .sb-brand i { color: var(--accent); font-size: 1.2rem; }
        .sb-label {
            font-size: .62rem; text-transform: uppercase; letter-spacing: 1.3px;
            color: rgba(255,255,255,.28); padding: 14px 20px 5px;
        }
        .sb-link {
            color: rgba(255,255,255,.6); padding: 9px 20px;
            display: flex; align-items: center; gap: 10px;
            font-size: .87rem; text-decoration: none;
            border-left: 3px solid transparent; transition: all .12s;
        }
        .sb-link:hover  { color: rgba(255,255,255,.9); background: rgba(255,255,255,.05); }
        .sb-link.active { color: #fff; background: rgba(255,255,255,.08); border-left-color: var(--accent); }
        .sb-bottom {
            margin-top: auto; padding: 14px 20px;
            border-top: 1px solid rgba(255,255,255,.07);
        }
        .sb-user { color: rgba(255,255,255,.5); font-size: .8rem; margin-bottom: 10px; }

        /* ── Main ── */
        #main { margin-left: var(--sb-w); min-height: 100vh; display: flex; flex-direction: column; }
        #topbar {
            background: #fff; border-bottom: 1px solid #e8edf2;
            padding: 0 26px; height: 54px; position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
        }
        .page-wrap { padding: 22px 26px; flex: 1; }

        /* ── Cards ── */
        .card { border: none; box-shadow: 0 1px 10px rgba(0,0,0,.07); border-radius: 10px; }
        .card-header { background: #fff; border-bottom: 1px solid #f0f2f5; font-weight: 600; padding: 14px 18px; }
        .card-body { padding: 18px; }

        /* ── Stat cards ── */
        .stat-card { border-radius: 10px; padding: 18px 20px; color: #fff; }
        .stat-val  { font-size: 1.9rem; font-weight: 800; line-height: 1; }
        .stat-lbl  { font-size: .75rem; opacity: .8; margin-top: 4px; }

        /* ── Tables ── */
        .table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: #8896a4; font-weight: 600; border-bottom-width: 1px; }
        .table td { vertical-align: middle; }

        /* ── Status badges ── */
        .s-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.72rem; font-weight:600; letter-spacing:.3px; }
        .s-draft       { background:#e9ecef; color:#555; }
        .s-sent        { background:#cff4fc; color:#087990; }
        .s-accepted    { background:#cfe2ff; color:#0a58ca; }
        .s-in_progress { background:#fff3cd; color:#856404; }
        .s-completed   { background:#d1e7dd; color:#0a5132; }
        .s-invoiced    { background:#212529; color:#fff; }
        .s-rejected    { background:#f8d7da; color:#842029; }
        .s-cancelled   { background:#dee2e6; color:#495057; }

        @media print {
            #sidebar, #topbar, .no-print { display: none !important; }
            #main { margin-left: 0 !important; }
            .page-wrap { padding: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div id="sidebar">
    <a href="<?= base_url('dashboard') ?>" class="sb-brand">
        <i class="bi bi-hammer"></i> Bulesi
    </a>
    <nav>
        <div class="sb-label">Overview</div>
        <a href="<?= base_url('dashboard') ?>" class="sb-link <?= $this->uri->segment(1)==='dashboard'?'active':'' ?>">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
        <div class="sb-label">Quotations</div>
        <a href="<?= base_url('quotation') ?>" class="sb-link <?= ($this->uri->segment(1)==='quotation' && $this->uri->segment(2)!=='create')?'active':'' ?>">
            <i class="bi bi-file-earmark-text-fill"></i> All Quotes
        </a>
        <a href="<?= base_url('quotation/create') ?>" class="sb-link <?= $this->uri->segment(2)==='create'?'active':'' ?>">
            <i class="bi bi-plus-square-fill"></i> New Quote
        </a>
        <?php if (in_array($user['group_name'] ?? '', ['Admin','Manager'])): ?>
        <a href="<?= base_url('quotation_types') ?>" class="sb-link <?= $this->uri->segment(1)==='quotation_types'?'active':'' ?>">
            <i class="bi bi-tags-fill"></i> Quote Types
        </a>
        <?php endif; ?>
        <div class="sb-label">Inventory</div>
        <a href="<?= base_url('stock') ?>" class="sb-link <?= $this->uri->segment(1)==='stock'?'active':'' ?>">
            <i class="bi bi-boxes"></i> Stock
        </a>
        <div class="sb-label">Analytics</div>
        <a href="<?= base_url('reports') ?>" class="sb-link <?= $this->uri->segment(1)==='reports'?'active':'' ?>">
            <i class="bi bi-bar-chart-line-fill"></i> Reports
        </a>
        <?php if (($user['group_name'] ?? '') === 'Admin'): ?>
        <div class="sb-label">System</div>
        <a href="<?= base_url('users') ?>" class="sb-link <?= $this->uri->segment(1)==='users'?'active':'' ?>">
            <i class="bi bi-people-fill"></i> Users
        </a>
        <?php endif; ?>
    </nav>
    <div class="sb-bottom">
        <div class="sb-user">
            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['username']) ?>
            <span class="d-block mt-1" style="font-size:.68rem;color:rgba(255,255,255,.3);padding-left:18px">
                <?= htmlspecialchars($user['group_name'] ?? '') ?>
            </span>
        </div>
        <a href="<?= base_url('auth/logout') ?>" class="btn btn-sm btn-outline-light w-100">
            <i class="bi bi-box-arrow-right"></i> Sign Out
        </a>
    </div>
</div>

<!-- Main -->
<div id="main">
    <div id="topbar">
        <span class="fw-semibold text-dark"><?= isset($title) ? htmlspecialchars($title) : '' ?></span>
        <span class="text-muted" style="font-size:.8rem"><?= date('D, d M Y') ?></span>
    </div>
    <div class="page-wrap">

    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
        <i class="bi bi-check-circle-fill me-1"></i><?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
        <i class="bi bi-exclamation-triangle-fill me-1"></i><?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
