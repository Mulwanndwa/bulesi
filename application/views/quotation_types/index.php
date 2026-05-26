<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small"><?= count($types) ?> type<?= count($types) !== 1 ? 's' : '' ?></span>
    <a href="<?= base_url('quotation_types/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Add Type
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($types)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-tag" style="font-size:2.5rem"></i>
            <p class="mt-2 mb-0">No quotation types yet. <a href="<?= base_url('quotation_types/create') ?>">Add one.</a></p>
        </div>
        <?php else: ?>
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="text-center">Used In</th>
                    <th class="text-center">Order</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($types as $t): ?>
                <tr>
                    <td class="text-muted"><?= $t->id ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($t->name) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($t->description ?: '—') ?></td>
                    <td class="text-center">
                        <?php if ($t->in_use > 0): ?>
                        <span class="badge bg-primary"><?= $t->in_use ?> quote<?= $t->in_use !== 1 ? 's' : '' ?></span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-muted"><?= $t->sort_order ?></td>
                    <td class="text-center">
                        <?php if ($t->is_active): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex justify-content-end gap-1">
                            <a href="<?= base_url('quotation_types/edit/'.$t->id) ?>" class="btn btn-sm btn-light" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if (($user['group_name'] ?? '') === 'Admin'): ?>
                            <button class="btn btn-sm btn-light text-danger <?= $t->in_use > 0 ? 'disabled' : '' ?>"
                                    title="<?= $t->in_use > 0 ? 'Cannot delete — in use' : 'Delete' ?>"
                                    <?php if ($t->in_use === 0): ?>
                                    data-bs-toggle="modal" data-bs-target="#delModal"
                                    data-id="<?= $t->id ?>" data-name="<?= htmlspecialchars($t->name) ?>"
                                    <?php endif; ?>>
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Delete modal -->
<div class="modal fade" id="delModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Delete Type</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="text-muted mb-0">Delete type <strong id="del-name"></strong>?</p>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <?= form_open('', ['id' => 'del-form']) ?>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('delModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    document.getElementById('del-name').textContent = btn.dataset.name;
    document.getElementById('del-form').action = '<?= base_url('quotation_types/delete/') ?>' + btn.dataset.id;
});
</script>
