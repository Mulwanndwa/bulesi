<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small"><?= count($plans) ?> plan<?= count($plans) !== 1 ? 's' : '' ?></span>
    <a href="<?= base_url('plans/draw') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>New Plan
    </a>
</div>

<?php if (empty($plans)): ?>
<div class="card">
    <div class="text-center text-muted py-5">
        <i class="bi bi-house-door" style="font-size:3rem"></i>
        <p class="mt-3 mb-2 fw-semibold">No house plans yet</p>
        <a href="<?= base_url('plans/draw') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Draw Your First Plan
        </a>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($plans as $p): ?>
    <div class="col-md-4 col-lg-3">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <i class="bi bi-house-door-fill text-primary" style="font-size:1.6rem"></i>
                    <?php if (in_array($user['group_name'] ?? '', ['Admin','Manager'])): ?>
                    <button class="btn btn-sm btn-light text-danger"
                            data-bs-toggle="modal" data-bs-target="#delModal"
                            data-id="<?= $p->id ?>" data-title="<?= htmlspecialchars($p->title) ?>">
                        <i class="bi bi-trash"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <h6 class="fw-bold mb-1"><?= htmlspecialchars($p->title) ?></h6>
                <p class="text-muted mb-2" style="font-size:.78rem">
                    By <?= htmlspecialchars($p->username) ?><br>
                    <?= date('d M Y H:i', strtotime($p->updated_at)) ?>
                </p>
                <div class="mt-auto d-flex flex-column gap-2">
                    <a href="<?= base_url('plans/draw/'.$p->id) ?>" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-pencil-square me-1"></i>Open &amp; Edit
                    </a>
                    <a href="<?= base_url('plans/view3d/'.$p->id) ?>" target="_blank" class="btn btn-sm btn-outline-dark w-100">
                        <i class="bi bi-cube me-1"></i>View in 3D
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Delete modal -->
<div class="modal fade" id="delModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Delete Plan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="text-muted mb-0">Delete <strong id="del-title"></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <?= form_open('', ['id'=>'del-form']) ?>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('delModal').addEventListener('show.bs.modal', function(e) {
    var b = e.relatedTarget;
    document.getElementById('del-title').textContent = b.dataset.title;
    document.getElementById('del-form').action = '<?= base_url('plans/delete/') ?>' + b.dataset.id;
});
</script>
