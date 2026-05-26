<?php
$is_out = $record->quantity_on_hand <= 0;
$is_low = !$is_out && $record->reorder_level > 0 && $record->quantity_on_hand <= $record->reorder_level;

$stock_pct = 0;
if ($record->reorder_level > 0) {
    $stock_pct = min(100, round($record->quantity_on_hand / ($record->reorder_level * 2) * 100));
}
$bar_color = $is_out ? 'bg-danger' : ($is_low ? 'bg-warning' : 'bg-success');

$mv_badge = ['in' => 'success', 'out' => 'danger', 'adjustment' => 'info'];
$mv_icon  = ['in' => 'arrow-down-circle-fill', 'out' => 'arrow-up-circle-fill', 'adjustment' => 'sliders'];
?>

<!-- Action bar -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="<?= base_url('stock') ?>" class="btn btn-sm btn-light"><i class="bi bi-arrow-left me-1"></i>All Stock</a>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#adjustModal">
            <i class="bi bi-arrow-left-right me-1"></i>Adjust Stock
        </button>
        <a href="<?= base_url('stock/edit/'.$record->id) ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <?php if (($user['group_name'] ?? '') === 'Admin'): ?>
        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delModal">
            <i class="bi bi-trash me-1"></i>Delete
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- ── Item Info ──────────────────────────────────────────────── -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-secondary mb-1" style="font-size:.7rem"><?= htmlspecialchars($record->code ?? 'No Code') ?></span>
                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($record->name) ?></h5>
                        <span class="text-muted"><?= htmlspecialchars($record->category_name) ?></span>
                    </div>
                    <span class="badge <?= $record->is_active ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $record->is_active ? 'Active' : 'Inactive' ?>
                    </span>
                </div>

                <?php if ($record->description): ?>
                <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($record->description)) ?></p>
                <?php endif; ?>

                <div class="row g-3">
                    <?php
                    $details = [
                        ['Unit',         $record->unit],
                        ['Location',     $record->location ?: '—'],
                        ['Unit Cost',    'R '.number_format($record->unit_cost, 2)],
                        ['Unit Price',   'R '.number_format($record->unit_price, 2)],
                        ['Stock Value',  'R '.number_format($record->stock_value, 2)],
                        ['Added',        date('d M Y', strtotime($record->created_at))],
                    ];
                    foreach ($details as $d): ?>
                    <div class="col-6 col-md-4">
                        <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px"><?= $d[0] ?></div>
                        <div class="fw-semibold"><?= htmlspecialchars($d[1]) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Stock Level ────────────────────────────────────────────── -->
    <div class="col-lg-4">
        <div class="card h-100 <?= $is_out ? 'border-danger' : ($is_low ? 'border-warning' : '') ?>">
            <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                <div class="mb-1 text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Quantity On Hand</div>
                <div style="font-size:3rem;font-weight:900;line-height:1;color:<?= $is_out ? '#dc3545' : ($is_low ? '#fd7e14' : '#198754') ?>">
                    <?= number_format($record->quantity_on_hand, 2) ?>
                </div>
                <div class="text-muted mb-3"><?= htmlspecialchars($record->unit) ?></div>

                <?php if ($record->reorder_level > 0): ?>
                <div class="progress mb-2" style="height:8px;border-radius:4px">
                    <div class="progress-bar <?= $bar_color ?>" style="width:<?= $stock_pct ?>%"></div>
                </div>
                <div class="text-muted" style="font-size:.78rem">
                    Reorder level: <strong><?= number_format($record->reorder_level, 2) ?> <?= htmlspecialchars($record->unit) ?></strong>
                </div>
                <?php endif; ?>

                <?php if ($is_out): ?>
                <span class="badge bg-danger mt-2">OUT OF STOCK</span>
                <?php elseif ($is_low): ?>
                <span class="badge bg-warning text-dark mt-2">LOW STOCK — REORDER NOW</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Movement History ──────────────────────────────────────────── -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2 text-muted"></i>Movement History</span>
        <span class="badge bg-secondary"><?= count($movements) ?> records</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($movements)): ?>
        <p class="text-muted text-center py-4">No movements recorded yet.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0" style="font-size:.85rem">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Before</th>
                        <th class="text-end">After</th>
                        <th class="text-end">Unit Cost</th>
                        <th>Reference</th>
                        <th>Notes</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $m): ?>
                    <tr>
                        <td class="text-muted"><?= date('d M Y H:i', strtotime($m->created_at)) ?></td>
                        <td>
                            <span class="badge bg-<?= $mv_badge[$m->movement_type] ?? 'secondary' ?>">
                                <i class="bi bi-<?= $mv_icon[$m->movement_type] ?? 'circle' ?> me-1"></i>
                                <?= ucfirst($m->movement_type) ?>
                            </span>
                        </td>
                        <td class="text-end fw-bold <?= $m->movement_type === 'out' ? 'text-danger' : 'text-success' ?>">
                            <?= $m->movement_type === 'out' ? '−' : '+' ?><?= number_format($m->quantity, 2) ?>
                        </td>
                        <td class="text-end text-muted"><?= number_format($m->quantity_before, 2) ?></td>
                        <td class="text-end fw-semibold"><?= number_format($m->quantity_after, 2) ?></td>
                        <td class="text-end">R <?= $m->unit_cost ? number_format($m->unit_cost, 2) : '—' ?></td>
                        <td><?= htmlspecialchars($m->reference ?: '—') ?></td>
                        <td class="text-muted"><?= htmlspecialchars($m->notes ?: '—') ?></td>
                        <td><?= htmlspecialchars($m->username) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Adjust Stock Modal ────────────────────────────────────────── -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>Adjust Stock — <?= htmlspecialchars($record->name) ?></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= form_open('stock/adjust/'.$record->id) ?>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Movement Type <span class="text-danger">*</span></label>
                    <div class="d-flex gap-2">
                        <?php foreach ([
                            'in'         => ['Stock In',    'success', 'arrow-down-circle'],
                            'out'        => ['Stock Out',   'danger',  'arrow-up-circle'],
                            'adjustment' => ['Set Exact',   'info',    'sliders'],
                        ] as $val => [$lbl, $col, $ico]): ?>
                        <div class="flex-fill">
                            <input type="radio" class="btn-check" name="movement_type" id="mt_<?= $val ?>" value="<?= $val ?>" <?= $val === 'in' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-<?= $col ?> w-100" for="mt_<?= $val ?>">
                                <i class="bi bi-<?= $ico ?> me-1"></i><?= $lbl ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-text mt-1" id="adj-hint">Adds quantity to current stock.</div>
                </div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold" id="qty-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" step="0.01" min="0.01" required
                               placeholder="e.g. 10">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Unit Cost (R) <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="number" name="unit_cost" class="form-control" step="0.01" min="0"
                               value="<?= $record->unit_cost ?>" placeholder="Leave blank to keep current">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Reference</label>
                        <input type="text" name="reference" class="form-control" placeholder="PO#, GRV#, WO#…">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Reason or details">
                    </div>
                </div>
                <div class="alert alert-secondary mt-3 mb-0 py-2">
                    Current stock: <strong><?= number_format($record->quantity_on_hand, 2) ?> <?= htmlspecialchars($record->unit) ?></strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply Adjustment</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Delete modal -->
<div class="modal fade" id="delModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Delete Item</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="text-muted mb-0">Delete <strong><?= htmlspecialchars($record->name) ?></strong> and all its movement history? This cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <?= form_open('stock/delete/'.$record->id) ?>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
var hints = {
    'in':         'Adds quantity to current stock.',
    'out':        'Subtracts quantity from current stock.',
    'adjustment': 'Sets stock to the exact quantity entered.'
};
var labels = {
    'in': 'Quantity to Add', 'out': 'Quantity to Remove', 'adjustment': 'New Exact Quantity'
};
document.querySelectorAll('[name="movement_type"]').forEach(function(el) {
    el.addEventListener('change', function() {
        document.getElementById('adj-hint').textContent  = hints[this.value];
        document.getElementById('qty-label').childNodes[0].textContent = labels[this.value] + ' ';
    });
});
</script>
