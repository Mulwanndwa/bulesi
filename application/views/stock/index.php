<!-- ── Stats ─────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php
    $stat_cards = [
        ['val' => (int)$stats->total_items,                               'lbl' => 'Total Items',     'icon' => 'boxes',            'color' => '#0d6efd'],
        ['val' => 'R '.number_format($stats->total_value, 2),             'lbl' => 'Stock Value',     'icon' => 'currency-dollar',  'color' => '#198754'],
        ['val' => (int)$stats->low_stock,                                 'lbl' => 'Low Stock',       'icon' => 'exclamation-triangle', 'color' => '#fd7e14'],
        ['val' => (int)$stats->out_of_stock,                              'lbl' => 'Out of Stock',    'icon' => 'x-circle',         'color' => '#dc3545'],
    ];
    foreach ($stat_cards as $c): ?>
    <div class="col-md-3 col-6">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px"><?= $c['lbl'] ?></span>
                    <i class="bi bi-<?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;font-size:1.1rem;opacity:.7"></i>
                </div>
                <div style="font-size:1.5rem;font-weight:800;color:<?= $c['color'] ?>;line-height:1.1"><?= $c['val'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Toolbar ────────────────────────────────────────────────────── -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <!-- Category filter -->
    <div class="d-flex flex-wrap gap-1">
        <a href="<?= base_url('stock') ?>" class="btn btn-sm <?= !$category_id ? 'btn-dark' : 'btn-outline-secondary' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
        <a href="<?= base_url('stock/index/'.$cat->id) ?>"
           class="btn btn-sm <?= (int)$category_id === (int)$cat->id ? 'btn-dark' : 'btn-outline-secondary' ?>">
            <?= htmlspecialchars($cat->name) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <input type="text" id="stock-search" class="form-control form-control-sm" placeholder="Search items…" style="width:200px">
        <a href="<?= base_url('stock/create') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Item
        </a>
    </div>
</div>

<!-- ── Table ─────────────────────────────────────────────────────── -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($items)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-boxes" style="font-size:2.5rem"></i>
            <p class="mt-2 mb-0">No stock items found. <a href="<?= base_url('stock/create') ?>">Add one.</a></p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="stock-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th class="text-end">On Hand</th>
                        <th class="text-end">Reorder</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Stock Value</th>
                        <th>Location</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item):
                        $is_out  = $item->quantity_on_hand <= 0;
                        $is_low  = !$is_out && $item->reorder_level > 0 && $item->quantity_on_hand <= $item->reorder_level;
                        $row_cls = $is_out ? 'table-danger' : ($is_low ? 'table-warning' : '');
                    ?>
                    <tr class="<?= $row_cls ?> stock-row">
                        <td class="text-muted" style="font-size:.8rem"><?= htmlspecialchars($item->code ?? '—') ?></td>
                        <td>
                            <a href="<?= base_url('stock/view/'.$item->id) ?>" class="fw-semibold text-decoration-none">
                                <?= htmlspecialchars($item->name) ?>
                            </a>
                            <?php if ($is_out): ?>
                            <span class="badge bg-danger ms-1" style="font-size:.6rem">OUT</span>
                            <?php elseif ($is_low): ?>
                            <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">LOW</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><?= htmlspecialchars($item->category_name) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($item->unit) ?></td>
                        <td class="text-end fw-bold <?= $is_out ? 'text-danger' : ($is_low ? 'text-warning' : '') ?>">
                            <?= number_format($item->quantity_on_hand, 2) ?>
                        </td>
                        <td class="text-end text-muted"><?= number_format($item->reorder_level, 2) ?></td>
                        <td class="text-end">R <?= number_format($item->unit_cost, 2) ?></td>
                        <td class="text-end fw-semibold">R <?= number_format($item->stock_value, 2) ?></td>
                        <td class="text-muted" style="font-size:.82rem"><?= htmlspecialchars($item->location ?: '—') ?></td>
                        <td>
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="<?= base_url('stock/view/'.$item->id) ?>" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
                                <a href="<?= base_url('stock/edit/'.$item->id) ?>" class="btn btn-sm btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php if (($user['group_name'] ?? '') === 'Admin'): ?>
                                <button class="btn btn-sm btn-light text-danger" title="Delete"
                                        data-bs-toggle="modal" data-bs-target="#delModal"
                                        data-id="<?= $item->id ?>" data-name="<?= htmlspecialchars($item->name) ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
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
                <p class="text-muted mb-0">Delete <strong id="del-name"></strong>? All movement history will also be deleted.</p>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <?= form_open('', ['id'=>'del-form']) ?>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
// Search filter
document.getElementById('stock-search').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#stock-table .stock-row').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// Delete modal
document.getElementById('delModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    document.getElementById('del-name').textContent = btn.dataset.name;
    document.getElementById('del-form').action = '<?= base_url('stock/delete/') ?>' + btn.dataset.id;
});
</script>
