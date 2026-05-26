<?php
$action = $is_edit ? base_url('quotation_types/update/'.$record->id) : base_url('quotation_types/store');
?>

<?= form_open($action) ?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-tag-fill me-2 text-muted"></i><?= $is_edit ? 'Edit Type' : 'New Quotation Type' ?></div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control <?= form_error('name') ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars(set_value('name', $record->name ?? '')) ?>"
                           placeholder="e.g. Vehicle, Steel Work…" required>
                    <div class="invalid-feedback"><?= form_error('name') ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <input type="text" name="description" class="form-control"
                           value="<?= htmlspecialchars(set_value('description', $record->description ?? '')) ?>"
                           placeholder="Short description (optional)">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" style="width:120px"
                           value="<?= htmlspecialchars(set_value('sort_order', $record->sort_order ?? '0')) ?>"
                           min="0" max="255">
                    <div class="form-text">Lower numbers appear first in dropdowns.</div>
                </div>

                <?php if ($is_edit): ?>
                <div class="mb-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                               <?= ($record->is_active ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="is_active">Active</label>
                        <div class="form-text">Inactive types won't appear in the quotation form.</div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="<?= base_url('quotation_types') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i><?= $is_edit ? 'Save Changes' : 'Add Type' ?>
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>
