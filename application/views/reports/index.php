<?php
// ── Prepare chart data ────────────────────────────────────────────────────────
$m_labels   = json_encode(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']);
$m_counts   = json_encode(array_map(fn($m) => (int)$m->quote_count,    $monthly));
$m_values   = json_encode(array_map(fn($m) => (float)$m->total_value,  $monthly));
$m_accepted = json_encode(array_map(fn($m) => (float)$m->accepted_value, $monthly));

$st_labels  = json_encode(array_map(fn($r) => $statuses[$r->status]['label'] ?? $r->status, $by_status));
$st_counts  = json_encode(array_map(fn($r) => (int)$r->count,                               $by_status));
$st_colors  = json_encode(array_map(fn($r) => $statuses[$r->status]['color'] ?? '#999',     $by_status));

$max_customer = !empty($top_customers) ? max(array_map(fn($c) => (float)$c->total_value, $top_customers)) : 1;
$max_item     = !empty($popular_items) ? max(array_map(fn($i) => (float)$i->total_value, $popular_items)) : 1;
?>

<!-- ── Toolbar ─────────────────────────────────────────────────── -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 no-print">
    <div class="d-flex flex-wrap gap-1">
        <?php foreach ($periods as $key => $label): ?>
        <a href="<?= base_url('reports/index/' . $key) ?>"
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

<!-- Period label -->
<p class="text-muted small mb-3">
    Showing: <strong><?= date('d M Y', strtotime($start)) ?></strong> — <strong><?= date('d M Y', strtotime($end)) ?></strong>
    &nbsp;|&nbsp; Monthly chart: <strong><?= $year ?></strong>
</p>

<!-- ── KPI Cards ───────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php
    $kpi_cards = [
        ['val' => $kpis->total_quotes,   'lbl' => 'Total Quotations',    'sub' => 'in period',               'icon' => 'file-earmark-text', 'color' => '#0d6efd'],
        ['val' => 'R '.number_format($kpis->total_value, 2),    'lbl' => 'Total Quote Value',     'sub' => 'gross value quoted',      'icon' => 'cash-stack',        'color' => '#6f42c1'],
        ['val' => 'R '.number_format($kpis->accepted_value, 2), 'lbl' => 'Accepted / Won',        'sub' => 'accepted + in progress + completed + invoiced', 'icon' => 'check-circle',      'color' => '#198754'],
        ['val' => 'R '.number_format($kpis->pipeline_value, 2), 'lbl' => 'In Pipeline',           'sub' => 'draft + sent',            'icon' => 'arrow-right-circle', 'color' => '#fd7e14'],
        ['val' => 'R '.number_format($kpis->completed_value,2), 'lbl' => 'Completed / Invoiced',  'sub' => 'actual revenue',          'icon' => 'patch-check',       'color' => '#20c997'],
        ['val' => $kpis->conversion_rate.'%',                   'lbl' => 'Win Rate',              'sub' => 'accepted ÷ total',        'icon' => 'graph-up-arrow',    'color' => '#e94560'],
    ];
    foreach ($kpi_cards as $c): ?>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px"><?= $c['lbl'] ?></span>
                    <i class="bi bi-<?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;font-size:1.1rem;opacity:.7"></i>
                </div>
                <div style="font-size:1.4rem;font-weight:800;color:<?= $c['color'] ?>;line-height:1"><?= $c['val'] ?></div>
                <div class="text-muted mt-1" style="font-size:.7rem"><?= $c['sub'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Monthly Chart + Status Donut ────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart-fill me-2 text-muted"></i>Monthly Volume &amp; Revenue — <?= $year ?></span>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart-fill me-2 text-muted"></i>Status Distribution</div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <?php if (empty($by_status)): ?>
                <p class="text-muted text-center">No data for this period.</p>
                <?php else: ?>
                <canvas id="statusChart" style="max-height:220px"></canvas>
                <div class="mt-3 w-100" style="font-size:.78rem">
                    <?php foreach ($by_status as $row): ?>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>
                            <span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:<?= $statuses[$row->status]['color'] ?? '#999' ?>"></span>
                            <?= $statuses[$row->status]['label'] ?? $row->status ?>
                        </span>
                        <span class="fw-semibold"><?= $row->count ?> &nbsp;<span class="text-muted fw-normal">(R <?= number_format($row->total_value, 0) ?>)</span></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Top Customers + Popular Items ───────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-people-fill me-2 text-muted"></i>Top Customers</div>
            <div class="card-body p-0">
                <?php if (empty($top_customers)): ?>
                <p class="text-muted text-center py-4">No data for this period.</p>
                <?php else: ?>
                <table class="table mb-0" style="font-size:.85rem">
                    <thead><tr>
                        <th>Customer</th>
                        <th class="text-center">Quotes</th>
                        <th class="text-end">Total Value</th>
                        <th class="text-end">Won Value</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($top_customers as $c): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($c->customer_name) ?></div>
                                <?php if ($c->customer_phone): ?>
                                <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($c->customer_phone) ?></div>
                                <?php endif; ?>
                                <div class="mt-1">
                                    <div class="bg-light rounded" style="height:4px;overflow:hidden">
                                        <div class="rounded" style="height:4px;width:<?= min(100, round($c->total_value / $max_customer * 100)) ?>%;background:#0d6efd"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center"><?= $c->quote_count ?></td>
                            <td class="text-end fw-semibold">R <?= number_format($c->total_value, 2) ?></td>
                            <td class="text-end text-success">R <?= number_format($c->accepted_value, 2) ?></td>
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
            <div class="card-header"><i class="bi bi-tools me-2 text-muted"></i>Top Line Items / Services</div>
            <div class="card-body p-0">
                <?php if (empty($popular_items)): ?>
                <p class="text-muted text-center py-4">No data for this period.</p>
                <?php else: ?>
                <table class="table mb-0" style="font-size:.85rem">
                    <thead><tr>
                        <th>Item / Service</th>
                        <th class="text-center">Times Used</th>
                        <th class="text-end">Total Value</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($popular_items as $item): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($item->item_description) ?></div>
                                <div class="mt-1">
                                    <div class="bg-light rounded" style="height:4px;overflow:hidden">
                                        <div class="rounded" style="height:4px;width:<?= min(100, round($item->total_value / $max_item * 100)) ?>%;background:#e94560"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center"><?= $item->occurrences ?></td>
                            <td class="text-end fw-semibold">R <?= number_format($item->total_value, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Summary Table (print-friendly) ─────────────────────────── -->
<div class="card mb-4 d-none d-print-block">
    <div class="card-header fw-bold">Period Summary: <?= date('d M Y', strtotime($start)) ?> — <?= date('d M Y', strtotime($end)) ?></div>
    <div class="card-body">
        <table class="table table-bordered" style="font-size:.85rem">
            <tr><td>Total Quotations</td><td class="fw-bold"><?= $kpis->total_quotes ?></td>
                <td>Total Quote Value</td><td class="fw-bold">R <?= number_format($kpis->total_value, 2) ?></td></tr>
            <tr><td>Accepted / Won Value</td><td class="fw-bold text-success">R <?= number_format($kpis->accepted_value, 2) ?></td>
                <td>Completed / Invoiced</td><td class="fw-bold">R <?= number_format($kpis->completed_value, 2) ?></td></tr>
            <tr><td>Pipeline Value</td><td class="fw-bold text-warning">R <?= number_format($kpis->pipeline_value, 2) ?></td>
                <td>Win Rate</td><td class="fw-bold"><?= $kpis->conversion_rate ?>%</td></tr>
        </table>
    </div>
</div>

<!-- ── Chart.js ───────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function() {
    var mLabels   = <?= $m_labels ?>;
    var mCounts   = <?= $m_counts ?>;
    var mValues   = <?= $m_values ?>;
    var mAccepted = <?= $m_accepted ?>;

    // Monthly chart
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: mLabels,
            datasets: [
                {
                    label: 'Quote Count',
                    data: mCounts,
                    backgroundColor: 'rgba(13,110,253,0.7)',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'yCount',
                    order: 2
                },
                {
                    label: 'Total Value (R)',
                    data: mValues,
                    type: 'line',
                    borderColor: '#6f42c1',
                    backgroundColor: 'rgba(111,66,193,0.08)',
                    borderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'yValue',
                    order: 1
                },
                {
                    label: 'Accepted Value (R)',
                    data: mAccepted,
                    type: 'line',
                    borderColor: '#198754',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5,4],
                    pointRadius: 3,
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'yValue',
                    order: 0
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
                yCount: {
                    type: 'linear', position: 'left',
                    ticks: { stepSize: 1, color: '#0d6efd' },
                    title: { display: true, text: 'Count', color: '#0d6efd' },
                    grid: { color: 'rgba(0,0,0,.06)' }
                },
                yValue: {
                    type: 'linear', position: 'right',
                    ticks: {
                        color: '#6f42c1',
                        callback: function(v) { return 'R ' + v.toLocaleString(); }
                    },
                    title: { display: true, text: 'Value (R)', color: '#6f42c1' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    // Status donut
    var statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: <?= $st_labels ?>,
                datasets: [{
                    data: <?= $st_counts ?>,
                    backgroundColor: <?= $st_colors ?>,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                cutout: '62%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // Custom date range
    window.applyCustom = function() {
        var s = document.getElementById('cs').value;
        var e = document.getElementById('ce').value;
        if (s && e) window.location.href = '<?= base_url('reports/custom/') ?>' + s + '/' + e;
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
