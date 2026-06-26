<!-- ── Toolbar ─────────────────────────────────────────────────────── -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 no-print">
    <div class="d-flex flex-wrap gap-1">
        <?php foreach ($periods as $key => $label): ?>
        <a href="<?= base_url('reports/chats/' . $key) ?>"
           class="btn btn-sm <?= $period === $key ? 'btn-dark' : 'btn-outline-secondary' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <input type="date" id="cs" class="form-control form-control-sm" value="<?= $period === 'custom' ? $start : '' ?>" style="width:140px">
        <span class="text-muted small">to</span>
        <input type="date" id="ce" class="form-control form-control-sm" value="<?= $period === 'custom' ? $end   : '' ?>" style="width:140px">
        <button class="btn btn-sm btn-primary" onclick="applyCustom()">Apply</button>
    </div>
</div>

<p class="text-muted small mb-3">
    Showing: <strong><?= date('d M Y', strtotime($start)) ?></strong> — <strong><?= date('d M Y', strtotime($end)) ?></strong>
</p>

<!-- ── KPI Cards ───────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['val' => (int)($kpis->total_messages       ?? 0), 'lbl' => 'Total Messages',  'sub' => 'sent in period',      'icon' => 'chat-fill',         'color' => '#0d6efd'],
        ['val' => (int)($kpis->total_conversations  ?? 0), 'lbl' => 'Active Threads',  'sub' => 'conversations used',  'icon' => 'chat-dots-fill',    'color' => '#6f42c1'],
        ['val' => (int)($kpis->active_users         ?? 0), 'lbl' => 'Active Users',    'sub' => 'unique senders',      'icon' => 'people-fill',       'color' => '#198754'],
        ['val' => (int)($kpis->image_messages       ?? 0), 'lbl' => 'Images Shared',   'sub' => 'photo messages',      'icon' => 'image-fill',        'color' => '#fd7e14'],
        ['val' => (int)($kpis->quote_messages       ?? 0), 'lbl' => 'Quotes in Chat',  'sub' => 'messages with quote', 'icon' => 'file-earmark-text', 'color' => '#e94560'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-xl col-md-4 col-6">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px"><?= $c['lbl'] ?></span>
                    <i class="bi bi-<?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;font-size:1.1rem;opacity:.7"></i>
                </div>
                <div style="font-size:1.6rem;font-weight:800;color:<?= $c['color'] ?>;line-height:1"><?= $c['val'] ?></div>
                <div class="text-muted mt-1" style="font-size:.7rem"><?= $c['sub'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Most Active Users ────────────────────────────────────────────── -->
<?php if (!empty($by_user)): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-person-badge-fill me-2 text-muted"></i>Most Active Users</div>
    <div class="card-body p-0">
        <?php $max = max(array_map(fn($u) => (int)$u->message_count, $by_user)); ?>
        <table class="table mb-0" style="font-size:.85rem">
            <thead><tr>
                <th>User</th>
                <th class="text-center">Threads</th>
                <th class="text-end">Messages</th>
                <th>Last Active</th>
            </tr></thead>
            <tbody>
                <?php foreach ($by_user as $u): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($u->full_name ?: $u->username) ?></div>
                        <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($u->username) ?></div>
                        <div class="mt-1">
                            <div class="bg-light rounded" style="height:4px;overflow:hidden">
                                <div class="rounded" style="height:4px;width:<?= min(100, round($u->message_count / $max * 100)) ?>%;background:#0d6efd"></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center"><?= $u->conversation_count ?></td>
                    <td class="text-end fw-semibold"><?= $u->message_count ?></td>
                    <td class="text-muted" style="font-size:.78rem;white-space:nowrap"><?= date('d M H:i', strtotime($u->last_active)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ── Chats grouped by user ─────────────────────────────────────────── -->
<h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size:.72rem;letter-spacing:1px">
    <i class="bi bi-person-lines-fill me-1"></i>Messages by User
</h6>

<?php if (empty($grouped)): ?>
<div class="card mb-4">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-chat-dots d-block mb-2" style="font-size:2.5rem"></i>
        No messages in this period.
    </div>
</div>
<?php else: ?>
<div class="row g-3 mb-4">
<?php foreach ($grouped as $uid => $group): ?>
    <div class="col-12">
        <div class="card">
            <!-- User header -->
            <div class="card-header d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold"
                      style="width:32px;height:32px;font-size:.8rem;flex-shrink:0">
                    <?= strtoupper(substr($group['full_name'], 0, 1)) ?>
                </span>
                <div>
                    <span class="fw-semibold"><?= htmlspecialchars($group['full_name']) ?></span>
                    <span class="text-muted ms-1" style="font-size:.78rem">@<?= htmlspecialchars($group['username']) ?></span>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-auto">
                    <?= count($group['messages']) ?> message<?= count($group['messages']) !== 1 ? 's' : '' ?>
                </span>
            </div>

            <!-- Messages -->
            <div class="card-body p-0">
                <?php
                // Show latest 20 messages; collapse older ones
                $msgs    = $group['messages'];
                $total   = count($msgs);
                $show    = array_slice($msgs, 0, 20);
                ?>
                <?php if ($total > 20): ?>
                <div class="text-center text-muted py-2 border-bottom" style="font-size:.8rem">
                    <?= $total - 20 ?> older message<?= ($total - 20) !== 1 ? 's' : '' ?> not shown
                </div>
                <?php endif; ?>

                <?php $is_admin = ($user['group_name'] === 'Admin'); ?>
                <div style="max-height:480px;overflow-y:auto">
                <?php foreach ($show as $m): ?>
                <div class="d-flex gap-3 px-3 py-2 border-bottom align-items-start" style="font-size:.83rem" data-msg-id="<?= $m->id ?>">

                    <!-- Image thumbnail -->
                    <?php if (!empty($m->image_path)): ?>
                    <a href="#" class="flex-shrink-0 img-preview-trigger"
                       data-src="<?= base_url($m->image_path) ?>">
                        <img src="<?= base_url($m->image_path) ?>" alt="Photo"
                             style="width:72px;height:72px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;cursor:zoom-in"
                             onerror="this.closest('a').style.display='none'">
                    </a>
                    <?php endif; ?>

                    <!-- Text / meta -->
                    <div class="flex-grow-1 min-width-0">
                        <?php if ($m->body): ?>
                        <div class="mb-1"><?= htmlspecialchars(mb_substr($m->body, 0, 120)) ?><?= mb_strlen($m->body) > 120 ? '…' : '' ?></div>
                        <?php endif; ?>

                        <?php if ($m->quote_number): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1" style="font-size:.72rem">
                            <i class="bi bi-file-earmark-text me-1"></i><?= htmlspecialchars($m->quote_number) ?>
                        </span>
                        <?php endif; ?>

                        <?php if (!empty($m->image_path) && !$m->body): ?>
                        <span class="text-muted" style="font-size:.78rem"><i class="bi bi-image me-1"></i>Photo</span>
                        <?php endif; ?>

                        <div class="text-muted mt-1" style="font-size:.72rem">
                            Thread #<?= $m->conversation_id ?> &nbsp;·&nbsp;
                            <?= date('d M Y H:i', strtotime($m->created_at)) ?>
                        </div>
                    </div>

                    <?php if ($is_admin): ?>
                    <button class="btn btn-sm btn-link text-danger p-0 ms-auto flex-shrink-0 delete-msg-btn"
                            data-id="<?= $m->id ?>" title="Delete message"
                            style="line-height:1;opacity:.55" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=.55">
                        <i class="bi bi-trash3"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Image preview modal -->
<div class="modal fade" id="imgPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                        data-bs-dismiss="modal" aria-label="Close" style="z-index:1"></button>
                <img id="imgPreviewSrc" src="" alt="Preview"
                     style="max-width:100%;max-height:90vh;border-radius:8px;object-fit:contain">
            </div>
        </div>
    </div>
</div>

<script>
window.applyCustom = function() {
    var s = document.getElementById('cs').value;
    var e = document.getElementById('ce').value;
    if (s && e) window.location.href = '<?= base_url('reports/chats/custom/') ?>' + s + '/' + e;
};

document.addEventListener('click', function(e) {
    var trigger = e.target.closest('.img-preview-trigger');
    if (trigger) {
        e.preventDefault();
        document.getElementById('imgPreviewSrc').src = trigger.dataset.src;
        new bootstrap.Modal(document.getElementById('imgPreviewModal')).show();
        return;
    }

    var delBtn = e.target.closest('.delete-msg-btn');
    if (!delBtn) return;
    if (!confirm('Delete this message? This cannot be undone.')) return;
    var id  = delBtn.dataset.id;
    var row = delBtn.closest('[data-msg-id]');
    fetch('<?= base_url('reports/chats/delete_message/') ?>' + id, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            row.style.transition = 'opacity .25s';
            row.style.opacity = '0';
            setTimeout(function() { row.remove(); }, 260);
        } else {
            alert('Could not delete message.');
        }
    })
    .catch(function() { alert('Request failed.'); });
});
</script>
