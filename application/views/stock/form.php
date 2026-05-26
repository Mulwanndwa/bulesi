<?php
$action = $is_edit ? base_url('stock/update/'.$record->id) : base_url('stock/store');
$v = fn($f, $d = '') => htmlspecialchars(set_value($f, $record->$f ?? $d));
?>

<?= form_open($action) ?>
<div class="row g-3">

    <!-- ── Identity ───────────────────────────────────────────────── -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-tag-fill me-2 text-muted"></i>Item Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label fw-semibold">Item Code</label>
                        <input type="text" name="code" class="form-control"
                               value="<?= $v('code') ?>"
                               placeholder="Auto if blank">
                        <div class="form-text">Leave blank to auto-generate.</div>
                    </div>
                    <div class="col-sm-8">
                        <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control <?= form_error('name') ? 'is-invalid' : '' ?>"
                               value="<?= $v('name') ?>" required>
                        <div class="invalid-feedback"><?= form_error('name') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select <?= form_error('category_id') ? 'is-invalid' : '' ?>">
                            <option value="">— Select —</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= (int)set_value('category_id', $record->category_id ?? '') === (int)$cat->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat->name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= form_error('category_id') ?></div>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                        <input type="text" name="unit"
                               class="form-control <?= form_error('unit') ? 'is-invalid' : '' ?>"
                               value="<?= $v('unit', 'pcs') ?>"
                               list="unit-list" placeholder="pcs, m, kg…">
                        <datalist id="unit-list">
                            <?php foreach (['pcs','m','kg','litre','box','roll','set','pair','hours'] as $u): ?>
                            <option value="<?= $u ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <div class="invalid-feedback"><?= form_error('unit') ?></div>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" name="location" class="form-control"
                               value="<?= $v('location') ?>" placeholder="Shelf A, Bay 2…">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars(set_value('description', $record->description ?? '')) ?></textarea>
                    </div>
                    <?php if ($is_edit): ?>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   <?= ($record->is_active ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Pricing & Stock ─────────────────────────────────────────── -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-cash-coin me-2 text-muted"></i>Pricing &amp; Stock</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Unit Cost (R)</label>
                    <input type="number" name="unit_cost" class="form-control"
                           value="<?= $v('unit_cost', '0.00') ?>" step="0.01" min="0">
                    <div class="form-text">Purchase / landing cost.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Unit Price (R)</label>
                    <input type="number" name="unit_price" class="form-control"
                           value="<?= $v('unit_price', '0.00') ?>" step="0.01" min="0">
                    <div class="form-text">Selling price per unit.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reorder Level</label>
                    <input type="number" name="reorder_level" class="form-control"
                           value="<?= $v('reorder_level', '0') ?>" step="0.01" min="0">
                    <div class="form-text">Alert when stock falls to this level.</div>
                </div>
                <?php if (!$is_edit): ?>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Opening Quantity</label>
                    <input type="number" name="quantity_on_hand" class="form-control"
                           value="<?= $v('quantity_on_hand', '0') ?>" step="0.01" min="0">
                    <div class="form-text">Use Stock Adjust to change quantity after creation.</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Actions ────────────────────────────────────────────────── -->
    <div class="col-12">
        <div class="d-flex gap-2 justify-content-end">
            <a href="<?= base_url($is_edit ? 'stock/view/'.$record->id : 'stock') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-1"></i><?= $is_edit ? 'Save Changes' : 'Add Item' ?>
            </button>
        </div>
    </div>

</div>
<?= form_close() ?>
