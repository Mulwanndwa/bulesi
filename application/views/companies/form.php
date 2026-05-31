<?php
$action = $is_edit ? base_url('companies/update/'.$record->id) : base_url('companies/store');
?>

<?= form_open($action) ?>
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
<?= form_close() ?>
