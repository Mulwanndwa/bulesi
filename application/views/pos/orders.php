<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders — Bulesi Tradings POS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root { --accent: #e94560; --dark: #12121f; }
        body { background: #f2f4f8; font-size: .88rem; }
        #pos-topbar {
            background: var(--dark); color: #fff;
            height: 50px; display: flex; align-items: center; justify-content: space-between;
            padding: 0 20px; position: sticky; top: 0; z-index: 100;
        }
        #pos-topbar .brand { font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 8px; }
        #pos-topbar .brand i { color: var(--accent); }
        .page-wrap { padding: 22px 26px; }
        .card { border: none; box-shadow: 0 1px 10px rgba(0,0,0,.07); border-radius: 10px; }
        .card-header { background: #fff; border-bottom: 1px solid #f0f2f5; font-weight: 600; padding: 14px 18px; }
        .table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: #8896a4; font-weight: 600; }
        .table td { vertical-align: middle; }
    </style>
</head>
<body>

<div id="pos-topbar">
    <div class="brand">
        <img src="<?= base_url('assets/img/logo.png') ?>" alt=""
             style="width:28px;height:28px;object-fit:contain">
        Bulesi Tradings POS
    </div>
    <div class="d-flex align-items-center gap-3">
        <span style="color:rgba(255,255,255,.5);font-size:.8rem">
            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['username']) ?>
        </span>
        <a href="<?= base_url('pos') ?>" class="btn btn-sm btn-outline-light" style="font-size:.75rem;padding:3px 10px">
            <i class="bi bi-arrow-left"></i> Back to POS
        </a>
        <a href="<?= base_url('auth/logout') ?>" class="btn btn-sm btn-outline-light" style="font-size:.75rem;padding:3px 10px">
            <i class="bi bi-box-arrow-right"></i> Sign Out
        </a>
    </div>
</div>

<div class="page-wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-muted"></i>My Orders</h5>
        <span class="text-muted small"><?= count($orders) ?> order<?= count($orders) !== 1 ? 's' : '' ?></span>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($orders)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-receipt d-block mb-2" style="font-size:2.5rem"></i>
                No orders placed yet.
            </div>
            <?php else: ?>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Notes</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">VAT</th>
                        <th class="text-end">Total</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($o->quote_number) ?></td>
                        <td><?= htmlspecialchars($o->customer_name) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($o->notes ?: '—') ?></td>
                        <td class="text-end">R <?= number_format($o->subtotal, 2) ?></td>
                        <td class="text-end text-muted">R <?= number_format($o->vat_amount, 2) ?></td>
                        <td class="text-end fw-bold" style="color:var(--accent)">R <?= number_format($o->total, 2) ?></td>
                        <td class="text-muted" style="white-space:nowrap"><?= date('d M Y H:i', strtotime($o->created_at)) ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-dark" onclick="printOrder(<?= $o->id ?>)">
                                <i class="bi bi-printer-fill me-1"></i>Print
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
<script src="<?= base_url('assets/js/pos-print.js') ?>"></script>
<script>
const BASE_URL = '<?= base_url() ?>';

async function printOrder(id) {
    try {
        const res  = await fetch(BASE_URL + 'pos/receipt/' + id);
        const data = await res.json();
        if (!data.success) { alert(data.error || 'Failed to load order.'); return; }

        const order = {
            quoteNumber:  data.quote.quote_number,
            customer:     data.quote.customer_name,
            notes:        data.quote.notes,
            subtotal:     data.quote.subtotal,
            vatAmount:    data.quote.vat_amount,
            total:        data.quote.total,
            createdAt:    data.quote.created_at,
            companyName:  data.company?.name     || '',
            companyLogo:  data.company?.logo_url || null,
        };
        PosPrint.printReceipt(order, data.items);
    } catch (e) {
        alert('Network error. Please try again.');
    }
}
</script>
</body>
</html>
