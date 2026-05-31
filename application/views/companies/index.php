<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small"><?= count($companies) ?> compan<?= count($companies) !== 1 ? 'ies' : 'y' ?></span>
    <a href="<?= base_url('companies/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Add Company
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($companies)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-building" style="font-size:2.5rem"></i>
            <p class="mt-2 mb-0">No companies yet. <a href="<?= base_url('companies/create') ?>">Add one.</a></p>
        </div>
        <?php else: ?>
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th class="text-center">Users</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $c): ?>
                <tr>
                    <td class="text-muted"><?= $c->id ?></td>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($c->name) ?></div>
                        <?php if ($c->address): ?>
                        <div class="text-muted small"><?= htmlspecialchars($c->address) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($c->phone ?: '—') ?></td>
                    <td class="text-muted"><?= htmlspecialchars($c->email ?: '—') ?></td>
                    <td class="text-center">
                        <?php if ($c->user_count > 0): ?>
                        <span class="badge bg-primary"><?= $c->user_count ?></span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($c->is_active): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex justify-content-end gap-1">
                            <a href="<?= base_url('companies/edit/'.$c->id) ?>" class="btn btn-sm btn-light" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-light text-danger <?= $c->user_count > 0 ? 'disabled' : '' ?>"
                                    title="<?= $c->user_count > 0 ? 'Cannot delete — users linked' : 'Delete' ?>"
                                    <?php if ($c->user_count === 0): ?>
                                    data-bs-toggle="modal" data-bs-target="#delModal"
                                    data-id="<?= $c->id ?>" data-name="<?= htmlspecialchars($c->name) ?>"
                                    <?php endif; ?>>
                                <i class="bi bi-trash"></i>
                            </button>
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
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Delete Company</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-1">
                <p class="text-muted mb-0">Delete <strong id="del-name"></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
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
    document.getElementById('del-form').action = '<?= base_url('companies/delete/') ?>' + btn.dataset.id;
});
</script>
