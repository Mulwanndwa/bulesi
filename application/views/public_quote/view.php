<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation <?= htmlspecialchars($quote->quote_number) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f4f6fb; font-size: .9rem; }
        .quote-wrap { max-width: 820px; margin: 32px auto 60px; padding: 0 16px; }

        /* Letterhead */
        .letterhead { background: #fff; border-radius: 12px 12px 0 0; padding: 28px 32px 20px; border-bottom: 3px solid #e94560; }
        .company-name { font-size: 1.4rem; font-weight: 800; line-height: 1.1; }
        .company-meta { font-size: .8rem; color: #6c757d; margin-top: 3px; }
        .quote-label { font-size: 2rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #12121f; line-height: 1; }
        .quote-number { font-size: 1rem; font-weight: 700; color: #e94560; }
        .quote-meta { font-size: .82rem; color: #555; margin-top: 4px; }

        /* Body card */
        .quote-body { background: #fff; padding: 28px 32px; }
        .section-label { font-size: .68rem; text-transform: uppercase; letter-spacing: 1px; color: #adb5bd; font-weight: 700; margin-bottom: 6px; }
        .customer-name { font-size: 1rem; font-weight: 700; }
        .table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; background: #f8f9fa; }
        .table-totals td { padding: 4px 0; font-size: .88rem; }
        .grand-total td { font-size: 1.05rem; font-weight: 800; border-top: 2px solid #dee2e6 !important; padding-top: 8px; }

        /* Signature section */
        .sig-section { background: #fff; border-radius: 0 0 12px 12px; padding: 28px 32px; border-top: 1px solid #f0f2f5; }
        .sig-canvas-wrap {
            border: 2px dashed #dee2e6; border-radius: 8px;
            background: #fafafa; position: relative;
            cursor: crosshair; user-select: none;
        }
        .sig-canvas-wrap.signed { border-color: #198754; border-style: solid; background: #fff; }
        #sig-canvas { display: block; width: 100%; height: 180px; touch-action: none; border-radius: 6px; }
        .sig-placeholder {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            color: #adb5bd; font-size: .85rem; pointer-events: none;
        }
        .signed-badge { background: #d1e7dd; color: #0a5132; border-radius: 6px; padding: 10px 14px; font-size: .85rem; }
        .sig-preview { max-height: 80px; border: 1px solid #dee2e6; border-radius: 6px; background: #fff; padding: 4px; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .quote-wrap { margin: 0; padding: 0; max-width: 100%; }
            .letterhead, .quote-body, .sig-section { border-radius: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
<div class="quote-wrap">

    <!-- Letterhead -->
    <div class="letterhead d-flex justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($quote->company_logo)): ?>
            <img src="<?= base_url($quote->company_logo) ?>" alt=""
                 style="height:64px;max-width:130px;object-fit:contain">
            <?php endif; ?>
            <div>
                <?php if (!empty($quote->company_name)): ?>
                <div class="company-name"><?= htmlspecialchars($quote->company_name) ?></div>
                <?php endif; ?>
                <?php if (!empty($quote->company_address)): ?>
                <div class="company-meta"><?= htmlspecialchars($quote->company_address) ?></div>
                <?php endif; ?>
                <?php if (!empty($quote->company_phone) || !empty($quote->company_email)): ?>
                <div class="company-meta">
                    <?= htmlspecialchars(implode(' · ', array_filter([$quote->company_phone ?? '', $quote->company_email ?? '']))) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-end flex-shrink-0">
            <div class="quote-label">Quotation</div>
            <div class="quote-number"><?= htmlspecialchars($quote->quote_number) ?></div>
            <div class="quote-meta"><strong>Date:</strong> <?= date('d F Y', strtotime($quote->quote_date)) ?></div>
            <?php if ($quote->valid_until): ?>
            <div class="quote-meta"><strong>Valid Until:</strong> <?= date('d F Y', strtotime($quote->valid_until)) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Body -->
    <div class="quote-body">

        <!-- Bill To -->
        <div class="row mb-4">
            <div class="col-md-5">
                <div class="section-label">Bill To</div>
                <div class="customer-name"><?= htmlspecialchars($quote->customer_name) ?></div>
                <?php if ($quote->customer_phone): ?>
                <div class="text-muted"><?= htmlspecialchars($quote->customer_phone) ?></div>
                <?php endif; ?>
                <?php if ($quote->customer_email): ?>
                <div class="text-muted"><?= htmlspecialchars($quote->customer_email) ?></div>
                <?php endif; ?>
            </div>
            <?php if ($quote->description): ?>
            <div class="col-md-7">
                <div class="section-label">Description</div>
                <p class="mb-0"><?= nl2br(htmlspecialchars($quote->description)) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Line items -->
        <table class="table table-bordered mb-0" style="font-size:.87rem">
            <thead>
                <tr>
                    <th style="width:36px" class="text-center">#</th>
                    <th>Description</th>
                    <th style="width:80px">Unit</th>
                    <th style="width:64px" class="text-center">Qty</th>
                    <th style="width:110px" class="text-end">Unit Price</th>
                    <th style="width:110px" class="text-end">Total</th>
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
        </table>

        <!-- Totals -->
        <div class="d-flex justify-content-end mt-3">
            <table class="table-totals" style="min-width:220px">
                <tr>
                    <td class="text-muted pe-4">Subtotal</td>
                    <td class="text-end fw-semibold">R <?= number_format($quote->subtotal, 2) ?></td>
                </tr>
                <tr>
                    <td class="text-muted pe-4">VAT (<?= $quote->vat_rate ?>%)</td>
                    <td class="text-end">R <?= number_format($quote->vat_amount, 2) ?></td>
                </tr>
                <tr class="grand-total">
                    <td class="pe-4 fw-bold">TOTAL</td>
                    <td class="text-end fw-bold" style="color:#e94560">R <?= number_format($quote->total, 2) ?></td>
                </tr>
            </table>
        </div>

        <?php if ($quote->notes): ?>
        <div class="border-top pt-3 mt-3">
            <div class="section-label">Notes</div>
            <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($quote->notes)) ?></p>
        </div>
        <?php endif; ?>

    </div>

    <!-- Signature section -->
    <div class="sig-section">

        <?php if (!empty($quote->cust_sig_data)): ?>
        <!-- Already signed -->
        <div class="signed-badge d-flex align-items-center gap-3 mb-3">
            <img src="<?= htmlspecialchars($quote->cust_sig_data) ?>" class="sig-preview" alt="Signature">
            <div>
                <div class="fw-semibold">Signed by <?= htmlspecialchars($quote->cust_sig_name) ?></div>
                <div style="font-size:.78rem;opacity:.7"><?= date('d M Y H:i', strtotime($quote->cust_signed_at)) ?></div>
            </div>
            <span class="ms-auto">✔</span>
        </div>
        <p class="text-muted small mb-0 no-print">To update your signature, draw a new one below and submit again.</p>
        <?php endif; ?>

        <!-- Signature pad -->
        <div class="no-print <?= !empty($quote->cust_sig_data) ? 'mt-3' : '' ?>">
            <div class="fw-semibold mb-1" style="font-size:.9rem">
                <?= empty($quote->cust_sig_data) ? 'Sign to accept this quotation' : 'Update signature' ?>
            </div>
            <p class="text-muted small mb-3">Draw your signature in the box below, enter your name, then click <strong>Submit Signature</strong>.</p>

            <div class="sig-canvas-wrap mb-3" id="sig-wrap">
                <canvas id="sig-canvas"></canvas>
                <div class="sig-placeholder" id="sig-placeholder">Draw your signature here</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="btn-clear">
                Clear
            </button>

            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                <input type="text" id="sig-name" class="form-control"
                       style="max-width:340px"
                       placeholder="Type your full name"
                       value="<?= htmlspecialchars($quote->customer_name) ?>">
            </div>

            <button type="button" class="btn btn-success px-4" id="btn-submit" disabled>
                <span id="btn-label"><i class="bi bi-pen me-1"></i>Submit Signature</span>
                <span id="btn-spinner" class="spinner-border spinner-border-sm d-none ms-1"></span>
            </button>

            <div id="sig-msg" class="mt-3 d-none"></div>
        </div>

        <!-- Print signature lines -->
        <div class="d-none d-print-block mt-4">
            <div class="row">
                <div class="col-5">
                    <div style="border-top:1px solid #000;margin-top:56px;padding-top:4px;font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Authorised Signature</div>
                    <div style="font-size:.72rem;color:#666;margin-top:6px">Date: ___________________________</div>
                </div>
                <div class="col-2"></div>
                <div class="col-5">
                    <div style="border-top:1px solid #000;margin-top:56px;padding-top:4px;font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Customer Signature</div>
                    <div style="font-size:.72rem;color:#666;margin-top:6px">Date: ___________________________</div>
                </div>
            </div>
        </div>

    </div>

    <div class="text-center text-muted small mt-3 no-print" style="font-size:.75rem">
        This is a secure, read-only quotation link. Your signature is legally binding.
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
(function() {
    const canvas      = document.getElementById('sig-canvas');
    const wrap        = document.getElementById('sig-wrap');
    const placeholder = document.getElementById('sig-placeholder');
    const btnClear    = document.getElementById('btn-clear');
    const btnSubmit   = document.getElementById('btn-submit');
    const btnLabel    = document.getElementById('btn-label');
    const btnSpinner  = document.getElementById('btn-spinner');
    const sigName     = document.getElementById('sig-name');
    const sigMsg      = document.getElementById('sig-msg');
    const SIGN_URL    = '<?= base_url('q/' . $quote->public_token . '/sign') ?>';

    // Resize canvas to match its CSS width
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        pad.clear();
    }

    const pad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255,255,255,0)',
        penColor: '#12121f',
    });

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    function updateBtn() {
        btnSubmit.disabled = pad.isEmpty() || sigName.value.trim() === '';
    }

    pad.addEventListener('endStroke', () => {
        placeholder.style.display = 'none';
        wrap.classList.add('signed');
        updateBtn();
    });

    sigName.addEventListener('input', updateBtn);

    btnClear.addEventListener('click', () => {
        pad.clear();
        wrap.classList.remove('signed');
        placeholder.style.display = '';
        updateBtn();
    });

    btnSubmit.addEventListener('click', async () => {
        if (pad.isEmpty() || !sigName.value.trim()) return;

        const sigData = pad.toDataURL('image/png');

        btnSubmit.disabled = true;
        btnLabel.textContent = 'Submitting…';
        btnSpinner.classList.remove('d-none');
        sigMsg.className = 'mt-3 d-none';

        try {
            const res  = await fetch(SIGN_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ signature: sigData, name: sigName.value.trim() }),
            });
            const data = await res.json();

            if (data.success) {
                sigMsg.className = 'mt-3 alert alert-success';
                sigMsg.innerHTML = `<strong>Signed!</strong> Thank you, ${data.name}. This quotation has been accepted on ${data.signed_at}.`;
                btnSubmit.classList.replace('btn-success', 'btn-outline-success');
                btnLabel.innerHTML = '<i class="bi bi-check-circle me-1"></i>Signed';
            } else {
                throw new Error(data.error || 'Submission failed.');
            }
        } catch (e) {
            sigMsg.className = 'mt-3 alert alert-danger';
            sigMsg.textContent = e.message;
            btnSubmit.disabled = false;
            btnLabel.innerHTML = '<i class="bi bi-pen me-1"></i>Submit Signature';
        }

        btnSpinner.classList.add('d-none');
    });
})();
</script>
</body>
</html>
