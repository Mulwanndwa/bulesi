<?php
$action = $is_edit
    ? base_url('quotation/update/' . $quote->id)
    : base_url('quotation/store');
$v = function($field, $default = '') use ($quote) {
    return htmlspecialchars(set_value($field, $quote ? ($quote->$field ?? $default) : $default));
};
?>

<?= form_open_multipart($action) ?>

<div class="row g-3">

    <!-- ── Customer Info ──────────────────────────── -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person-lines-fill me-2 text-muted"></i>Customer</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" name="customer_name" class="form-control <?= form_error('customer_name') ? 'is-invalid' : '' ?>"
                           value="<?= $v('customer_name') ?>" required>
                    <div class="invalid-feedback"><?= form_error('customer_name') ?></div>
                </div>
                <div class="row g-2">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="customer_phone" class="form-control" value="<?= $v('customer_phone') ?>">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="customer_email" class="form-control" value="<?= $v('customer_email') ?>">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-semibold">Job Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars(set_value('description', $quote->description ?? '')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Quote Details ──────────────────────────── -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-calendar3 me-2 text-muted"></i>Quote Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Quotation Type <span class="text-danger">*</span></label>
                    <select name="type_id" class="form-select">
                        <?php foreach ($quote_types as $type): ?>
                        <option value="<?= $type->id ?>" <?= (int)set_value('type_id', $quote->type_id ?? 0) === (int)$type->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        <a href="<?= base_url('quotation_types') ?>" target="_blank">Manage types <i class="bi bi-box-arrow-up-right"></i></a>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Quote Date <span class="text-danger">*</span></label>
                    <input type="date" name="quote_date" class="form-control <?= form_error('quote_date') ? 'is-invalid' : '' ?>"
                           value="<?= $v('quote_date', date('Y-m-d')) ?>" required>
                    <div class="invalid-feedback"><?= form_error('quote_date') ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Valid Until</label>
                    <input type="date" name="valid_until" class="form-control" value="<?= $v('valid_until') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">VAT Rate (%)</label>
                    <input type="number" id="vat_rate" name="vat_rate" class="form-control"
                           value="<?= htmlspecialchars(set_value('vat_rate', $quote->vat_rate ?? '15')) ?>"
                           step="0.01" min="0" max="100">
                </div>
                <?php if ($is_edit): ?>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach ($statuses as $key => $s): ?>
                        <option value="<?= $key ?>" <?= ($quote->status ?? '') === $key ? 'selected' : '' ?>><?= $s['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Line Items ─────────────────────────────── -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2 text-muted"></i>Line Items</span>
                <button type="button" id="btn-add-row" class="btn btn-sm btn-outline-primary no-print">
                    <i class="bi bi-plus-lg me-1"></i> Add Item
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" style="font-size:.87rem">
                        <thead class="table-light">
                            <tr>
                                <th style="width:36px" class="text-center">#</th>
                                <th>Description</th>
                                <th style="width:100px">Unit</th>
                                <th style="width:80px">Qty</th>
                                <th style="width:130px">Unit Price (R)</th>
                                <th style="width:130px">Line Total</th>
                                <?php if (($user['group_name'] ?? '') === 'Admin'): ?>
                                <th style="width:44px" class="no-print"></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            <?php
                            $init_items = !empty($items) ? $items : [null];
                            foreach ($init_items as $i => $item):
                            ?>
                            <tr>
                                <td class="text-center row-num"><?= $i + 1 ?></td>
                                <td><input type="text" class="form-control form-control-sm" name="items[<?= $i ?>][item_description]"
                                           value="<?= htmlspecialchars($item->item_description ?? '') ?>" required></td>
                                <td><input type="text" class="form-control form-control-sm" name="items[<?= $i ?>][unit]"
                                           value="<?= htmlspecialchars($item->unit ?? '') ?>" placeholder="m, hrs, pcs"></td>
                                <td><input type="number" class="form-control form-control-sm item-qty" name="items[<?= $i ?>][quantity]"
                                           value="<?= $item->quantity ?? '1' ?>" step="0.01" min="0.01"></td>
                                <td><input type="number" class="form-control form-control-sm item-price" name="items[<?= $i ?>][unit_price]"
                                           value="<?= $item->unit_price ?? '0.00' ?>" step="0.01" min="0"></td>
                                <td>
                                    <input type="hidden" name="items[<?= $i ?>][line_total]" class="item-line-total" value="<?= $item->line_total ?? '0.00' ?>">
                                    <span class="line-total-display fw-semibold">R <?= number_format($item->line_total ?? 0, 2) ?></span>
                                </td>
                                <?php if (($user['group_name'] ?? '') === 'Admin'): ?>
                                <td class="text-center no-print">
                                    <button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="bi bi-x-lg"></i></button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Totals + Notes ─────────────────────────── -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-chat-left-text me-2 text-muted"></i>Notes</div>
            <div class="card-body">
                <textarea name="notes" class="form-control" rows="5" placeholder="Internal or customer-facing notes..."><?= htmlspecialchars(set_value('notes', $quote->notes ?? '')) ?></textarea>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-calculator me-2 text-muted"></i>Totals</div>
            <div class="card-body">
                <table class="table table-borderless mb-0 ms-auto" style="max-width:320px">
                    <tr>
                        <td class="text-muted">Subtotal</td>
                        <td class="text-end fw-semibold" id="subtotal-display">R 0.00</td>
                    </tr>
                    <tr>
                        <td class="text-muted">VAT</td>
                        <td class="text-end" id="vat-display">R 0.00</td>
                    </tr>
                    <tr class="border-top">
                        <td class="fw-bold fs-5">TOTAL</td>
                        <td class="text-end fw-bold fs-5 text-primary" id="total-display">R 0.00</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Photos ─────────────────────────────────── -->
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-images me-2 text-muted"></i>Photos <span class="text-muted fw-normal" style="font-size:.8rem">— up to 4 images (JPG, PNG, GIF, WEBP · max 5 MB each)</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <?php for ($i = 1; $i <= 4; $i++):
                        $existing = $quote ? ($quote->{"image_$i"} ?? NULL) : NULL;
                    ?>
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label fw-semibold">Photo <?= $i ?></label>
                        <?php if ($is_edit && $existing): ?>
                        <div class="mb-2 position-relative">
                            <img src="<?= base_url($existing) ?>" class="img-thumbnail w-100" style="height:130px;object-fit:cover">
                            <span class="badge bg-dark position-absolute bottom-0 start-0 m-1" style="font-size:.65rem">Current</span>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="image_<?= $i ?>" class="form-control form-control-sm" accept="image/*">
                        <?php if ($is_edit && $existing): ?>
                        <div class="form-text">Upload a new file to replace.</div>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Actions ────────────────────────────────── -->
    <div class="col-12 no-print">
        <div class="d-flex gap-2 justify-content-end">
            <a href="<?= base_url($is_edit ? 'quotation/view/'.$quote->id : 'quotation') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-1"></i> <?= $is_edit ? 'Save Changes' : 'Create Quotation' ?>
            </button>
        </div>
    </div>

</div><!-- /.row -->
<?= form_close() ?>

<script>
(function() {
    var body    = document.getElementById('items-body');
    var isAdmin = <?= ($user['group_name'] ?? '') === 'Admin' ? 'true' : 'false' ?>;

    document.getElementById('btn-add-row').addEventListener('click', function() {
        var idx = body.querySelectorAll('tr').length;
        body.insertAdjacentHTML('beforeend', makeRow(idx));
        recalculate();
    });

    body.addEventListener('click', function(e) {
        if (!isAdmin) return;
        var btn = e.target.closest('.btn-remove-row');
        if (!btn) return;
        if (body.querySelectorAll('tr').length <= 1) return;
        btn.closest('tr').remove();
        reindex();
        recalculate();
    });

    body.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) {
            rowTotal(e.target.closest('tr'));
            recalculate();
        }
    });

    document.getElementById('vat_rate').addEventListener('input', recalculate);

    recalculate();

    function makeRow(i) {
        return '<tr>' +
            '<td class="text-center row-num">' + (i+1) + '</td>' +
            '<td><input type="text" class="form-control form-control-sm" name="items['+i+'][item_description]" required></td>' +
            '<td><input type="text" class="form-control form-control-sm" name="items['+i+'][unit]" placeholder="m, hrs, pcs"></td>' +
            '<td><input type="number" class="form-control form-control-sm item-qty" name="items['+i+'][quantity]" value="1" step="0.01" min="0.01"></td>' +
            '<td><input type="number" class="form-control form-control-sm item-price" name="items['+i+'][unit_price]" value="0.00" step="0.01" min="0"></td>' +
            '<td><input type="hidden" name="items['+i+'][line_total]" class="item-line-total" value="0.00"><span class="line-total-display fw-semibold">R 0.00</span></td>' +
            (isAdmin ? '<td class="text-center no-print"><button type="button" class="btn btn-sm btn-light text-danger btn-remove-row"><i class="bi bi-x-lg"></i></button></td>' : '') +
            '</tr>';
    }

    function reindex() {
        body.querySelectorAll('tr').forEach(function(row, i) {
            row.querySelector('.row-num').textContent = i + 1;
            row.querySelectorAll('[name]').forEach(function(el) {
                el.name = el.name.replace(/items\[\d+\]/, 'items[' + i + ']');
            });
        });
    }

    function rowTotal(row) {
        var qty   = parseFloat(row.querySelector('.item-qty').value)   || 0;
        var price = parseFloat(row.querySelector('.item-price').value) || 0;
        var tot   = qty * price;
        row.querySelector('.item-line-total').value          = tot.toFixed(2);
        row.querySelector('.line-total-display').textContent = 'R ' + fmt(tot);
    }

    function recalculate() {
        var subtotal = 0;
        body.querySelectorAll('tr').forEach(function(row) {
            rowTotal(row);
            subtotal += parseFloat(row.querySelector('.item-line-total').value) || 0;
        });
        var vatRate = parseFloat(document.getElementById('vat_rate').value) || 0;
        var vatAmt  = subtotal * vatRate / 100;
        document.getElementById('subtotal-display').textContent = 'R ' + fmt(subtotal);
        document.getElementById('vat-display').textContent      = 'R ' + fmt(vatAmt);
        document.getElementById('total-display').textContent    = 'R ' + fmt(subtotal + vatAmt);
    }

    function fmt(n) { return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
})();
</script>
