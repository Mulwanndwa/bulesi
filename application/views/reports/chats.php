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
        ['val' => (int)($kpis->total_messages       ?? 0), 'lbl' => 'Total Messages',     'sub' => 'sent in period',        'icon' => 'chat-fill',        'color' => '#0d6efd'],
        ['val' => (int)($kpis->total_conversations  ?? 0), 'lbl' => 'Active Threads',     'sub' => 'conversations used',    'icon' => 'chat-dots-fill',   'color' => '#6f42c1'],
        ['val' => (int)($kpis->active_users         ?? 0), 'lbl' => 'Active Users',       'sub' => 'unique senders',        'icon' => 'people-fill',      'color' => '#198754'],
        ['val' => (int)($kpis->image_messages       ?? 0), 'lbl' => 'Images Shared',      'sub' => 'photo messages',        'icon' => 'image-fill',       'color' => '#fd7e14'],
        ['val' => (int)($kpis->quote_messages       ?? 0), 'lbl' => 'Quotes in Chat',     'sub' => 'messages with quote',   'icon' => 'file-earmark-text','color' => '#e94560'],
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
<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person-badge-fill me-2 text-muted"></i>Most Active Users</div>
            <div class="card-body p-0">
                <?php if (empty($by_user)): ?>
                <p class="text-muted text-center py-4">No messages in this period.</p>
                <?php else:
                $max = max(array_map(fn($u) => (int)$u->message_count, $by_user));
                ?>
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
                                <div class="fw-semibold"><?= htmlspecialchars(trim($u->full_name) ?: $u->username) ?></div>
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Recent Messages ─────────────────────────────────────────────── -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2 text-muted"></i>Recent Messages</span>
                <span class="badge bg-secondary-subtle text-secondary border"><?= count($recent) ?></span>
            </div>
            <div class="card-body p-0" style="max-height:420px;overflow-y:auto">
                <?php if (empty($recent)): ?>
                <p class="text-muted text-center py-4">No messages in this period.</p>
                <?php else: ?>
                <table class="table table-hover mb-0" style="font-size:.83rem">
                    <thead><tr>
                        <th>Sender</th>
                        <th>Message</th>
                        <th>Thread</th>
                        <th>Time</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($recent as $m): ?>
                        <tr>
                            <td class="fw-semibold text-nowrap">
                                <?= htmlspecialchars(trim($m->sender_full_name) ?: $m->sender_username) ?>
                            </td>
                            <td>
                                <?php if (!empty($m->image_path)): ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle me-1">
                                    <i class="bi bi-image"></i> Photo
                                </span>
                                <?php endif; ?>
                                <?php if ($m->quote_number): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">
                                    <i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars($m->quote_number) ?>
                                </span>
                                <?php endif; ?>
                                <?php if ($m->body): ?>
                                <span class="text-muted"><?= htmlspecialchars(mb_substr($m->body, 0, 60)) ?><?= mb_strlen($m->body) > 60 ? '…' : '' ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted">#<?= $m->conversation_id ?></td>
                            <td class="text-muted text-nowrap"><?= date('d M H:i', strtotime($m->created_at)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
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
</script>
