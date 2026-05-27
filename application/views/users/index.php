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
                            <th>API Key</th>
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
                        <?php
                            $new_token = $this->session->flashdata('api_token_' . $u->id);
                            $has_token = !empty($u->api_token) || $new_token;
                        ?>
                        <td>
                            <?php if ($new_token): ?>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#keyModal"
                                    data-uid="<?= $u->id ?>"
                                    data-uname="<?= htmlspecialchars($u->username) ?>"
                                    data-token="<?= htmlspecialchars($new_token) ?>"
                                    data-has="1">
                                <i class="bi bi-key-fill me-1"></i> Show Key
                            </button>
                            <?php elseif ($has_token): ?>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#keyModal"
                                    data-uid="<?= $u->id ?>"
                                    data-uname="<?= htmlspecialchars($u->username) ?>"
                                    data-token=""
                                    data-has="1">
                                <i class="bi bi-key me-1"></i> Has Key
                            </button>
                            <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#keyModal"
                                    data-uid="<?= $u->id ?>"
                                    data-uname="<?= htmlspecialchars($u->username) ?>"
                                    data-token=""
                                    data-has="0">
                                <i class="bi bi-key me-1 text-muted"></i><span class="text-muted">No Key</span>
                            </button>
                            <?php endif; ?>
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

<!-- API Key modal -->
<div class="modal fade" id="keyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title"><i class="bi bi-key-fill text-warning me-1"></i> API Key — <span id="key-uname"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- New token just generated -->
                <div id="key-new-block" class="d-none">
                    <div class="alert alert-success py-2 mb-3">
                        <i class="bi bi-check-circle-fill me-1"></i> New API key generated. Copy it now — it won't be shown again in full.
                    </div>
                    <label class="form-label fw-semibold">Bearer Token</label>
                    <div class="input-group mb-1">
                        <input type="text" class="form-control font-monospace" id="key-token-val" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyApiKey()">
                            <i class="bi bi-clipboard" id="copy-icon"></i>
                        </button>
                    </div>
                    <div class="form-text">Use this in the <code>Authorization</code> header: <code>Bearer &lt;token&gt;</code></div>
                </div>
                <!-- Existing token (hidden for security) -->
                <div id="key-existing-block" class="d-none">
                    <p class="text-muted mb-3">This user already has an API key. For security, the full token is not displayed again. You can generate a new one (this will invalidate the current key) or revoke it.</p>
                </div>
                <!-- No token -->
                <div id="key-none-block" class="d-none">
                    <p class="text-muted mb-0">No API key exists for this user. Generate one to allow API access.</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                <div>
                    <?= form_open('', ['id' => 'revoke-form', 'class' => 'd-inline']) ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger" id="revoke-btn">
                            <i class="bi bi-x-circle me-1"></i> Revoke Key
                        </button>
                    <?= form_close() ?>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <?= form_open('', ['id' => 'genkey-form', 'class' => 'd-inline']) ?>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-arrow-repeat me-1"></i> Generate New Key
                        </button>
                    <?= form_close() ?>
                </div>
            </div>
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

document.getElementById('keyModal').addEventListener('show.bs.modal', function(e) {
    var btn     = e.relatedTarget;
    var uid     = btn.dataset.uid;
    var uname   = btn.dataset.uname;
    var token   = btn.dataset.token;
    var hasKey  = btn.dataset.has === '1';

    document.getElementById('key-uname').textContent = uname;
    document.getElementById('genkey-form').action    = '<?= base_url('users/generate_token/') ?>' + uid;
    document.getElementById('revoke-form').action    = '<?= base_url('users/revoke_token/') ?>' + uid;

    document.getElementById('key-new-block').classList.add('d-none');
    document.getElementById('key-existing-block').classList.add('d-none');
    document.getElementById('key-none-block').classList.add('d-none');
    document.getElementById('revoke-btn').classList.add('d-none');

    if (token) {
        document.getElementById('key-token-val').value = token;
        document.getElementById('key-new-block').classList.remove('d-none');
        document.getElementById('revoke-btn').classList.remove('d-none');
    } else if (hasKey) {
        document.getElementById('key-existing-block').classList.remove('d-none');
        document.getElementById('revoke-btn').classList.remove('d-none');
    } else {
        document.getElementById('key-none-block').classList.remove('d-none');
    }
});

function copyApiKey() {
    var val  = document.getElementById('key-token-val').value;
    var icon = document.getElementById('copy-icon');
    navigator.clipboard.writeText(val).then(function() {
        icon.className = 'bi bi-check2';
        setTimeout(function() { icon.className = 'bi bi-clipboard'; }, 1500);
    });
}
</script>
