<?php
$action = $is_edit
    ? base_url('users/update/' . $record->id)
    : base_url('users/store');
$is_self = $is_edit && (int)$record->id === (int)$user['id'];
?>

<?= form_open_multipart($action) ?>
<div class="row g-3">

    <!-- Avatar card -->
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-bounding-box me-2 text-muted"></i>Profile Photo</div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <!-- Preview -->
                    <div id="avatar-preview-wrap">
                        <?php if ($is_edit && !empty($record->avatar_path)): ?>
                        <img id="avatar-preview" src="<?= base_url($record->avatar_path) ?>"
                             style="width:96px;height:96px;object-fit:cover;border-radius:50%;border:2px solid #dee2e6">
                        <?php else: ?>
                        <div id="avatar-initials"
                             class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                             style="width:96px;height:96px;font-size:1.6rem;background:#0d5c0d;flex-shrink:0">
                            <?= strtoupper(substr($record->first_name ?? $record->username ?? 'U', 0, 1)) ?>
                        </div>
                        <img id="avatar-preview" src="" alt=""
                             style="width:96px;height:96px;object-fit:cover;border-radius:50%;border:2px solid #dee2e6;display:none">
                        <?php endif; ?>
                    </div>
                    <!-- Controls -->
                    <div>
                        <label class="btn btn-outline-primary btn-sm mb-2" for="avatar-input">
                            <i class="bi bi-upload me-1"></i>
                            <?= ($is_edit && !empty($record->avatar_path)) ? 'Replace Photo' : 'Upload Photo' ?>
                        </label>
                        <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp"
                               class="d-none" onchange="previewAvatar(this)">
                        <div class="text-muted" style="font-size:.75rem">JPG, PNG or WebP · max 5 MB<br>Will be cropped to a square.</div>
                        <?php if ($is_edit && !empty($record->avatar_path)): ?>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="remove_avatar" id="remove_avatar" value="1" class="form-check-input">
                            <label class="form-check-label text-danger" for="remove_avatar" style="font-size:.82rem">Remove current photo</label>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-fill me-2 text-muted"></i>Account Details</div>
            <div class="card-body">
                <div class="row g-3">

                    <!-- First name -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name"
                               class="form-control <?= form_error('first_name') ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars(set_value('first_name', $record->first_name ?? '')) ?>"
                               required maxlength="75">
                        <div class="invalid-feedback"><?= form_error('first_name') ?></div>
                    </div>

                    <!-- Last name -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name"
                               class="form-control <?= form_error('last_name') ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars(set_value('last_name', $record->last_name ?? '')) ?>"
                               required maxlength="75">
                        <div class="invalid-feedback"><?= form_error('last_name') ?></div>
                    </div>

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

                    <!-- Company -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-select <?= form_error('company_id') ? 'is-invalid' : '' ?>" required>
                            <option value="">— Select company —</option>
                            <?php foreach ($companies as $co): ?>
                            <option value="<?= $co->id ?>"
                                <?= (int)set_value('company_id', $record->company_id ?? '') === (int)$co->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($co->name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= form_error('company_id') ?></div>
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

function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('avatar-preview');
        var initials = document.getElementById('avatar-initials');
        preview.src = e.target.result;
        preview.style.display = 'block';
        if (initials) initials.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
