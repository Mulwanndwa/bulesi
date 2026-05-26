<?php
$group_badge = [
    'Admin'   => 'danger',
    'Manager' => 'warning',
    'Staff'   => 'primary',
    'Viewer'  => 'secondary',
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small"><?= count($users) ?> user<?= count($users) !== 1 ? 's' : '' ?></span>
    <a href="<?= base_url('users/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Add User
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <?php $is_self = (int)$u->id === (int)$user['id']; ?>
                    <tr class="<?= !$u->is_active ? 'table-light text-muted' : '' ?>">
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <!-- Avatar initials -->
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                     style="width:38px;height:38px;font-size:.85rem;flex-shrink:0;
                                            background:<?= $u->is_active ? '#0d6efd' : '#adb5bd' ?>">
                                    <?= strtoupper(substr($u->username, 0, 2)) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($u->username) ?>
                                        <?php if ($is_self): ?>
                                        <span class="badge bg-light text-dark border ms-1" style="font-size:.65rem">You</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted" style="font-size:.75rem">ID #<?= $u->id ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($u->email) ?></td>
                        <td>
                            <span class="badge bg-<?= $group_badge[$u->group_name] ?? 'secondary' ?>">
                                <?= htmlspecialchars($u->group_name) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u->is_active): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted" style="font-size:.82rem">
                            <?= $u->last_login ? date('d M Y H:i', strtotime($u->last_login)) : '—' ?>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-1">
                                <a href="<?= base_url('users/edit/'.$u->id) ?>" class="btn btn-sm btn-light" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if (!$is_self): ?>
                                <!-- Toggle active -->
                                <?= form_open('users/toggle/'.$u->id, ['class'=>'d-inline']) ?>
                                    <button type="submit" class="btn btn-sm btn-light" title="<?= $u->is_active ? 'Deactivate' : 'Activate' ?>">
                                        <i class="bi bi-<?= $u->is_active ? 'toggle-on text-success' : 'toggle-off text-muted' ?>"></i>
                                    </button>
                                <?= form_close() ?>
                                <!-- Delete -->
                                <button class="btn btn-sm btn-light text-danger" title="Delete"
                                        data-bs-toggle="modal" data-bs-target="#delModal"
                                        data-id="<?= $u->id ?>" data-name="<?= htmlspecialchars($u->username) ?>">
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
    </div>
</div>

<!-- Delete modal -->
<div class="modal fade" id="delModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Delete User</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-1">
                <p class="text-muted mb-0">Delete user <strong id="del-name"></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <?= form_open('', ['id'=>'del-form']) ?>
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
    document.getElementById('del-form').action = '<?= base_url('users/delete/') ?>' + btn.dataset.id;
});
</script>
