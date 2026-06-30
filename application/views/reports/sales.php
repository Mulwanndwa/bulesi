<?php
$m_labels  = json_encode(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']);
$m_revenue = json_encode(array_map(fn($m) => (float)$m->revenue,      $monthly));
$m_vat     = json_encode(array_map(fn($m) => (float)$m->vat_collected, $monthly));
$m_counts  = json_encode(array_map(fn($m) => (int)$m->sales_count,    $monthly));

$max_user    = !empty($by_user)    ? max(array_map(fn($u) => (float)$u->total_revenue, $by_user))    : 1;
$max_company = !empty($by_company) ? max(array_map(fn($c) => (float)$c->total_revenue, $by_company)) : 1;

$u_labels   = json_encode(array_map(fn($u) => $u->username,      $by_user));
$u_revenues = json_encode(array_map(fn($u) => (float)$u->total_revenue, $by_user));
?>

<!-- ── Toolbar ─────────────────────────────────────────────────────── -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 no-print">
    <div class="d-flex flex-wrap gap-1">
        <?php foreach ($periods as $key => $label): ?>
        <a href="<?= base_url('reports/sales/' . $key) ?>"
           class="btn btn-sm <?= $period === $key ? 'btn-dark' : 'btn-outline-secondary' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <input type="date" id="cs" class="form-control form-control-sm" value="<?= $period === 'custom' ? $start : '' ?>" style="width:140px">
        <span class="text-muted small">to</span>
        <input type="date" id="ce" class="form-control form-control-sm" value="<?= $period === 'custom' ? $end   : '' ?>" style="width:140px">
        <button class="btn btn-sm btn-primary" onclick="applyCustom()">Apply</button>
        <button class="btn btn-sm btn-light" onclick="window.print()"><i class="bi bi-printer"></i></button>
    </div>
</div>

<p class="text-muted small mb-3">
    Showing completed &amp; invoiced sales: <strong><?= date('d M Y', strtotime($start)) ?></strong> — <strong><?= date('d M Y', strtotime($end)) ?></strong>
    &nbsp;|&nbsp; Monthly chart: <strong><?= $year ?></strong>
</p>

<!-- ── KPI Cards ───────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php
    $avg = $kpis->total_sales > 0 ? $kpis->total_revenue / $kpis->total_sales : 0;
    $cards = [
        ['val' => (int)$kpis->total_sales,                              'lbl' => 'Total Sales',        'sub' => 'completed + invoiced',   'icon' => 'patch-check-fill',   'color' => '#198754'],
        ['val' => 'R '.number_format($kpis->total_revenue, 2),          'lbl' => 'Total Revenue',       'sub' => 'incl. VAT',              'icon' => 'cash-stack',         'color' => '#0d5c0d'],
        ['val' => 'R '.number_format($kpis->total_subtotal, 2),         'lbl' => 'Revenue excl. VAT',   'sub' => 'subtotal before VAT',    'icon' => 'receipt',            'color' => '#6f42c1'],
        ['val' => 'R '.number_format($kpis->total_vat, 2),              'lbl' => 'VAT Collected',       'sub' => '15% VAT component',      'icon' => 'percent',            'color' => '#fd7e14'],
        ['val' => 'R '.number_format($avg, 2),                          'lbl' => 'Avg Sale Value',      'sub' => 'revenue ÷ sales',        'icon' => 'graph-up',           'color' => '#20c997'],
        ['val' => (int)$kpis->unique_customers,                         'lbl' => 'Unique Customers',    'sub' => 'distinct customer names', 'icon' => 'people-fill',        'color' => '#e94560'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px"><?= $c['lbl'] ?></span>
                    <i class="bi bi-<?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;font-size:1.1rem;opacity:.7"></i>
                </div>
                <div style="font-size:1.35rem;font-weight:800;color:<?= $c['color'] ?>;line-height:1"><?= $c['val'] ?></div>
                <div class="text-muted mt-1" style="font-size:.7rem"><?= $c['sub'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Revenue Chart + Sales by User ───────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart-fill me-2 text-muted"></i>Monthly Sales Revenue — <?= $year ?></div>
            <div class="card-body">
                <canvas id="revenueChart" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart-steps me-2 text-muted"></i>Revenue by Salesperson</div>
            <div class="card-body p-0">
                <?php if (empty($by_user)): ?>
                <p class="text-muted text-center py-4">No sales data for this period.</p>
                <?php else: ?>
                <canvas id="userChart" style="max-height:260px;padding:16px"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── By Salesperson Table + By Company ───────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person-badge-fill me-2 text-muted"></i>Sales by Salesperson</div>
            <div class="card-body p-0">
                <?php if (empty($by_user)): ?>
                <p class="text-muted text-center py-4">No data for this period.</p>
                <?php else: ?>
                <table class="table mb-0" style="font-size:.85rem">
                    <thead><tr>
                        <th>Salesperson</th>
                        <th class="text-center">Sales</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Avg</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($by_user as $u): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($u->username) ?></div>
                                <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($u->group_name) ?></div>
                                <div class="mt-1">
                                    <div class="bg-light rounded" style="height:4px;overflow:hidden">
                                        <div class="rounded" style="height:4px;width:<?= min(100, round($u->total_revenue / $max_user * 100)) ?>%;background:#198754"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center"><?= $u->sales_count ?></td>
                            <td class="text-end fw-semibold">R <?= number_format($u->total_revenue, 2) ?></td>
                            <td class="text-end text-muted">R <?= number_format($u->avg_value, 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-building me-2 text-muted"></i>Sales by Company</div>
            <div class="card-body p-0">
                <?php if (empty($by_company)): ?>
                <p class="text-muted text-center py-4">No data for this period.</p>
                <?php else: ?>
                <table class="table mb-0" style="font-size:.85rem">
                    <thead><tr>
                        <th>Company</th>
                        <th class="text-center">Sales</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Avg</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($by_company as $c): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($c->company_name) ?></div>
                                <div class="mt-1">
                                    <div class="bg-light rounded" style="height:4px;overflow:hidden">
                                        <div class="rounded" style="height:4px;width:<?= min(100, round($c->total_revenue / $max_company * 100)) ?>%;background:#0d5c0d"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center"><?= $c->sales_count ?></td>
                            <td class="text-end fw-semibold">R <?= number_format($c->total_revenue, 2) ?></td>
                            <td class="text-end text-muted">R <?= number_format($c->avg_value, 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Completed Sales List ─────────────────────────────────────────── -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-check me-2 text-muted"></i>Completed Sales (latest 50)</span>
        <span class="badge bg-success-subtle text-success border border-success-subtle"><?= count($sales_list) ?> records</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($sales_list)): ?>
        <p class="text-muted text-center py-4">No completed sales in this period.</p>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="table table-hover mb-0" style="font-size:.83rem">
            <thead><tr>
                <th>Quote #</th>
                <th>Customer</th>
                <th>Sold By</th>
                <th>Company</th>
                <th class="text-end">Subtotal</th>
                <th class="text-end">VAT</th>
                <th class="text-end">Total</th>
                <th class="text-center">Status</th>
                <th>Date</th>
            </tr></thead>
            <tbody>
                <?php foreach ($sales_list as $s): ?>
                <tr>
                    <td class="fw-semibold text-primary"><?= htmlspecialchars($s->quote_number) ?></td>
                    <td>
                        <div><?= htmlspecialchars($s->customer_name) ?></div>
                        <?php if ($s->customer_phone): ?>
                        <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($s->customer_phone) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($s->sold_by) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($s->company_name) ?></td>
                    <td class="text-end">R <?= number_format($s->subtotal, 2) ?></td>
                    <td class="text-end text-muted">R <?= number_format($s->vat_amount, 2) ?></td>
                    <td class="text-end fw-semibold">R <?= number_format($s->total, 2) ?></td>
                    <td class="text-center">
                        <span class="s-badge s-<?= $s->status ?>"><?= ucfirst($s->status) ?></span>
                    </td>
                    <td class="text-muted"><?= date('d M Y', strtotime($s->quote_date)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="4" class="fw-semibold text-end">Totals</td>
                    <td class="text-end fw-semibold">R <?= number_format($kpis->total_subtotal, 2) ?></td>
                    <td class="text-end fw-semibold">R <?= number_format($kpis->total_vat, 2) ?></td>
                    <td class="text-end fw-bold text-success">R <?= number_format($kpis->total_revenue, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Print summary ────────────────────────────────────────────────── -->
<div class="card mb-4 d-none d-print-block">
    <div class="card-header fw-bold">Sales Report: <?= date('d M Y', strtotime($start)) ?> — <?= date('d M Y', strtotime($end)) ?></div>
    <div class="card-body">
        <table class="table table-bordered" style="font-size:.85rem">
            <tr><td>Total Sales</td><td class="fw-bold"><?= $kpis->total_sales ?></td>
                <td>Unique Customers</td><td class="fw-bold"><?= $kpis->unique_customers ?></td></tr>
            <tr><td>Total Revenue (incl. VAT)</td><td class="fw-bold text-success">R <?= number_format($kpis->total_revenue, 2) ?></td>
                <td>Revenue excl. VAT</td><td class="fw-bold">R <?= number_format($kpis->total_subtotal, 2) ?></td></tr>
            <tr><td>VAT Collected</td><td class="fw-bold">R <?= number_format($kpis->total_vat, 2) ?></td>
                <td>Avg Sale Value</td><td class="fw-bold">R <?= number_format($avg, 2) ?></td></tr>
        </table>
    </div>
</div>

<!-- ── Chart.js ─────────────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function() {
    // Monthly revenue chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: <?= $m_labels ?>,
            datasets: [
                {
                    label: 'Revenue (R)',
                    data: <?= $m_revenue ?>,
                    backgroundColor: 'rgba(25,135,84,0.75)',
                    borderColor: '#198754',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'yValue',
                    order: 2
                },
                {
                    label: 'VAT (R)',
                    data: <?= $m_vat ?>,
                    backgroundColor: 'rgba(253,126,20,0.55)',
                    borderColor: '#fd7e14',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'yValue',
                    order: 3
                },
                {
                    label: 'Sales Count',
                    data: <?= $m_counts ?>,
                    type: 'line',
                    borderColor: '#0d5c0d',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 4,
                    tension: 0.4,
                    yAxisID: 'yCount',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { padding: 14, boxWidth: 12 } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.yAxisID === 'yValue'
                                ? ctx.dataset.label + ': R ' + ctx.parsed.y.toLocaleString('en-ZA', {minimumFractionDigits:2})
                                : ctx.dataset.label + ': ' + ctx.parsed.y;
                        }
                    }
                }
            },
            scales: {
                yValue: {
                    type: 'linear', position: 'left',
                    ticks: { color: '#198754', callback: function(v) { return 'R ' + v.toLocaleString(); } },
                    title: { display: true, text: 'Revenue (R)', color: '#198754' },
                    grid: { color: 'rgba(0,0,0,.06)' }
                },
                yCount: {
                    type: 'linear', position: 'right',
                    ticks: { stepSize: 1, color: '#0d5c0d' },
                    title: { display: true, text: 'Count', color: '#0d5c0d' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    <?php if (!empty($by_user)): ?>
    // Salesperson bar chart
    new Chart(document.getElementById('userChart'), {
        type: 'bar',
        data: {
            labels: <?= $u_labels ?>,
            datasets: [{
                label: 'Revenue (R)',
                data: <?= $u_revenues ?>,
                backgroundColor: 'rgba(25,135,84,0.7)',
                borderColor: '#198754',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return 'R ' + ctx.parsed.x.toLocaleString('en-ZA', {minimumFractionDigits:2});
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: { callback: function(v) { return 'R ' + v.toLocaleString(); } },
                    grid: { color: 'rgba(0,0,0,.06)' }
                },
                y: { grid: { display: false } }
            }
        }
    });
    <?php endif; ?>

    window.applyCustom = function() {
        var s = document.getElementById('cs').value;
        var e = document.getElementById('ce').value;
        if (s && e) window.location.href = '<?= base_url('reports/sales/custom/') ?>' + s + '/' + e;
    };
})();
</script>

<style>
@media print {
    .no-print { display: none !important; }
    canvas { max-width: 100% !important; }
    .d-print-block { display: block !important; }
}
</style>
