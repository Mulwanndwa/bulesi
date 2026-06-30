<?php
$stat_cards = [
    ['key'=>'total',       'label'=>'Total Quotes',  'color'=>'#0d5c0d', 'icon'=>'file-earmark-text-fill'],
    ['key'=>'draft',       'label'=>'Draft',         'color'=>'#6c757d', 'icon'=>'pencil-fill'],
    ['key'=>'sent',        'label'=>'Sent',          'color'=>'#0dcaf0', 'icon'=>'send-fill'],
    ['key'=>'accepted',    'label'=>'Accepted',      'color'=>'#198754', 'icon'=>'check-circle-fill'],
    ['key'=>'in_progress', 'label'=>'In Progress',   'color'=>'#fd7e14', 'icon'=>'tools'],
    ['key'=>'completed',   'label'=>'Completed',     'color'=>'#20c997', 'icon'=>'patch-check-fill'],
];
?>

<!-- Stat cards -->
<div class="row g-3 mb-4">
    <?php foreach ($stat_cards as $c): ?>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card" style="background:<?= $c['color'] ?>">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-val"><?= $stats[$c['key']] ?></div>
                    <div class="stat-lbl"><?= $c['label'] ?></div>
                </div>
                <i class="bi bi-<?= $c['icon'] ?>" style="font-size:1.6rem;opacity:.4"></i>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Total value banner -->
<div class="alert mb-4 py-2" style="background:#fff;border-left:4px solid #0d5c0d;border-radius:8px;box-shadow:0 1px 6px rgba(0,0,0,.06)">
    <span class="text-muted me-2">Total Quote Value:</span>
    <strong class="text-primary fs-5">R <?= number_format($stats['total_value'], 2) ?></strong>
</div>

<!-- Recent quotes -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2 text-muted"></i>Recent Quotations</span>
        <a href="<?= base_url('quotation') ?>" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recent)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-file-earmark-x" style="font-size:2.5rem"></i>
            <p class="mt-2 mb-0">No quotations yet. <a href="<?= base_url('quotation/create') ?>">Create your first one.</a></p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $q): ?>
                    <tr>
                        <td><a href="<?= base_url('quotation/view/'.$q->id) ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($q->quote_number) ?></a></td>
                        <td><?= htmlspecialchars($q->customer_name) ?></td>
                        <td><?= date('d M Y', strtotime($q->quote_date)) ?></td>
                        <td class="fw-semibold">R <?= number_format($q->total, 2) ?></td>
                        <td><span class="s-badge s-<?= $q->status ?>"><?= $statuses[$q->status]['label'] ?? $q->status ?></span></td>
                        <td><a href="<?= base_url('quotation/view/'.$q->id) ?>" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
