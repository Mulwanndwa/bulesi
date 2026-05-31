<!-- Action bar -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="<?= base_url('quotation') ?>" class="btn btn-sm btn-light"><i class="bi bi-arrow-left me-1"></i>All Quotes</a>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-sm btn-light"><i class="bi bi-printer me-1"></i>Print</button>
        <a href="<?= base_url('quotation/edit/'.$quote->id) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        <?php if (($user['group_name'] ?? '') === 'Admin'): ?>
        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delModal">
            <i class="bi bi-trash me-1"></i>Delete
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Quote card -->
<div class="card mb-3" id="quote-card">
    <div class="card-body">

        <!-- ── Print letterhead (hidden on screen) ── -->
        <div class="print-header d-none d-print-block mb-4">
            <div style="display:flex;align-items:flex-start;justify-content:space-between">
                <div style="display:flex;align-items:center;gap:12px">
                    <?php if (!empty($quote->company_logo)): ?>
                    <img src="<?= base_url($quote->company_logo) ?>" alt=""
                         style="height:64px;max-width:140px;object-fit:contain">
                    <?php endif; ?>
                    <div>
                        <?php if (!empty($quote->company_name)): ?>
                        <div style="font-size:18px;font-weight:800;line-height:1.1"><?= htmlspecialchars($quote->company_name) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($quote->company_address)): ?>
                        <div style="font-size:11px;color:#555;margin-top:2px"><?= htmlspecialchars($quote->company_address) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($quote->company_phone) || !empty($quote->company_email)): ?>
                        <div style="font-size:11px;color:#555">
                            <?= htmlspecialchars(implode(' · ', array_filter([$quote->company_phone, $quote->company_email]))) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:22px;font-weight:800;letter-spacing:1px;text-transform:uppercase">Quotation</div>
                    <div style="font-size:14px;font-weight:700;color:#333"><?= htmlspecialchars($quote->quote_number) ?></div>
                    <div style="font-size:11px;color:#555;margin-top:4px">
                        <strong>Date:</strong> <?= date('d F Y', strtotime($quote->quote_date)) ?>
                    </div>
                    <?php if ($quote->valid_until): ?>
                    <div style="font-size:11px;color:#555">
                        <strong>Valid Until:</strong> <?= date('d F Y', strtotime($quote->valid_until)) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <hr style="border-top:2px solid #000;margin:12px 0 0">
        </div>

        <!-- ── Screen header row (hidden on print) ── -->
        <div class="row align-items-start mb-4 no-print">
            <div class="col">
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($quote->quote_number) ?></h4>
                <span class="s-badge s-<?= $quote->status ?>" style="font-size:.8rem"><?= $statuses[$quote->status]['label'] ?? $quote->status ?></span>
                <span class="badge bg-light text-dark border ms-1" style="font-size:.8rem"><?= htmlspecialchars($quote->type_name ?? '—') ?></span>
            </div>
            <div class="col-auto text-end text-muted" style="font-size:.83rem">
                <div><strong>Date:</strong> <?= date('d F Y', strtotime($quote->quote_date)) ?></div>
                <?php if ($quote->valid_until): ?>
                <div><strong>Valid Until:</strong> <?= date('d F Y', strtotime($quote->valid_until)) ?></div>
                <?php endif; ?>
                <div><strong>Created By:</strong> <?= htmlspecialchars($quote->created_by) ?></div>
            </div>
        </div>

        <!-- Customer + Description -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.8px">Bill To</h6>
                <div class="fw-bold"><?= htmlspecialchars($quote->customer_name) ?></div>
                <?php if ($quote->customer_phone): ?>
                <div class="text-muted"><i class="bi bi-telephone me-1 no-print"></i><?= htmlspecialchars($quote->customer_phone) ?></div>
                <?php endif; ?>
                <?php if ($quote->customer_email): ?>
                <div class="text-muted"><i class="bi bi-envelope me-1 no-print"></i><?= htmlspecialchars($quote->customer_email) ?></div>
                <?php endif; ?>
            </div>
            <?php if ($quote->description): ?>
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.8px">Description</h6>
                <p class="mb-0"><?= nl2br(htmlspecialchars($quote->description)) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Items table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered" style="font-size:.88rem">
                <thead class="table-light">
                    <tr>
                        <th style="width:36px" class="text-center">#</th>
                        <th>Description</th>
                        <th style="width:90px">Unit</th>
                        <th style="width:70px" class="text-center">Qty</th>
                        <th style="width:120px" class="text-end">Unit Price</th>
                        <th style="width:120px" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td class="text-center text-muted"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($item->item_description) ?></td>
                        <td><?= htmlspecialchars($item->unit ?: '—') ?></td>
                        <td class="text-center"><?= rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') ?></td>
                        <td class="text-end">R <?= number_format($item->unit_price, 2) ?></td>
                        <td class="text-end fw-semibold">R <?= number_format($item->line_total, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="5" class="text-end text-muted">Subtotal</td>
                        <td class="text-end fw-semibold">R <?= number_format($quote->subtotal, 2) ?></td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="5" class="text-end text-muted">VAT (<?= $quote->vat_rate ?>%)</td>
                        <td class="text-end">R <?= number_format($quote->vat_amount, 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end fw-bold fs-6">TOTAL</td>
                        <td class="text-end fw-bold fs-6 text-primary">R <?= number_format($quote->total, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Photos (screen only) -->
        <?php
        $photos = array_filter([
            $quote->image_1 ?? null,
            $quote->image_2 ?? null,
            $quote->image_3 ?? null,
            $quote->image_4 ?? null,
        ]); ?>
        <div class="border-top pt-3 mb-3 no-print">
            <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.8px">
                <i class="bi bi-images me-1"></i>Photos
            </h6>
            <?php if (!empty($photos)): ?>
            <div class="row g-2">
                <?php foreach (array_values($photos) as $img): ?>
                <div class="col-6 col-md-3">
                    <a href="<?= base_url($img) ?>" target="_blank" title="Click to view full size">
                        <img src="<?= base_url($img) ?>"
                             class="img-thumbnail w-100"
                             style="height:160px;object-fit:cover;cursor:zoom-in"
                             onerror="this.style.border='2px solid red';this.alt='Image not found'">
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0" style="font-size:.85rem">No photos attached to this quotation.</p>
            <?php endif; ?>
        </div>

        <!-- Notes -->
        <?php if ($quote->notes): ?>
        <div class="border-top pt-3">
            <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;letter-spacing:.8px">Notes</h6>
            <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($quote->notes)) ?></p>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Change status -->
<div class="card no-print">
    <div class="card-body d-flex align-items-center gap-3">
        <span class="fw-semibold text-muted">Update Status:</span>
        <?= form_open('quotation/status/' . $quote->id, ['class'=>'d-flex gap-2 align-items-center']) ?>
            <select name="status" class="form-select form-select-sm" style="width:160px">
                <?php foreach ($statuses as $key => $s): ?>
                <option value="<?= $key ?>" <?= $quote->status === $key ? 'selected' : '' ?>><?= $s['label'] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Save</button>
        <?= form_close() ?>
    </div>
</div>

<!-- Delete modal -->
<div class="modal fade no-print" id="delModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Delete Quote</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="text-muted mb-0">Delete <strong><?= htmlspecialchars($quote->quote_number) ?></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <?= form_open('quotation/delete/' . $quote->id) ?>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide all UI chrome */
    .no-print, #sidebar, #topbar { display: none !important; }

    /* Full-width, no margin */
    #main  { margin-left: 0 !important; }
    .page-wrap { padding: 0 !important; }

    /* Card: flat, no shadow */
    #quote-card { box-shadow: none !important; border: none !important; }
    #quote-card .card-body { padding: 0 !important; }

    /* Tables: keep borders */
    .table-bordered td, .table-bordered th { border: 1px solid #ccc !important; }
    .table-light { background: #f5f5f5 !important; -webkit-print-color-adjust: exact; }

    /* Typography */
    body { font-size: 12px !important; color: #000 !important; }
    .text-primary { color: #000 !important; }

    /* Ensure print header shows */
    .print-header { display: block !important; }
}
</style>
