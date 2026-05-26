<?php
$filters = ['all'=>'All'] + array_map(fn($s)=>$s['label'], $statuses);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <a href="<?= base_url('quotation/create') ?>" class="btn btn-primary btn-sm no-print">
        <i class="bi bi-plus-lg me-1"></i> New Quotation
    </a>
</div>

<!-- Filter tabs -->
<div class="mb-3 no-print d-flex flex-wrap gap-1">
    <?php foreach ($filters as $key => $label): ?>
    <a href="<?= base_url($key === 'all' ? 'quotation' : 'quotation/index/'.$key) ?>"
       class="btn btn-sm <?= $filter === $key ? 'btn-dark' : 'btn-outline-secondary' ?>">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($quotes)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-file-earmark-x" style="font-size:2.5rem"></i>
            <p class="mt-2 mb-0">No quotations found.
                <?php if ($filter === 'all'): ?>
                    <a href="<?= base_url('quotation/create') ?>">Create one.</a>
                <?php endif; ?>
            </p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Date</th>
                        <th>Valid Until</th>
                        <th class="text-end">Total (R)</th>
                        <th>Status</th>
                        <th>By</th>
                        <th class="no-print"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotes as $q): ?>
                    <tr>
                        <td>
                            <a href="<?= base_url('quotation/view/'.$q->id) ?>" class="fw-semibold text-decoration-none">
                                <?= htmlspecialchars($q->quote_number) ?>
                            </a>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($q->type_name ?? '—') ?></span></td>
                        <td><?= htmlspecialchars($q->customer_name) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($q->customer_phone ?: '—') ?></td>
                        <td><?= date('d M Y', strtotime($q->quote_date)) ?></td>
                        <td><?= $q->valid_until ? date('d M Y', strtotime($q->valid_until)) : '—' ?></td>
                        <td class="text-end fw-semibold"><?= number_format($q->total, 2) ?></td>
                        <td><span class="s-badge s-<?= $q->status ?>"><?= $statuses[$q->status]['label'] ?? $q->status ?></span></td>
                        <td class="text-muted"><?= htmlspecialchars($q->created_by) ?></td>
                        <td class="no-print">
                            <div class="d-flex gap-1">
                                <a href="<?= base_url('quotation/view/'.$q->id) ?>" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
                                <a href="<?= base_url('quotation/edit/'.$q->id) ?>" class="btn btn-sm btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php if (($user['group_name'] ?? '') === 'Admin'): ?>
                                <button class="btn btn-sm btn-light text-danger" title="Delete"
                                    data-bs-toggle="modal" data-bs-target="#delModal"
                                    data-id="<?= $q->id ?>" data-num="<?= htmlspecialchars($q->quote_number) ?>">
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
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Delete Quote</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-1">
                <p class="text-muted mb-0">Delete <strong id="del-num"></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="del-form" method="post" action="">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('delModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    document.getElementById('del-num').textContent  = btn.dataset.num;
    document.getElementById('del-form').action = '<?= base_url('quotation/delete/') ?>' + btn.dataset.id;
});
</script>
