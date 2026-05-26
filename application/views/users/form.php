<?php
$action = $is_edit
    ? base_url('users/update/' . $record->id)
    : base_url('users/store');
$is_self = $is_edit && (int)$record->id === (int)$user['id'];
?>

<?= form_open($action) ?>
<div class="row g-3">

    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-fill me-2 text-muted"></i>Account Details</div>
            <div class="card-body">
                <div class="row g-3">

                    <!-- Username -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username"
                               class="form-control <?= form_error('username') ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars(set_value('username', $record->username ?? '')) ?>"
                               required minlength="3" maxlength="100">
                        <div class="invalid-feedback"><?= form_error('username') ?></div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                               class="form-control <?= form_error('email') ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars(set_value('email', $record->email ?? '')) ?>"
                               required maxlength="150">
                        <div class="invalid-feedback"><?= form_error('email') ?></div>
                    </div>

                    <!-- Group -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">User Group <span class="text-danger">*</span></label>
                        <select name="group_id" class="form-select <?= form_error('group_id') ? 'is-invalid' : '' ?>">
                            <option value="">— Select group —</option>
                            <?php foreach ($groups as $g): ?>
                            <option value="<?= $g->id ?>"
                                <?= (int)set_value('group_id', $record->group_id ?? '') === (int)$g->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g->name) ?>
                                <?php if ($g->description): ?>— <span class="text-muted"><?= htmlspecialchars($g->description) ?></span><?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= form_error('group_id') ?></div>
                    </div>

                    <!-- Active toggle -->
                    <div class="col-md-6 d-flex align-items-end pb-1">
                        <?php if ($is_self): ?>
                        <div class="text-muted small fst-italic">
                            <i class="bi bi-info-circle me-1"></i>You cannot change the status of your own account.
                        </div>
                        <?php else: ?>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   <?= set_value('is_active', $record->is_active ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="is_active">Active account</label>
                            <div class="text-muted" style="font-size:.75rem">Inactive users cannot log in.</div>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Password card -->
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-lock-fill me-2 text-muted"></i>Password
                <?php if ($is_edit): ?>
                <span class="text-muted fw-normal ms-1" style="font-size:.8rem">— leave blank to keep current password</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Password <?= !$is_edit ? '<span class="text-danger">*</span>' : '' ?>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" id="pwd"
                                   class="form-control <?= form_error('password') ? 'is-invalid' : '' ?>"
                                   <?= !$is_edit ? 'required minlength="6"' : 'minlength="6"' ?>
                                   placeholder="<?= $is_edit ? 'New password (optional)' : 'Min. 6 characters' ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('pwd', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="invalid-feedback"><?= form_error('password') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Confirm Password <?= !$is_edit ? '<span class="text-danger">*</span>' : '' ?>
                        </label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="cpwd"
                                   class="form-control <?= form_error('confirm_password') ? 'is-invalid' : '' ?>"
                                   placeholder="Repeat password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('cpwd', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="invalid-feedback"><?= form_error('confirm_password') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="col-12">
        <div class="d-flex gap-2 justify-content-end">
            <a href="<?= base_url('users') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-1"></i> <?= $is_edit ? 'Save Changes' : 'Create User' ?>
            </button>
        </div>
    </div>

</div>
<?= form_close() ?>

<script>
function togglePwd(id, btn) {
    var input = document.getElementById(id);
    var showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    btn.querySelector('i').className = showing ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
