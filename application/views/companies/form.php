<?php
$action = $is_edit ? site_url('companies/update/'.$record->id) : site_url('companies/store');
$csrf_name  = $this->security->get_csrf_token_name();
$csrf_hash  = $this->security->get_csrf_hash();
?>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
<input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-building me-2 text-muted"></i><?= $is_edit ? 'Edit Company' : 'New Company' ?>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control <?= form_error('name') ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars(set_value('name', $record->name ?? '')) ?>"
                           placeholder="e.g. Acme Holdings" required>
                    <div class="invalid-feedback"><?= form_error('name') ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control" rows="2"
                              placeholder="Street, City, Province"><?= htmlspecialchars(set_value('address', $record->address ?? '')) ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= htmlspecialchars(set_value('phone', $record->phone ?? '')) ?>"
                               placeholder="+27 11 123 4567">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email"
                               class="form-control <?= form_error('email') ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars(set_value('email', $record->email ?? '')) ?>"
                               placeholder="info@company.co.za">
                        <div class="invalid-feedback"><?= form_error('email') ?></div>
                    </div>
                </div>

                <!-- Logo upload -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Logo</label>

                    <?php if ($is_edit && !empty($record->logo)): ?>
                    <div class="mb-2 d-flex align-items-center gap-3" id="current-logo-wrap">
                        <img src="<?= base_url($record->logo) ?>" alt="Logo"
                             style="height:56px;width:56px;object-fit:contain;border:1px solid #dee2e6;border-radius:6px;padding:4px;background:#fff">
                        <div>
                            <div class="text-muted small mb-1">Current logo</div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" name="remove_logo" id="remove_logo" value="1">
                                <label class="form-check-label small text-danger" for="remove_logo">Remove logo</label>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
                    <div class="form-text">PNG, JPG, WebP or GIF — max 2 MB. Uploading a new file replaces the current logo.</div>

                    <!-- Live preview -->
                    <div id="logo-preview-wrap" class="mt-2" style="display:none">
                        <img id="logo-preview" src="" alt="Preview"
                             style="height:56px;width:56px;object-fit:contain;border:1px solid #dee2e6;border-radius:6px;padding:4px;background:#fff">
                        <span class="text-muted small ms-2">Preview</span>
                    </div>
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           <?= ($record->is_active ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="<?= base_url('companies') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i><?= $is_edit ? 'Save Changes' : 'Add Company' ?>
                </button>
            </div>
        </div>
    </div>
</div>
</form>

<script>
document.getElementById('logo').addEventListener('change', function() {
    var file = this.files[0];
    var wrap = document.getElementById('logo-preview-wrap');
    if (!file) { wrap.style.display = 'none'; return; }
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('logo-preview').src = e.target.result;
        wrap.style.display = '';
    };
    reader.readAsDataURL(file);
});
</script>
