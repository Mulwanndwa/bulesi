<?php
$status_map = [
    'draft'       => ['label' => 'Draft',       'class' => 's-draft'],
    'sent'        => ['label' => 'Sent',        'class' => 's-sent'],
    'accepted'    => ['label' => 'Accepted',    'class' => 's-accepted'],
    'in_progress' => ['label' => 'In Progress', 'class' => 's-in_progress'],
    'completed'   => ['label' => 'Completed',   'class' => 's-completed'],
    'invoiced'    => ['label' => 'Invoiced',    'class' => 's-invoiced'],
    'rejected'    => ['label' => 'Rejected',    'class' => 's-rejected'],
    'cancelled'   => ['label' => 'Cancelled',   'class' => 's-cancelled'],
];
?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= base_url('companies') ?>" class="btn btn-sm btn-light">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="d-flex align-items-center gap-2">
        <?php if (!empty($company->logo)): ?>
        <img src="<?= base_url($company->logo) ?>" alt=""
             style="height:32px;width:32px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;padding:2px;background:#fff">
        <?php endif; ?>
        <div>
            <span class="fw-semibold"><?= htmlspecialchars($company->name) ?></span>
            <span class="text-muted ms-2 small">— Quotes</span>
        </div>
    </div>
    <span class="badge bg-secondary ms-auto"><?= count($quotes) ?> quote<?= count($quotes) !== 1 ? 's' : '' ?></span>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($quotes)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-file-earmark-text d-block mb-2" style="font-size:2.5rem"></i>
            No quotations found for users linked to this company.
        </div>
        <?php else: ?>
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem">
            <thead>
                <tr>
                    <th>Quote #</th>
                    <th>Customer</th>
                    <th>Created By</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Total</th>
                    <th>Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                foreach ($quotes as $q):
                    $grand_total += $q->total;
                    $st = $status_map[$q->status] ?? ['label' => ucfirst($q->status), 'class' => 's-draft'];
                ?>
                <tr>
                    <td class="fw-semibold text-primary"><?= htmlspecialchars($q->quote_number) ?></td>
                    <td><?= htmlspecialchars($q->customer_name) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($q->created_by) ?></td>
                    <td class="text-center">
                        <span class="s-badge <?= $st['class'] ?>"><?= $st['label'] ?></span>
                    </td>
                    <td class="text-end fw-semibold">R <?= number_format($q->total, 2) ?></td>
                    <td class="text-muted" style="white-space:nowrap"><?= date('d M Y', strtotime($q->quote_date)) ?></td>
                    <td class="text-end">
                        <a href="<?= base_url('quotation/view/'.$q->id) ?>" class="btn btn-sm btn-light" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="4" class="text-end fw-semibold">Total</td>
                    <td class="text-end fw-bold">R <?= number_format($grand_total, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>
</div>
